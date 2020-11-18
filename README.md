# iPay integration for Laravel

[![Packagist](https://img.shields.io/packagist/v/zgabievi/laravel-ipay.svg)](https://packagist.org/packages/zgabievi/laravel-ipay)
[![Packagist](https://img.shields.io/packagist/dt/zgabievi/laravel-ipay.svg)](https://packagist.org/packages/zgabievi/laravel-ipay)
[![license](https://img.shields.io/github/license/zgabievi/laravel-ipay.svg)](https://packagist.org/packages/zgabievi/laravel-ipay)

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [ENV Variables](#environment-variables)
- [License](#license)

## Installation

To get started, you need to install package:

```shell script
composer require zgabievi/laravel-ipay
```

If your Laravel version is older than **5.5**, then add this to your service providers in *config/app.php*:

```php
'providers' => [
    ...
    Zorb\IPay\IPayServiceProvider::class,
    ...
];
```

You can publish config file using this command:

```shell script
php artisan vendor:publish --provider="Zorb\IPay\IPayServiceProvider"
```

This command will copy config file for you.

## Usage

> All of the responses are *stdClasses*. 
> Errors are handled by laravel **abort** helper. 
> Catch exceptions on your own, if you want to handle them.

**Here are methods provided by this package:**

- [Payment Process](#payment-process) - Initialization of payment process
    - [Generate Token](#generate-token) - *(Optional)* Token will be auto generated if not provided
    - [Checkout](#checkout) - Request checkout order to iPay
    - [Redirect](#redirect) - Redirect user to url that was provided by checkout
- [Recurring](#recurring) - Repeat payment process using saved order id
- [Refund](#refund) - Reversal of transaction
- [Order Details](#order-details) - Check details of order
- [Order Status](#order-status) - Check status of order
- [Payment Details](#payment-details) - Check details of payment
- [Complete Pre-Authentication](#complete-pre-authentication) - Complete pre-authorized order
- [Helpers](#helpers) - Helper methods to generate checkout
    - [Purchase Unit](#purchase-unit) - Helper to generate object for purchase unit
    - [Purchase Item](#purchase-item) - Helper to generate object for purchase item

### Payment Process

#### Generate Token

This step is optional, and if you don't provide token to next request, it will automatically fetch token.

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $response = IPay::token();
    }
}
```

Example `$response:`

```json
{
  "access_token": "eyJraWQiOiIxMDA2IiwiY3R5IjoiYXBwbGljYXRpb25cL2pzb24iLCJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJQdWJsaWMgcGF5bWVudCBBUEkgVjEiLCJhdWQiOiJpUGF5IERlbW8iLCJpc3M",
  "token_type": "Bearer",
  "app_id": "1A2019",
  "expires_in": 1605623557393
}
```

#### Checkout

Generate order in iPay system and get back order details and redirect urls.

```php
use Zorb\IPay\Facades\IPay;
use Zorb\IPay\Enums\Intent;

class PaymentController
{
    public function __invoke()
    {
        $order_id = 1;

        $units = [
          IPay::purchaseUnit(10), // read more about purchaseUnit bellow
        ];

        $items = [
          IPay::purchaseItem(1, 10, 1, 'Item #1'), // read more about purchaseItem bellow
          IPay::purchaseItem(2, 10, 1, 'Item #2'), // read more about purchaseItem bellow
        ];

        // string $intent - 'CAPTURE', 'AUTHORIZE', 'LOAN'
        // int $order_id - Your order id
        // array $units - Purchase units
        // array $items = [] - (optional) Purchase items
        // string $token = null - (optional) JWT Token
        // string $capture_method = 'AUTOMATIC' - (optional) 'AUTOMATIC', 'MANUAL'
        // string $transaction_id = '' - (optional) Transaction id for recurring
        $response = IPay::checkout(Intent::Capture, $order_id, $units, $items);
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;
use Zorb\IPay\Enums\Intent;
use Zorb\IPay\Enums\CaptureMethod;

class PaymentController
{
    public function __invoke()
    {
        $order_id = 1;

        $response = IPay::setIntent(Intent::Capture)
            ->setShopOrder($order_id)
            ->setPurchaseUnits([ IPay::purchaseUnit(10) ])
            ->setItems([
                IPay::purchaseItem(1, 10, 1, 'Item #1'),
                IPay::purchaseItem(2, 10, 1, 'Item #2'),
            ])
            ->setToken('IPAY_JWT_TOKEN')
            ->setCaptureMethod(CaptureMethod::Manual)
            ->setTransaction('IPAY_TRANSACTION_ID')
            ->checkout();
    }
}
```

Example `$response`:

```json
{
  "status": "CREATED",
  "payment_hash": "d7936f718c2b0ec2517a28c9de76966bcbecfe29",
  "links": [
    {
      "href": "https://ipay.ge/opay/api/v1/checkout/orders/899318b1ce0d5885cb7405fe86e3930178ff90be",
      "rel": "self",
      "method": "GET"
    },
    {
      "href": "https://ipay.ge/?order_id=899318b1ce0d5885cb7405fe86e3930178ff90be&locale=ka",
      "rel": "approve",
      "method": "REDIRECT"
    }
  ],
  "order_id": "899318b1ce0d5885cb7405fe86e3930178ff90be"
}
```

#### Redirect

Redirect to payment page which is provided by checkout method.

```php
use Zorb\IPay\Facades\IPay;
use Zorb\IPay\Enums\Intent;
use Zorb\IPay\Enums\CheckoutStatus;

class PaymentController
{
    public function __invoke()
    {
        $order_id = 1;

        $units = [
          IPay::purchaseUnit(10), // read more about purchaseUnit bellow
        ];

        $items = [
          IPay::purchaseItem(1, 10, 1, 'Item #1'), // read more about purchaseItem bellow
          IPay::purchaseItem(2, 10, 1, 'Item #2'), // read more about purchaseItem bellow
        ];

        $response = IPay::checkout(Intent::Capture, $order_id, $units, $items);

        if (isset($response->status) && $response->status === CheckoutStatus::Created) {
            return IPay::redirect($response);
            // IPay::redirectUrl($response); - will be used in some cases, like InertiaJS
        }
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;
use Zorb\IPay\Enums\Intent;
use Zorb\IPay\Enums\CaptureMethod;
use Zorb\IPay\Enums\CheckoutStatus;

class PaymentController
{
    public function __invoke()
    {
        $order_id = 1;

        $response = IPay::setIntent(Intent::Capture)
            ->setShopOrder($order_id)
            ->setPurchaseUnits([ IPay::purchaseUnit(10) ])
            ->setItems([
                IPay::purchaseItem(1, 10, 1, 'Item #1'),
                IPay::purchaseItem(2, 10, 1, 'Item #2'),
            ])
            ->setToken('IPAY_JWT_TOKEN')
            ->setCaptureMethod(CaptureMethod::Manual)
            ->setTransaction('IPAY_TRANSACTION_ID')
            ->checkout();

        if (isset($response->status) && $response->status === CheckoutStatus::Created) {
            return IPay::setResponse($response)->redirect();
            // IPay::setResponse($response)->redirectUrl();
        }
    }
}
```

Redirect method will find redirect link for payment and redirect user to that page.

### Recurring

Recurring process is the same as checkout process. 
You just have to provide transaction id you want to be used for recurring.

```php
use Zorb\IPay\Facades\IPay;
use Zorb\IPay\Enums\Intent;
use Zorb\IPay\Enums\CheckoutStatus;

class PaymentController
{
    public function __invoke(string $trx_id)
    {
        $order_id = 1;
        $transaction_id = '899318B1CE0D5885CB7'; // Transaction id was provided in you callback url 
        
        $units = [
          IPay::purchaseUnit(10), // read more about purchaseUnit bellow
        ];

        $items = [
          IPay::purchaseItem(1, 10, 1, 'Item #1'), // read more about purchaseItem bellow
          IPay::purchaseItem(2, 10, 1, 'Item #2'), // read more about purchaseItem bellow
        ];

        $response = IPay::repeat($transaction_id, Intent::Capture, $order_id, $units, $items);

        if (isset($response->status) && $response->status === CheckoutStatus::Created) {
            return IPay::redirect($response);
        }
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;
use Zorb\IPay\Enums\Intent;
use Zorb\IPay\Enums\CheckoutStatus;

class PaymentController
{
    public function __invoke()
    {
        $order_id = 1;
        $transaction_id = '899318B1CE0D5885CB7';

        $response = IPay::setIntent(Intent::Capture)
            ->setShopOrder($order_id)
            ->setPurchaseUnits([ IPay::purchaseUnit(10) ])
            ->setItems([
                IPay::purchaseItem(1, 10, 1, 'Item #1'),
                IPay::purchaseItem(2, 10, 1, 'Item #2'),
            ])
            ->setTransaction($transaction_id)
            ->repeat();

        if (isset($response->status) && $response->status === CheckoutStatus::Created) {
            return IPay::setResponse($response)->redirect();
        }
    }
}
```

### Refund

In order to refund money you need to have order_id of payment.

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        // string $order_id - Order id provided by checkout process
        // int $amount - Amount you want to refund (in cents)
        // string $token = null - (optional) JWT Token
        $response = IPay::refund($order_id, 10);
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        $response = IPay::setOrder($order_id)
            ->setAmount(10)
            ->refund();
    }
}
```

If response is OK, it means refund process was successful.

### Order Details

In order to get order details you need to have order_id of payment.

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        // string $order_id - Order id provided by checkout process
        // string $token = null - (optional) JWT Token
        $response = IPay::orderDetails($order_id);
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        $response = IPay::setOrder($order_id)
            ->orderDetails();
    }
}
```

Example `$response`:

```json
{
  "id": "6ed105e54e703fb6d2e5b7f68a0face71fea2cc6",
  "status": "PERFORMED",
  "intent": "CAPTURE",
  "payer": {
     "name": null,
     "email_address": null,
     "payer_id": null
  },
  "purchaseUnit": {
     "amount": {
         "value": "0.10",
         "currency_code": "GEL"
     },
     "payee": {
         "addres": "Shartava str., 77",
         "contact": "0322444444",
         "email_address": "support@ipay.ge"
     },
     "payments": [
         {
             "captures": [
                 {
                     "id": "1",
                     "status": "PERFORMED",
                     "amount": {
                         "value": "0.10",
                         "currency_code": "GEL"
                     },
                     "final_capture": "true",
                     "create_time": "Tue Nov 17 19:04:29 GET 2020",
                     "update_time": "Tue Nov 17 19:04:29 GET 2020"
                 },
                 {
                     "id": "2",
                     "status": "PERFORMED",
                     "amount": {
                         "value": "0.10",
                         "currency_code": "GEL"
                     },
                     "final_capture": "true",
                     "create_time": "Tue Nov 17 19:04:29 GET 2020",
                     "update_time": "Tue Nov 17 19:04:29 GET 2020"
                 }
             ]
         }
     ],
     "shop_order_id": "1"
  },
  "createTime": null,
  "updateTime": null,
  "errorHistory": []
}
```

### Order Status

In order to get order status you need to have order_id of payment.

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        // string $order_id - Order id provided by checkout process
        // string $token = null - (optional) JWT Token
        $response = IPay::orderStatus($order_id);
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        $response = IPay::setOrder($order_id)
            ->orderStatus();
    }
}
```

Example `$response`:

```json
{
  "status": "REJECTED"
}
```

### Payment Details

In order to get payment details you need to have order_id of payment.

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        // string $order_id - Order id provided by checkout process
        // string $token = null - (optional) JWT Token
        $response = IPay::paymentDetails($order_id);
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        $response = IPay::setOrder($order_id)
            ->paymentDetails();
    }
}
```

Example `$response`:

```json
{
  "status": "error",
  "pan": null,
  "order_id": "899318b1ce0d5885cb7405fe86e3930178ff90be",
  "pre_auth_status": null,
  "payment_hash": "d7936f718c2b0ec2517a28c9de76966bcbecfe29",
  "ipay_payment_id": "18625",
  "status_description": "REJECTED",
  "shop_order_id": "1",
  "payment_method": "UNKNOWN",
  "card_type": "UNKNOWN",
  "transaction_id": null
}
```

### Complete Pre-Authentication

In order to get complete pre-authorized order you need to have order_id of payment.

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        // string $order_id - Order id provided by checkout process
        // string $token = null - (optional) JWT Token
        $response = IPay::completePreAuth($order_id);
    }
}
```

You can set parameters separately:

```php
use Zorb\IPay\Facades\IPay;

class PaymentController
{
    public function __invoke()
    {
        $order_id = '899318b1ce0d5885cb7405fe86e3930178ff90be';

        $response = IPay::setOrder($order_id)
            ->completePreAuth();
    }
}
```

## All helper methods to set parameters separately
| Method | Description | Possible values | Default | Used in |
| :----- | :---------- | :-------------- | :------ | :------ |
| setResponse | Response from other iPay request | - | - | redirect, redirectUrl |
| setRel | Rel to point correct link from response | approve, self | approve | redirect, redirectUrl |
| setAmount | Money amount in cents (tetris) | - | - | purchaseUnit, purchaseItem, refund |
| setCurrency | Currency of the amount | GEL, USD, EUR | GEL | purchaseUnit |
| setIndustryType | Industry type of purchase unit | ECOMMERCE | ECOMMERCE | purchaseUnit |
| setProduct | Your product id | - | - | purchaseItem |
| setQuantity | Quantity of purchase item | - | - | purchaseItem |
| setDescription | Description of purchase item | - | - | purchaseItem |
| setTransaction | Transaction id for recurring | - | - | checkout, repeat |
| setIntent | Intent for payment | CAPTURE, AUTHORIZE, LOAN | CAPTURE | checkout, repeat |
| setShopOrder | Your order id | - | - | checkout, repeat |
| setPurchaseUnits | One purchase unit as an array | - | - | checkout, repeat |
| setItems | List of items of purchase | - | - | checkout, repeat |
| setToken | JWT Token from iPay | - | - | checkout, repeat, refund, orderDetails, orderStatus, paymentDetails, completePreAuth |
| setCaptureMethod | Method for checkout | AUTOMATIC, MANUAL | AUTOMATIC | checkout, repeat |
| setOrder | Order id from iPay responses | - | - | refund, orderDetails, orderStatus, paymentDetails, completePreAuth |

## Environment Variables

| Key | Meaning | Type | Default |
| --- | --- | :---: | --- |
| IPAY_DEBUG | This value decides to log or not to log requests | bool | false |
| IPAY_URL | Payment url from Bank of Georgia | string | https://ipay.ge/opay/api/v1 |
| IPAY_REDIRECT_URL | Callback url where will be redirected after a success/failure payment | string | https://website.com/payments/redirect |
| IPAY_CLIENT_ID | Client ID provided by Bank of Georgia | string |  |
| IPAY_LANGUAGE | Default language for Bank of Georgia payment | string | ka |
| IPAY_SECRET_KEY | Secret key provided by Bank of Georgia | string |  |

## License

[zgabievi/laravel-ipay](https://github.com/zgabievi/laravel-ipay) is licensed under a [MIT License](https://github.com/zgabievi/laravel-ipay/blob/master/LICENSE).
