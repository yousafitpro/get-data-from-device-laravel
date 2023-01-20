@extends('layout.master')
@section('title',"All Files")
@section('content')

        <div class="col-12">
            <div class="card">
                <div class="card-header with-border">
                    <h3 class="card-title">All Files</h3>
                </div>
                <!-- /.box-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="myTable" class="table table-sm table-bordered table-hover display  margin-top-10 w-p100">
                            <thead>
                            <tr>
                                <th>Display</th>
                                <th>Download</th>

                            </tr>
                            </thead>

                            @foreach($list as $item)
                                <tr>
                                    <td>
                                        {{$item->url}}
                                       @if($item->type=='image')
                                           <img src="{{asset('device/files/'.$item->url)}}" style="width: 100px; height: 100px">
                                           @endif
                                           @if($item->type=='video')
                                               <video width="320" height="240" controls>
                                                   <source src="{{url($item->url)}}" type="video/mp4">

                                                   Your browser does not support the video tag.
                                               </video>
                                           @endif
                                    </td>
                                    <td>
                                      <a href="{{asset('device/files/'.$item->url)}}" download="true"><button class="btn btn-primary">Download</button></a>
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


@stop
@section('script')

@stop
