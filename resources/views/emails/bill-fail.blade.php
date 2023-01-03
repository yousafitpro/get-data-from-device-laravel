@extends('emails.master')
@section('title','Notification email')
@section('content')
    <p>
        Dear zPAYD Member,
        <br>
        <br>
        This email is to inform you that currently you do not have sufficient account balance to cover upcoming scheduled bill charges.
        <br> In order to avoid any bill payment cancellation, we ask that you deposit funds into your account as soon as possible.
        <br> You can also apply (if you haven't done already) for a credit line through zPAYD lenders to ensure account has sufficient funds available for future transactions.
        <br>
        <br>
        Schedules bills will not be paid should the account balance remain insufficient.
        <br>
        <br>
        <br>
        Thank you for your understanding.
        <br>
        zPAYD Team!
    </p>
@stop
