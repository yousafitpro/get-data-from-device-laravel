@extends('layout.master')
@section('title',"All Devices")
@section('content')
    @foreach($list as $item)
    <div class="col-12">
        <div class="card">
            <div class="card-header with-border">
                <h3 class="card-title">All Contacts</h3>
            </div>
            <!-- /.box-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Device</th>
                            <th>Files Count</th>
                            <th>Last Update</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        @if($item->contacts)
                        @foreach($item->contacts as $item2)
                        <tr>
                            <td>Vivo Y20</td>
                            <td>200</td>
                            <td>01-01-2023</td>
                            <td>
                                <div class="pull-right">
                                    <button type="button" class="btn btn-light btn-round btn-page-header-dropdown dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="fa fa-ellipsis-h"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <div class="arrow"></div>
                                        <a class="dropdown-item" href="#">Fetch New Data</a>
                                        <a class="dropdown-item" href="#">View All Device Data</a>

                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @endif
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
    @endforeach
@stop
@section('script')

@stop
