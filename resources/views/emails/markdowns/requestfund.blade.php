@extends('emails.partials.layout')
@section('content')
    <h2>Hi</h2>

{{$user->name}} is requesting {{$amount}}$
@if(!$has_account)
    <h3 style="text-align: center">
        ,however you do not  have  {{config('app.name')}} account to complete the transaction. Please  click <a href="{{url('/register')}}">here</a> to register yourself then come back to complete this transaction.

    </h3>
    @endif

    <br>
    <br>
    <a  href="{{route('fund.acceptrequest',$item->id)}}">
        <button style="color: white; background-color: green; padding: 5px">Confirm</button>
    </a>
    <a  href="{{route('fund.denyrequest',$item->id)}}">
        <button style=" color: white; background-color: darkred; padding: 5px">Deny</button>
    </a>
<br>
 <br>
Thanks,<br>
{{ config('app.name') }}

@endsection
