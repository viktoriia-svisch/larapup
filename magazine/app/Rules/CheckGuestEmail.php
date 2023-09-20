<?php
namespace App\Rules;
use App\Models\Guest;
use Illuminate\Contracts\Validation\Rule;
class CheckGuestEmail implements Rule
{
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        $guest = Guest::where('email', $value)->first();
        return $guest == null;
    }
    public function message()
    {
        return 'The email had existed.';
    }
}
