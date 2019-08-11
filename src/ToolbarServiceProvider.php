<?php

namespace Fruitcake\TelescopeToolbar;

use Fruitcake\TelescopeToolbar\Http\Middleware\ToolbarMiddleware;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ToolbarServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(Toolbar $toolbar)
    {
        if (! config('telescope.enabled') || ! config('telescope-toolbar.enabled') || ! config('app.debug')) {
            return;
        }

        $this->registerRoutes();
        $this->registerPublishing();
        $this->registerResponseHandler($toolbar);
        $this->loadViewsFrom(
            __DIR__.'/../resources/views', 'telescope-toolbar'
        );
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Get the Telescope Toolbar route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'namespace' => 'Fruitcake\TelescopeToolbar\Http\Controllers',
            'prefix' => '_tt',
            'middleware' => 'telescope',
        ];
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/telescope-toolbar.php' => config_path('telescope-toolbar.php'),
            ], 'telescope-toolbar-config');
        }
    }

    /**
     * Listen to the RequestHandled event to prepare the Response.
     *
     * @return void
     */
    private function registerResponseHandler(Toolbar $toolbar)
    {
        $this->app['events']->listen(RequestHandled::class, [$toolbar, 'requestHandled']);
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/telescope-toolbar.php', 'telescope-toolbar'
        );

        $this->app->singleton(Toolbar::class);
    }
}