<?php

namespace Zorb\IPay;

use Zorb\IPay\Enums\Intent;
use Zorb\IPay\Enums\Currency;
use Zorb\IPay\Enums\IndustryType;
use Zorb\IPay\Enums\CaptureMethod;
use Illuminate\Http\RedirectResponse;
use Zorb\IPay\Contracts\IPay as IPayContract;

class IPay
{
    /**
     * @var \stdClass
     */
    protected $response;

    /**
     * @var string
     */
    protected $rel = 'approve';

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currency = Currency::GEL;

    /**
     * @var string
     */
    protected $industry_type = IndustryType::Ecommerce;

    /**
     * @var int
     */
    protected $product_id;

    /**
     * @var int
     */
    protected $quantity = 1;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $txn_id = '';

    /**
     * @var string
     */
    protected $intent = Intent::Capture;

    /**
     * @var string|int
     */
    protected $shop_order_id;

    /**
     * @var array
     */
    protected $purchase_units = [];

    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $order_id;

    /**
     * @var string
     */
    protected $capture_method = CaptureMethod::Automatic;

    /**
     * @var IPayContract
     */
    protected $ipay;

    /**
     * IPay constructor.
     * @param IPayContract $ipay
     */
    public function __construct(IPayContract $ipay)
    {
        $this->ipay = $ipay;
    }

    /**
     * @param \stdClass $response
     * @return $this
     */
    public function setResponse(\stdClass $response): self
    {
        $this->response = $response;
        return $this;
    }

    /**
     * @param string $rel
     * @return $this
     */
    public function setRel(string $rel): self
    {
        $this->rel = $rel;
        return $this;
    }

    /**
     * @param int $amount
     * @return $this
     */
    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @param string $industry_type
     * @return $this
     */
    public function setIndustryType(string $industry_type): self
    {
        $this->industry_type = $industry_type;
        return $this;
    }

    /**
     * @param int $product_id
     * @return $this
     */
    public function setProduct(int $product_id): self
    {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     * @param int $quantity
     * @return $this
     */
    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string $txn_id
     * @return $this
     */
    public function setTransaction(string $txn_id): self
    {
        $this->txn_id = $txn_id;
        return $this;
    }

    /**
     * @param string $intent
     * @return $this
     */
    public function setIntent(string $intent): self
    {
        $this->intent = $intent;
        return $this;
    }

    /**
     * @param string|int $shop_order_id
     * @return $this
     */
    public function setShopOrder($shop_order_id): self
    {
        $this->shop_order_id = $shop_order_id;
        return $this;
    }

    /**
     * @param array $units
     * @return $this
     */
    public function setPurchaseUnits(array $units = []): self
    {
        $this->purchase_units = $units;
        return $this;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function setItems(array $items = []): self
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): self
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param string $capture_method
     * @return $this
     */
    public function setCaptureMethod(string $capture_method): self
    {
        $this->capture_method = $capture_method;
        return $this;
    }

    /**
     * @param string $order_id
     * @return $this
     */
    public function setOrder(string $order_id): self
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param IPayContract $ipay
     * @param \stdClass|null $response
     * @param string|null $rel
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(\stdClass $response = null, string $rel = null): RedirectResponse
    {
        return $this->ipay->redirect(
            $response ?: $this->response,
            $rel ?: $this->rel
        );
    }

    /**
     * @param IPayContract $ipay
     * @param \stdClass|null $response
     * @param string|null $rel
     * @return string|null
     */
    public function redirectUrl(\stdClass $response = null, string $rel = null): ?string
    {
        return $this->ipay->redirectUrl(
            $response ?: $this->response,
            $rel ?: $this->rel
        );
    }

    /**
     * @param IPayContract $ipay
     * @param int|null $amount
     * @param string|null $currency
     * @param string|null $industry_type
     * @return array
     */
    public function purchaseUnit(int $amount = null, string $currency = null, string $industry_type = null): array
    {
        return $this->ipay->purchaseUnit(
            $amount ?: $this->amount,
            $currency ?: $this->currency,
            $industry_type ?: $this->industry_type
        );
    }

    /**
     * @param IPayContract $ipay
     * @param int|null $product_id
     * @param int|null $amount
     * @param int|null $quantity
     * @param string|null $description
     * @return array
     */
    public function purchaseItem(int $product_id = null, int $amount = null, int $quantity = null, string $description = null): array
    {
        return $this->ipay->purchaseItem(
            $product_id ?: $this->product_id,
            $amount ?: $this->amount,
            $quantity ?: $this->quantity,
            $description ?: $this->description
        );
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $txn_id
     * @param string|null $intent
     * @param string|int|null $shop_order_id
     * @param array|null $purchase_units
     * @param array|null $items
     * @param string|null $token
     * @param string|null $capture_method
     * @return \stdClass|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function repeat(string $txn_id = null, string $intent = null, $shop_order_id = null, array $purchase_units = null, array $items = null, string $token = null, string $capture_method = null): ?\stdClass
    {
        return $this->ipay->repeat(
            $txn_id ?: $this->txn_id,
            $intent ?: $this->intent,
            $shop_order_id ?: $this->shop_order_id,
            $purchase_units ?: $this->purchase_units,
            $items ?: $this->items,
            $token ?: $this->token,
            $capture_method ?: $this->capture_method
        );
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $intent
     * @param null $shop_order_id
     * @param array|null $purchase_units
     * @param array|null $items
     * @param string|null $token
     * @param string|null $capture_method
     * @param string|null $txn_id
     * @return \stdClass|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function checkout(string $intent = null, $shop_order_id = null, array $purchase_units = null, array $items = null, string $token = null, string $capture_method = null, string $txn_id = null): ?\stdClass
    {
        return $this->ipay->checkout(
            $intent ?: $this->intent,
            $shop_order_id ?: $this->shop_order_id,
            $purchase_units ?: $this->purchase_units,
            $items ?: $this->items,
            $token ?: $this->token,
            $capture_method ?: $this->capture_method,
            $txn_id ?: $this->txn_id
        );
    }

    /**
     * @return \stdClass|void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function token(): ?\stdClass
    {
        return $this->ipay->token();
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $order_id
     * @param int|null $amount
     * @param string|null $token
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function refund(string $order_id = null, int $amount = null, string $token = null): ?\stdClass
    {
        return $this->ipay->refund(
            $order_id ?: $this->order_id,
            $amount ?: $this->amount,
            $token ?: $this->token
        );
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $order_id
     * @param string|null $token
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function orderDetails(string $order_id = null, string $token = null): ?\stdClass
    {
        return $this->ipay->orderDetails(
            $order_id ?: $this->order_id,
            $token ?: $this->token
        );
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $order_id
     * @param string|null $token
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function orderStatus(string $order_id = null, string $token = null): ?\stdClass
    {
        return $this->ipay->orderStatus(
            $order_id ?: $this->order_id,
            $token ?: $this->token
        );
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $order_id
     * @param string|null $token
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function paymentDetails(string $order_id = null, string $token = null): ?\stdClass
    {
        return $this->ipay->paymentDetails(
            $order_id ?: $this->order_id,
            $token ?: $this->token
        );
    }

    /**
     * @param IPayContract $ipay
     * @param string|null $order_id
     * @param string|null $token
     * @return \stdClass|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function completePreAuth(string $order_id = null, string $token = null): ?\stdClass
    {
        return $this->ipay->completePreAuth(
            $order_id ?: $this->order_id,
            $token ?: $this->token
        );
    }
}
