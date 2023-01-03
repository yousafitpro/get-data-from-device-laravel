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
                    <table id="myTable" class="table table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>From Account</th>
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
                {data: 'vendor_bill_category.title'},
                {data: 'date'},
                {data: 'amount'},
                {data: 'from_account'},
            ],
            dom: 'lBfrtip',
            buttons: datatable_buttons,
        })
    </script>
@stop
