<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class ValidateRoleSpecificInformation implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    private string $message;

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
        $rules = $this->getRules($value);
        $rules[array_key_last($rules)][] = new ProhibitExtraFeilds($rules);

        $validator = Validator::make($this->data, $rules);
        if ($validator->fails()) {
            $errors = Arr::flatten($validator->errors());
            $this->message = $errors[array_key_first($errors)];

            return false;
        }

        return true;
    }

    public function getRules(string $roleName): array
    {
        return include(base_path() . '/app/Rules/BuiltInRules/Models/' . ucfirst($roleName) . '/rules.php');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
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
