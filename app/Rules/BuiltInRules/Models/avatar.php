<?php

return [
    'avatar' => ['required', 'image', 'max:500', 'mimes:jpg,jpeg,png'],
    'avatar_optional' => ['image', 'max:500', 'mimes:jpg,jpeg,png']
];
