<?php

namespace App\Providers;

use App\Models\TribeJoinRequest;
use App\Policies\TribeJoinRequestPolicy;
use App\Models\User; // Import User model
use App\Policies\UserPolicy; // Import UserPolicy
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
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        TribeJoinRequest::class => TribeJoinRequestPolicy::class,
        User::class => UserPolicy::class, // Add UserPolicy mapping
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
