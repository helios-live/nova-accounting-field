<?php

namespace IdeaToCode\Nova\Fields\Accounting;


use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    protected $policies = [];
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        $this->registerPolicies();
    }
    public function registerPolicies()
    {

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'itc-accounting');

        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
}
