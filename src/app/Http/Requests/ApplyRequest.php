<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'year' => 'required',
            'date' => 'required',
            'work_start' => 'required',
            'work_end' => 'required',
            'break_start' => 'required',
            'break_end' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'year.required' => '該当年度を入力してください',
            'date.required' => '該当の日付を入力してください',
            'work_start.required' => '出勤時刻を入力してください',
            'work_end.required' => '退勤時刻を入力してください',
            'break_start.required' => '休憩入時刻を入力してください',
            'break_end.required' => '休憩戻時刻を入力してください',
        ];
    }
}
