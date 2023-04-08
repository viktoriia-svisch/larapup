<?php
namespace App\Rules;
use Illuminate\Contracts\Validation\Rule;
class PublishDescriptionRule implements Rule
{
    private $message;
    public function __construct()
    {
    }
    public function passes($attribute, $value)
    {
        foreach ($value as $key => $desc) {
            if (strlen($desc) < 3 || strlen($desc) > 450) {
                $this->message = "Description of section " . ($key + 1) . " must have more than or equal 3 characters and less than 450 characters";
                return false;
            }
        }
        return true;
    }
    public function message()
    {
        return $this->message;
    }
}
