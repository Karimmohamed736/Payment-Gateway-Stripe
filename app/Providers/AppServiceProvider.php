<?php

namespace App\Providers;

use App\Factories\PaymentFactory;
use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ده الرابط بين كل حاجة:
        // Laravel لما يشوف Interface في أي Constructor هيعمل StripePaymentService أوتوماتيك
        $this->app->bind(PaymentGatewayInterface::class, function(){
            $gateway = request()->input('Payment Method','stripe');
            return PaymentFactory::make($gateway);
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
