<?php
namespace App\Rules;
use App\Helpers\DateTimeHelper;
use App\Models\Student;
use Illuminate\Contracts\Validation\Rule;
class CheckAgeAccount implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        return DateTimeHelper::checkIfPassedYears('-16', $value);
    }
    public function message()
    {
        return __('validation.age_not16');
    }
}
