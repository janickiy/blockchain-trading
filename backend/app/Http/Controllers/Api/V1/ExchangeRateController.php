<?php

namespace App\Http\Controllers\Api\V1;

use App\Bitclout\Facades\Bitclout;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExchangeRateController extends Controller
{
    public function index()
    {
        return Bitclout::exchangeRate();
    }

    public function ticker()
    {
        return Bitclout::exchangeRateTicker();
    }
}
