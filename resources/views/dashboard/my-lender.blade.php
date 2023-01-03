<?php
$user = auth()->user();
?>
@extends('layout.master')
@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="box">
                <div class="box-body">
                        @if(auth()->user()->is_loc_bankinfo_config==1)

                            @foreach(my_bank(auth()->user()->plaid_loc_access_token)->accounts as $b)
                                @if(auth()->user()->plaid_loc_account_title==$b->name)
                                    <div class="col-md-3">
                                        <div class=" card">
                                            <div class="box-body text-bold">
                                                {{$b->name}}<br>
                                                <hr>
                                                Available: <small> {{$b->balances->available}}</small><br>
                                                Current: <small> {{$b->balances->current}}</small><br>
                                                Currency <small> {{$b->balances->iso_currency_code}}</small>

                                            </div>
                                        </div>
                                    </div>
                                @endif
                        @endforeach
                                <a href="#" onclick="VerifyBank()"
                                   class=" pull-center btn    mb-5" style="background-color: lightblue"><span class="fa fa-pencil" style="color: white;" ></span>
                                    Change Line of credit
                                </a>
                    @endif
                            @if(auth()->user()->is_loc_bankinfo_config!=1)
                    <a class="btn " href="{{url('applications')}}" style="background-color: lightblue"><span style="color: white" class="fa fa-plus"></span> Add Credit account</a>
              @endif
                </div>
            </div>
        </div>
    </div>
@stop
@section('script')
    <script src="https://cdn.plaid.com/link/v2/stable/link-initialize.js"></script>
    <script>
        function VerifyBank()
        {

            $.ajax({
                type: 'get',
                url: "{{route('plaid.getLinkToken')}}",
                data: {"_token": "{{ csrf_token() }}"},
                success: function (data) {

                    const handler = Plaid.create({
                        token:data.link_token,
                        onSuccess: function (public_token, metadata){
                            $.ajax({
                                type: 'get',
                                url: "{{route('plaid.getAccessToken')}}",
                                data: {"_token": "{{ csrf_token() }}","public_token":public_token,"type":"loc","bank_id":metadata.institution.institution_id,"name":metadata.account.name},
                                success: function (data) {
                                    // window.location.reload()
                                    if(data.error!=undefined)
                                    {
                                        alert("Please use Line of Credit Account")
                                        window.location.reload()
                                    }
                                    console.log("Access Token ",data)

                                }})
                            console.log("Public Token : ",public_token)
                            console.log("metadata : ",metadata)
                        },
                        onLoad: function (){

                        },
                        onExit: function (err, metadata)  {
                            console.log("Error",err)
                            console.log("Metadata",metadata)
                            alert("There is something going wrong")
                        },
                        onEvent: function (eventName, metadata) {
                            if (eventName=="HANDOFF")
                            {
                                window.location.reload()
                            }
                        },
                        //required for OAuth; if not using OAuth, set to null or omit:
                        receivedRedirectUri:null,

                    });

                    handler.open();
                }
            });
        }
    </script>

@stop
