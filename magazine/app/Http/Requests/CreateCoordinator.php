<?php
namespace App\Http\Requests;
use App\Rules\CheckCoordinatorEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class CreateCoordinator extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'email' => ['required', 'email', new CheckCoordinatorEmail(), 'bail'],
            'password' => 'required|min:3|bail',
            'first_name' => 'required|min:2|max:40|bail',
            'last_name' => 'required|min:2|max:80|bail',
            'gender' => 'required|integer',
            'dateOfBirth' => 'required', 'date_format:d/m/Y'
        ];
    }
}
