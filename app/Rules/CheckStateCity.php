<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CheckStateCity implements Rule
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
        foreach (json_decode(Storage::disk('public')->get('cities.json'), true) as $city) {
            if ($city['name'] !== $value) {
                continue;
            }

            $province_id = $city['province_id'];
        }

        $found = false;
        foreach (json_decode(Storage::disk('public')->get('states.json'), true) as $state) {
            if ($state['id'] !== $province_id) {
                continue;
            }

            $found = true;
            break;
        }

        return $found;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans_choice('validation.custom.city.check_state_city', 0);
    }
}
