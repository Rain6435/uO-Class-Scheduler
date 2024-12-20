<?php

namespace App\Http\Requests\Professor;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public access allowed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => 'required|string|min:2',
            'subject' => 'sometimes|string|size:3|uppercase',
            'rating_min' => 'sometimes|numeric|min:0|max:5',
            'rating_max' => 'sometimes|numeric|min:0|max:5|gte:rating_min',
            'total_ratings_min' => 'sometimes|integer|min:0',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'query.min' => 'Search query must be at least 2 characters',
            'subject.size' => 'Subject code must be exactly 3 characters',
            'subject.uppercase' => 'Subject code must be in uppercase',
            'rating_min.min' => 'Minimum rating must be at least 0',
            'rating_min.max' => 'Maximum rating cannot exceed 5',
            'rating_max.min' => 'Minimum rating must be at least 0',
            'rating_max.max' => 'Maximum rating cannot exceed 5',
            'rating_max.gte' => 'Maximum rating must be greater than or equal to minimum rating',
            'total_ratings_min.min' => 'Minimum total ratings must be at least 0',
        ];
    }
}
