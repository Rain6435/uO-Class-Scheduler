<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SectionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'section_id' => $this->section_id,
            'course_code' => $this->course_code,
            'term_id' => $this->term_id,
            'type' => $this->type,
            'status' => $this->status,
            'course' => new CourseResource($this->whenLoaded('course')),
            'term' => new TermResource($this->whenLoaded('term')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'professors' => ProfessorResource::collection($this->whenLoaded('professors')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
