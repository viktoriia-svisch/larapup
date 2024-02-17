<?php
namespace App\Rules;
use App\Models\Student;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
class CheckStudentEmailSelf implements Rule
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        $existedRecord = Student::with("faculty_semester_student")
            ->where("email", $value)
            ->whereKeyNot($this->request->get("student_id")
            )->first();
        return $existedRecord == true;
    }
    public function message()
    {
        return 'The email was already existed!';
    }
}
