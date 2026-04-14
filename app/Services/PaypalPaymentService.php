<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Http\Request;

class PaypalPaymentService extends BasePaymentService implements PaymentGatewayInterface
{
    public function sendPayment(Request $request): array
    {
        $data     = $this->formatData($request);
        $response = $this->buildRequest('POST', '/v1/checkout/sessions', $data, 'form_params');

        if ($response['success']) {
            return ['success' => true, 'url' => $response['data']['url']];
        }

        return ['success' => false, 'url' => route('payment.failed')];
    }

    public function formatData(){

    }

    public function callBack(Request $request): bool
    {
        $session_id = $request->get('session_id');
        $response   = $this->buildRequest('GET', '/v1/checkout/sessions/' . $session_id);

        return $response['success'] && $response['data']['payment_status'] === 'paid';
    }
}
