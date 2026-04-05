<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\CashSession;
use App\Models\DigitalTransaction;
use App\Models\Sale;
use App\Models\User;
use App\Policies\CashSessionPolicy;
use App\Policies\DigitalTransactionPolicy;
use App\Policies\SalePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole(UserRole::Owner)) {
                return true;
            }
        });

        Gate::policy(DigitalTransaction::class, DigitalTransactionPolicy::class);
        Gate::policy(CashSession::class, CashSessionPolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
    }
}
