<?php
namespace App\Rules;
use App\Models\Semester;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
class ValidateSemesterDate implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        $sem = Semester::with('faculty_semester')
            ->where('start_date', '<=', $value)
            ->where('end_date', '>=', $value)
            ->first();
        return $sem == null;
    }
    public function message()
    {
        return 'The validation error message.';
    }
}
