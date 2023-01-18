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
                    <table id="myTable{{$item->id}}" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                        <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>

                        </tr>
                        </thead>

                        @foreach($item->contacts as $item2)
                        <tr>
                            <td>
                           @if(isset($item2->_objectInstance->name) && isset($item2->_objectInstance->name->formatted))
                               {{$item2->_objectInstance->name->formatted}}
                                @endif



                            </td>
                            <td>
                                @if(isset($item2->_objectInstance->phoneNumbers[0]) && isset($item2->_objectInstance->phoneNumbers[0]->value))
                                    {{$item2->_objectInstance->phoneNumbers[0]->value}}
                                @endif
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
