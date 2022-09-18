<?php
namespace App\Rules;
use App\Models\FacultySemester;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
class ArticleUploadCondition implements Rule
{
    private $typeInteraction;
    public function __construct($type = "update")
    {
        $this->typeInteraction = $type;
    }
    public function passes($attribute, $value)
    {
        $findArticle = FacultySemester::with("semester")
            ->where('id', $value)
        ->whereHas("faculty_semester_student", function ($query) {
            $query->where("student_id", Auth::guard(STUDENT_GUARD)->user()->id);
        });
        if ($this->typeInteraction == "update")
            $findArticle->where('second_deadline', ">=", Carbon::now()->toDateTimeString());
        elseif($this->typeInteraction == "new")
            $findArticle->where('first_deadline', ">=", Carbon::now()->toDateTimeString());
        return $findArticle->first() != null;
    }
    public function message()
    {
        return 'The submission was overdue!';
    }
}
