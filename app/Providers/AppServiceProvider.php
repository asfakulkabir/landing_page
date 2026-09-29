<?php

namespace App\Providers;

use App\Models\Order;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // This project runs on shared hosting where the document root is the
        // project root, not a "public" subfolder. Repointing Laravel's public
        // path here keeps public_path() and `php artisan serve` working.
        $this->app->usePublicPath($this->app->basePath());
    }

    public function boot(): void
    {
        View::composer('layouts.admin', function ($view) {
            $view->with('pendingBadge', Order::where('status', 'pending')->count());
        });
    }
}
