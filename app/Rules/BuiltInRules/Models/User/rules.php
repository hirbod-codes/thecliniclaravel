<?php

return [
    'phonenumber' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/phonenumber.php'))['phonenumber'],
    'firstname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php'))['firstname'],
    'lastname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php'))['lastname'],
    'username' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/username.php'))['username'],
    'email' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_optional'],
    'password' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/password.php'))['password'],
    'password_confirmation' => ['required', 'string', 'same:password'],
    'gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender'],
    'avatar' => (include(base_path() . '/app/Rules/BuiltInRules/Models/avatar.php'))['avatar_optional'],
];
