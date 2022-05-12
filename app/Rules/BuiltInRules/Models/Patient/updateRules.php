<?php

return [
    'age' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/age.php'))['age_optional'],
    'state' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/state.php'))['state_optional'],
    'city' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/city.php'))['city_optional'],
    'address' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/address.php'))['address_optional'],
    'laser_grade' => (include(base_path() . '/app/Rules/BuiltInRules/Models/Patient/laser_grade.php'))['laser_grade_optional'],
];
