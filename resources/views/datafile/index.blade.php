@extends('layout.master')
@section('title',"All Devices")
@section('content')
    <div class="col-12">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title">All Devices</h3>
            </div>
            <!-- /.box-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Device</th>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        @foreach($list as $item)
                        <tr>
                            <td>{{$item->device_id}}</td>
                            <td>{{$item->name}}</td>
                            <td>{{$item->created_at}}</td>
                            <td>
                                <div class="pull-right">
                                    <button type="button" class="btn btn-light btn-round btn-page-header-dropdown dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <div class="arrow"></div>
                                        <a class="dropdown-item" href="{{url('Datafile/contacts',$item->device_id)}}">Contacts</a>
                                        <a class="dropdown-item" href="{{url('Datafile/messages',$item->device_id)}}">Messages</a>
                                        <a class="dropdown-item" href="{{url('Datafile/latest-messages',$item->device_id)}}">Latest Messages</a>
                                        <a class="dropdown-item" href="{{url('Datafile/files',$item->device_id)}}">Files</a>
                                        <a class="dropdown-item" onclick="confirm('Are you sure?')" href="{{url('Datafile/delete_device',$item->device_id)}}">Delete</a>


                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
@stop
@section('script')

@stop
