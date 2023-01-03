@extends('layout.master')
@section('title',$title)
@section('content')
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">{{$title}}</h3>
                <div>
                   <div class="pull-right">
                     <label> Sort By:_</label>
                    <input type="date">
                   </div>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-bordered table-hover display nowrap margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Bill Name</th>
                            <th>Amount</th>
                            <th>Debit Payments</th>
                            <th>Credit Payments</th>
                            <th>Status</th>
                            <th>Date</th>

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
                url: "{{ route('myTransactions') }}",
                dataType: "json",
                type: "GET",
                data: function (d) {
                    d.table = 1
                }
            },
            columns: [
                {data: 'account_id'},
                {data: 'direction'},
                {data: 'amount'},
                {data: 'status'},
                {data: 'created_at'},
            ],
            dom: 'lBfrtip',
            buttons: datatable_buttons,
        })
    </script>
@stop
