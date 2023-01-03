<?php
$user = auth()->user();
?>
@extends('layout.master')
@section('content')

<div class="box box-body">
    <div class="box-header with-border text-center">
        <a href="{{url('railzConnect')}}@if(request('is_from_login',false)){{'?is_from_login=true'}}@endif"
           class=" pull-left btn btn-circle btn-success mb-5"><span
                class="fa fa-arrow-left"></span>
        </a>

    </div>
    <div id="railz-connect"></div>
</div>
        <!-- Insert this div where you want Railz Connect to be initialized -->



        <!-- Start of Railz Connect script -->
        <script src="https://connect.railz.ai/v1/railz-connect.js"></script>
        <script>
            var widget = new RailzConnect();
            widget.mount({
                parentElement: document.getElementById('railz-connect'),
                widgetId: 'wid_prod_9f401b23-86f0-483d-a3de-32895abddb63',
                businessName:'{{$bname}}',
                redirectUrl: '{{url("railzConnect")}}',
                serviceFilterEnabled: true,
                closeEnabled: true,
                closeRedirectUrl: '{{url("railzConnect")}}',
                headerEnabled: true,
                removeRailzWatermark: true
            });
        </script>
        <!-- End of Railz Connect script -->


        <br>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12 box box-body">

                  <div class="table-responsive">
                      <table id="myTablem1" class="table table-bordered table-hover display  margin-top-10 w-p100">
                          <thead>
                          <tr>
                              <th>Bussiness Name</th>
{{--                              <th>Service name</th>--}}

                              <th>Status</th>
                              <th>Date</th>
                              <th>Actions</th>
                          </tr>
                          </thead>
                          <tbody>
                          @foreach($data['data'] as $b)
                              <tr>
                                  <td>{{$b['businessName']}}
                                  <br>
                                      @if($b['status']=="invalid")
                                          {{$b['disconnectReason']}}
                                          @endif
                                  </td>

                                  <td>{{$b['status']}}</td>
                                  <td>{{\Carbon\Carbon::parse($b['createdAt'])->format('D M Y')}}</td>
                                  <td>
                                      @if(($b['status']!='disconnected' && $b['status']!='invalid'))
                                      <a href="{{route('railz.Disconnect',$b['connectionId'])}}">
                                          <button class="btn btn-primary"  >Disconnect</button>
                                      </a>
                                          @endif
                                          @if($b['status']=='disconnected')
                                              <a href="#">
                                                  <button class="btn btn-default"  >Disconnected</button>
                                              </a>
                                          @endif

                                  </td>


                              </tr>
{{--asas--}}

                          @endforeach
                          </tbody>
                      </table>
                  </div>


            </div>
        </div>
    </div>
@stop
@section('script')

@stop
