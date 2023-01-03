<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class cardController extends Controller
{
    public function get_today_card_usage_count($card_number)
    {
        $v= card_usage_count_today($card_number);
        return $v;
    }
}
