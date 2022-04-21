<?php

return [
    'avatar' => ['required', 'file', 'file|max:700', 'mimes:jpg,jpeg,png'],
    'avatar_optional' => ['nullable', 'file', 'file|max:700', 'mimes:jpg,jpeg,png']
];
