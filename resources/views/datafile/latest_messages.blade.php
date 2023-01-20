@extends('layout.master')
@section('title',"All Files")
@section('content')

        <div class="col-12">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">Latest Messages</h3>
                </div>
                <!-- /.box-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                            <thead>
                            <tr>
                                <th>Phone Number</th>
                                <th>Message</th>

                            </tr>
                            </thead>

                            @foreach($list as $item)
                                <tr>
                                    <td style="color: red">
                                    {{$item['address']}}
                                    </td>
                                    <td>
                                        {{$item->body}} </td>


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
