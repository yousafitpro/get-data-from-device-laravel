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
This email is to confirm bill cancellation you initiated on {{$bill->due_date}}.
    Please note, bill payment has been cancelled and no further action is required from you. <br>
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
    {{ config('app.name2') }}

@endsection
