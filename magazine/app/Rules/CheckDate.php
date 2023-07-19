<?php
namespace App\Rules;
use App\Models\Semester;
use Illuminate\Contracts\Validation\Rule;
class CheckDate implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        $sem = Semester:: where('end_date', '>=', $value)->first();
        return $sem == null;
    }
    public function message()
    {
        return __('validation.semester_startDateError');
    }
}
