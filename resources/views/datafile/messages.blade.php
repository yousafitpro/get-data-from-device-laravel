@extends('layout.master')
@section('title',"All Devices")
@section('content')
    @foreach($list as $item)
        <div class="col-12">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">All Messages</h3>
                </div>
                <!-- /.box-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="myTable{{$item->id}}" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                            <thead>
                            <tr>
                                <th>Phone Number</th>
                                <th>Message</th>

                            </tr>
                            </thead>

                            @foreach($item->messages as $item2)
                                <tr>
                                    <td>
                                        {{$item2->address}}
                                    </td>
                                    <td>
                                        {{$item2->body}}
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
        <script>

            // setTimeout(function (){
            //
            // },3000)
        </script>
    @endforeach
@stop
@section('script')

@stop
