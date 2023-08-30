<?php
namespace App\Http\Requests;
use App\Rules\CheckDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class UpdateSemester extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(COORDINATOR_GUARD)->check() || Auth::guard(ADMIN_GUARD)->check();
    }
    public function rules()
    {
        return [
            'start_date' => ['date','bail'],
            'end_date'=>['after:start_date','date','bail']
        ];
    }
    public function messages()
    {
        return [
            'end_date.after' => 'End date must after start date?'
        ];
    }
}
