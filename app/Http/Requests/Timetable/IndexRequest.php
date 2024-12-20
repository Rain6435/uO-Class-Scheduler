<?php

namespace App\Http\Requests\Timetable;

use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'term' => 'required|string|in:fall,winter,summer',
            'year' => 'required|integer|min:2021',
            'subject' => 'sometimes|string|size:3|uppercase',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
