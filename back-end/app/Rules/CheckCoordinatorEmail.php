<?php

namespace App\Rules;

use App\Models\Coordinator;
use Illuminate\Contracts\Validation\Rule;

class CheckCoordinatorEmail implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $coordinator = Coordinator::where('email', $value)->first();
        return $coordinator == null;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email had existed.';
    }
}
