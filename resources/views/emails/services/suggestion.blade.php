@extends('emails.master')
@section('content')
    <p><b>Provider: </b> {{$record['provider']}}</p>
    <p><b>Service: </b> {{$record['service']}}</p>
    <p>{{$record['remarks']}}</p>
@stop
