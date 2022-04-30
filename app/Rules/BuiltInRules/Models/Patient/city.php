<?php

use App\Rules\CheckCity;
use App\Rules\CheckStateCity;

return [
    'city' => ['required', 'string', new CheckCity, 'bail', new CheckStateCity],
];
