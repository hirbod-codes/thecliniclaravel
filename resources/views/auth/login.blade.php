<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} | {{ trans_choice('auth.Login', 0) }}</title>
    <link rel="stylesheet" href="/css/base.css">
</head>

<body>
    <div class="parent_box" id="parent_box">
        <div class="box" id="box">
            <div class="message"> Please enter your Email or Username: </div>

            <input class="input" type="email" name="email" id="email" oninput="emailInput(this.value)" title="email" placeholder="email@example.com" value="">
            <input class="input" type="text" name="username" id="username" oninput="usernameInput(this.value)" title="username" placeholder="Username" value="">
            <input class="input" type="password" name="password" id="password" title="password" placeholder="Password" value="">

            <button class="button" id="button" type="button" onclick="submit()">Submit</button>

            <div class="message" style="margin: 0;height: auto;">_________________________________</div>
            <div class="message">
                <a href="http://localhost/forgot-password" style="color:#bbb;">Forget your password?</a>
            </div>
        </div>
    </div>
</body>

<script src="/js/base.js"></script>
<script src="/js/login.js"></script>

</html>
