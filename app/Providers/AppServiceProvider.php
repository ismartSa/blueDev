<?php

namespace App\Providers;

use App\Models\Lecture;
use App\Observers\LectureObserver;
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

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Lecture::observe(LectureObserver::class);
    }
}
