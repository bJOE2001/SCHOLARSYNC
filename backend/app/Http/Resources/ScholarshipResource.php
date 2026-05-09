<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScholarshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'scholarshipName' => $this->scholarship_name,
            'description' => $this->description,
            'eligibilityRequirements' => $this->eligibility_requirements,
            'requiredDocuments' => $this->required_documents,
            'deadline' => $this->deadline?->format('F j, Y'),
            'deadlineIso' => $this->deadline?->toDateString(),
            'availableSlots' => (string) $this->available_slots,
            'announcementDetails' => $this->announcement_details,
            'status' => $this->status,
            'scholarshipType' => $this->scholarship_type,
            'academicYear' => $this->academic_year,
            'semester' => $this->semester,
            'minimumGpa' => $this->minimum_gpa,
            'yearLevelAllowed' => $this->year_level_allowed,
            'programAllowed' => $this->program_allowed,
            'contactPerson' => $this->contact_person,
            'datePosted' => $this->date_posted?->format('F j, Y') ?? ($this->status === 'Draft' ? 'Draft' : null),
            'datePostedIso' => $this->date_posted?->toDateString(),
        ];
    }
}
