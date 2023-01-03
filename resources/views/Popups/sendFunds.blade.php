<div class="modal fade" id="sendFundPopup" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg"  role="document">
        <div class="modal-content">
            <div class="modal-header">
                <label>Select Method</label>

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
                            <div class="col-xl-3 col-12" style="cursor: pointer;  min-width: 250px" >
                                <a href="{{url('aptpay/send-pay-view')}}">


                                    <div class="box text-center" style="padding: 10px;">

                                        <div>
                                            <img style="width: 60px" src="{{asset('smallicons/bank-logo.jpg')}}">
                                        </div><br>

                                        <h5>Bank Transfer</h5>
                                        <small>Send Money To Bank Accounts</small>
                                    </div>
                                </a>
                            </div>
                            <div class="col-xl-3 col-12" style="cursor: pointer; min-width: 250px" >
                                <a href="{{route('banking.eTransferDetail').'?tab=Send'}}">
                                <div class="box text-center" style="padding: 10px;">

                                    <div>
                                        <img style="width: 60px" src="{{asset('smallicons/wallet-logo.jpg')}}">
                                    </div><br>

                                    <h5>Wallet</h5>
                                    <small>Send Money To Wallets ( free )</small>
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
