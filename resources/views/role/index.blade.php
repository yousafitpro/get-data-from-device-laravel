@extends('layout.master')
@section('title',"Roles")
@section('content')
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Roles</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Permissions</th>
                            <th>Users</th>

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
                                    {{$t->permissions->count()}}

                                </td>
                                <td>
                                    {{$t->users->count()}}

                                </td>
                                <td>
                                    <div class="dropdown pull-right">

                                        <a style="font-size: 15px; margin-right: 20px"  href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Actions <i class="fas fa-caret-down pull-left"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink" >

                                                <a class="dropdown-item" href="{{route('role.permissions',$t->id)}}" style="cursor: pointer"  ><i class="fas fa-cogs"></i>  Permissions</a>



                                        </div>
                                    </div>
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
