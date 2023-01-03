@extends('auth.layout')
@section('content')
    <body class="login">
    <div class="wrapper wrapper-login">
        <form action="{{url('login')}}" method="post" class="web-form">
            @csrf
            @include('includes.form-errors')
        <div class="container container-login animated fadeIn">
            <div style="width: 100%" class="myFlex">
                <img src="{{$business->headerLogo()}}" style="width: 80px" alt="">
            </div>
<br><br>
{{--            <h3 class="text-center" style="margin-top: 6px">{{config('app.name')}}</h3>--}}
            <div class="login-form">
                <div class="form-group form-floating-label">
                    <input id="username" name="email" type="text" class="form-control input-border-bottom" required>
                    <label for="username" class="placeholder">Username</label>
                </div>
                <div class="form-group form-floating-label">
                    <input id="password" name="password" type="password" class="form-control input-border-bottom" required>
                    <label for="password" class="placeholder">Password</label>
                    <div class="show-password">
                        <i class="flaticon-interface"></i>
                    </div>
                </div>
                <div class="row form-sub m-0">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="rememberme">
                        <label class="custom-control-label" for="rememberme">Remember Me</label>
                    </div>

                    <a href="{{route('webAuth.resetEmail') }}" class="link float-right">Forget Password ?</a>
                </div>
                <div class="form-action mb-3">
                    <button type="submit" class="btn btn-primary btn-rounded btn-login">Sign In</button>
                </div>
{{--                <div class="login-account">--}}
{{--                    <span class="msg">Don't have an account yet ?</span>--}}
{{--                    <a href="#" id="show-signup" class="link">Sign Up</a>--}}
{{--                </div>--}}
            </div>
        </div>
        </form>

{{--        <div class="container container-signup animated fadeIn">--}}
{{--            <h3 class="text-center">Sign Up</h3>--}}
{{--            <div class="login-form">--}}
{{--                <div class="form-group form-floating-label">--}}
{{--                    <input  id="fullname" name="fullname" type="text" class="form-control input-border-bottom" required>--}}
{{--                    <label for="fullname" class="placeholder">Fullname</label>--}}
{{--                </div>--}}
{{--                <div class="form-group form-floating-label">--}}
{{--                    <input  id="email" name="email" type="email" class="form-control input-border-bottom" required>--}}
{{--                    <label for="email" class="placeholder">Email</label>--}}
{{--                </div>--}}
{{--                <div class="form-group form-floating-label">--}}
{{--                    <input  id="passwordsignin" name="passwordsignin" type="password" class="form-control input-border-bottom" required>--}}
{{--                    <label for="passwordsignin" class="placeholder">Password</label>--}}
{{--                    <div class="show-password">--}}
{{--                        <i class="flaticon-interface"></i>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="form-group form-floating-label">--}}
{{--                    <input  id="confirmpassword" name="confirmpassword" type="password" class="form-control input-border-bottom" required>--}}
{{--                    <label for="confirmpassword" class="placeholder">Confirm Password</label>--}}
{{--                    <div class="show-password">--}}
{{--                        <i class="flaticon-interface"></i>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="row form-sub m-0">--}}
{{--                    <div class="custom-control custom-checkbox">--}}
{{--                        <input type="checkbox" class="custom-control-input" name="agree" id="agree">--}}
{{--                        <label class="custom-control-label" for="agree">I Agree the terms and conditions.</label>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="form-action">--}}
{{--                    <a href="#" id="show-signin" class="btn btn-danger btn-rounded btn-login mr-3">Cancel</a>--}}
{{--                    <a href="#" class="btn btn-primary btn-rounded btn-login">Sign Up</a>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>

    </body>

@stop
