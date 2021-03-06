<?php
namespace App\Http\Requests;
use App\Rules\CheckCoordinatorEmail;
use Illuminate\Foundation\Http\FormRequest;
class CreateCoordinator extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    public function rules()
    {
        return [
            'email' => ['required', 'email', new CheckCoordinatorEmail(), 'bail'],
            'password' => 'required|min:3|bail',
            'first_name' => 'required|min:2|bail',
            'last_name' => 'required|min:2|bail',
        ];
    }
}
