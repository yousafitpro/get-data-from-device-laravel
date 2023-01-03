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
Dear zPAYD User<br>
This is to notify you that a bill was created for payment to take place in the future. Bill will be paid on {{$bill->effective_from}} and on frequency of  <label style="font-weight: bold">{{$bill->frequency}}</label> you chosen. <br>

<br>
<div class="mytable">
    <table  class="mtable">
        <tr>
            <th class="mth" >Payee</th>
            <th class="mth" >Amount</th>
        </tr>
        <tr >
            <td class="mtd" >{{$bill->payee->nickname}}</td>
            <td class="mtd" >{{$bill->amount}}$</td>
        </tr>
    </table>
</div>
<br>
    Thanks,<br>
    {{ config('app.name') }}

@endsection
