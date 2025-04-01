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
            'work_end' => 'required|after:work_start',
            'break_start.*' => 'nullable',
            'break_end.*' => 'nullable',
            // 'break_start.*' => [
            //     'nullable',
            //     'after:work_start',
            //     'before:work_end',
            //     function ($attribute, $value, $fail) {
            //         if (isset($this->work_end) && strtotime($value) >= strtotime($this->work_end)) {
            //             $fail('休憩時間が勤務時間外です');
            //         }
            //         if (isset($this->work_start) && strtotime($value) <= strtotime($this->work_start)) {
            //             $fail('休憩時間が勤務時間外です');
            //         }
            //     },
            // ],
            // 'break_end.*' => [
            //     'nullable',
            //     'after:break_start.*',
            //     'before:work_end',
            //     function ($attribute, $value, $fail) {
            //         if (isset($this->work_end) && strtotime($value) >= strtotime($this->work_end)) {
            //             $fail('休憩時間が勤務時間外です');
            //         }
            //         if (isset($this->work_start) && strtotime($value) <= strtotime($this->work_start)) {
            //             $fail('休憩時間が勤務時間外です');
            //         }
            //     },
            // ],
            'remarks' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'year.required' => '該当年度を入力してください',
            'date.required' => '該当の日付を入力してください',
            'work_start.required' => '出勤時刻を入力してください',
            'work_end.required' => '退勤時刻を入力してください',
            'work_end.after' => '出勤時刻もしくは退勤時刻が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }
}
