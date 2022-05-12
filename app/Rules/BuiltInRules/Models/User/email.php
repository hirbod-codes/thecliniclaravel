<?php

return [
    'email' => ['required', 'string', 'email:rfc,dns', 'unique:users,email'],
    'email_optional' => ['string', 'email:rfc,dns', 'unique:users,email'],
    'email_not_unique_not_required' => ['string', 'email:rfc,dns'],
];
