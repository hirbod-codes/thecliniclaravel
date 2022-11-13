<?php

return [
    'firstname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/firstname.php'))['firstname_optional'],
    'lastname' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/lastname.php'))['lastname_optional'],
    'username' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/username.php'))['username_optional'],
    'email' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/email.php'))['email_optional'],
    'gender' => (include(base_path() . '/app/Rules/BuiltInRules/Models/User/gender.php'))['gender_optional']
];
