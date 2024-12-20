<?php

namespace App\Http\Requests\Timetable;

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
            'term' => 'required|string|in:fall,winter,summer',
            'year' => 'required|integer|min:2021',
            'subject' => 'sometimes|string|size:3|uppercase',
            'course' => 'sometimes|string|regex:/^[A-Z]{3}[0-9]{4}$/',
            'professor' => 'sometimes|string|min:3',
            'type' => 'sometimes|string|in:LEC,DGD,LAB,TUT,SEM,STG,TST',
            'days' => 'sometimes|array',
            'days.*' => 'required|string|in:MON,TUE,WED,THU,FRI,SAT,SUN',
            'status' => 'sometimes|string|in:OPEN,CLOSED',
            'time_start' => 'sometimes|date_format:H:i',
            'time_end' => 'sometimes|date_format:H:i|after:time_start',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
