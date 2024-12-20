<?php

namespace App\Http\Requests\Course;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'query' => 'required_without_all:subject,level,credits|string|min:3',
            'subject' => 'sometimes|string|size:3|uppercase',
            'level' => 'sometimes|integer|between:1,5',
            'credits' => 'sometimes|integer|between:1,9',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
