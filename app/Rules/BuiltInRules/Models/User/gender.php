<?php

use App\Models\BusinessDefault;
use Illuminate\Validation\Rule;

$genders = BusinessDefault::first()->genders;

return [
    'gender' => ['required', 'integer', 'numeric', Rule::in($genders)],
    'gender_optional' => ['nullable', 'integer', 'numeric', Rule::in($genders)]
];
