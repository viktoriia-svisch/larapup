<?php
namespace App\Rules;
use App\Student;
use Illuminate\Contracts\Validation\Rule;
class CheckStudentEmail implements Rule
{
    public function passes($attribute, $value)
    {
        $std = Student::where('email', $value)->first();
        return $std == null;
    }
    public function message()
    {
        return 'This email was existed before';
    }
}
