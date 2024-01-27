<?php
namespace App\Http\Requests;
use App\Rules\FacultySemesterDate;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
class CreateFacultySemester extends FormRequest
{
    public function authorize()
    {
        return Auth::guard(ADMIN_GUARD)->check()
            || (Auth::guard(COORDINATOR_GUARD)->check()
                && Auth::guard(COORDINATOR_GUARD)->user()->type == COORDINATOR_LEVEL["MASTER"]);
    }
    public function rules()
    {
        return [
            'faculty_id' => ['required', 'exists:faculties,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'first_deadline' => ['required', 'date', new FacultySemesterDate($this)],
            'second_deadline' => ['required', 'date', new FacultySemesterDate($this, false), 'after:first_deadline'],
            'description' => ['max:1500']
        ];
    }
}
