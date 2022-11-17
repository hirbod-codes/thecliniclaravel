<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class UniqueFullName implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    protected string $firstnameAddress;

    protected string $lastnameAddress;

    protected string $forbiddenKey;

    public function __construct(string $firstnameAddress, string $lastnameAddress)
    {
        $this->firstnameAddress = $firstnameAddress;
        $this->lastnameAddress = $lastnameAddress;
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
        $firstname = Arr::get($this->data, $this->firstnameAddress);
        $lastname = Arr::get($this->data, $this->lastnameAddress);

        $t = (User::query()->where('firstname', '=', $firstname)->where('lastname', '=', $lastname)->first()) === null;
        return $t;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans_choice('auth.dublicate-fullname',0);
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
