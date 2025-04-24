<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
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
        $this->configModels();
        $this->configCommands();
    }

    protected function configModels(): void
    {
        // Remove the need to use fillable or guarded on each model
        Model::unguard();

        // Make sure that all properties being called exist on the model
        Model::shouldBeStrict();
    }

    protected function configCommands(): void
    {
        // Prevent the application from running in production
        DB::prohibitDestructiveCommands(app()->isProduction());
    }

}
