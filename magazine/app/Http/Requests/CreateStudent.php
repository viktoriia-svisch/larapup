<?php
namespace App\Http\Requests;
use App\Rules\CheckStudentEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class CreateStudent extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'email' => ['required', 'email', new CheckStudentEmail(), 'bail'],
            'password' => 'required|min:3|bail',
            'first_name' => 'required|min:2|bail',
            'last_name' => 'required|min:2|bail',
        ];
    }
}
