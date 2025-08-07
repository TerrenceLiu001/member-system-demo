<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

use App\Services\Strategies\Verification\VerificationEmailOrchestrator;
use App\Services\Strategies\Verification\Implementations\RegisterVerificationStrategy;
use App\Services\Strategies\Verification\Implementations\ForgotPasswordVerificationStrategy;
use App\Services\Strategies\Verification\Implementations\UpdateContactVerificationStrategy;
use App\Services\MemberEmailService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->tag([
            RegisterVerificationStrategy::class,
            ForgotPasswordVerificationStrategy::class,
            UpdateContactVerificationStrategy::class,
        ], 'verification.strategy');

        $this->app->singleton(VerificationEmailOrchestrator::class, function ($app) {
            return new VerificationEmailOrchestrator(
                $app->make(MemberEmailService::class),
                $app->tagged('verification.strategy') 
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) 
        {
            URL::forceScheme('https');
        }
    }
}