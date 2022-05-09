<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | {{ trans_choice('auth.Verify-Email', 0) }}</title>
    <link rel="stylesheet" href="/backend/css/base.css">
</head>

<body>
    <div class="parent_box" id="parent_box">
        <div class="box" id="box">
            <div class="message">{{ trans_choice('auth.Verify-Email-Request-Message', 0) }}</div>

            <button type="button" id="send-button" class="button" onclick="sendEmailVeificationMessage()">{{ trans_choice('auth.Verify-Email-Send-Message', 0) }}</button>
        </div>
    </div>
</body>

<div style="display: none;position: absolute;" id="sending...">{{ trans_choice('auth.sending', 0) }}</div>

<script src="/backend/js/base.js"></script>
<script src="/backend/js/verify-email.js"></script>

</html>
