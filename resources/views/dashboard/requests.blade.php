<?php
$user = auth()->user();
?>
@extends('layout.master')
@section('content')

    <div class="padding">
        <div class="box">
            <div class="box-header with-border text-center">
                <a href="{{url('Banking/sendAndRequestFund')}}"
                   class=" pull-left btn btn-circle  mb-5"><span
                        class="fa fa-arrow-left" style="font-size: 25px; color: var(--primary)"></span>
                </a>
                <h4 class="box-title text-capitalize">Share Bills </h4>
{{--                <a href="{{route('transaction.history')}}" class="pull-right">Transfer Logs</a>--}}
            </div>
            <div class="box-body">
                <div class="container-fluid">
{{--                    <div class="row">--}}
{{--                        <div class="col-md-12">--}}
{{--                            <div class="mtabOuter">--}}
{{--                                <div class="mtab" style="width: 500px" >--}}
{{--                                    <a href="{{route('dashboard.paymentRequests').'?tab=Requests'}}">--}}
{{--                                        <button class="mtablinks {{$_GET['tab']=="Requests"?'active':''}} ">Bill Sharing</button>--}}
{{--                                    </a>--}}
{{--                                    <a href="{{route('dashboard.paymentRequests').'?tab=Send'}}">--}}
{{--                                        <button class="mtablinks  {{$_GET['tab']=="Send"?'active':''}}" >Request Other Amount</button>--}}
{{--                                    </a>--}}
{{--                                    <a href="{{route('dashboard.paymentRequests').'?tab=History'}}">--}}
{{--                                        <button class="mtablinks  {{$_GET['tab']=="History"?'active':''}}" >History</button>--}}
{{--                                    </a>--}}

{{--                                </div>--}}
{{--                            </div>--}}

{{--                        </div>--}}
{{--                    </div>--}}
                </div>
              @if($_GET['tab']=="Requests")
                  <br>
                    <div class="row">
                        <div class="col-md-12" >
                            <a href="{{url('bills/create')}}" >  <button class="btn btn-primary pull-right btn-small" style="font-size: 13px" >Add New Payee</button>
                            </a>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-md-4">
                            <h5 style="font-weight: bold">Payee</h5>

                        </div>
                        <div class="col-md-3">
                            <h5>Date</h5>
                        </div>
                        <div class="col-md-3">
                            <h5>Amount</h5>
                        </div>
                        <div class="col-md-2">
                            <h5></h5>
                        </div>
                    </div>
                <br>

                    @foreach(my_own_payees(auth()->user()->id) as $p)
                        <form id="form-id{{$p->id}}" method="post" action="{{route('sharedBill.add')}}" >

                            @csrf
                            <input name="payee_id" hidden value="{{$p->payee_id}}">
                            <input name="frequency" hidden value="once">
                            <div class="row">
                                <div class="col-md-4">
                                    <a href="#">

                                        <h5>{{$p->nickname}}</h5>
                                        <small>(Last Paid:200$-1231df1)</small>
                                    </a>

                                </div>
                                <div class="col-md-3">
                                    <input style="font-size: 10px" required id="due_date{{$p->id}}" name="due_date" class="form-control" type="date">
                                </div>
                                <div class="col-md-3">
                                    <input required class="form-control" id="amount{{$p->id}}" name="amount" type="number">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-primary btn-small" style="margin-top: -8px" onclick="ShareBill('{{$p->id}}')">Share</button>
{{--                                    <div class="dropdown pull-right">--}}

