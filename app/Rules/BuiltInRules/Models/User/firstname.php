<?php

return [
    'firstname' => ['required', 'string', 'min:3', 'regex:/\A[a-zA-Z]{3}[a-zA-Z]*\z/'],
    'firstname_optional' => ['nullable', 'string', 'min:3', 'regex:/\A[a-zA-Z]{3}[a-zA-Z]*\z/']
];
