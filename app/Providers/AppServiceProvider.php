<?php

namespace App\Providers;

use App\Models\Business;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        Schema::defaultStringLength(191);
        view()
            ->share([
                'business' =>['logo'=>asset('images/logo.png'),'title'=>'asdas'],
                'asset_v' => 2
            ]);
    }
}
