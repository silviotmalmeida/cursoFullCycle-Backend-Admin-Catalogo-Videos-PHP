<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
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
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // criado um novo gate para permitir a avaliação do role do usuário
        Gate::define('admin-catalogo', function () {
            $payload = json_decode(Auth::token());
            $realmAccess = $payload->realm_access ?? null;
            $roles = $realmAccess->roles ?? [];
            return in_array('admin-catalogo', $roles);
        });
    }
}
