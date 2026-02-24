<?php

namespace Modules\cims_pm_pro\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    protected $moduleNamespace = 'Modules\\cims_pm_pro\\Http\\Controllers';

    public function boot(): void
    {
        parent::boot();
    }

    public function map(): void
    {
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware(['web', 'auth'])
            ->prefix('cims/pm')
            ->namespace($this->moduleNamespace)
            ->group(module_path('cims_pm_pro', '/Routes/web.php'));
    }
}
