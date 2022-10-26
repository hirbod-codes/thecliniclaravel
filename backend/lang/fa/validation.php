<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'                          => 'مقدار :attribute باید قابل تایید باشد',
    'accepted_if'                       => 'مقدار :attribute باید قابل تایید باشد وقتی :other هست :value .',
    'active_url'                        => 'مقدار :attribute باید یک آدرس معتبر باشد',
    'after'                             => 'مقدار :attribute باید پس از :date باشد',
    'after_or_equal'                    => 'مقدار :attribute باید برابر یا بیشتر از :date باشد',
    'alpha'                             => 'مقدار :attribute تنها باید حروف باشد',
    'alpha_dash'                        => 'مقدار :attribute باید تنها حروف،اعداد و dash باشد',
    'alpha_num'                         => 'مقدار :attribute باید تنها اعداد و حروف باشد',
    'array'                             => 'مقدار :attribute باید آرایه باشد',
    'before'                            => 'مقدار :attribute باید قبل از :date باشد',
    'before_or_equal'                   => 'مقدار :attribute باید کمتر یا برابر :date باشد',
    'between'                           => [
        'numeric'   => 'مقدار :attribute باید بین :min و :max باشد',
        'file'      => 'مقدار :attribute باید بین :min kb و :max kb باشد',
        'string'    => 'مقدار :attribute باید از حرف :min بیشتر و  از حرف :max کمتر باشد',
        'array'     => 'مقدار :attribute باید بین :min و :max باشد',
    ],
    'boolean'                           => 'مقدار :attribute تنها دوحالت true/false می تواند باشد',
    'confirmed'                         => ':attribute همخوانی ندارد',
    'current_password'                  => 'The password is incorrect.',
    'date'                              => ':attribute تاریخ معتبر نیست',
    'date_equals'                       => 'فیلد :attribute باید یک تاریخ برابر با :date باشد. ',
    'date_format'                       => ':attribute الگوی :format ندارد',
    'declined'                          => 'فیلد :attribute باید رد شود. ',
    'declined_if'                       => 'فیلد :attribute باید رد شود وقتی :other هست :value . ',
    'different'                         => 'مقدار :attribute و :other متفاوت باید باشد',
    'digits'                            => 'مقدار :attribute باید ارقام :digits باشد',
    'digits_between'                    => 'ارقام :attribute باید بین :min و :max باشد',
    'dimensions'                        => ':attribute اندازه غیرمعتبر دارد',
    'distinct'                          => ':attribute تکراری می باشد',
    'email'                             => ':attribute نامعتبر است',
    'ends_with'                         => 'فیلد :attribute باید با یکی از این موارد پایان یابد: :values .',
    'enum'                              => 'فیلد :attribute انتخاب شده غیر قابل قبول است.',
    'exists'                            => ':attribute انتخاب شده نامعتبر است',
    'file'                              => ':attribute فایل باید باشد',
    'filled'                            => ':attribute باید مقدار داشته باشد',
    'gt' => [
        'array'     => 'فیلد :attribute باید بیشتر از :value آیتم داشته باشد. ',
        'file'      => 'فیلد :attribute باید بیشتر از :value کیلوبایت باشد. ',
        'numeric'   => 'فیلد :attribute باید بیشتر از :value باشد. ',
        'string'    => 'فیلد :attribute باید بیشتر از :value کاراکتر باشد. ',
    ],
    'gte' => [
        'array' => 'فیلد :attribute باید بیشتر از یا مساوی با :value آیتم داشته باشد. ',
        'file' => 'فیلد :attribute باید بیشتر از یا مساوی با :value کیلوبایت باشد. ',
        'numeric' => 'فیلد :attribute باید بیشتر از یا مساوی با :value باشد. ',
        'string' => 'فیلد :attribute باید بیشتر از یا مساوی با :value کاراکتر باشد.',
    ],
    'image'                             => ':attribute باید تصویر باشد',
    'in'                                => ':attribute انتخاب شده نامعتبر است',
    'in_array'                          => 'مقدار :attribute در :other موجود نیست',
    'integer'                           => ':attribute باید عدد باشد',
    'ip'                                => ':attribute باید آدرس ip معتبر باشد',
    'ipv4'                              => ':attribute باید آدرس IPv4 معتبر باشد',
    'ipv6'                              => ':attribute باید آدرس IPv6 معتبر باشد',
    'json'                              => ':attribute باید متن JSON معتبر باشد',
    'lt' => [
        'array'     => 'فیلد :attribute باید کمتر از :value آیتم داشته باشد. ',
        'file'      => 'فیلد :attribute باید کمتر از :value کیلوبایت باشد. ',
        'numeric'   => 'فیلد :attribute باید کمتر از :value باشد. ',
        'string'    => 'فیلد :attribute باید کمتر از :value کاراکتر باشد. ',
    ],
    'lte' => [
        'array'     => 'فیلد :attribute باید کمتر از یا مساوی با :value آیتم داشته باشد. ',
        'file'      => 'فیلد :attribute باید کمتر از یا مساوی با :value کیلوبایت باشد. ',
        'numeric'   => 'فیلد :attribute باید کمتر از یا مساوی با :value باشد. ',
        'string'    => 'فیلد :attribute باید کمتر از یا مساوی با :value کاراکتر باشد.',
    ],
    'mac_address'                       => 'فیلد :attribute باید یک آدرس MAC صحیح باشد. ',
    'max' => [
        'numeric'   => ':attribute نباید بیشتر از :max باشد',
        'file'      => ':attribute نباید بیشتر از :max kb باشد',
        'string'    => ':attribute نباید بیشتر از :max حرف باشد',
        'array'     => 'در :attribute نباید بیشتر از :max مقدار باشد',
    ],
    'mimes'                             => ':attribute باید نوع :type داشته باشد',
    'mimetypes'                         => ':attribute باید نوع :type داشته باشد',
    'min'                               => [
        'numeric'   => 'مقدار :attribute باید حداقل :min باشد',
        'file'      => ':attribute باید حداقل :min kb باشد',
        'string'    => ':attribute باید حداقل :min حرف باشد',
        'array'     => ':attribute باید حداقل :min مقدار داشته باشد',
    ],
    'multiple_of'                       => 'فیلد :attribute باید مضربی از :value باشد.',
    'not_in'                            => 'مقدار انتخاب شده برای :attribute نامعتبر است',
    'not_regex'                         => 'الگوی :attribute نامعتبر است',
    'numeric'                           => ':attribute باید عدد باشد',
    'password'                          => 'رمز عبور قابل قبول نیست.',
    'present'                           => ':attribute باید مقدار داشته باشد',
    'prohibited'                        => 'فیلد :attribute ممنوع است. ',
    'prohibited_if'                     => 'فیلد :attribute ممنوع است وقتی :other هست :value .',
    'prohibited_unless'                 => 'فیلد :attribute ممنوع است مگر اینکه :other باشد :value . ',
    'prohibits'                         => 'فیلد :attribute حضور :other را منع می کند.',
    'regex'                             => 'الگوی :attribute نامعتبر است',
    'required'                          => ':attribute مورد نیاز است',
    'required_array_keys'               => 'فیلد :attribute باید شامل ورودی های رو به رو باشد: :values .',
    'required_if'                       => ':attribute وقتی که :other مقدار :value دارد ضروری است',
    'required_unless'                   => ':attribute وقتی که :other :value نیست مورد نیاز است',
    'required_with'                     => ':attribute وقتی که :other مقدار دارد مورد نیاز است',
    'required_with_all'                 => ':attribute وقتی که :other مقدار دارد مورد نیاز است',
    'required_without'                  => ':attribute وقتی که :other مقدار ندارد مورد نیاز است',
    'required_without_all'              => ':attribute وقتی که هیچ کدام از :other مقدار ندارند مورد نیاز است',
    'same'                              => ':attribute و :other باید همخوانی داشته باشد',
    'size'                              => [
        'numeric'   => 'اندازه :attribute باید :size باشد',
        'file'      => 'اندازه :attribute باید :size kb باشد',
        'string'    => 'اندازه :attribute باید :size حرف باشد',
        'array'     => 'اندازه :attribute باید :size مقدار باشد',
    ],
    'starts_with'                       => 'فیلد :attribute باید با یکی از این موارد شروع شود: :values .',
    'string'                            => ':attribute باید متنی باشد',
    'timezone'                          => ':attribute باید موقعیت زمانی معتبر باشد',
    'unique'                            => ':attribute تکراری است',
    'uploaded'                          => ':attribute در آپلود موفق نشد',
    'url'                               => ':attribute نامعتبر است',
    'uuid'                              => 'فیلد :attribute باید یک UUID صحیح باشد.',

    'presence_prohibited_with'          => "{0} فیلد :current_field ممنوع است وقتی فیلد :other_field فراهم شده.",
    'prohibited_with_required_with'     => "{0} فیلد :current_field ممنوع است وقتی فیلد :prohibited_field فراهم شده و همچنین ضروری است وقتی :required_field فراهم شده.",

    'parts-packages-business-conflict'  => "{0} ناحیه ها و پکیج ها مجاز نمی باشند در این کسب و کار: :businessName .",
    'parts-packages-requirement'        => "{0} حداقل یکی از فیلد های ناحیه یا پکیج ضروری است.",

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        // 'attribute-name' => [
        //     'rule-name' => 'custom-message',
        // ],
        'username' => [
            'regex' => "مقدار :attribute باید با یک حرف یا عدد شروع شود. "
        ],
        'firstname' => [
            'regex' => "مقدار :attribute باید فقط شامل حروف کوچک و بزرگ باشد. "
        ],
        'lastname' => [
            'regex' => "مقدار :attribute باید فقط شامل حروف کوچک و بزرگ باشد. "
        ],
        'city' => [
            'check_city' => 'شهر فراهم شده وجود ندارد!',
            'check_state_city' => 'این شهر در این استان وجود ندارد.',
        ],
        'state' => [
            'check_state' => 'استان فراهم شده وجود ندارد.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'role-name' => 'نام نقش',
        'locale' => 'تنظیمات محیطی',
        'firstname' => 'نام',
        'lastname' => 'نام خوانوادگی',
        'username' => 'نام کاربری',
        'email' => 'ایمیل',
        'email_optional' => 'ایمیل (اختیاری)',
        'phonenumber' => 'شماره تلفن همراه',
        'phonenumber_placeholder' => '09#########',
        'password' => 'رمز عبور',
        'password_confirmation' => 'تکرار رمز عبور',
        'age' => 'سن',
        'gender' => 'جنسیت',
        'state' => 'استان',
        'city' => 'شهر',
        'address' => 'آدرس',
        'address_optional' => 'آدرس (اختیاری)',
        'avatar' => 'عکس پروفایل',
        'avatar_label' => 'عکس پروفایل: ',
        'avatar_optional' => 'عکس (اختیاری)',
        'avatar_label_optional' => 'عکس (اختیاری): ',
        'weeklyTimePatterns' => 'الگو های زمانی در روز های هفته',
    ],

];
