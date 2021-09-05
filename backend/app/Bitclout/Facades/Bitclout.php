<?php

namespace App\Bitclout\Facades;

use Illuminate\Support\Facades\Facade;

class Bitclout extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'bitclout';
    }
}