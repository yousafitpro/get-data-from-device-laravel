<?php

namespace App\Http\Controllers;

use App\Models\applicationoffer;
use App\Models\Bill;
use App\Models\UserApplication;
use App\Models\VendorBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ApplicationofferController extends Controller
{
    public $VIEW = 'application_offers';
    public $TITLE = 'Credit Offers';
    public $URL = 'applicationoffers';
    public $SRC = 'images/applicationoffers/';

    public function __construct()
    {
        view()->share([
            'title' => $this->TITLE,
            'url' => url($this->URL),
        ]);
    }

    public function postOffer(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'application_id' => 'required|exists:user_applications,id',
            'lender_id' => 'required|exists:users,id',
            'reference' => 'required',
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' =>$validator->errors()->all()],409);
        }
        if(applicationoffer::where('lender_id',$request->lender_id)->where('user_application_id',$request->application_id)->exists())
        {
            return response()->json(['message' =>"Application already sent"]);
        }
          $item=new applicationoffer();
        $item->user_application_id=$request->application_id;
        $item->reference=$request->reference;
        $item->lender_id=$request->lender_id;
        $item->lender_comment=$request->comment;
        $item->save();

        return response()->json(['message'=>"Offer Sucessfully Sent"]);

    }

    public function index(Request $request)
    {
        $records = applicationoffer::where('user_application_id', $_GET['application_id'])->get();
        return view($this->VIEW . '.index')->with(['records'=>$records]);
    }


}
