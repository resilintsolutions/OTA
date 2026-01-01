<?php

namespace App\Providers;

use App\Models\PromoEngineSetting;
use App\Policies\PromoEnginePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        PromoEngineSetting::class => PromoEnginePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            // Web dashboard routes are session-authenticated, so keep this check
            // guard-explicit to avoid false negatives.
            return $user->hasRole('admin', 'web') ? true : null;
        });
    }
}
