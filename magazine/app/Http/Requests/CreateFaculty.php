<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class CreateFaculty extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
         return [
               'name' => 'required|regex:/^[a-zA-Z\s]*$/u|min:2|unique:faculties,name|bail',
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
