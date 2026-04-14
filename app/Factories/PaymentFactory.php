<?php

namespace App\Factories;

use App\Interfaces\PaymentGatewayInterface;
use App\Services\PaypalPaymentService;
use App\Services\StripePaymentService;

class PaymentFactory {
    public static function make(string $gateway): PaymentGatewayInterface
    {
        return match($gateway){
            'stripe'=>new StripePaymentService(),
            'paypal'=>new PaypalPaymentService(),
        };

    }
}
