<?php
namespace App\Rules;
use App\Models\Coordinator;
use Illuminate\Contracts\Validation\Rule;
class CheckCoordinatorEmail implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        $coordinator = Coordinator::where('email', $value)->first();
        return $coordinator == null;
    }
    public function message()
    {
        return 'The email had existed.';
    }
}
