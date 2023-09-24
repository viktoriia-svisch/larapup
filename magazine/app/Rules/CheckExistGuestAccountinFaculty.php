<?php
namespace App\Rules;
use App\Models\Guest;
use Illuminate\Contracts\Validation\Rule;
class CheckExistGuestAccountinFaculty implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        $guest = Guest::where('faculty_id', $value)->first();
        return $guest == null;
    }
    public function message()
    {
        return 'The faculty already had a guest account.';
    }
}
