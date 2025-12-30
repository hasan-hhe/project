<!DOCTYPE html>
<html lang="ar">

<head>
    <title>تسجيل الدخول للوحة التحكم</title>
    @include('admin.layouts.head')

    <style>
        body {
            margin: 0;
            color: #6a6f8c;
            /* background: #c8c8c8; */
            background: #1a2035;
            font: 600 16px/18px 'Open Sans', sans-serif;
        }

        *,
        :after,
        :before {
            box-sizing: border-box
        }

        .clearfix:after,
        .clearfix:before {
            content: '';
            display: table
        }

        .clearfix:after {
            clear: both;
            display: block
        }

        a {
            color: inherit;
            text-decoration: none
        }

        .login-wrap {
            width: 100%;
            margin: auto;
            margin-top: 50px;
            max-width: 525px;
            min-height: 670px;
            position: relative;
            background: url(https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSQNJriuleusU4oIvXn53y8NPUEJk-XCVqFEA&s) no-repeat center;
            background-size: 100% 100%;
            box-shadow: 0 12px 15px 0 rgba(0, 0, 0, .24), 0 17px 50px 0 rgba(0, 0, 0, .19);
        }

        .login-html {
            width: 100%;
            height: 100%;
            position: absolute;
            padding: 90px 70px 50px 70px;
            background: rgba(40, 57, 101, .9);
        }

        .login-html .sign-in-htm,
        .login-html .sign-up-htm {
            top: 0;
            right: 0;
            left: 0;
            bottom: 0;
            position: absolute;
            transform: rotateY(180deg);
            backface-visibility: hidden;
            transition: all .4s linear;
        }

        .login-html .sign-in,
        .login-html .sign-up,
        .login-form .group .check {
            display: none;
        }

        .login-html .tab,
        .login-form .group .label,
        .login-form .group .button {
            text-transform: uppercase;
        }

        .login-html .tab {
            font-size: 22px;
            margin-left: 15px;
            padding-bottom: 5px;
            margin: 0 0 10px 15px;
            display: inline-block;
            border-bottom: 2px solid transparent;
        }

        .login-html .sign-in:checked+.tab,
        .login-html .sign-up:checked+.tab {
            color: #fff !important;
            border-color: #1161ee;
        }

        .login-form {
            min-height: 345px;
            position: relative;
            perspective: 1000px;
            transform-style: preserve-3d;
        }

        .login-form .group {
            margin-bottom: 15px;
        }

        .login-form .group .label,
        .login-form .group .input,
        .login-form .group .button {
            width: 100%;
            color: #fff !important;
            display: block;
        }

        .login-form .group .input,
        .login-form .group .button {
            border: none;
            padding: 15px 20px;
            border-radius: 25px;
            background: rgba(255, 255, 255, .1);
        }

        .login-form .group input[data-type="password"] {
            text-security: circle;
            -webkit-text-security: circle;
        }

        .login-form .group .label {
            color: #aaa;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .login-form .group .button {
            background: #1161ee;
        }

        .login-form .group label .icon {
            width: 15px;
            height: 15px;
            border-radius: 2px;
            margin-right: 10px;
            position: relative;
            display: inline-block;
            background: rgba(255, 255, 255, .1);
        }

        .login-form .group label .icon:before,
        .login-form .group label .icon:after {
            content: '';
            width: 10px;
            height: 2px;
            background: #fff;
            position: absolute;
            transition: all .2s ease-in-out 0s;
        }

        .login-form .group label .icon:before {
            right: 8px;
            width: 5px;
            bottom: 5px;
            transform: scale(0) rotate(0);
        }

        .login-form .group label .icon:after {
            top: 6px;
            left: 4px;
            transform: scale(0) rotate(0);
        }

        .login-form .group .check:checked+label {
            color: #fff !important;
        }

        .login-form .group .check:checked+label .icon {
            background: #1161ee;
        }

        .login-form .group .check:checked+label .icon:before {
            transform: scale(1) rotate(45deg);
        }

        .login-form .group .check:checked+label .icon:after {
            transform: scale(1) rotate(-60deg);
        }

        .sign-in-htm {
            transform: rotate(0) !important;
        }

        .hr {
            height: 2px;
            margin: 60px 0 50px 0;
            background: rgba(255, 255, 255, .2);
        }

        .foot-lnk {
            text-align: center;
        }

        .login-title {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            position: relative;
            display: inline-block;
        }

        .login-title::after {
            content: '';
            display: block;
            width: 120px;
            height: 3px;
            background-color: #1161ee;
            margin: 10px auto 0 auto;
            border-radius: 2px;
        }
    </style>
</head>

<body dir="rtl">

    <div class="login-wrap">
        <div class="login-html">
            <div style="text-align: center;">
                <h2 class="login-title">تسجيل الدخول</h2>
            </div>

            <div class="login-form">
                <form class="sign-in-htm" id="form" method="POST" action="{{ route('admin.auth.login.post') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="group">
                        <label for="user" class="label">الرقم</label>
                        <input id="user" type="text" value="{{ old('phone_number') }}" name="phone_number"
                            class="input">

                        @error('phone_number')
                            <small class="form-text text-muted">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="group">
                        <label for="pass" class="label">كلمة المرور</label>
                        <input id="pass" type="password" value="{{ old('password') }}" name="password"
                            class="input" data-type="password">

                        @error('password')
                            <small class="form-text text-muted">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="group">
                        <input id="check" type="checkbox" class="check" name="remeber"
                            @if (old('remember') == true) 'checked' @endif>
                        <label for="check"><span class="icon"></span> تذكرني</label>
                    </div>
                    <div class="group">
                        <input type="submit" class="button" value="تسجيل الدخول">
                    </div>
                    <div class="hr"></div>
                </form>
            </div>
        </div>
    </div>

</body>

</html>
