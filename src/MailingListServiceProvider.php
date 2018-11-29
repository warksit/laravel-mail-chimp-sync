<?php namespace Warksit\LaravelMailChimpSync;

use HipsterJazzbo\Landlord\Landlord;
use Illuminate\Support\ServiceProvider;

class MailingListServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            realpath(__DIR__.'/../migrations') => $this->app->databasePath().'/migrations',
            __DIR__.'/config/mailinglist.php' => config_path('mailinglist.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
       //
    }
}
