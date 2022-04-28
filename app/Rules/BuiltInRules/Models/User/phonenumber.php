<?php

return [
    'phonenumber' => ['required', 'string', 'numeric', 'regex:/\A[0-9]{11}\z/', 'unique:users,phonenumber'],
    'phonenumber_not_unique' => ['required', 'string', 'numeric', 'regex:/\A[0-9]{11}\z/'],
    'phonenumber_optional' => ['string', 'numeric', 'regex:/\A[0-9]{11}\z/', 'unique:users,phonenumber'],
    'phonenumber_not_unique_not_required' => ['string', 'numeric', 'regex:/\A[0-9]{11}\z/']
];
