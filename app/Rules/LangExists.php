<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class LangExists implements Rule
{
    private array $locales;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $locales = [];
        foreach ($dirs = scandir(base_path() . '/lang') as $value) {
            if (in_array($value, ['..', '.'])) {
                continue;
            }

            if (is_dir(base_path() . '/lang/' . $value)) {
                $locales[] = $value;
            }
        }

        $this->locales = $locales;

        if (!is_string($value) || !in_array($value, $locales)) {
            return abort(422, 'The provided locale option must be one of the following: ' . implode(', ', $locales) . '.');
        }

        return in_array($value, $locales);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.in_array', ['attribute' => __('validation.attributes.locale'), 'other' => 'one of the followings: ' . implode(', ', $this->locales) . '.']);
    }
}
