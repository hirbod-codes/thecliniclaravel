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
        foreach ($dirs = scandir(base_path() . '/lang') as $dir) {
            if (in_array($dir, ['..', '.'])) {
                continue;
            }

            if (is_dir(base_path() . '/lang/' . $dir)) {
                $locales[] = $dir;
            }
        }

        $this->locales = $locales;

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
