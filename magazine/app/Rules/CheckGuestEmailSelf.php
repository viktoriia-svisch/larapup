<?php
namespace App\Rules;
use App\Models\Guest;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\Request;
class CheckGuestEmailSelf implements Rule
{
    private $request;
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    public function passes($attribute, $value)
    {
        $existedRecord = Guest::with("faculty")
            ->where("email", $value)
            ->whereKeyNot($this->request->get("guest_id"))
            ->first();
        return !($existedRecord == true);
    }
    public function message()
    {
        return 'The email was already existed!';
    }
}
