<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'subject_code' => $this->subject_code,
            'title' => $this->title,
            'description' => $this->description,
            'credits' => $this->credits,
            'components' => $this->components,
            'prerequisites' => $this->prerequisites->map(fn($prereq) => $prereq->code),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
