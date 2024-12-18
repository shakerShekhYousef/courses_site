<?php

namespace App\PaymentChannels\Drivers\Paymob;

use App\Models\Order;
use App\Models\PaymentChannel;
use App\PaymentChannels\BasePaymentChannel;
use App\PaymentChannels\IChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Paytabscom\Laravel_paytabs\Facades\paypage;


class Channel extends BasePaymentChannel implements IChannel
{
    protected $currency;
    protected $test_mode;
    protected $order_session_key;
    protected $api_key;
    protected $integration_id;
    protected $identifier;


    protected array $credentialItems = [
        'api_key',
        'integration_id',
        'identifier',
    ];

    // https://github.com/ctf0/laravel-paymob

    public function __construct(PaymentChannel $paymentChannel)
    {
        $this->currency = currency();
        $this->setCredentialItems($paymentChannel);
        $this->order_session_key = 'paymob.payments.order_id';
    }

    private function handleConfigs()
    {

    }


    public function paymentRequest(Order $order)
    {
        $this->handleConfigs();
        $generalSettings = getGeneralSettings();
        $user = $order->user;

        try {
            $token = config('paymob.public_key');
            $paymentToken = $this->getPaymentToken($order, $token);
            header('Location: https://uae.paymob.com/unifiedcheckout/?publicKey=' . $token . '&clientSecret=' . $paymentToken);
            exit(); // Ensure script stops executing after redirect
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    private function makeCallbackUrl()
    {
        $callbackUrl = route('payment_verify', [
            'gateway' => 'Paytabs'
        ]);

        return $callbackUrl;
    }

    public function verify(Request $request)
    {
        $this->handleConfigs();

        $data = $request->all();
        

        $order = Order::where('id', $orderId)
            ->where('user_id', $userId)
            ->first();

        if (!empty($order)) {
            $orderStatus = Order::$fail;
            Auth::loginUsingId($userId);

            if ($response->isSuccess()) {
                $orderStatus = Order::$paying;
            }

            $order->update([
                'status' => $orderStatus,
            ]);
        }

        return $order;
    }

    private function getPrice($order)
    {
        return $this->makeAmountByCurrency($order->total_amount, $this->currency);
    }

    public function getToken() {
        $response = Http::post('https://accept.paymobsolutions.com/api/auth/tokens', [
            'api_key' => $this->api_key
        ]);
        return $response->object()->token;
    }

    private function createPaymobOrder($order, $token)
    {
        $items = [];

        foreach ($order->orderItems as $orderItem) {
            $items[] = [
                'title' => 'Order ' . $orderItem->id,
            ];
        }

        $data = [
            "auth_token" => $token,
            "merchant_order_id" => $order->id,
            "delivery_needed" => "false",
            "amount_cents" => $this->getPrice($order),
            "currency" => $this->currency,
            "items" => $items,
        ];

        $response = Http::post('https://accept.paymobsolutions.com/api/ecommerce/orders', $data);

        return $response->object();
    }

    private function getPaymentToken($order, $token)
    {
        $user = $order->user;
        $name = explode(' ', $user->full_name);
        $nameCount = count($name);
        $billingData = [
            "first_name" => $nameCount ? $name[0] : '',
            "last_name" => ($nameCount >= 2) ? $name[$nameCount - 1] : '-',
            "email" => $user->email,
            "phone_number" => "0555555555",
            "apartment" => "NA",
            "floor" => "NA",
            "street" => "NA",
            "building" => "NA",
            "shipping_method" => "NA",
            "postal_code" => "NA",
            "city" => "NA",
            "country" => "NA",
            "state" => "NA"
        ];

        // Ensure amount is in the smallest currency unit (e.g., AED: 1.00 -> 100)
        $amount = intval($this->getPrice($order) * 100); // Multiply by 100 for smallest unit
        $callbackUrl = route('payment_verify', [
            'gateway' => 'Paymob'
        ]);
        $data = [
            "amount" => $amount, // Pass amount as an integer
            "currency" => "AED",
            "payment_methods" => [49485],
            "items" => $this->formatOrderItems($order), // Format order items as a list
            "billing_data" => $billingData,
            "redirection_url"=>$callbackUrl
        ];
        
        $private_key = config('paymob.private_key');
        $response = Http::withHeaders([
                'Authorization' =>'Token '.$private_key,
            ]
        )->post('https://uae.paymob.com/v1/intention/', $data);
        
        return $response->object()->client_secret;
    }

    private function formatOrderItems($order)
    {
        // Assuming you have a collection of items in the order, format them as an array
        return [
            [
                'name' => "item_name", // Replace with actual item name
                'amount' => intval($this->getPrice($order) * 100), // Convert price to smallest unit
                'description' => 'item_description', // Replace with actual item description
                'quantity' => 1 // Replace with actual quantity
            ]
        ]; // Return a list of item dictionaries
    }



}
