<?php
$user = auth()->user();
?>
@extends('layout.master')
@section('content')

    <form method="post" action="{{route('railz.connectPost')}}">
        <input hidden name="maction" value="{{ $user->is_railz_ai_con==1?'update':'create'}}">
        @csrf
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 box box-body">
                    <h3 class="text-center" style="font-size: 20px">Business Name</h3>
                    <input class="form-control text-center" name="business_name" ">
                    <br>
                    <button class="form-control btn btn-primary" type="submit">Add</button>
                </div>
            </div>
        </div>
    </form>


    <div class="container-fluid">
        <div class="row">
            @foreach($data as $b)
            <div class="col-md-4 " >
                <div class="box box-body">
                    <div class="dropdown">

                        <div>
                            <div class="pull-right" >
                                <a style="font-size: 15px; margin-right: 20px"  href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Actions <i class="fas fa-caret-down "></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink" >

                                    <a class="dropdown-item" href="{{route('railz.AddService',$b['name'])}}"><i class="fas fa-edit"></i> Edit</a>
                                    {{--                                       <a class="dropdown-item" onclick="ShareBill('{{$p->id}}')" style="cursor: pointer"  ><i class="fas fa-share"></i> Share</a>--}}
                                    {{--                                    <a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Edit Payee Info</a>--}}
                                    @if($b['service_status']!="active")

                                        <a class="dropdown-item" href="{{route('railz.AddService',$b['name'])}}@if(request('is_from_login',false)){{'?is_from_login=true'}}@endif" ><i class="fas fa-link"></i> Connect</a>
                                    @endif

                                </div>
                            </div>

                            <h5 class="pull-left">{{$b['name']}}</h5>
                            <br><br>
                            <h5 >{{\Carbon\Carbon::parse($b['created_at'])->format('D M Y')}}</h5>
                            <div style="text-align: right; color:var(--primary)">
                                @if($b['service_status']=="active")
                                    {{$b['serviceName']}}
                                @else
                                    not connected yet
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            @endforeach
        </div>

    </div>
<script>
    function Update(id)
    {
        $("#updateB"+id).submit();
    }
</script>
@stop
@section('script')

@stop
