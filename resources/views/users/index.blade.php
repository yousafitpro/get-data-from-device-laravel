@extends('layout.master')
@section('title',$title)
@section('content')
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{{$title}}</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Zipcode</th>
                            <th>Membership</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
@stop
@section('script')
    <script>
        myTable = $('#myTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ $url }}",
                dataType: "json",
                type: "GET",
                data: function (d) {
                    d.table = 1
                }
            },
            columns: [
                {data: 'name'},
                {data: 'email'},
                {data: 'phone'},
                {data: 'city'},
                {data: 'zipcode'},
                {data: 'package.title'},
                {data: 'actions'},
            ],
            dom: 'lBfrtip',
            buttons: datatable_buttons,
        })
    </script>
@stop
