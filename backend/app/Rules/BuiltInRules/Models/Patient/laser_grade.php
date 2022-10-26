<?php

return [
    'laser_grade' => ['required', 'string', 'regex:/\A[0-9]{3}\/[0-9]{3}\z/'],
    'laser_grade_optional' => ['string', 'regex:/\A[0-9]{3}\/[0-9]{3}\z/'],
];
