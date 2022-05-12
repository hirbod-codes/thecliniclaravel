<?php

use App\Models\BusinessDefault;
use Illuminate\Validation\Rule;

$genders = BusinessDefault::first()->genders;

return [
    'gender' => ['required', 'string', Rule::in($genders)],
    'gender_optional' => ['string', Rule::in($genders)]
];
