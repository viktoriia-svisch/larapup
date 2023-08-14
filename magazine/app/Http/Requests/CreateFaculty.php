<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class CreateFaculty extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
         return [
               'name' => 'required|min:2|bail',
         ];
    }
    public function messages()
    {
         return [
             'name.required' =>  'Please input Faculty name',
             'name.min' => 'Faculty name must contain at least 2 characters',
         ];
    }
}
