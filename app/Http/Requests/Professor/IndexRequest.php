<?php

namespace App\Http\Requests\Professor;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
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
            'subject' => 'sometimes|string|size:3|uppercase',
            'term' => 'sometimes|string|in:winter,summer,fall',
            'year' => 'sometimes|integer|min:2020',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'sort_by' => 'sometimes|string|in:last_name,first_name,rating,total_ratings',
            'sort_direction' => 'sometimes|string|in:asc,desc',
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
            'subject.size' => 'Subject code must be exactly 3 characters',
            'subject.uppercase' => 'Subject code must be in uppercase',
            'term.in' => 'Term must be one of: winter, summer, fall',
            'year.min' => 'Year must be 2020 or later',
            'sort_by.in' => 'Sort field must be one of: last_name, first_name, rating, total_ratings',
            'sort_direction.in' => 'Sort direction must be either asc or desc',
        ];
    }
}
