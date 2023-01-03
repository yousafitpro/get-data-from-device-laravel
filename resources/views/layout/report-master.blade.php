<!DOCTYPE html>
<html lang="en" class="loading">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>{{$company_name}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{asset('app-assets/vendors/js/core/jquery-3.2.1.min.js')}}" type="text/javascript"></script>
    <link
        href="https://fonts.googleapis.com/css?family=Rubik:300,400,500,700,900|Montserrat:300,400,500,600,700,800,900"
        rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/fonts/font-awesome/css/font-awesome.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/prism.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/chartist.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/app.css')}}">
    <style>
        .pt-25 {
            padding-top: 25px !important;
        }

        .w-300 {
            width: 300px !important;
        }

        .w-200 {
            width: 200px !important;
        }

        .w-150 {
            width: 150px !important;
        }

        .w-100 {
            width: 100px !important;
        }

        .pb-0 {
            margin-bottom: 0px !important;
        }

        thead {
            background-color: lightgrey;
        }

        body {
            background-color: white;
        }
    </style>
    @yield('css')
</head>
<body>
<div class="px-3">

    @yield('content')
</div>
<script src="{{asset('app-assets/vendors/js/core/bootstrap.min.js')}}" type="text/javascript"></script>

<script src="{{asset('app-assets/js/my-custom.js')}}" type="text/javascript"></script>
@include('layout.includes.toast')
@yield('js')
</body>
</html>
