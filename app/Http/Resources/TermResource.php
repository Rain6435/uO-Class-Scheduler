<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TermResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'term' => $this->term,
            'year' => $this->year,
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'important_dates' => ImportantDateResource::collection($this->whenLoaded('importantDates')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}