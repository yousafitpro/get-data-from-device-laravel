@extends('emails.partials.layout')
@section('content')
    <h3>Welcome!</h3>
    <p>We're excited to have you get started. First, you need to confirm your account. Just press the button below.</p>
    <a href="{{url('verify-email/welcome-'.$record->id)}}">Confirm Email</a>
    <br>
    Thanks,<br>
    {{ config('app.name2') }}

@stop
