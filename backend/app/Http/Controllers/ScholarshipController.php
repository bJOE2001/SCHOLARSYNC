<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesRequestInput;
use App\Http\Resources\ScholarshipResource;
use App\Models\Scholarship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ScholarshipController extends Controller
{
    use NormalizesRequestInput;

    public function index(Request $request): AnonymousResourceCollection
    {
        $scholarships = Scholarship::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->lower()->toString();

                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(scholarship_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(scholarship_type) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when($request->filled('status') && $request->status !== 'All', fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->get();

        return ScholarshipResource::collection($scholarships);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateScholarship($request);

        $scholarship = Scholarship::create($validated);

        return response()->json([
            'message' => 'Scholarship saved.',
            'data' => new ScholarshipResource($scholarship),
        ], 201);
    }

    public function show(Scholarship $scholarship): ScholarshipResource
    {
        return new ScholarshipResource($scholarship);
    }

    public function update(Request $request, Scholarship $scholarship): ScholarshipResource
    {
        $scholarship->update($this->validateScholarship($request, updating: true));

        return new ScholarshipResource($scholarship->refresh());
    }

    public function destroy(Scholarship $scholarship): JsonResponse
    {
        $scholarship->delete();

        return response()->json([
            'message' => 'Scholarship deleted.',
        ]);
    }

    private function validateScholarship(Request $request, bool $updating = false): array
    {
        $payload = $this->normalizedInput($request, [
            'scholarshipName' => 'scholarship_name',
            'scholarship_name' => 'scholarship_name',
            'description' => 'description',
            'eligibilityRequirements' => 'eligibility_requirements',
            'eligibility_requirements' => 'eligibility_requirements',
            'requiredDocuments' => 'required_documents',
            'required_documents' => 'required_documents',
            'deadline' => 'deadline',
            'availableSlots' => 'available_slots',
            'available_slots' => 'available_slots',
            'announcementDetails' => 'announcement_details',
            'announcement_details' => 'announcement_details',
            'status' => 'status',
            'scholarshipType' => 'scholarship_type',
            'scholarship_type' => 'scholarship_type',
            'academicYear' => 'academic_year',
            'academic_year' => 'academic_year',
            'semester' => 'semester',
            'minimumGpa' => 'minimum_gpa',
            'minimum_gpa' => 'minimum_gpa',
            'yearLevelAllowed' => 'year_level_allowed',
            'year_level_allowed' => 'year_level_allowed',
            'programAllowed' => 'program_allowed',
            'program_allowed' => 'program_allowed',
            'contactPerson' => 'contact_person',
            'contact_person' => 'contact_person',
            'datePosted' => 'date_posted',
            'date_posted' => 'date_posted',
        ]);

        $required = $updating ? 'sometimes' : 'required';

        return validator($payload, [
            'scholarship_name' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'eligibility_requirements' => ['nullable', 'string'],
            'required_documents' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'available_slots' => [$required, 'integer', 'min:0'],
            'announcement_details' => ['nullable', 'string'],
            'status' => [$required, Rule::in(['Open', 'Closed', 'Draft'])],
            'scholarship_type' => ['nullable', 'string', 'max:120'],
            'academic_year' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'string', 'max:50'],
            'minimum_gpa' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'year_level_allowed' => ['nullable', 'string', 'max:120'],
            'program_allowed' => ['nullable', 'string', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'date_posted' => ['nullable', 'date'],
        ])->validate();
    }
}
