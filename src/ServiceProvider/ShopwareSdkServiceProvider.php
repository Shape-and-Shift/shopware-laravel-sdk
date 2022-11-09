<?php declare(strict_types=1);

namespace Sas\ShopwareLaravelSdk\ServiceProvider;

use Sas\ShopwareLaravelSdk\Http\Middleware\SwAppHeaderMiddleware;
use Sas\ShopwareLaravelSdk\Http\Middleware\SwAppIframeMiddleware;
use Sas\ShopwareLaravelSdk\Http\Middleware\SwAppMiddleware;
use Illuminate\Support\ServiceProvider;
use Sas\ShopwareLaravelSdk\Utils\AppHelper;

class ShopwareSdkServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/sas_app.php',
            'sas_app'
        );

        $this->app->bind('AppHelper', function () {
            return new AppHelper($this->app->get('request'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/sas_app.php' => config_path('sas_app.php'),
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/app.php');

        $this->app->get('router')->aliasMiddleware('sas.app.auth', SwAppMiddleware::class);
        $this->app->get('router')->aliasMiddleware('sas.app.auth.iframe', SwAppIframeMiddleware::class);
        $this->app->get('router')->aliasMiddleware('sas.app.auth.header', SwAppHeaderMiddleware::class);
    }
}
