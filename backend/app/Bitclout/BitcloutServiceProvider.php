<?php

namespace App\Bitclout;

use Illuminate\Support\ServiceProvider;

class BitcloutServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('bitclout', function ($app) {
            $transport = new Transport($app['config']->get('app.proxy'));
            return new Client($transport);
        });
    }
}