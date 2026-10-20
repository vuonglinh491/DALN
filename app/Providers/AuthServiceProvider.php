<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\User;
use App\Policies\DocumentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Cổng quản trị: kiểm tra ở server, không chỉ ẩn nút giao diện
        Gate::define('admin', fn (User $user) => $user->is_active && $user->isAdmin());
    }
}
