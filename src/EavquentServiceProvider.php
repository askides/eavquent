<?php

namespace ItsRennyMan\Eavquent;

use Illuminate\Support\ServiceProvider;

class EavquentServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //
    }

    public function register()
    {
        $this->app->singleton('eavquent', function () {
            return new Eavquent;
        });
    }
}
