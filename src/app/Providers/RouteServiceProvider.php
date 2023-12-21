<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
    // protected function configureRateLimiting()
    // {
    //     RateLimiter::for('api', function (Request $request) {
    //         return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
    //     });

    //     RateLimiter::for('login_rate_limit', function (Request $request) {
    //         return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
    //     });

    //     RateLimiter::for('sms_rate_limit', function (Request $request) {
    //         return Limit::perMinutes(3, 1)->by(optional($request->user())->id ?: $request->ip());
    //     });

    //     RateLimiter::for('transfer_rate_limit', function (Request $request) {
    //         return Limit::perMinutes(0.2, 1)->by(optional($request->user())->id ?: $request->ip() . 'transfer');
    //     });

    //     RateLimiter::for('api_transfer_rate_limit', function (Request $request) {
    //         return Limit::perMinute(30)->by($request->private_key ?: $request->ip() . 'transfer');
    //     });
    // }
}
