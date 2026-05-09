<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'applicantName' => $this->applicant_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'program' => $this->program,
            'scholarshipId' => $this->scholarship_id,
            'course' => $this->course,
            'yearLevel' => $this->year_level,
            'gpa' => $this->gpa,
            'address' => $this->address,
            'reason' => $this->reason,
            'dateSubmitted' => $this->date_submitted?->format('F j, Y'),
            'dateSubmittedIso' => $this->date_submitted?->toDateString(),
            'status' => $this->status,
            'remarks' => $this->remarks,
            'documents' => DocumentResource::collection($this->whenLoaded('documents')),
        ];
    }
}
