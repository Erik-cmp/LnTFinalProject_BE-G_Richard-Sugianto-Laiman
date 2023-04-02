<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StartsWith08 implements Rule
{
    public function passes($attribute, $value)
    {
        return substr($value, 0, 2) === '08';
    }

    public function message()
    {
        return 'The :attribute must start with 08.';
    }
}