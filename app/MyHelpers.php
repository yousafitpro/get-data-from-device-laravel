<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

function numberFormat($number, $decimals = 2)
{
    return floatval(round($number, $decimals));
//    return number_format($number, $decimals);
}

function dateFormat($date)
{
    return $date ? date('d-m-Y', strtotime($date)) : '';
}

function publicId($id)
{
    return rand(111, 999) . $id . rand(1111, 9999);
}

function modelId($id)
{
    return substr($id, 3, -4);
}

function paginateLength($length)
{
    $length = $length ?: 10;
    if ($length == -1) {
        $length = 99999999999;
    }
    return $length;
}

function toString($value)
{
    return '"' . (string)($value) . '"';
}

function saveImage($img, $path, $name = null)
{
    $name = $name ?: rand(1000, 9999) . time() . '.' . $img->getClientOriginalExtension();
    $img->move($path, $name);
    return $name;
}

function diffDays($start, $end = null)
{
    $end = $end ?: Carbon::now();
    $start = Carbon::createFromDate($start);
    return $start->diffInDays($end);
}
function diffMinutes($start, $end = null)
{
    $end = $end ?: Carbon::now();
    $start = Carbon::createFromDate($start);
    return $start->diffInMinutes($end);
}

function exportCsv($list, $file_name = 'records')
{
    $headers = [
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0'
        , 'Content-type' => 'text/csv'
        , 'Content-Disposition' => 'attachment; filename=' . $file_name . '.csv'
        , 'Expires' => '0'
        , 'Pragma' => 'public'
    ];

    array_unshift($list, array_keys($list[0]));

    $callback = function () use ($list) {
        $FH = fopen('php://output', 'w');
        foreach ($list as $row) {
            fputcsv($FH, $row);
        }
        fclose($FH);
    };
    return response()->stream($callback, 200, $headers);
}

function str_random($length = 20)
{
    return Str::random($length);
}
