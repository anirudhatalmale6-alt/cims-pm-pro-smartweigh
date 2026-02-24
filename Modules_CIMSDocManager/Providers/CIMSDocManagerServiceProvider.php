<?php

namespace Modules\CIMSDocManager\Providers;

use Illuminate\Support\ServiceProvider;

class CIMSDocManagerServiceProvider extends ServiceProvider
{
    protected $moduleName = 'CIMSDocManager';
    protected $moduleNameLower = 'cimsdocmanager';

    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
    }

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }

    protected function registerConfig()
    {
        $configPath = module_path($this->moduleName, 'Config/config.php');
        if (file_exists($configPath)) {
            $this->mergeConfigFrom($configPath, $this->moduleNameLower);
        }
    }

    public function registerViews()
    {
        $sourcePath = module_path($this->moduleName, 'Resources/views');
        $this->loadViewsFrom($sourcePath, $this->moduleNameLower);
    }

    public function provides()
    {
        return [];
    }
}
