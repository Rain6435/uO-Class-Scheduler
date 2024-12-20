<?php

namespace App\Http\Requests\Schedule;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'term' => ['required', 'string', 'in:fall,winter,summer'],
            'year' => ['required', 'integer', 'min:2000', 'max:' . (date('Y') + 1)],
            'name' => ['required', 'string', 'max:255'],
            'section_ids' => ['required', 'array', 'min:1'],
            'section_ids.*' => ['required', 'integer', 'exists:course_sections,id'],
            'is_shared' => ['sometimes', 'boolean']
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
            'section_ids.required' => 'At least one course section must be selected',
            'section_ids.*.exists' => 'One or more selected course sections do not exist',
        ];
    }
}
