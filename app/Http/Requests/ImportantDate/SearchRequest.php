<?php

namespace App\Http\Requests\ImportantDate;

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
            'term' => 'sometimes|string|in:winter,summer,fall',
            'year' => 'sometimes|integer|min:2020',
            'category' => 'sometimes|string',
            'description' => 'sometimes|string|min:3',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
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
            'term.in' => 'Term must be one of: winter, summer, fall',
            'year.min' => 'Year must be 2020 or later',
            'description.min' => 'Search query must be at least 3 characters',
            'end_date.after_or_equal' => 'End date must be after or equal to start date',
            'per_page.min' => 'Items per page must be at least 1',
            'per_page.max' => 'Items per page cannot exceed 100',
        ];
    }
}
