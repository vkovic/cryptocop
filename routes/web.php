<?php

use App\Models\Trader;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    $trader = Trader::first();
    dd($trader->positions()->count());



    return view('home');
});
