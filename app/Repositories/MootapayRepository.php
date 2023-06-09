<?php
namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class MootapayRepository
{
    public static function getUrl()
    {
        return config("mootapay.debug") == true ? config('mootapay.debug_url') : config('mootapay.url');
    }

    public static function getToken()
    {
        return config("mootapay.token");
    }

    public static function getMerchantId()
    {
        return config("mootapay.merchant_id");
    }

    public static function getCallbackURL()
    {
        return env("APP_URL") . config('mootapay.callback_url');
    }

    public static function getMaxUniqueCode()
    {
        return config("mootapay.max_unique_code");
    }

    public static function payment_method()
    {
        $response = Http::withoutVerifying()
        ->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . self::getToken()
        ])
        ->get(self::getUrl() . 'payment-methods/' . self::getMerchantId(), [
            'per_page' => 100,
        ])
        ->json();

        return $response;
    }

    public static function transaction($data)
    {
        $input = [
            'invoice_number' => $data['invoice_number'],
            'merchant_id' => self::getMerchantId(),
            'amount' => $data['amount'],
            'payment_method_id' => $data['payment_method_id'],
            'type' => 'payment',
            'callback_url' => self::getCallbackURL(),
            // 'expired_date' => Carbon::parse($data['expired_date'])->format('Y-m-d H:i:00'),
            'increase_total_from_unique_code' => 1,
            'customer' => [
                'name' => $data['customer']['name'],
                'email' => $data['customer']['email'],
                'phone' => $data['customer']['phone'],
            ],
            'items' => $data['items'],
            'with_unique_code' => 1,
            'unique_code' => $data['unique_code'],
            // 'start_unique_code' => 1,
            // 'end_unique_code' => self::getMaxUniqueCode(),
        ];

        
        $response = Http::withoutVerifying()
        ->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . env("MOOTAPAY_TOKEN")
        ])
        ->post(self::getUrl() . 'transaction', $input);

        return $response;
    }

    public static function ewallet_request_payment($motapay_trx_id)
    {
        $response = Http::withoutVerifying()
        ->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'pplication/json',
            'Authorization' => 'Bearer ' . env("MOOTAPAY_TOKEN")
        ])
        ->post(self::getUrl() . 'transaction/ewallet-request-payment/' . $motapay_trx_id);

        return $response;
    }

    public static function transaction_canceled($motapay_trx_id)
    {
        $response = Http::withoutVerifying()
        ->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'pplication/json',
            'Authorization' => 'Bearer ' . env("MOOTAPAY_TOKEN")
        ])
        ->post(self::getUrl() . 'transaction/cancel/' . $motapay_trx_id);

        return $response;
    }
}