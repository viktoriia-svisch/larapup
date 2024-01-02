<?php
namespace App\Rules;
use App\Models\Coordinator;
use App\Models\Guest;
use App\Models\Student;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
class AccountStatus implements Rule
{
    private $request;
    private $userModel;
    public function __construct(Request $request, string $userModel)
    {
        $this->request = $request;
        $this->userModel = $userModel;
    }
    public function passes($attribute, $value)
    {
        if ($this->userModel == COORDINATOR_GUARD)
            $user = Coordinator::with("faculty_semester_coordinator")
                ->where('email', $value)->first();
        else if ($this->userModel == STUDENT_GUARD)
            $user = Student::with("faculty_semester_student")
                ->where('email', $value)->first();
        else if ($this->userModel == GUEST_GUARD)
            $user = Guest::with("faculty")
                ->where('email', $value)->first();
        else
            return false;
        if ($user->status == ACCOUNT_DEACTIVATED) {
            return false;
        }
        return true;
    }
    public function message()
    {
        return __("auth.statusDeactivated");
    }
}
