@extends('emails.master')
@section('content')
    <style>
        table{
            width: 100%;
        }
        table ,tr,th ,td{
            border: 1px solid black;
            text-align: center;
        }
        th ,td{
             padding: 8px;
         }
    </style>
    <h2 class="text-center">New Order # {{$record->id}}</h2>
    <p>Name: <b>{{$record->name}}</b></p>
    <p>Email: <b>{{$record->email}}</b></p>
    <p>Phone: <b>{{$record->phone}}</b></p>
    <p>City: <b>{{$record->city}}</b></p>
    <p>Postal Code: <b>{{$record->postal_code}}</b></p>
    <p>Address: <b>{{$record->address}}</b></p>
    <p>Country: <b>{{$record->country}}</b></p>
    <p>Message: <b>{{$record->message}}</b></p>
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Description</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Total Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach($record->products as $key=>$detail)
            <tr>
                <td>{{$key+1}}</td>
                <td>
                    <p>{{$detail->title}}</p>
                    <p>Size: <b>{{$detail->size}}</b></p>
                    <p>Material: <b>{{$detail->material}}</b></p>
                </td>
                <td>{{$detail->qty}}</td>
                <td>{{$detail->price}}</td>
                <td>{{$detail->total_amount}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@stop
