<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PhonenumberVerificationCode implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    private bool $expired;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $session = session();
        $future = (new \DateTime)->modify('+1 day')->getTimestamp();
        if ((new \DateTime)->getTimestamp() > (new \DateTime)->setTimestamp(strval($session->get('password_reset_verification_timestamp', $future)))->modify('+90 seconds')->getTimestamp()) {
            $this->expired = true;
            return false;
        }

        return ($session->get('verificationCode', 0) === $this->data['code']) &&
            ($session->get('phonenumber', '') === $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->expired) {
            return trans_choice('auth.vierfication_code_expired', 0);
        }

        return trans_choice('auth.phonenumber_verification_failed', 0);
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
