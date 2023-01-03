@extends('layout.master')
@section('title',$title)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">{{$title}}</h3>
                </div>
                <div class="box-body">
                    <div class="table-responsive">
                        <table id="myTable"
                               class="table table-bordered table-hover display  margin-top-10 w-p100">
                            <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Email</th>
                                <th>Lender Balance</th>
                                <th>Interest Rate%</th>
                                <th>Interest Amount</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
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
                {data: 'bank_account_number'},
                {data: 'email'},
                {data: 'my_lender_balance'},
                {data: 'interest_rate'},
                {data: 'interest_balance'},
            ],
            dom: 'lBfrtip',
            buttons: datatable_buttons,
        })
    </script>
@stop
