<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesRequestInput;
use App\Http\Resources\ScholarshipApplicationResource;
use App\Models\ComplianceRecord;
use App\Models\Scholarship;
use App\Models\ScholarshipApplication;
use App\Models\StudentNotification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ScholarshipApplicationController extends Controller
{
    use NormalizesRequestInput;

    public function index(Request $request): AnonymousResourceCollection
    {
        $applications = ScholarshipApplication::query()
            ->with('documents')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->lower()->toString();

                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(applicant_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(program) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($request->filled('status') && $request->status !== 'All', fn ($query) => $query->where('status', $request->status))
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->latest('date_submitted')
            ->get();

        return ScholarshipApplicationResource::collection($applications);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->validateApplication($request);
        $student = $this->resolveStudent($request);
        $scholarship = $this->resolveScholarship($payload);

        $application = ScholarshipApplication::create([
            ...$payload,
            'user_id' => $payload['user_id'] ?? $student?->id,
            'scholarship_id' => $payload['scholarship_id'] ?? $scholarship?->id,
            'applicant_name' => $payload['applicant_name'] ?? $student?->name,
            'email' => $payload['email'] ?? $student?->email,
            'phone' => $payload['phone'] ?? $student?->phone,
            'program' => $payload['program'] ?? $scholarship?->scholarship_name,
            'course' => $payload['course'] ?? $student?->program,
            'year_level' => $payload['year_level'] ?? $student?->year_level,
            'address' => $payload['address'] ?? $student?->address,
            'date_submitted' => $payload['date_submitted'] ?? now()->toDateString(),
            'status' => $payload['status'] ?? 'Pending',
            'remarks' => $payload['remarks'] ?? 'Application submitted and awaiting initial screening.',
        ]);

        $this->createApplicationNotifications($application);

        return response()->json([
            'message' => 'Application submitted.',
            'data' => new ScholarshipApplicationResource($application->load('documents')),
        ], 201);
    }

    public function show(ScholarshipApplication $application): ScholarshipApplicationResource
    {
        return new ScholarshipApplicationResource($application->load('documents'));
    }

    public function update(Request $request, ScholarshipApplication $application): ScholarshipApplicationResource
    {
        $application->update($this->validateApplication($request, updating: true));

        return new ScholarshipApplicationResource($application->refresh()->load('documents'));
    }

    public function updateStatus(Request $request, ScholarshipApplication $application): ScholarshipApplicationResource
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['Pending', 'Under Review', 'Approved', 'Rejected', 'For Revision'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $application->update($validated);

        $application->refresh();

        if ($application->status === 'Approved') {
            $this->promoteApplicantToScholar($application);
        }

        $this->createStatusNotification($application);

        return new ScholarshipApplicationResource($application->load('documents'));
    }

    public function destroy(ScholarshipApplication $application): JsonResponse
    {
        $application->delete();

        return response()->json([
            'message' => 'Application deleted.',
        ]);
    }

    private function validateApplication(Request $request, bool $updating = false): array
    {
        $payload = $this->normalizedInput($request, [
            'userId' => 'user_id',
            'user_id' => 'user_id',
            'scholarshipId' => 'scholarship_id',
            'scholarship_id' => 'scholarship_id',
            'scholarshipProgram' => 'program',
            'scholarship_program' => 'program',
            'program' => 'program',
            'applicantName' => 'applicant_name',
            'applicant_name' => 'applicant_name',
            'email' => 'email',
            'phone' => 'phone',
            'course' => 'course',
            'yearLevel' => 'year_level',
            'year_level' => 'year_level',
            'gpa' => 'gpa',
            'address' => 'address',
            'reason' => 'reason',
            'dateSubmitted' => 'date_submitted',
            'date_submitted' => 'date_submitted',
            'status' => 'status',
            'remarks' => 'remarks',
        ]);

        $required = $updating ? 'sometimes' : 'required';

        return validator($payload, [
            'user_id' => ['nullable', 'exists:users,id'],
            'scholarship_id' => ['nullable', 'exists:scholarships,id'],
            'program' => [$required, 'string', 'max:255'],
            'applicant_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'course' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'string', 'max:50'],
            'gpa' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'address' => ['nullable', 'string'],
            'reason' => [$required, 'string'],
            'date_submitted' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(['Pending', 'Under Review', 'Approved', 'Rejected', 'For Revision'])],
            'remarks' => ['nullable', 'string'],
        ])->validate();
    }

    private function resolveStudent(Request $request): ?User
    {
        $userId = $request->input('userId', $request->input('user_id'));

        if ($userId) {
            return User::where('role', 'student')->find($userId);
        }

        return User::where('role', 'student')->first();
    }

    private function resolveScholarship(array $payload): ?Scholarship
    {
        if (! empty($payload['scholarship_id'])) {
            return Scholarship::find($payload['scholarship_id']);
        }

        if (! empty($payload['program'])) {
            return Scholarship::where('scholarship_name', $payload['program'])->first();
        }

        return null;
    }

    private function createApplicationNotifications(ScholarshipApplication $application): void
    {
        StudentNotification::create([
            'user_id' => null,
            'audience' => 'admin',
            'title' => 'New scholarship application',
            'message' => "{$application->applicant_name} applied for {$application->program}.",
            'delivered_at' => now(),
        ]);

        if ($application->user_id) {
            StudentNotification::create([
                'user_id' => $application->user_id,
                'audience' => 'student',
                'title' => 'Application submitted',
                'message' => "Your application for {$application->program} was submitted successfully.",
                'delivered_at' => now(),
            ]);
        }
    }

    private function createStatusNotification(ScholarshipApplication $application): void
    {
        if (! $application->user_id) {
            return;
        }

        $titles = [
            'Under Review' => 'Application under review',
            'Approved' => 'Application approved',
            'Rejected' => 'Application rejected',
            'For Revision' => 'Application needs revision',
            'Pending' => 'Application status updated',
        ];

        $messages = [
            'Under Review' => "Your application for {$application->program} is now under review.",
            'Approved' => "Your application for {$application->program} has been approved.",
            'Rejected' => "Your application for {$application->program} has been rejected.",
            'For Revision' => "Your application for {$application->program} needs revision. Please check the remarks.",
            'Pending' => "Your application for {$application->program} is pending review.",
        ];

        StudentNotification::create([
            'user_id' => $application->user_id,
            'audience' => 'student',
            'title' => $titles[$application->status] ?? 'Application status updated',
            'message' => $messages[$application->status] ?? "Your application for {$application->program} was updated.",
            'delivered_at' => now(),
        ]);
    }

    private function promoteApplicantToScholar(ScholarshipApplication $application): void
    {
        ComplianceRecord::updateOrCreate(
            ['user_id' => $application->user_id],
            [
                'scholar_name' => $application->applicant_name,
                'gpa' => $application->gpa ?? 0,
                'compliance_score' => 100,
                'compliance_status' => 'Compliant',
                'risk_level' => 'Low',
                'gpa_trend' => 'New Scholar',
                'risk_score' => 0,
                'forecast_label' => 'Low Risk',
            ]
        );
    }
}
