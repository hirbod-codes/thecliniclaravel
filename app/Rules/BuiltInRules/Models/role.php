<?php

use Illuminate\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSUser;

return [
    'role' => ['required', 'string', 'regex:/\A[a-z]+\z/', Rule::in(DSUser::$roles)],
];
