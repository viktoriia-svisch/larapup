<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateFacultySemester extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(COORDINATOR_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'first_deadline' => 'required', 'date_format:d/m/Y',
            'second_deadline'=> 'required', 'date_format:d/m/Y',
        ];
    }
    public function messages()
    {
        return [
        ];
    }
}
