<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateSemester extends FormRequest
{
    public function authorize()
    {
        return false;
    }
    public function rules()
    {
        return [
            'start_date' => [new checkDate(),'date','bail'],
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
