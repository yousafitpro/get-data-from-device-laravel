<div class="modal fade" id="requestFundPopup" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label>Select</label>

                <button type="button" class="close" data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div>
                <div class="modal-body">
                    @csrf



                    <div class="box-body myFlex">

                        <div class="row">
                            <div class="col-xl-3 col-12" style="cursor: pointer; min-width: 250px" >
                                <a href="{{url('aptpay/request-pay-view')}}">
                                <div class="box text-center" style="padding: 10px;">

                                    <div>
                                        <img style="width: 60px" src="{{asset('smallicons/requestpay.PNG')}}">
                                    </div><br>

                                    <h5>Request Funds</h5>
                                    <small>Create a Request For Funds</small>
                                </div>
                                </a>
                            </div>



                            <div class="col-xl-3 col-12" style="cursor: pointer;  min-width: 250px" >
                                <a href="{{route('dashboard.paymentRequests').'?tab=Requests'}}">


                                <div class="box text-center" style="padding: 10px;">

                                    <div>
                                        <img style="width: 60px" src="{{asset('smallicons/internationaltransfer.PNG')}}">
                                    </div><br>

                                    <h5>Bill Sharing</h5>
                                    <small>Bill Sharing Fund Request</small>
                                </div>
                                </a>
                            </div>
                        </div>
                    </div>


                    <br>



                </div>





            </div>
        </div>

    </div>


</div>
