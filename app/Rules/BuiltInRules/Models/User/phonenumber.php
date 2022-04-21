<?php

return [
    'phonenumber' => ['required', 'string', 'numeric', 'regex:/\A[0-9]{11}\z/', 'unique:users,phonenumber'],
    'phonenumber_optional' => ['string', 'numeric', 'regex:/\A[0-9]{11}\z/', 'unique:users,phonenumber']
];
