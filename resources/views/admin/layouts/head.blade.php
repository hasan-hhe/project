<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title')</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/logo.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/img/logo.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/logo.png') }}">


    <link rel="stylesheet" href="{{ asset('assets/css/fonts.min.css') }}">

    <style>
        /* Tajawal Font - Local */
        @font-face {
            font-family: 'Tajawal';
            src: url('{{ asset('assets/fonts/tajawal/Tajawal-Regular.ttf') }}') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Tajawal';
            src: url('{{ asset('assets/fonts/tajawal/Tajawal-Medium.ttf') }}') format('truetype');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }

        @font-face {
            font-family: 'Tajawal';
            src: url('{{ asset('assets/fonts/tajawal/Tajawal-Bold.ttf') }}') format('truetype');
            font-weight: 600;
            font-style: normal;
            font-display: swap;
        }
    </style>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.rtl.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/plugins.rtl.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.rtl.css') }}" />

    <style>
        .alert,
        .brand,
        .btn-simple,
        .h1,
        .h2,
        .h3,
        .h4,
        .h5,
        .h6,
        .navbar,
        .td-name,
        a,
        body,
        button.close,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        td {
            font-family: 'Tajawal', sans-serif !important;
        }

        .table thead th {
            padding: 0 10px 10px 0 !important;
        }

        table th,
        table td {
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }
    </style>

    <style>
        .custom-select-wrapper {
            position: relative;
            width: 100%;
            font-family: sans-serif;
        }

        .custom-label {
            position: absolute;
            top: 16px;
            right: 20px;
            color: #aaa;
            font-size: 16px;
            pointer-events: none;
            transition: all 0.3s ease;
            font-family: 'Tajawal';
        }

        .custom-select-wrapper.open .custom-label,
        .custom-select-wrapper.active .custom-label {
            top: -10px;
            right: 15px;
            font-size: 12px;
            background-color: #fff;
            padding: 0 5px;
            color: #435dd8;
            z-index: 9;
        }

        .custom-select {
            background-color: #fff;
            color: #2d3750;
            padding: 15px 20px 15px 20px;
            border-radius: 1rem;
            border: 1.5px solid #9e9e9e;
            cursor: pointer;
            position: relative;
            user-select: none;
            font-family: 'Tajawal';
        }

        .custom-select:hover,
        .select-options:hover,
        .select-options:active,
        .custom-select:active {
            outline: none;
            border: 1.5px solid #1a73e8;
        }

        .select-options {
            position: absolute;
            top: 100%;
            right: 0;
            left: 0;
            background: white;
            border-radius: 10px;
            border: 1px solid #9e9e9e;
            display: none;
            flex-direction: column;
            margin-top: 5px;
            z-index: 10;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            font-family: 'Tajawal';
        }

        .select-options div {
            padding: 12px 20px;
            cursor: pointer;
            color: #2d3750;
        }

        .select-options div:hover {
            background-color: #2a2e3c;
            color: #fff;
        }

        .custom-select-wrapper.open .select-options {
            display: flex;
        }

        .custom-select-wrapper.disabled {
            opacity: 0.6;
            pointer-events: none;
            /* يمنع أي كليك */
        }
    </style>

    <style>
        .input-group-animation {
            position: relative;
        }

        .input-new {
            width: 100%;
            border: solid 1.5px #9e9e9e;
            border-radius: 1rem;
            background: none;
            padding: 15px 20px 15px 20px;
            font-size: 1rem;
            color: rgb(0, 0, 0);
            transition: border 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-label {
            position: absolute;
            right: 15px;
            color: #e8e8e8;
            pointer-events: none;
            transform: translateY(1rem);
            transition: 150ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        .input-new:focus,
        .input-new:valid {
            outline: none;
            border: 1.5px solid #1a73e8;
        }

        .input-new:focus~label,
        .input-new:valid~label {
            transform: translateY(-50%) scale(0.8);
            background-color: white;
            padding: 0 .2em;
            color: #2196f3;
        }

        .input-new:disabled~.input-label {
            transform: translateY(-50%) scale(0.8);
            background-color: white;
            padding: 0 .2em;
            color: #aaa;
            cursor: not-allowed;
        }

        .input-new:disabled {
            background-color: #d4dadf2f;
        }

        .input-group .input-new {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />


    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">

    <style>
        #loader {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: radial-gradient(circle farthest-corner at center, rgb(39, 42, 54) 0%, #1a2035 100%);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }

        #loader-new.show {
            opacity: 1;
            visibility: visible;
        }

        #loader.show {
            opacity: .5;
            visibility: visible;
        }

        #loader.hide,
        #loader-new.hide {
            opacity: 0;
            visibility: hidden;
        }

        .loader-new {
            position: absolute;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            top: calc(50% - 150px);
            left: calc(50% - 150px);
            width: 300px;
            height: 300px;
            border-radius: 50%;
            perspective: 800px;
            opacity: 0;
            visibility: hidden;
            z-index: 1000;
        }

        .inner {
            position: absolute;
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }

        .inner.one {
            left: 0%;
            top: 0%;
            animation: rotate-one 1s linear infinite;
            border-bottom: 10px solid #1a2035;
        }

        .inner.two {
            right: 0%;
            top: 0%;
            animation: rotate-two 1s linear infinite;
            border-right: 10px solid #1a2035;
        }

        .inner.three {
            right: 0%;
            bottom: 0%;
            animation: rotate-three 1s linear infinite;
            border-top: 10px solid #1a2035;
        }

        @keyframes rotate-one {
            0% {
                transform: rotateX(35deg) rotateY(-45deg) rotateZ(0deg);
            }

            100% {
                transform: rotateX(35deg) rotateY(-45deg) rotateZ(360deg);
            }
        }

        @keyframes rotate-two {
            0% {
                transform: rotateX(50deg) rotateY(10deg) rotateZ(0deg);
            }

            100% {
                transform: rotateX(50deg) rotateY(10deg) rotateZ(360deg);
            }
        }

        @keyframes rotate-three {
            0% {
                transform: rotateX(35deg) rotateY(55deg) rotateZ(0deg);
            }

            100% {
                transform: rotateX(35deg) rotateY(55deg) rotateZ(360deg);
            }
        }
    </style>


    @stack('styles')
</head>
