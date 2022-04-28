<?php

$usernameRegex = '/\A[a-zA-Z0-9]{1}[a-zA-Z0-9_.-]{3,}\z/';

return [
    'username' => ['required', 'string', 'min:4', 'regex:' . $usernameRegex, 'unique:users,username'],
    'username_not_unique' => ['required', 'string', 'min:4', 'regex:' . $usernameRegex],
    'username_optional' => ['nullable', 'string', 'min:4', 'regex:' . $usernameRegex, 'unique:users,username'],
    'username_not_unique_not_required_exists' => ['required', 'string', 'min:4', 'regex:' . $usernameRegex, 'exists:users,username'],
];
