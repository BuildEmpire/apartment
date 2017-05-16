<?php

namespace BuildEmpire\Apartment\Providers;

use BuildEmpire\Apartment\Commands\ApartmentMakeCommand;
use BuildEmpire\Apartment\Commands\ApartmentDropCommand;
use BuildEmpire\Apartment\Commands\ApartmentListCommand;
use BuildEmpire\Apartment\Commands\ApartmentMigrationCommand;
use BuildEmpire\Apartment\Commands\ApartmentForceCommand;
use Illuminate\Support\ServiceProvider;
use BuildEmpire\Apartment\Schema;

class ApartmentServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->loadMigrationsFrom(__DIR__.'/../Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ApartmentMakeCommand::class,
                ApartmentDropCommand::class,
                ApartmentListCommand::class,
                ApartmentMigrationCommand::class,
                ApartmentForceCommand::class,
            ]);
        }

        $this->app->singleton('BuildEmpire\Apartment\Schema', function () {
            return new Schema();
        });
    }

    /**
     * Boot any requirements.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'apartment');
    }
}