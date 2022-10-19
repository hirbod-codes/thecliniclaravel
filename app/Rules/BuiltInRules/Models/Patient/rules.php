<?php

return [
    'age' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/age.php'))['age'],
    'state' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/state.php'))['state'],
    'city' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/city.php'))['city'],
    'address' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/address.php'))['address_optional'],
];
