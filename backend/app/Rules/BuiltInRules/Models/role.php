<?php

use App\Models\RoleName;
use Illuminate\Validation\Rule;

return [
    'role' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', Rule::in(RoleName::query()->get(['name'])->toArray())],
    'roleName' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', 'exists:' . (new RoleName)->getTable() . ',name'],
    'roleName_optional' => ['string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', 'exists:' . (new RoleName)->getTable() . ',name'],
    'roleName_unique' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', 'unique:' . (new RoleName)->getTable() . ',name'],
];
