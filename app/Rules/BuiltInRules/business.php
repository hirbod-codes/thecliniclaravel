<?php

use App\Models\Business;

return [
    'businessNames' => ['required', 'string', 'regex:/\A[a-zA-Z0-9_\/-]+\z/', 'exists:' . (new Business)->getTable() . ',name'],
];
