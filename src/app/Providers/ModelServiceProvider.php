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
        $pathMigrations = $pathDatabase . 'migrations';
        $pathSeeds = $pathDatabase . 'seeds';
        
        $this->publishes([
            $pathMigrations => base_path('database/migrations'),
            $pathSeeds => base_path('database/seeds')
        ]);
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
