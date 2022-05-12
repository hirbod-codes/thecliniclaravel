<?php

return [
    'lastname' => ['required', 'string', 'min:3', 'regex:/\A[a-zA-Z]{3}[a-zA-Z]*\z/'],
    'lastname_optional' => ['string', 'min:3', 'regex:/\A[a-zA-Z]{3}[a-zA-Z]*\z/']
];
