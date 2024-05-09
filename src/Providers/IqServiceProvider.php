<?php

namespace Mralston\Iq\Providers;

use Illuminate\Support\ServiceProvider;
use Mralston\Iq\Models\Customer;
use Mralston\Iq\Observers\CustomerObserver;
use Mralston\Iq\Services\CustomerService;

class IqServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/config.php', 'iq');
        
        $this->app->bind(CustomerService::class, function () {
            return new CustomerService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/config.php' => config_path('iq.php'),
            ], 'pdf-config');
        }
        
        Customer::observe(CustomerObserver::class);
    }
}