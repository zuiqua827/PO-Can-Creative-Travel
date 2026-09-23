<?php

namespace App\Providers;

use App\Services\Payment\FakePaymentGateway;
use App\Services\Payment\MidtransPaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            $driver = config('payment.driver', 'fake');

            return match ($driver) {
                'midtrans' => $app->make(MidtransPaymentGateway::class),
                default => $app->make(FakePaymentGateway::class),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
