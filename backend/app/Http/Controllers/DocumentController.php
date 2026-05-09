<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormalizesRequestInput;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Models\ScholarshipApplication;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    use NormalizesRequestInput;

    public function index(Request $request): AnonymousResourceCollection
    {
        $documents = Document::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->lower()->toString();

                $query->where(function ($query) use ($search): void {
                    $query->whereRaw('LOWER(student_name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(document_type) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(file_name) LIKE ?', ["%{$search}%"]);
                });
            })
            ->when(
                $request->filled('status') && $request->status !== 'All',
                fn ($query) => $query->where('verification_status', $request->status)
            )
            ->when($request->filled('user_id'), fn ($query) => $query->where('user_id', $request->integer('user_id')))
            ->latest('upload_date')
            ->get();

        return DocumentResource::collection($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $this->validateDocument($request);
        $application = $this->resolveApplication($payload);
        $student = $this->resolveStudent($payload, $application);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $payload['file_path'] = $file->store('documents', 'public');
            $payload['file_name'] = $payload['file_name'] ?? $file->getClientOriginalName();
            $payload['file_size'] = $this->humanFileSize($file->getSize());
            $payload['file_type'] = Str::upper($file->getClientOriginalExtension());
            $payload['verification_status'] = $payload['verification_status'] ?? 'Uploaded';
        }

        $document = Document::create([
            ...$payload,
            'user_id' => $payload['user_id'] ?? $application?->user_id ?? $student?->id,
            'scholarship_application_id' => $payload['scholarship_application_id'] ?? $application?->id,
            'student_name' => $payload['student_name'] ?? $application?->applicant_name ?? $student?->name,
            'program' => $payload['program'] ?? $application?->program,
            'upload_date' => $payload['upload_date'] ?? now()->toDateString(),
            'verification_status' => $payload['verification_status'] ?? 'Pending',
        ]);

        return response()->json([
            'message' => 'Document saved.',
            'data' => new DocumentResource($document),
        ], 201);
    }

    public function show(Document $document): DocumentResource
    {
        return new DocumentResource($document);
    }

    public function update(Request $request, Document $document): DocumentResource
    {
        $document->update($this->validateDocument($request, updating: true));

        return new DocumentResource($document->refresh());
    }

    public function updateStatus(Request $request, Document $document): DocumentResource
    {
        $validated = $request->validate([
            'verificationStatus' => ['sometimes', Rule::in(['Pending', 'Uploaded', 'Verified', 'For Revision'])],
            'verification_status' => ['sometimes', Rule::in(['Pending', 'Uploaded', 'Verified', 'For Revision'])],
            'remarks' => ['nullable', 'string'],
        ]);

        $document->update([
            'verification_status' => $validated['verificationStatus'] ?? $validated['verification_status'] ?? $document->verification_status,
            'remarks' => $validated['remarks'] ?? $document->remarks,
        ]);

        return new DocumentResource($document->refresh());
    }

    public function destroy(Document $document): JsonResponse
    {
        $document->delete();

        return response()->json([
            'message' => 'Document deleted.',
        ]);
    }

    private function validateDocument(Request $request, bool $updating = false): array
    {
        $payload = $this->normalizedInput($request, [
            'userId' => 'user_id',
            'user_id' => 'user_id',
            'applicationId' => 'scholarship_application_id',
            'application_id' => 'scholarship_application_id',
            'scholarship_application_id' => 'scholarship_application_id',
            'studentName' => 'student_name',
            'student_name' => 'student_name',
            'program' => 'program',
            'documentType' => 'document_type',
            'document_type' => 'document_type',
            'fileName' => 'file_name',
            'file_name' => 'file_name',
            'filePath' => 'file_path',
            'file_path' => 'file_path',
            'fileSize' => 'file_size',
            'file_size' => 'file_size',
            'fileType' => 'file_type',
            'file_type' => 'file_type',
            'uploadDate' => 'upload_date',
            'upload_date' => 'upload_date',
            'verificationStatus' => 'verification_status',
            'verification_status' => 'verification_status',
            'remarks' => 'remarks',
        ]);

        $required = $updating ? 'sometimes' : 'required';
        $validated = validator($payload, [
            'user_id' => ['nullable', 'exists:users,id'],
            'scholarship_application_id' => ['nullable', 'exists:scholarship_applications,id'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'program' => ['nullable', 'string', 'max:255'],
            'document_type' => [$required, 'string', 'max:255'],
            'file_name' => ['nullable', 'string', 'max:255'],
            'file_path' => ['nullable', 'string', 'max:255'],
            'file_size' => ['nullable', 'string', 'max:50'],
            'file_type' => ['nullable', 'string', 'max:50'],
            'upload_date' => ['nullable', 'date'],
            'verification_status' => ['nullable', Rule::in(['Pending', 'Uploaded', 'Verified', 'For Revision'])],
            'remarks' => ['nullable', 'string'],
        ])->validate();

        $request->validate([
            'file' => ['nullable', 'file', 'max:10240'],
        ]);

        return $validated;
    }

    private function resolveApplication(array $payload): ?ScholarshipApplication
    {
        if (empty($payload['scholarship_application_id'])) {
            return null;
        }

        return ScholarshipApplication::find($payload['scholarship_application_id']);
    }

    private function resolveStudent(array $payload, ?ScholarshipApplication $application): ?User
    {
        if (! empty($payload['user_id'])) {
            return User::find($payload['user_id']);
        }

        if ($application?->user_id) {
            return $application->user;
        }

        return User::where('role', 'student')->first();
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' MB';
        }

        return round($bytes / 1024).' KB';
    }
}
