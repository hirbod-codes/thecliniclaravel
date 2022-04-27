<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }}</title>
    <link rel="stylesheet" href="/css/base.css">
</head>

<body>
    <div class="parent_box" id="parent_box">
        <div class="box" id="box">
            <div class="message">Please enter your Email or Phonenumber:</div>

            <input class="input" type="email" name="email" id="email" oninput="emailInput(this.value)" title="email" placeholder="email@example.com" value="">
            <input class="input" type="text" name="phonenumber" id="phonenumber" oninput="phonenumberInput(this.value)" title="phonenumber" placeholder="09#########" value="">

            <button class="button" id="button" type="button" onclick="send()">Send</button>
        </div>
    </div>

</body>

<script src="/js/base.js"></script>
<script src="/js/forgot-password.js"></script>

</html>
