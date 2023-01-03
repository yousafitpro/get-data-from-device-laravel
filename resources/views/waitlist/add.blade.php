@extends('auth.layout')
@section('content')
    <div class="row justify-content-center no-gutters">
        <div class="col-lg-4 col-md-6 col-12 p-30 rounded10 b-2 b-dashed border-info">
            <div class="content-top-agile p-10">
                <a href="#" class="aut-logo">
                    <img src="{{$business->headerLogo()}}" style="width: 150px" alt="">
                </a>
                <h2 class="text-primary" style="color: {{$business->theme_color}} !important;">Wait-List</h2>
                <p class="text-black-50">Welcome</p>

            </div>
            <div class="">
                <form action="{{route('waitlist.save')}}" method="post" class="web-form">
                    @csrf
                    <input hidden name="is_from_server" value="{{request('email',false)?'yes':'no'}}" >
                    @include('includes.form-errors')
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-user"></i></span>
                            </div>
                            <input value="{{request('name',false)?request('name',false):old('name')}}" name="name" type="text"
                                   class="form-control pl-15 bg-transparent plc-black"
                                   placeholder="Full Name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-mobile"></i></span>
                            </div>
                            <input value="{{old('phone')}}" type="tel"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Phone"
                                   required name="phone">
                        </div>
                    </div>
{{--                    <div class="form-group">--}}
{{--                        <div class="input-group mb-3">--}}
{{--                            <div class="input-group-prepend">--}}
{{--                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>--}}
{{--                            </div>--}}
{{--                            <input value="{{old('address')}}" type="text"--}}
{{--                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Address"--}}
{{--                                   required name="address">--}}
{{--                        </div>--}}
{{--                    </div>--}}


                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-email"></i></span>
                            </div>
                            <input value="{{request('email',false)?request('email',false):old('email')}}" type="email"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Email"
                                   required name="email">
                        </div>
                    </div>
                    <label>Captcha Code is :{{$code}}</label>


                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-lock"></i></span>
                            </div>
                            <input
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Captcha Code"
                                   required name="code">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12" hidden>
                            <div class="checkbox">
                                <input type="checkbox" id="basic_checkbox_1" name="terms_agree">
                                <label for="basic_checkbox_1">I agree to the <a href="#"
                                                                                class="text-warning"><b>Terms</b></a></label>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-info mt-10 theme-bg">SUBMIT</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>


            </div>
        </div>
    </div>
    <script>
       function selectPackage(me)
       {
           if (me.target.value==2)
           {
                $("#bname").append(` <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>
                                <input value="{{old('business_name')}}" type="text"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Business Name"
                                       required name="business_name">
                            </div>
                        </div>`)
           }else
           {
               $("#bname").empty();
           }
       }
    </script>
@stop
