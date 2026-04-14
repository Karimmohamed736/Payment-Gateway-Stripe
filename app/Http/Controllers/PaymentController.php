<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class PaymentController extends Controller
{
    protected PaymentGatewayInterface $paymentGateway;

    public function __construct(PaymentGatewayInterface $paymentGateway)
    {

        $this->paymentGateway = $paymentGateway;
    }


    //User pay
    public function paymentProcess(Request $request)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'currency'       => 'required|string',
            'payment_method' => 'required|in:stripe,paypal',
        ]);
        // dd(request()->input('payment_method'));

        return $this->paymentGateway->sendPayment($request);
        if ($result['success']) {
            return redirect($result['url']); //stripe url
        }

        return redirect()->route('payment.failed');
    }

    //after payment
    public function callBack(Request $request): \Illuminate\Http\RedirectResponse
    {
        $response = $this->paymentGateway->callBack($request);
        if ($response) {

            return redirect()->route('payment.success');
        }
        return redirect()->route('payment.failed');
    }

    public function webhook(Request $request): \Illuminate\Http\Response
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = env("STRIPE_WEBHOOK_SECRET");

        // 1. تتأكد إن الـ Request جاي من Stripe فعلاً
        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $e) {
            //if fake request
            Log::warning('Invalid Stripe webhook signature');
            return response('Invalid signature', 400);
        }

        // 2. تتعامل مع نوع الـ Event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                if ($session->payment_status === 'paid') {
                    //payment success
                    // update order, send email, etc.
                    Log::info('Payment confirmed', ['session' => $session->id]);
                }
                break;

            case 'payment_intent.payment_failed':
                //failed to pay
                Log::info('Payment failed');
                break;
        }

        // Stripe محتاج response بـ 200 عشان ميبعتش تاني
        return response('OK', 200);
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
