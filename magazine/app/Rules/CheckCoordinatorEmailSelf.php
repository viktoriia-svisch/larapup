<?php
namespace App\Rules;
use App\Models\Coordinator;
use App\Models\Student;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
class CheckCoordinatorEmailSelf implements Rule
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        $existedRecord = Coordinator::with("faculty_semester_coordinator")
            ->where("email", $value)
            ->whereKeyNot($this->request->get("coordinator_id")
            )->first();
        return $existedRecord == true;
    }
    public function message()
    {
        return 'The email was already existed!';
    }
}
