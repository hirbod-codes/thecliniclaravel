<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | {{ trans_choice('auth.Register', 0) }}</title>
    <link rel="stylesheet" href="/backend/css/base.css">
</head>

<body>
    <div class="parent_box" id="parent_box">
        <div class="box" id="box">
            <div class="message">{{ trans_choice('auth.fill_registration_form', 0) }}</div>

            <input type="text" name="firstname" class="input" id="firstname" placeholder="{{ trans_choice('validation.attributes.firstname', 0) }}" required minlength="3" oninput="firstname(this.value)">
            <input type="text" name="lastname" class="input" id="lastname" placeholder="{{ trans_choice('validation.attributes.lastname', 0) }}" required minlength="3" oninput="lastname(this.value)">
            <input type="text" name="username" class="input" id="username" placeholder="{{ trans_choice('validation.attributes.username', 0) }}" required minlength="4" oninput="username(this.value)">
            <input type="text" name="email" class="input" id="email" placeholder="{{ trans_choice('validation.attributes.email_optional', 0) }}">
            <input type="text" name="phonenumber" class="input" id="phonenumber" placeholder="{{ trans_choice('validation.attributes.phonenumber_placeholder', 0) }}" required="11" disabled>

            <button type="button" class="button" onclick="phonenumberReverify()" style="width:85%;margin:0;margin-bottom:1em;height:2em;">{{ trans_choice('auth.phonenumber_verification_resend', 0) }}</button>

            <input type="password" name="password" class="input" id="password" placeholder="{{ trans_choice('validation.attributes.password', 0) }}" required minlength="8" oninput="password(this.value)">
            <input type="password" name="password_confirmation" class="input" id="password_confirmation" minlength="8" placeholder="{{ trans_choice('validation.attributes.password_confirmation', 0) }}" required oninput="password_confirmation(this.value)">
            <input type="number" name="age" class="input" id="age" placeholder="{{ trans_choice('validation.attributes.age', 0) }}" required oninput="age(this.value)">

            <select title="gender" class="input" name="gender" id="gender" oninput="gender(this.value)" required style="width: 85%;">
                <option id="gender-label" value="">{{ trans_choice('validation.attributes.gender', 0) }}</option>
            </select>

            <select title="state" class="input" name="state" id="state" oninput="state(this.value)" required style="width: 85%;">
                <option id="state-label" value="">{{ trans_choice('validation.attributes.state', 0) }}</option>
            </select>
            <select title="city" class="input" name="city" id="city" disabled oninput="city(this.value)" required style="width: 85%;">
                <option id="city-label" value="">{{ trans_choice('validation.attributes.city', 0) }}</option>
            </select>

            <textarea class="input" name="address" id="address" cols="30" rows="10" placeholder="{{ trans_choice('validation.attributes.address_optional', 0) }}" style="padding-bottom: 4em; resize: none;"></textarea>

            <label id="avatar-label" for="avatar" class="input" style="cursor: pointer;">
                <p style="color: #777;">{{ trans_choice('validation.attributes.avatar_label_optional', 0) }}</p>
                <input type="file" name="avatar" id="avatar" class="input" capture="user" placeholder="{{ trans_choice('validation.attributes.avatar', 0) }}" style="width:85%;height: auto;display: none;" accept="image/png, image/jpg, image/jpeg" oninput="avatar(this.value)">
            </label>

            <div id="img" style="width: 80%;height: 14em;"></div>

            <div class="message" style="margin: 0;height: auto;">_________________________________</div>

            <button type="button" class="button" id="button" onclick="register()">{{ trans_choice('auth.Register', 0) }}</button>
        </div>
    </div>
</body>

<div style="display: none;position: absolute;" id="phonenumber_verification_request_message">{{ trans_choice('auth.phonenumber_verification_request_message', 0) }}</div>
<div style="display: none;position: absolute;" id="phonenumber_verification_send_code_request_message">{{ trans_choice('auth.phonenumber_verification_send_code_request_message', 0) }}</div>
<div style="display: none;position: absolute;" id="phonenumber_verification_resend">{{ trans_choice('auth.phonenumber_verification_resend', 0) }}</div>
<div style="display: none;position: absolute;" id="Register">{{ trans_choice('auth.Register', 0) }}</div>
<div style="display: none;position: absolute;" id="Send">{{ trans_choice('auth.Send', 0) }}</div>
<div style="display: none;position: absolute;" id="Send-Code">{{ trans_choice('auth.Send-Code', 0) }}</div>
<div style="display: none;position: absolute;" id="Resend">{{ trans_choice('auth.Resend', 0) }}</div>
<div style="display: none;position: absolute;" id="Close">{{ trans_choice('auth.Close', 0) }}</div>

<script src="/backend/js/base.js"></script>
<script src="/backend/js/register.js"></script>
<script src="/backend/js/phonenumber-verification.js"></script>

</html>
