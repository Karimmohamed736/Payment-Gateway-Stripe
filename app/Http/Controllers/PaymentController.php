<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {

        $this->paymentGateway = $paymentGateway;
    }


    public function paymentProcess(Request $request)
    {

        return $this->paymentGateway->sendPayment($request);
        if ($result['success']) {
            return redirect($result['url']);
        }

        return redirect()->route('payment.failed');
    }

    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    {
        $response = $this->paymentGateway->callBack($request);
        if ($response) {

            return redirect()->route('payment.success');
        }
        return redirect()->route('payment.failed');
    }



    public function success()
    {

        return view('payment-success');
    }
    public function failed()
    {

        return view('payment-failed');
    }
}
