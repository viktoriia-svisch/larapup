<?php
namespace App\Rules;
use App\Models\Semester;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
class CheckDeadline implements Rule
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        $sem = Semester::with('faculty_semester')
            ->whereHas('faculty_semester', function (Builder $builder){
                $builder->where('faculty_id', $this->request->get('faculty_id'))
                    ->where('semester_id', $this->request->get('semester_id'));
            })
            ->where('start_date', '<=', $value)
            ->where('end_date', '>=', $value)
            ->first();
        return $sem != null;
    }
    public function message()
    {
        return 'The date range of semester for deadline of this faculty is incorrect .';
    }
}
