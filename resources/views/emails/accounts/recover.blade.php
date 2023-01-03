@extends('emails.master')
@section('content')
    <style>
        table {
            width: 100%;
        }

        table, tr, th, td {
            border: 1px solid black;
            text-align: center;
        }

        th, td {
            padding: 8px;
        }
    </style>
    <h2 class="text-center">Recover Account</h2>
    <p>Please click here to recover your account: <a target="_blank"
                                                     href="{{url('account/recover/'.$record->remember_token.'-'.$record->id)}}">Recover Account</a>
    </p>
@stop
