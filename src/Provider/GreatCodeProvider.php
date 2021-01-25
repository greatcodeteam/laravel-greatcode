<?php


namespace GreatCode\Provider;

use GreatCode\CheckStatus;
use Illuminate\Support\ServiceProvider;

class GreatCodeProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $status = new CheckStatus(config('greatcode.product_UUID'), config('greatcode.domain'));
        $status->init();
    }
}
