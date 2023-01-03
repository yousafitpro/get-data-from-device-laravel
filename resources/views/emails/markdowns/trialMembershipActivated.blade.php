@extends('emails.partials.layout')
@section('content')
<style>
    .mtable, .mth, .mtd {
        border: 1px solid;
    }
    .mtable{
        border-collapse: collapse;
    }
    .mth, .mtd {
        text-align: center;
        width: 200px;
        padding: 5px;
    }
</style>

<h3>Hello {{$user->name}}</h3>

<h4>
    <?php
    $date=\Carbon\Carbon::parse(today_date())->addDays(30);
    ?>
    Your 30 Day Free Trial has been activated effective {{$date->toDateString()}}.
    This no cost trial period allows you to evaluate our platform and all great benefits we offer to you. When you sign up for free trial, you are enrolling in an zPAYD subscription product. If you do not cancel your product before the trial period ends, you will be charged for your account on a monthly or annual basis until you cancel. If you wish to cancel your subscription, you can do so by emailing us at support@zpayd.com or contact us through inquiry box straight from your zPAYD account.
</h4>
<br>
Thank you for using zPAYD!

@endsection
