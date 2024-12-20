<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'day' => $this->day,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'room' => $this->room,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'section' => new SectionResource($this->whenLoaded('section')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
