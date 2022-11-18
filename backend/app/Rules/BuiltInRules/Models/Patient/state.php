<?php

use App\Rules\CheckState;

return [
    'state' => ['required', 'string', new CheckState],
    'state_optional' => ['string', new CheckState],
];
