<?php
namespace App\Http\Requests;
use App\Rules\CheckAgeAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateCoordinatorByAdmin extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(STUDENT_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'first_name' => 'required|min:2|max:40|bail',
            'last_name' => 'required|min:2|max:80|bail',
            'gender' => 'required|integer',
            'status' => 'required|integer',
            'dateOfBirth' => ['required', 'date_format:d/m/Y', new CheckAgeAccount]
        ];
    }
    public function messages()
    {
        return [
            'first_name' => 'The First Name must be between 2 and 40 characters',
            'last_name' => 'The Last Name must be between 2 and 80 characters'
        ];
    }
}
