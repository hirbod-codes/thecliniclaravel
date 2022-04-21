<?php

use Illuminate\Validation\Rules\Password;

return [
    'password' => ['required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()]
];
