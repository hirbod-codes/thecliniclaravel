<?php

use Illuminate\Validation\Rules\Password;

return [
    'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()],
    'password_string' => ['required', 'string'],
    'password_string_confirmed' => ['required', 'string', 'confirmed'],
];