{{--                                        <a style="font-size: 15px; margin-right: 20px"  href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">--}}
{{--                                            Actions <i class="fas fa-caret-down pull-left"></i>--}}
{{--                                        </a>--}}
{{--                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink" >--}}
{{--                                            <a class="dropdown-item" style="cursor: pointer" onclick="SubmitMe('form-id{{$p->id}}')"><i class="fas fa-dollar"></i> Pay</a>--}}
{{--                                            <a class="dropdown-item" href="javascript:void" data-target="#removePayee{{$p->id}}" data-toggle="modal"><i class="fas fa-minus-circle"></i> Remove Payee</a>--}}
{{--                                            <a class="dropdown-item" onclick="ShareBill('{{$p->id}}')" style="cursor: pointer"  ><i class="fas fa-share"></i> Share</a>--}}
{{--                                            --}}{{--                                    <a class="dropdown-item" href="#"><i class="fas fa-edit"></i> Edit Payee Info</a>--}}
{{--                                            <a class="dropdown-item" href="{{route('dashboard.setReoccurringBill').'?payee_id='.$p->payee->id}}" ><i class="fas fa-refresh"></i> Setup Recurring Payment</a>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="modal fade" id="removePayee{{$p->id}}" tabindex="-1" role="dialog"
                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Alert</h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>

                                    <div class="modal-body">
                                        @csrf
                                        <div class="container-fluid">
                                            <div class="row">
                                                <div class="col-md-12">

                                                    <h4>Are you sure you want to remove this payee ?</h4>
                                                </div>
                                            </div>


                                            <hr>
                                        </div>
                                    </div>
                                    <div class="modal-footer ">
                                        <a href="{{route('payee.removeMyPayee',$p->id)}}" class="pull-right">
                                            <button
                                                class="btn btn-primary" style="min-width: 70px"> Yes
                                            </button>
                                        </a>
                                        <button type="button" class="btn btn-secondary pull-right" style="min-width: 70px"
                                                data-dismiss="modal"> Cancel
                                        </button>


                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal fade" id="shareWith{{$p->id}}" tabindex="-1" role="dialog"
                             aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Share Bill</h5>
                                        <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <form class="web-form"  method="POST" id="shareForm"
                                          action="{{route('sharedBill.add')}}">
                                        <div class="modal-body">
                                            @csrf
                                            <div class="container-fluid">
                                                <input hidden name="mypayee_id" value="{{$p->id}}">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label>Amount</label>
                                                        <input  name="amount" type="number" required class="form-control">
                                                    </div>
                                                </div>
                                                <br>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label>Due Date</label>
                                                        <input  name="due_date" required class="form-control" type="date">
                                                    </div>
                                                </div>
                                                <hr>
                                            </div>
                                        </div>
                                        <div class="modal-footer text-center">
                                            <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal"> Close
                                            </button>
                                            <button onclick="SubmitMe('shareForm')" type="submit" form="service-suggestion"
                                                    class="btn btn-primary"> Save
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                  @endif
{{--                @if($_GET['tab']=="Send")--}}
{{--                    <br>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-12">--}}
{{--                            <div class="table-responsive">--}}
{{--                                <table  class="table  table-hover display  margin-top-10 w-p100">--}}
{{--                                    <thead>--}}
{{--                                    <tr>--}}
{{--                                        <th>User</th>--}}
{{--                                        <th></th>--}}
{{--                                        <th style="text-align: center"></th>--}}
{{--                                        <th style="text-align: center"></th>--}}
{{--                                        <th>Actions</th>--}}
{{--                                    </tr>--}}
{{--                                    </thead>--}}
{{--                                    <tbody>--}}

{{--                                    <tr>--}}
{{--                                        <td><input id="user0" type="email" class="form-control" placeholder="Request Fund From Anyone..." required> </td>--}}

{{--                                        <td><input id="amount0" class="form-control" placeholder="Enter amount here..." style="width: 150px" type="number"></td>--}}

{{--                                        <td>--}}

{{--                                        </td>--}}

{{--                                        <td>--}}
{{--                                            <button onclick="requestFund('0')"  class="btn btn-outline-primary pull-center" id="btn0"  >  <i class="ti-amount"></i> Request </button>--}}
{{--                                        </td>--}}


{{--                                    </tr>--}}
{{--                                    <tr>--}}
{{--                                        <td>--}}
{{--                                            <label>Choose from Network</label>--}}
{{--                                        </td>--}}

{{--                                        <td>--}}
{{--                                        </td>--}}
{{--                                        <td></td>--}}

{{--                                        <td>--}}
{{--                                        </td>--}}


{{--                                    </tr>--}}
{{--                                    @foreach($users as $r)--}}
{{--                                        <tr>--}}
{{--                                            <td>--}}
{{--                                                <input hidden value="{{$r->username}}" id="user{{$r->id}}">--}}
{{--                                                {{$r->full_name}}<br>{{$r->username}} </td>--}}
{{--                                            <td>--}}
{{--                                                <select class="form-control" id="bank{{$r->id}}" name="fromSend" required style="width: 150px">--}}

{{--                                                    @foreach(my_credits() as $d)--}}
{{--                                                        <option value="{{$d->id}}">{{$d->bank_name }}( {{$d->title}} )</option>--}}
{{--                                                    @endforeach--}}
{{--                                                </select>--}}
{{--                                            </td>--}}
{{--                                            <td><input id="amount{{$r->id}}" class="form-control" placeholder="Enter amount here..." style="width: 150px" type="number"></td>--}}
{{--                                            <td></td>--}}

{{--                                            <td>--}}
{{--                                                <button onclick="requestFund('{{$r->id}}')" class="btn btn-outline-primary pull-center" id="btn{{$r->id}}"  >  <i class="ti-amount"></i> Request </button>--}}
{{--                                            </td>--}}


{{--                                        </tr>--}}

{{--                                    @endforeach--}}
{{--                                    </tbody>--}}
{{--                                </table>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                @endif--}}
{{--                @if($_GET['tab']=="History")--}}
{{--                    <br>--}}
{{--                    <div class="row">--}}
{{--                        <div class="col-md-12">--}}
{{--                            <div class="table-responsive">--}}
{{--                                <table id="myTablem1" class="table table-bordered table-hover display  margin-top-10 w-p100">--}}
{{--                                    <thead>--}}
{{--                                    <tr>--}}
{{--                                        <th>User</th>--}}
{{--                                        <th>Amount</th>--}}
{{--                                        <th>Status</th>--}}
{{--                                        <th>Date</th>--}}
{{--                                        <th>Actions</th>--}}
{{--                                    </tr>--}}
{{--                                    </thead>--}}
{{--                                    <tbody>--}}
{{--                                    @foreach($history as $item)--}}
{{--                                        <tr>--}}
{{--                                            @if($item->receiver!=null)--}}
{{--                                                <td>{{$item->receiver->name}}<br>{{$item->receiver->email}} </td>--}}
{{--                                            @else--}}
{{--                                                <td> {{$item->receiver_username}} </td>--}}
{{--                                                @endif--}}
{{--                                            <td>{{$item->amount}}$</td>--}}
{{--                                                <td>{{$item->status}}</td>--}}
{{--                                            <td>--}}
{{--                                                <span class="pull-left">{{\Carbon\Carbon::parse($item->created_at)->format('Y-m-d')}}</span>--}}
{{--                                            <br>--}}
{{--                                                <i  class="ti-arrow-up pull-right"></i>--}}
{{--                                            </td>--}}



{{--                                        </tr>--}}
{{--                                    @endforeach--}}
{{--                                    </tbody>--}}
{{--                                </table>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    @endif--}}

            </div>
        </div>
        <br>

    </div>

<script>
    function requestFund(id)
    {
        var email=$("#user"+id).val()

        var amount=$("#amount"+id).val()
        if (email=='')
        {
            alert("Email is required.")
            return
        }
        if (amount=='')
        {
            alert("Amount is required.")
            return
        }

        if ($("#btn"+id).text()=="Requesting...")
        {
            alert("Already Requesting...");
        }
        $("#btn"+id).text("Requesting...")
        $.ajax({
            type: 'post',
            url: "{{route('fund.request')}}",
            data: {"_token": "{{ csrf_token() }}",'amount':amount,'email':email},
            success: function (data) {
         alert(data.message)
                window.location.reload()
            }
        });
    }
    function ShareBill(id)
    {

        var due_date=$("#due_date"+id).val()
        var amount=$("#amount"+id).val()

        if (due_date=='')
        {
            alert("Please select due date")
            return;
        }
        if (amount=='' || amount=='0')
        {
            alert("Please Enter amount Correctly")
            return;
        }

        $.ajax({
            type: 'post',
            url: "{{route('sharedBill.add')}}",
            data: {"_token": "{{ csrf_token() }}",'amount':amount,'due_date':due_date,'mypayee_id':id},
            success: function (data) {

                window.location.href='{{url('shared-bill/edit')}}/'+data.id
            }
        });
    }
function pay(id)
{
    var amount=$("#amount"+id).val()
    var date=$("#date"+id).val()
    var bank=$("#bank"+id).val()
    if (bank==''||amount==''||date==''||amount=='0')
    {
        alert("Please Enter Data Correctly")
        return
    }

    alert("Payment Successfully Started")
    window.location.reload()
}
</script>
@endsection
