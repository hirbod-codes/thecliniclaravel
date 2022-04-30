<?php

use App\Rules\CheckState;

return [
    'state' => ['required', 'string', new CheckState],
];
