@extends('layout.master')
@section('title',"Wait-List")
@section('content')
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Wait-List</h3>
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
{{--                            <th>address</th>--}}
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>

                        </thead>
                        <tbody>
                        @foreach($list as $t)
                            <tr>

                                <td>
                                    {{$t->name}}
                                </td>
                                <td>
                                    {{$t->email}}
                                </td>

                                <td>
                                    {{$t->phone}}
                                </td>
{{--                                <td>--}}
{{--                                    {{$t->address}}--}}
{{--                                </td>--}}
                                <td>
                                    {{$t->created_at}}
                                </td>
                                <td>
                                    <a href="{{route('waitlist.delete',$t->id)}}" class="btn bg-danger">Remove</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>

@stop
@section('script')
    <script>
        myTable = $('#myTable').DataTable()
    </script>
@stop
