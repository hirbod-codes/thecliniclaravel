<?php

namespace App\Rules\Orders;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class PartsPackagesRequirement implements Rule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array
     */
    protected $data = [];

    private bool $prohibited = false;

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
        if ($this->data['businessName'] !== 'laser') {
            if (array_search('parts', array_keys($this->data)) !== false || array_search('packages', array_keys($this->data)) !== false) {
                $this->prohibited = true;
                return false;
            }

            return true;
        }

        return (array_key_exists('parts', $this->data) && !empty($this->data['parts'])) ||
            (array_key_exists('packages', $this->data) && !empty($this->data['packages']));
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        if ($this->prohibited) {
            return trans_choice('validation.parts-packages-business-conflict', 0);
        } else {
            return trans_choice('validation.parts-packages-requirement', 0);
        }
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
