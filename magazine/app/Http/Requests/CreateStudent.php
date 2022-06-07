<?php
namespace App\Http\Requests;
use App\Rules\CheckAgeAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class CreateStudent extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'email' => ['required', 'email', 'unique:students,email', 'bail'],
            'password' => 'required|min:3|bail',
            'first_name' => 'required|min:2|max:40|bail',
            'last_name' => 'required|min:2|max:80|bail',
            'gender' => 'required|integer',
            'dateOfBirth' => ['required', 'date_format:d/m/Y', new CheckAgeAccount]
        ];
    }
}
