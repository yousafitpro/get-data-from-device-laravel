
@extends('emails.partials.layout')
@section('content')
    Hello {{$user->name}}<br>

        We have received your request to fund your zPAYD wallet. Account will be updated as soon as the payment is processed. EFT processing flat fee is C0.99 per transaction.

    <br>
    Thank you for using zPAYD!
@endsection
