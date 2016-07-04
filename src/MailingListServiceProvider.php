<?php namespace Warksit\LaravelMailChimpSync;

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
            realpath(__DIR__.__DIR__.'/migrations') => $this->app->databasePath().'/migrations',
            realpath(__DIR__.__DIR__.'/config/maillist.php') => config_path('maillist.php'),
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
