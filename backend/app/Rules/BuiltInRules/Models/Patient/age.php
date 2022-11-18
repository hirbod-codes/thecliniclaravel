<?php

use App\Models\BusinessDefault;

$min_age = BusinessDefault::first()->min_age;

return [
    'age' => ['required', 'integer', 'numeric', 'min:' . strval($min_age)],
    'age_optional' => ['nullable', 'integer', 'numeric']
];
