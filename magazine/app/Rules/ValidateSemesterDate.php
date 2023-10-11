<?php
namespace App\Rules;
use App\Models\Semester;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
class ValidateSemesterDate implements Rule
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        $semester_id = $this->request->get("semester_id");
        $sem = Semester::with('faculty_semester')
            ->whereKeyNot($semester_id)
            ->where('start_date', '<=', $value)
            ->where('end_date', '>=', $value)
            ->first();
        $semDate = Semester::with("faculty_semester")
            ->where("id", $semester_id)
            ->where("start_date", ">", Carbon::now()->toDateTimeString())
            ->first();
        return !$sem && $semDate;
    }
    public function message()
    {
        return 'The semester duration is invalid. Either the start/end date is invalid or the semester was already passed';
    }
}
