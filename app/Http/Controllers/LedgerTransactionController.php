<?php

namespace App\Http\Controllers;

use App\Models\ledgerTransaction;
use Illuminate\Http\Request;

class LedgerTransactionController extends Controller
{
   public static function store($account,$direction,$amount)
   {
       $lt=new ledgerTransaction();
       $lt->account=$account;
       $lt->amount=$amount;
       $lt->direction=$direction;
       $lt->save();
   }
}
