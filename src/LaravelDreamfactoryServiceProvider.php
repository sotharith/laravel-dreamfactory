<?php namespace GDCE\LaravelDreamfactory;

use Illuminate\Support\ServiceProvider;

/**
 * A Laravel 5's package template.
 *
 * @author: Rémi Collin 
 */
class LaravelDreamfactoryServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/laravel-df.php' => config_path('laravel-df.php')
        ], 'laravel-dreamfactory');
    }

        /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['laravel-dreamfactory'] = $this->app->share(function($app)
        {
            return new LaravelDreamfactory($app['router']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('laravel-dreamfactory');
    }

}
