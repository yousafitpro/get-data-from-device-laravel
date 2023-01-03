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

    Hi {{$user->name}}<br>
<small>This is to confirm your payment has been successfully sent to the following<br> biller:</small>
<div class="mytable">
    <table  class="mtable">
        <tr>
            <th class="mth" >Payee</th>
            <th class="mth" >Amount</th>
        </tr>
        <tr >
            <td class="mtd" >{{$payee->nickname}}</td>
            <td class="mtd" >{{$bill->actual_amount}}$</td>
        </tr>
    </table>
</div>
<br>
    Thanks,<br>
    {{ config('app.name') }}

@endsection
