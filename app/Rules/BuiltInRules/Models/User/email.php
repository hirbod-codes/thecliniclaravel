<?php

use App\Models\User;

return [
    'email' => ['required', 'string', 'email:rfc,dns', 'unique:' . (new User)->getTable() . ',email'],
    'email_exists' => ['required', 'string', 'email:rfc,dns', 'exists:' . (new User)->getTable() . ',email'],
    'email_optional' => ['string', 'email:rfc,dns', 'unique:' . (new User)->getTable() . ',email'],
    'email_not_unique_not_required' => ['string', 'email:rfc,dns'],
];
