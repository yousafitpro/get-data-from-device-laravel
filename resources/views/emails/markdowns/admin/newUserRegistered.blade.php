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

       padding: 5px;
   }
</style>

    Hi Admin<br>
<small>New user with email {{$user->name}} has been registered</small>
<div class="mytable">
    <table  class="mtable">
        <tr>
            <th class="mth" >Name</th>
            <th class="mth" >Email</th>
            <th class="mth" >Package</th>
        </tr>
        <tr >
            <td class="mtd" >{{$user->name}}</td>
            <td class="mtd" >{{$user->email}}</td>
            <td class="mtd" >{{$user->package->title}}</td>
        </tr>
    </table>
</div>
<br>
    Thanks,<br>
    {{ config('app.name') }}

@endsection
