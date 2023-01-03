@extends('layout.master')
@section('title',$title)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="box">
                <div class="box-header with-border text-center">
                    <h4 class="box-title">Update {{$title}}</h4>
                </div>
                <form class="form-horizontal form-element" method="POST" action="{{$url.'/'.$record->id}}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="offset-sm-3 col-sm-6">
                            <div class="box-body">
                                @include('includes.form-errors')
                                <div class="form-group row">
                                    <label class="col-sm-2 control-label">Status</label>
                                    <div class="col-sm-10">
                                        <select class="form-control" name="status" id="status"
                                        >
                                            <option value="" selected disabled hidden></option>
                                            <option @if($record->status=='approved') selected @endif value="approved">Approved</option>
                                            <option @if($record->status=='banned') selected @endif value="banned">Banned</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 control-label">Name</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="name"
                                               value="{{$record->name}}"
                                               required/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 control-label">Email</label>
                                    <div class="col-sm-10">
                                        <input type="email" class="form-control" name="email"
                                               value="{{$record->email}}"
                                               required/>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 control-label">Password</label>
                                    <div class="col-sm-10">
                                        <input type="password" class="form-control" name="password"/>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer text-center">
                                <button type="submit" class="btn btn-primary ">
                                    <i class="ti-save-alt"></i> Update
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop
@section('js')

@endsection
