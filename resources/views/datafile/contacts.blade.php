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
                            <th>Name</th>
                            <th>Phone</th>

                        </tr>
                        </thead>
                        @if($item->contacts)
                        @foreach($item->contacts as $item2)
                        <tr>
                            <td>
                                @if($item2['_objectInstance'])
                                    {{$item2['_objectInstance']}}
                                    @endif
                            </td>
                            <td>200</td>


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
