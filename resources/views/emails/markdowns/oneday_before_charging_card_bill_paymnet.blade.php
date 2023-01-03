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

Hello {{$user->name}}<br>
This email is to notify you that you have an upcoming bill for payment. Your wallet balance is insufficient to cover this bill, therefore we will complete the bill payment using one of the  cards you added as a backup in your account. Card processing fee is 3%.
    If you want to fund your account, please login in to zPAYD and complete transfer before bill is set to be paid.
    If you don't want this bill paid with credit or you want to cancel the bill, please login to your account, go to my bills section and cancel the bill.

<br>
Thank you for using zPAYD!

@endsection
