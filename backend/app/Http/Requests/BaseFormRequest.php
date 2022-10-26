<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BaseFormRequest extends FormRequest
{
    /**
     * Validate the class instance.
     *
     * @return void
     */
    public function validateResolved()
    {
        $this->prepareForValidation();

        $instance = $this->getValidatorInstance();

        if ($instance->fails()) {
            $this->failedValidation($instance);
        }

        $this->passedValidation();

        if (!$this->passesAuthorization()) {
            $this->failedAuthorization();
        }
    }
}
