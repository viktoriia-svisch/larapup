<?php
namespace App\Rules;
use App\Models\Student;
use Illuminate\Contracts\Validation\Rule;
class CheckStudentEmail implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        $student = Student::where('email', $value)->first();
        return $student == null;
    }
    public function message()
    {
        return 'The validation error message.';
    }
}
