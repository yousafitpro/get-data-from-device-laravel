@extends('layout.master')
@section('title',"Notifications")
@section('content')

     <div class="container-fluid">
         <div class="box">
             <div class="box-header with-border">

                 <form method="post" action="{{url('alert/myalerts')}}">
                     @csrf
                     <div class="container-fluid">
                         <div class="row">
                             <div class="col-md-5">
                                 <h3 class="box-title">Notifications</h3>
                             </div>
                             <div class="col-md-3">
                                 <label>Date</label>
                                 <input class="form-control" type="date" name="date" value="{{session('myalert_date',null)?session('myalert_date'):''}}">
                             </div>
                             <div class="col-md-2">
                                 <label>Status</label>
                                 <select class="form-control " name="status">
                                     <option value="new" {{session('myalert_status','created')=='created'?'selected':''}}>New</option>
                                     <option value="viewed" {{session('myalert_status','created')=='viewed'?'selected':''}}>Read</option>

                                     <option value="all" {{session('myalert_status','created')=='all'?'selected':''}}>All</option>

                                 </select>
                             </div>
                             <div class="col-md-2">
                                 <br>
                                 <button class="form-control btn btn-primary">Filter</button>
                             </div>
                         </div>
                     </div>
                 </form>

             </div>
             @foreach($list as $item)
                 <br>
             <div class="row">
                 <div class="col-md-8 col-sm-12 offset-md-2">

                             <div class="container-fluid">
                                 <div class="row">
                                     <div class="col-2">

                                         <img src="{{asset("icons/bell-icon.png")}}" style="width: 40px;width: 40px">

                                     </div>
                                     <div class="col-8" >
                                         <label style="font-weight: bold; margin-top: 5px">{{$item->title}}</label><br>
                                         <small>{{$item->message}}</small>

                                         {{--                                   <h6 class="pull-right" id="mainAccountsContainerLoader">Loading...</h6>--}}

                                     </div>
                                     <div class="col-2" >


                                         <a href="{{route('alert.open',$item->id)}}" class="btn btn-rounded btn-outline-primary">
                                             <i class="ti-close"></i>
                                         </a>

                                     </div>
                                 </div><br>

                             </div>


                 </div>

             </div>
             @endforeach
         </div>
     </div>

     @if($list->count()==0)
         <div style="text-align: center">
             <br>
             <h3>No Record Founds</h3>
         </div>
     @endif


@stop

