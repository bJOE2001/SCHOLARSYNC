<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'audience' => $this->audience,
            'date' => $this->publish_date?->format('F j, Y'),
            'dateIso' => $this->publish_date?->toDateString(),
            'message' => $this->message,
        ];
    }
}
