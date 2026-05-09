<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $application = $this->relationLoaded('applications') ? $this->applications->first() : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'program' => $this->program,
            'yearLevel' => $this->year_level,
            'scholarshipProgram' => $application?->program,
            'address' => $this->address,
            'role' => $this->role,
        ];
    }
}
