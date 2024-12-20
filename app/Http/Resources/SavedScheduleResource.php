<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SavedScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'term_id' => $this->term_id,
            'name' => $this->name,
            'description' => $this->description,
            'is_public' => $this->is_public,
            'color_scheme' => $this->color_scheme,
            'share_token' => $this->when($this->share_token, $this->share_token),
            'user' => new UserResource($this->whenLoaded('user')),
            'term' => new TermResource($this->whenLoaded('term')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
