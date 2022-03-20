<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateSemester extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
              'name' => 'required|min:2|bail',
              'start_date' => 'required|after:now|date|bail',
              'end_date'=>'required|after:start_date|date|bail'
        ];
    }
    public function messages()
    {
        return [
            'name.required' =>  'Please input Semester name',
            'name.min' => 'Semester name must contain at least 2 characters',
            'start_date.required' => 'Please input start date',
            'start_date.after' => 'Start date must after today?',
            'end_date.required' => 'Please input end date',
            'end_date.after' => 'End date must after start date?',
        ];
    }
}
