<?php namespace Ognestraz\Model\Providers;

use Illuminate\Support\ServiceProvider;

class ModelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $pathDatabase = __DIR__.'/../../database/';
        $this->publishes([
            $pathDatabase . 'migrations' => base_path('database/migrations'),
            $pathDatabase . 'seeds'      => base_path('database/seeds')
        ]);
        
        $this->loadMigrationsFrom($pathDatabase . 'migrations');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
