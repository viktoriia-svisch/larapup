<?php
namespace App\Http\Requests;
use App\Rules\CheckDeadline;
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
            'first_deadline' => ['required', 'date_format:Y-m-d', new CheckDeadline($this)],
            'second_deadline'=> ['required', 'date_format:Y-m-d', 'after:first_deadline', new CheckDeadline($this)],
            'description' => 'required',
        ];
    }
    public function messages()
    {
        return [
            'first_deadline.required' => 'Please input the date of first deadline',
            'first_deadline.date_format' => 'Wrong the date format',
            'second_deadline.required' => 'Please input the date of second deadline',
            'second_deadline.after' => 'The date of second deadline must after the first_deadline',
            'description.required' => 'The description must required full fill'
        ];
    }
}
