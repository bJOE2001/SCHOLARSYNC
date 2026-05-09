<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'studentName' => $this->student_name,
            'program' => $this->program,
            'applicationId' => $this->scholarship_application_id,
            'documentType' => $this->document_type,
            'fileName' => $this->file_name,
            'fileSize' => $this->file_size,
            'fileType' => $this->file_type,
            'fileUrl' => $this->file_path ? Storage::url($this->file_path) : null,
            'uploadDate' => $this->upload_date?->format('F j, Y'),
            'uploadDateIso' => $this->upload_date?->toDateString(),
            'verificationStatus' => $this->verification_status,
            'remarks' => $this->remarks,
        ];
    }
}
