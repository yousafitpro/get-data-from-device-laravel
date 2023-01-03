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


{{$content}}
<br>
Thank you for using zPAYD!

@endsection
