<?php

use App\Models\Role;
use Illuminate\Validation\Rule;
use TheClinicDataStructures\DataStructures\User\DSUser;

return [
    'role' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', Rule::in(DSUser::$roles)],
    'roleName' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', 'exists:' . (new Role)->getTable() . ',name'],
    'roleName_unique' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', 'unique:' . (new Role)->getTable() . ',name'],
];
