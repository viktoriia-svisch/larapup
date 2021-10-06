<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class CreateFaculty extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {


         return [
             
               'semester_id' => 'required',
               'name' => 'required|min:2|bail',
               'first_deadline' => 'required|date|bail',
               'second_deadline'=>'required|after:first_deadline|date|bail'

         ];
    }

    public function messages()
    {
         return [
             'name.required' =>  'Please input Faculty name',
             'name.min' => 'Faculty name must contain at least 2 characters',
             'first_deadline.required' => 'Please input first deadline',
             'first_deadline.after' => 'first date must after today?',
             'second_deadline.required' => 'Please input second date',
             'second_deadline.after' => 'End date must after start date?',
         ];
    }
}
