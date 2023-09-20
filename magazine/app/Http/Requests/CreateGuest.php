<?php
namespace App\Http\Requests;
use App\Rules\CheckExistGuestAccountinFaculty;
use App\Rules\CheckGuestEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class CreateGuest extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'email' => ['required', 'email', new CheckGuestEmail(), 'bail'],
            'password' => 'required|min:6|bail',
            'faculty_id' => ['required', 'integer', new CheckExistGuestAccountinFaculty()]
        ];
    }
}
