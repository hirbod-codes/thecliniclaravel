<?php

namespace App\Rules;

use App\Http\Controllers\AccountsController;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class CheckEnryptedValuesIds implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
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
        foreach ($this->data as $key => $value) {
            if (!Str::endsWith($key, '_encrypted')) {
                continue;
            }

            if (!Str::contains(Crypt::decryptString($value), AccountsController::SEPARATOR)) {
                return false;
            }

            if (!isset($id)) {
                $id = explode(AccountsController::SEPARATOR, Crypt::decryptString($value))[1];
            } elseif ($id !== explode(AccountsController::SEPARATOR, Crypt::decryptString($value))[1]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The provided json data is corrupted.';
    }

    /**
     * Set the data under validation.
     *
     * @param  array  $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }
}
