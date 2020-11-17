<?php

namespace Zorb\IPay;

use GuzzleHttp\Client;
use Zorb\IPay\Enums\Currency;
use Zorb\IPay\Enums\IndustryType;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;

class IPay
{
    /**
     * Request token for other operations
     *
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function token(): \stdClass
    {
        $client_id = config('ipay.client_id');
        $secret_key = config('ipay.secret_key');
        $url = config('ipay.url') . '/oauth2/token';

        $client = new Client();
        try {
            $response = $client->post($url, [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ],
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $secret_key),
                ],
            ]);
        } catch (ClientException $exception) {
            Log::error($exception);
            return json_decode($exception->getResponse()->getBody());
        }

        return json_decode($response->getBody());
    }

    /**
     * Start checkout process
     *
     * @param string $intent
     * @param string $token
     * @param int $order_id
     * @param array $units
     * @param array $items
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkout(string $intent, string $token, int $order_id, array $units, array $items = []): \stdClass
    {
        $url = config('ipay.url') . '/checkout/orders';

        $client = new Client();
        try {
            $response = $client->post($url, [
                'json' => [
                    'intent' => $intent,
                    'redirect_url' => config('ipay.payment_callback_url'),
                    'shop_order_id' => $order_id,
                    'locale' => config('ipay.language'),
                    'show_shop_order_id_on_extract' => true,
                    'purchase_units' => $units,
                    'items' => $items,
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
        } catch (ClientException $exception) {
            Log::error($exception);
            return json_decode($exception->getResponse()->getBody());
        }

        return json_decode($response->getBody());
    }

    /**
     * @param \stdClass $response
     * @param string $rel
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(\stdClass $response, string $rel = 'approve'): \Illuminate\Http\RedirectResponse
    {
        if (isset($response->links)) {
            $link = collect($response->links)->filter(function ($item) use ($rel) {
                return isset($item->rel) && $item->rel === $rel;
            })->first();

            if (!$link || !isset($link->href)) {
                return back();
            }

            return redirect($link->href);
        }

        return back();
    }

    //
    public function purchaseUnit(int $amount, string $currency = null, string $industry_type = null): array
    {
        return [
            'amount' => [
                'currency_code' => $currency ?: Currency::GEL,
                'value' => $amount / 100,
            ],
            'industry_type' => $industry_type ?: IndustryType::Ecommerce,
        ];
    }

    // refund
    // repeat
    // orderDetails
    // orderStatus
    // paymentDetails
    // completePreAuth
}
