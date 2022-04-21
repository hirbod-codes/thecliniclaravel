<?php

return [
    'username' => ['required', 'string', 'min:4', 'regex:/\A[a-zA-Z0-9]{1}[a-zA-Z0-9_.-]{3,}\z/', 'unique:users,username'],
    'username_not_unique' => ['required', 'string', 'min:4', 'regex:/\A[a-zA-Z0-9]{1}[a-zA-Z0-9_.-]{3,}\z/'],
    'username_optional' => ['nullable', 'string', 'min:4', 'regex:/\A[a-zA-Z0-9]{1}[a-zA-Z0-9_.-]{3,}\z/', 'unique:users,username']
];
