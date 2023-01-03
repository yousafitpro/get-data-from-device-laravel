@extends('auth.layout')
@section('content')
    <div class="row justify-content-center no-gutters">
        <div class="col-lg-4 col-md-6 col-12 p-30 rounded10 b-2 b-dashed border-info">
            <div class="content-top-agile p-10">
                <a href="#" class="aut-logo">
                    <img src="{{$business->headerLogo()}}" style="width: 150px" alt="">
                </a>
                <h2 class="text-primary" style="color: {{$business->theme_color}} !important;">Get started with Us</h2>
                <p class="text-black-50">Register a new membership</p>
            </div>
            <div class="">
                <form action="{{url('register')}}" method="post" class="web-form">
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
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                            </div>
                            <input id="pac-input" value="{{old('address')}}" type="text"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Address"
                                   required name="address">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                            </div>
                            <input value="{{old('city')}}" type="text"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="City"
                                   required name="city">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                            </div>
                            <input value="{{old('country')}}" type="text"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Country"
                                   required name="country">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                            </div>
                            <input value="{{old('zipcode')}}" type="text"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Zipcode"
                                   required name="zipcode">
                        </div>
                    </div>
                    <div class="form-group" >
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                            </div>

                            <input value="{{old('occupation')}}"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Occupation"
                                   required name="occupation">
                        </div>
                    </div>
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
                    <div class="form-group" >
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                            </div>

                            <input value="{{old('dateOfBirth')}}" type="date"
                                   class="form-control pl-15 bg-transparent plc-black" placeholder="Date of Birth"
                                   required name="dateOfBirth">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-briefcase"></i></span>
                            </div>
                            <select onchange="selectPackage(event)" class="form-control pl-15 bg-transparent plc-black" required name="plan">
                                <option value="" selected hidden disabled>Select Membership</option>
                                @foreach($plans as $plan)
                                    <option @if(old('plan')==$plan->id) selected
                                            @endif value="{{$plan->id}}">{{$plan->title}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="bname">

                    </div>

                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-lock"></i></span>
                            </div>
                            <input type="password" class="form-control pl-15 bg-transparent plc-black"
                                   placeholder="Password" name="password" required>
                        </div>
                            <span>At least 8 characters with upper, lower, number and Special </span>
                    </div>
                    <div class="form-group">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-transparent"><i class="ti-lock"></i></span>
                            </div>
                            <input type="password" class="form-control pl-15 bg-transparent plc-black"
                                   placeholder="Retype Password" required name="password_confirmation">
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
                            <button type="submit" class="btn btn-info mt-10 theme-bg">SIGN UP</button>
                        </div>
                        <!-- /.col -->
                    </div>
                </form>

                <div class="text-center">
                    <p class="mt-15 mb-0">Already have an account? <a href="{{url('login')}}" class="text-info ml-5">
                            Sign In</a></p>
                </div>
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
                        </div>
                         <label>Date of Birth</label>

                          <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('dbaName')}}"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="DBA Name"
                                       required name="dbaName">
                            </div>
                        </div>
                         <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('url')}}"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Website URL"
                                       required name="url">
                            </div>
                        </div>

                            <label>Type Of Business</label>
                          <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>
                                <select name="typeOfBusiness" class="form-control pl-15 bg-transparent plc-black">
                                <option value="Corporation">Corporation</option>
                                <option value="LLC">LLC</option>
                                <option value="Sole Proprietorship">Sole Proprietorship</option>
                                <option value="Medical or legal corporation">Medical or legal corporation</option>
                                <option value="Association/Estate/Trust">Association/Estate/Trust</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Tax Exempt Organization (501c)">Tax Exempt Organization (501c)</option>
                                <option value="Charity">Charity</option>
                                <option value="International organization">International organization</option>
                                <option value="Trust">Trust</option>
                                <option value="Government/Municipality">Government/Municipality</option>
                                <option value="Not for Profit">Not for Profit</option>
                                <option value="Professional Association">Professional Association</option>
                                </select>

                            </div>
                        </div>
                            <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('dateOfIncorporation')}}" type="date"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Date Of Incorporation"
                                       required name="dateOfIncorporation">
                            </div>

                                    <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('province')}}"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Province"
                                       required name="province">
                            </div>
                            <label>2 letter country code (ISO 3166 ALPHA-2)</label><br>
                                    <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('countryOfRegistration')}}"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Country Code Of Registration"
                                       required name="countryOfRegistration">
                            </div>
                                    <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('provinceOfRegistration')}}"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Province Of Registration"
                                       required name="provinceOfRegistration">
                            </div>
                                    <div class="form-group" >
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-transparent"><i class="ti-map"></i></span>
                                </div>

                                <input value="{{old('businessTaxId')}}"
                                       class="form-control pl-15 bg-transparent plc-black" placeholder="Business Tax Id"
                                       required name="businessTaxId">
                            </div>
                        `)
           }else
           {
               $("#bname").empty();
           }
       }
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?libraries=places&key=AIzaSyDQS3W-GRJu-gB6I-rSh0Z4ANIJ8YGnEwo"></script>

    <script>

        const input = document.getElementById("pac-input");


        const options = {
            fields: ["formatted_address", "geometry", "name"],
            strictBounds: false,
            types: ["establishment"],
        };



        const autocomplete = new google.maps.places.Autocomplete(input, options);
        //
        // getPlaceAutocomplete()
        // }
        //
        // function getPlaceAutocomplete() {
        //     const autocomplete = new google.maps.places.Autocomplete(this.addresstext.nativeElement,
        //         {
        //             componentRestrictions: {country: 'US'},
        //             types: [this.adressType]  // 'establishment' / 'address' / 'geocode'
        //         });
        //     google.maps.event.addListener(autocomplete, 'place_changed', () => {
        //         const place = autocomplete.getPlace();
        //         console.log("Place", place)
        //     });
        // }
    </script>
@stop
