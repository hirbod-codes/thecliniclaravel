<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'User-Not-Authorized' => 'You are not authorized for this action.',

    'fill_registration_form' => "{0} Please fill these fields in order to register as a patient:",

    'access_token_limit' => '{0} Your account already have an access tokens.',
    'admin_conflict' => '{0} User trys to modify an admin, Forbidden.',
    'duplicate_fullname' => '{0} A user with the same full name already exists.',

    'vierfication_code_expired' => "{0} Your verification code is expired, please use another verificatopn code.",

    'phonenumber_verification_mismatch' => "{0} The provided phonenumber mismatches with the verified one.\nPlease verify your phonenumber again.",
    'phonenumber_verification_request_message' => "{0} We need to Verify your Phonenumber, before the registration: ",
    'phonenumber_verification_resend' => "{0} Try another phonenumber to verify.",
    'phonenumber_verification_send_code_request_message' => "{0} Please enter the 6-digit code that you received on your phone.\nIf you haven't received one use another code.",
    'phonenumber_verification_text_message_sms' => "{0} This your private 6-digit verification code: :code .\nFor your account's security don't share this code with anyone.",
    'phonenumber_verification_code_sent' => "{0} You will receive a text message on your cell phone including a 6-digit code.\nPlease send it back to us.",
    'phonenumber_verification_failed' => "{0} The provided code or phonenumber does not match with our records, please use another verificatopn code.",
    'phonenumber_verification_failed_code' => "{0} The provided code does not match with our records, please use another verificatopn code.",
    'phonenumber_not_verification' => "{0} Your phonenumber is not verified, please verify it first.",
    'phonenumber_already_verification' => "{0} Your phonenumber is already verified.",
    'phonenumber_verification_successful' => "{0} Your phonenumber is now verified.",

    'email_verified_sucessfully' => "{0} Your email has verified.",
    'email_verification_code_sent' => "{0} You will receive a text message on your email including a 6-digit code.\nPlease send it back to us.",
    'email_verification_failed' => "{0} The provided code or email does not match with our records, please use another verificatopn code.",

    'password_reset_failed' => '{0} System failed to reset your password.',
    'password' => 'The provided password is incorrect.',
    'password_reset_code_mail_message' => "{0} Here's your your 6-digit verification code: \n:code .",
    'password-reset-successful' => "{0} your password has successfully changed.\nYou will be loged out, Please log in again.",

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'registration_successful' => "{0} Your account successfully registered.",

    'Send' => "{0} Send",
    'Send-Code' => "{0} Send Code",
    'Resend' => "{0} Resend",
    'Close' => "{0} Close",

    'Register' => "{0} Register",
    'Login' => "{0} Login",
    'Forgot-Password' => "{0} Forgot Password",
    'Verify-Email' => "{0} Verify Email",

    'Verify-Email-Request-Message' => 'Please verify your email by clicking on the sent link in your mail box.',
    'Verify-Email-Send-Message' => 'Send verification message to my email',

    'sending' => 'Sending...',
];
