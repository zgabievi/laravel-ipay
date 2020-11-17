# BOG Payment (iPay) integration for Laravel

[![Packagist](https://img.shields.io/packagist/v/zgabievi/laravel-ipay.svg)](https://packagist.org/packages/zgabievi/laravel-ipay)
[![Packagist](https://img.shields.io/packagist/dt/zgabievi/laravel-ipay.svg)](https://packagist.org/packages/zgabievi/laravel-ipay)
[![license](https://img.shields.io/github/license/zgabievi/laravel-ipay.svg)](https://packagist.org/packages/zgabievi/laravel-ipay)

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
    - [Payment](#payment)
    - [Recurring](#recurring)
    - [Refund](#refund)
- [Additional Information](#additional-information)
- [Environment Variables](#environment-variables)
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

- [Payment](#payment)
- [Recurring](#recurring)
- [Refund](#refund)

### Payment

Default process has several to be completed:

1. Redirect to card details page
2. Bank will check payment details on your route
3. Bank will register payment details on your route

#### Step #1

On this step you should redirect user to card details page

```php
use Zorb\IPay\Facades\IPay;

class PaymentController extends Controller
{
    //
    public function __invoke()
    {
        return IPay::redirect([
            'order_id' => 1,
        ], false);
    }
}
```

Pass any parameter you want to recieve on check and register step as a first value. (default: `[]`)

Second value is boolean and defines if you want to pre-authorize payment, block amount. (default: `false`)

#### Step #2

On this step bank will check that you are ready to accept payment.

This process is called **PaymentAvail**.

```php
use Zorb\IPay\Facades\IPay;

class PaymentCheckController extends Controller
{
    //
    public function __invoke()
    {
        // chek that http authentication is correct
        IPay::checkAuth();

        // check if you are getting request from allowed ip
        IPay::checkIpAllowed();

        // check if you can find order with provided id
        $order_id = IPay::getParam('o.order_id');
        $order = Order::find($order_id);
    
        if (!$order) {
            IPay::sendError('check', 'Order couldn\'t be found with provided id');
        }

        $trx_id = IPay::getParam('trx_id');

        // send success response
        IPay::sendSuccess('check', [
            'amount' => $order->amount,
            'short_desc' => $order->short_desc,
            'long_desc' => $order->long_desc,
            'trx_id' => $trx_id,
            'account_id' => config('ipay.account_id'),
            'currency' => config('ipay.currency'),
        ]);
    }
}
```

*Check [request parameters](#parameters-of-check-request) here*

#### Step #3

On this step bank will provide details of the payment.

This process is called **RegisterPayment**.

```php
use Zorb\IPay\Facades\IPay;

class PaymentRegisterController extends Controller
{
    //
    public function __invoke()
    {
        // chek that http authentication is correct
        IPay::checkAuth();

        // check if you are getting request from allowed ip
        IPay::checkIpAllowed();

        // check if provided signature matches certificate
        IPay::checkSignature('register');

        // check if you can find order with provided id
        $order_id = IPay::getParam('o.order_id');
        $order = Order::find($order_id);
    
        if (!$order) {
            IPay::sendError('check', 'Order couldn\'t be found with provided id');
        }

        $trx_id = IPay::getParam('trx_id');
        $result_code = IPay::getParam('result_code');

        if (empty($result_code)) {
            IPay::sendError('register', 'Result code has not been provided');
        }
    
        if ((int)$result_code === 1) {
            // payment has been succeeded
        } else {
            // payment has been failed
        }

        // send success response
        IPay::sendSuccess('register');
    }
}
```

*Check [request parameters](#parameters-of-register-request) here*

### Recurring

Recurring process is the same as default process. Difference is that user doesn't have to fill card details again.

1. Request will be sent to bank to start recurring process
2. Bank will check payment details on your route
3. Bank will register payment details on your route

```php
use Zorb\IPay\Facades\IPay;

class PaymentRecurringController extends Controller
{
    //
    public function __invoke(string $trx_id)
    {
        return IPay::repeat($trx_id, [
            'recurring' => true,
        ]);
    }
}
```

In your check and register controllers you can catch `IPay::getParam('o.recurring')` parameter and now you will know that this process is from recurring request.

### Refund

In order to refund money you need to have trx_id of payment and rrn.

```php
use Zorb\IPay\Facades\IPay;

class PaymentRefundController extends Controller
{
    //
    public function __invoke(string $trx_id, string $rrn)
    {
        $result = IPay::refund($trx_id, $rrn);

        if ((int)$result->code === 1) {
            // refund process succeeded
        } else {
            // refund process failed
        }
    }
}
```

*Check [result parameters](#refund-result) here*

## Additional Information

### Parameters of check request

| Param | Meaning | 
| --- | --- |
| merch_id | Merchant ID of your shop *(length 32)* |
| trx_id | Transaction ID of current payment *(length 32)* |
| lang_code | ISO 639 language codes *(EN/KA/RU)* |
| o.* | Additional parameters provided by you on redirect |
| ts | Payment creation time *(yyyyMMdd HH:mm:ss)* |

### Parameters of register request

| Param | Meaning | 
| --- | --- |
| merch_id | Merchant ID of your shop *(length 32)* |
| trx_id | Transaction ID of current payment *(length 32)* |
| merchant_trx | Transaction ID, if it is provided by shop |
| result_code | Result code of the payment *(1 - Success, 2 - Fail)* |
| amount | Integer value of payment amount |
| p.rrn | RRN of payment |
| p.transmissionDateTime | Authorization request date and time *(MMddHHmmss)* |
| o.* | Additional parameters provided by you on redirect |
| m.* | Parameters provided on first phase of payment process |
| ts | Payment creation time *(yyyyMMdd HH:mm:ss)* |
| signature | Base64 encoded signature to compare with certificate |
| p.cardholder | Cardholder name |
| p.authcode | Authentication code from processing *(ISO 8583 Field 38)* |
| p.maskedPan | Masked card number |
| p.isFullyAuthenticated | Result of 3D authentication *(Y - Success, N - Fail)* |
| p.storage.card.ref | Parameters of the card |
| p.storage.card.expDt | Expiration date of card *(YYMM)* |
| p.storage.card.recurrent | Authorization status of recurring process *(Y - Recurring is possible, N - Reucrring is not possive)* |
| p.storage.card.registered | Card registration status *(Y - Card has been registered, N - Card was not registered)* |
| ext_result_code | Additional information about result code |

### Refund result

| Key | Meaning  | 
| --- | --- |
| code | Numeric value for result code |
| desc | Description of payment result | 

### Extended result codes

| Code | Number | Key | Description |
| --- | :---: | --- | --- |
| OK | 0 | SUCCESS | The payment was completed successfully, the result was successfully communicated to the store |
| PREAUTHORIZE_OK | 3 | SUCCESS | The blocking of the amount was completed successfully, the result was successfully reported to the store |
| ONLINE_RP_FAILED | 1 | SEMI-SUCCESSFUL | The payment was completed successfully, but the result was not successfully delivered to the store in Online mode |
| CPA_REJECTED | 2 | FAILED | The store refused to process the payment in the first phase |
| CPA_NONE | 21 | FAILED | In-store payment verification was not performed. Perhaps the store or billing has been blocked |
| CPA_FAILED | 4 | FAILED | An error occurred while interacting with the store during the first phase |
| CLIENT_LOST | 53 | FAILED | The transaction timed out because the user refused to continue the payment for some reason |
| USER_CANCEL | 54 | FAILED | The user deliberately chose to cancel the payment |
| PAYMENT_REJECTED | -2 | FAILED | Refusal to process payment |
| PAYMENT_FAILED | -3 | FAILED | Error during payment |
| PAYMENT_REVERSED | -4 | FAILED | A successful payment was canceled on the initiative of the store at the registerPayment stage. The use of this code is for the future |
| CS_NOTSUPPORTED | 11 | FAILED | The option of saving cards is not available for this store, payment for previously saved cards, recurring payments or currency is not supported for recurrent checks |
| CS_LIMITEXCEEDED | 12 | FAILED | The amount in the response of the merchant PaymentAvail Response exceeds maxCardRegAmount |
| CS_CARDNOTFOUND | 13 | FAILED | PaymentAvail Response received card details expired, card is not registered (received cardId is not available for this store) or card does not support recurring payments |

## Environment Variables

| Key | Meaning | Type | Default |
| --- | --- | :---: | --- |
| BOG_PAYMENT_DEBUG | This value decides to log or not to log requests | bool | false |
| BOG_PAYMENT_URL | Payment url from Bank of Georgia | string | https://3dacq.georgiancard.ge/payment/start.wsm |
| BOG_PAYMENT_MERCHANT_ID | Merchant ID from Bank of Georgia | string |  |
| BOG_PAYMENT_PAGE_ID | Page ID from Bank of Georgia | string |  |
| BOG_PAYMENT_ACCOUNT_ID | Account ID from Bank of Georgia | string |  |
| BOG_PAYMENT_SHOP_NAME | Shop Name for Bank of Georgia payment | string | APP_NAME |
| BOG_PAYMENT_SUCCESS_URL | Success callback url for Bank of Georgia | string | /payments/success | 
| BOG_PAYMENT_FAIL_URL | Fail callback url for Bank of Georgia | string | /payments/fail | 
| BOG_PAYMENT_CURRENCY | Default currency for Bank of Georgia payment | int | 981 | 
| BOG_PAYMENT_LANGUAGE | Default language for Bank of Georgia payment | string | KA |
| BOG_PAYMENT_HTTP_AUTH_USER | HTTP Authentication username for Bank of Georgia payment | string |  |
| BOG_PAYMENT_HTTP_AUTH_PASS | HTTP Authentication password for Bank of Georgia payment | string |  |
| BOG_PAYMENT_ALLOWED_IPS | Comma separated list of allowed ips to access your system from Bank of Georgia | string | 213.131.36.62 |
| BOG_PAYMENT_CERTIFICATE_PATH | Bank of Georgia certificate path from storage | string | app/bog.cer |
| BOG_PAYMENT_REFUND_API_PASS | Bank of Georgia api password for refund operation | string |  |

## License

[zgabievi/laravel-ipay](https://github.com/zgabievi/laravel-ipay) is licensed under a [MIT License](https://github.com/zgabievi/laravel-ipay/blob/master/LICENSE).
