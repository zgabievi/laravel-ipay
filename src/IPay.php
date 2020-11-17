<?php

namespace Zorb\IPay;

use GuzzleHttp\Client;
use Zorb\IPay\Enums\Currency;
use Zorb\IPay\Enums\IndustryType;
use Zorb\IPay\Enums\CaptureMethod;
use GuzzleHttp\Exception\ClientException;

class IPay
{
    /**
     * Redirect to payment link
     *
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

    /**
     * Get redirect url
     *
     * @param \stdClass $response
     * @param string $rel
     * @return string|null
     */
    public function redirectUrl(\stdClass $response, string $rel = 'approve'): ?string
    {
        if (isset($response->links)) {
            $link = collect($response->links)->filter(function ($item) use ($rel) {
                return isset($item->rel) && $item->rel === $rel;
            })->first();

            if (!$link || !isset($link->href)) {
                return back();
            }

            return $link->href;
        }

        return null;
    }

    /**
     * Generate array for purchase unit
     *
     * @param int $amount
     * @param string|null $currency
     * @param string|null $industry_type
     * @return array
     */
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

    /**
     * Generate array for purchase item
     *
     * @param int $product_id
     * @param int $amount
     * @param int $quantity
     * @param string $description
     * @return array
     */
    public function purchaseItem(int $product_id, int $amount, int $quantity = 1, string $description = ''): array
    {
        return [
            'amount' => $amount / 100,
            'description' => $description,
            'quantity' => $quantity,
            'product_id' => $product_id,
        ];
    }

    /**
     * Recurring method to repeat transaction
     *
     * @param string $transaction_id
     * @param string $intent
     * @param string|null $token
     * @param int $order_id
     * @param array $units
     * @param array $items
     * @param string $capture_method
     * @return \stdClass|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function repeat(string $transaction_id, string $intent, int $order_id, array $units, array $items = [], string $token = null, string $capture_method = ''): \stdClass
    {
        return $this->checkout($intent, $order_id, $units, $items, $token, $capture_method, $transaction_id);
    }

    /**
     * Start checkout process
     *
     * @param string $intent
     * @param int $order_id
     * @param array $units
     * @param string|null $token
     * @param array $items
     * @param string $capture_method
     * @param string $transaction_id
     * @return \stdClass|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkout(string $intent, int $order_id, array $units, array $items = [], string $token = null, string $capture_method = '', string $transaction_id = ''): \stdClass
    {
        $url = config('ipay.url') . '/checkout/orders';

        return $this->postRequest($url, [
            'intent' => $intent,
            'redirect_url' => url(config('ipay.redirect_url')),
            'shop_order_id' => $order_id,
            'locale' => config('ipay.language'),
            'show_shop_order_id_on_extract' => true,
            'capture_method' => $capture_method ?: CaptureMethod::Automatic,
            'card_transaction_id' => $transaction_id,
            'purchase_units' => $units,
            'items' => $items,
        ], $token, null, 'json');
    }

    /**
     * Reusable http post request
     *
     * @param string $url
     * @param array $data
     * @param string|null $token
     * @param string|null $authorization
     * @param string $type
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function postRequest(string $url, array $data, string $token = null, string $authorization = null, string $type = 'form_params'): \stdClass
    {
        if (!$authorization) {
            $token = $this->requestToken($token);
        }

        $client = new Client();
        try {
            $params = $type === 'json' ? ['json' => $data] : ['form_params' => $data];
            $response = $client->post($url, array_merge($params, [
                'headers' => [
                    'Authorization' => $authorization ?: 'Bearer ' . $token,
                ],
            ]));
        } catch (ClientException $exception) {
            $error = json_decode($exception->getResponse()->getBody());

            if (!isset($error->error_code)) {
                return $error;
            }

            abort($error->error_code, isset($error->error_message) ? $error->error_message : '');
        }

        return json_decode($response->getBody());
    }

    /**
     * Use or get token for next request
     *
     * @param string|null $token
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function requestToken(string $token = null): string
    {
        if (!$token) {
            $request = self::token();

            if (isset($request->access_token)) {
                return $request->access_token;
            }
        }

        return $token;
    }

    /**
     * Request token for other operations
     *
     * @return \stdClass|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function token()
    {
        $client_id = config('ipay.client_id');
        $secret_key = config('ipay.secret_key');
        $url = config('ipay.url') . '/oauth2/token';
        $authorization = 'Basic ' . base64_encode($client_id . ':' . $secret_key);

        return $this->postRequest($url, [
            'grant_type' => 'client_credentials',
        ], null, $authorization);
    }

    /**
     * Refund some amount back
     *
     * @param string $order_id
     * @param int $amount
     * @param string|null $token
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refund(string $order_id, int $amount, string $token = null): \stdClass
    {
        $url = config('ipay.url') . '/checkout/refund';

        return $this->postRequest($url, [
            'order_id' => $order_id,
            'amount' => $amount / 100,
        ], $token);
    }

    /**
     * Get details of order
     *
     * @param string $order_id
     * @param string|null $token
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function orderDetails(string $order_id, string $token = null): \stdClass
    {
        $url = config('ipay.url') . '/checkout/orders/' . $order_id;

        return $this->getRequest($url, $token);
    }

    /**
     * Reusable http get request
     *
     * @param string $url
     * @param string|null $token
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function getRequest(string $url, string $token = null): \stdClass
    {
        $token = $this->requestToken($token);
        $client = new Client();
        try {
            $response = $client->get($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);
        } catch (ClientException $exception) {
            $error = json_decode($exception->getResponse()->getBody());

            if (!isset($error->error_code)) {
                return $error;
            }

            abort($error->error_code, isset($error->error_message) ? $error->error_message : '');
        }

        return json_decode($response->getBody());
    }

    /**
     * Get status of order
     *
     * @param string $order_id
     * @param string|null $token
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function orderStatus(string $order_id, string $token = null): \stdClass
    {
        $url = config('ipay.url') . '/checkout/orders/status/' . $order_id;

        return $this->getRequest($url, $token);
    }

    /**
     * Get details of payment
     *
     * @param string $order_id
     * @param string|null $token
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function paymentDetails(string $order_id, string $token = null): \stdClass
    {
        $url = config('ipay.url') . '/checkout/payment/' . $order_id;

        return $this->getRequest($url, $token);
    }

    /**
     * Complete pre authorized orders
     *
     * @param string $order_id
     * @param string|null $token
     * @return \stdClass
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function completePreAuth(string $order_id, string $token = null): \stdClass
    {
        $url = config('ipay.url') . '/checkout/payment/pre-auth/complete/' . $order_id;

        return $this->getRequest($url, $token);
    }
}
