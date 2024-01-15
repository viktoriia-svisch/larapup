<?php
namespace App\Rules;
use App\Helpers\DateTimeHelper;
use App\Models\Semester;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
class FacultySemesterDate implements Rule
{
    private $semester;
    private $isFirst;
    private $message;
    public function __construct(Request $request, bool $isFirst = true)
    {
        $this->semester = Semester::with("faculty_semester")
            ->find($request->get("semester_id"));
        $this->isFirst = $isFirst;
    }
    public function passes($attribute, $value)
    {
        if ($this->isFirst) {
            if (!DateTimeHelper::time1BeforeTime2($value, $this->semester->start_date)) {
                $this->message = "The first deadline was before the start date of the semester. Please input correctly";
                return false;
            }
        } else {
            if (!DateTimeHelper::time1BeforeTime2($this->semester->end_date, $value)) {
                $this->message = "The second deadline was after the end date of the semester. Please input correctly";
                return false;
            }
        }
        return true;
    }
    public function message()
    {
        return $this->message;
    }
}
