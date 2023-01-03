@extends('auth.layout')

@section('content')
<div class="container">
    <br>
    <br>
    <br>
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <br>
             <h3 class="text-center" >Email Verification</h3>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('webAuth.resetEmailSend') }}">
                        @csrf

                        <div class="form-group row">

                            <div class="col-md-12">
                                <input  type="email"  class="form-control @error('email') is-invalid @enderror" name="email" value="{{request('email',false)?request('email'):''}}" required autocomplete="email" autofocus>

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button id="btnsend" onclick="send()" type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    <script>
        function send()
        {
            $("#email").prop('disabled',true)
            $("#btnsend").text("Sending...")
        }
    </script>
@endsection
