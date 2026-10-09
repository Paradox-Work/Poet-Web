<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (
            $this->app->environment('local') &&
            class_exists(
                \Laravel\Telescope\TelescopeServiceProvider::class
            )
        ) {
            $this->app->register(
                \Laravel\Telescope\TelescopeServiceProvider::class
            );

            $this->app->register(
                TelescopeServiceProvider::class
            );
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        JsonResource::withoutWrapping();

        Password::defaults(
            function () {
                $rule =
                    Password::min(12)
                        ->mixedCase()
                        ->numbers()
                        ->symbols();

                return $this->app
                    ->isProduction()
                        ? $rule->uncompromised()
                        : $rule;
            }
        );
    }
}
