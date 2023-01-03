@extends('layout.master')
@section('title',"Role Permissions")
@section('content')
    <div class="col-12">
        <div class="box">
            <div class="box-header with-border">
                <a href="{{url('role/index')}}"
                   class=" pull-left btn btn-circle  mb-5"><span
                        class="fa fa-arrow-left" style="font-size: 25px; color: var(--primary)"></span>
                </a>
                <a href="#" data-target="#addPermission" data-toggle="modal"
                   class=" pull-right btn  btn-outline-primary mb-5">
                    Add Permission
                </a>
                <h3 style="margin-left: 10px" class="box-title">{{$role->name}} Permissions</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="table-responsive">
                    <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Name</th>


                            <th style="text-align: right">Actions</th>
                        </tr>

                        </thead>
                        <tbody>
                        @foreach($role->permissions as $t)
                            <tr>
                                <td>
                                    {{$t->name}}
                                </td>


                                <td>
                                    <div class="dropdown pull-right">

                                        <a style="font-size: 15px; margin-right: 20px"  href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Actions <i class="fas fa-caret-down pull-left"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink" >

                                                <a class="dropdown-item" href="{{route('role.revokePermission').'?name='.$t->name.'&role_id='.$role->id}}" style="cursor: pointer"  ><i class="fas fa-minus"></i>  Revoke</a>



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
    <div class="modal fade" id="addPermission" tabindex="-1" role="dialog" data-backdrop="static"
         aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">All Permissions</h5>
                    <button onclick="window.location.reload()" type="button" class="close" data-dismiss="modal"
                            aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @csrf
                    <div class="table-responsive">
                        <table id="myTablem2" class="table table-bordered table-hover display  margin-top-10 w-p100">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th>Action</th>

                            </tr>
                            </thead>
                            <tbody>
                            @foreach($permissions as $key=>$value)
                                <tr>
                                    <td>

                                        {{$value}}

                                    </td>

                                    <td>

                                        <button  id="btn{{$key}}" onclick="addPermission('{{$key}}','{{$value}}')" class="btn btn-outline-primary pull-right">Assign</button>
                                    </td>



                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop
@section('script')

    <script>
        function addPermission(id,value)
        {

            $("#btn"+id).text('Adding...')
            $.ajax({
                type: 'post',
                url: "{{route('role.addPermission')}}",
                data: {"_token": "{{ csrf_token() }}",'name':value,'role_id':'{{$role->id}}'},
                success: function (data) {

                    $("#btn"+id).text('Assigned')
                }
            });
        }
        myTable = $('#myTable').DataTable()
    </script>
@stop
