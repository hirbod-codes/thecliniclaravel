<?php

use Illuminate\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSUser;

return [
    'role' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', Rule::in(DSUser::$roles)],
];
