@extends('layout.master')
@section('title',"Setting")
@section('content')
    <div class="box box-body">
        <div class="row">
            <div class="col-12">
                <h4>Notifications</h4>
                <small>Please Enable these to get alerts.</small>
            </div>

        </div><br>

        <br>
        @foreach(auth()->user()->notificationSetting as $n)
        <div class="row" style="border-bottom: solid 1px grey; padding-bottom: 10px; padding-top: 10px">
            <div class="col-md-8">
                {{$n->title}}
            </div>
            <div class="col-md-2">
                <label>Email</label><br>
               <select id="slectemail{{$n->id}}" class="form-control" onchange="updateMySetting('#slectemail{{$n->id}}','email','{{$n->name}}','')">
                   <option {{$n->email=='yes'?'selected':''}}>Yes</option>
                   <option {{$n->email=='no'?'selected':''}}>No</option>
               </select>
            </div>
            <div class="col-md-2">
                <label>SMS</label><br>
                <select id="slectsms{{$n->id}}" class="form-control" onchange="updateMySetting('#slectsms{{$n->id}}','sms','{{$n->name}}','')">
                    <option {{$n->sms=='yes'?'selected':''}}>Yes</option>
                    <option {{$n->sms=='no'?'selected':''}}>No</option>
                </select>
            </div>
        </div>
        @endforeach
    </div>


<script>
    function updateMySetting(id,column,name,value)
    {

        $.ajax({
            type: 'post',
            url: "{{route('setting.notification.update_column')}}",
            data: {"_token": "{{ csrf_token() }}","column":column,"name":name,'value':$(id).val()},
            success: function (data) {



            }})
    }
</script>
@endsection
