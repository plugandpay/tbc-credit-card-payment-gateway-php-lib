![face_post_tbc_payment](https://cloud.githubusercontent.com/assets/8479569/7435079/5aebe7cc-f051-11e4-8ee1-d85b0e36a8a9.jpg)

# Tbcpay - online payments php SDK

Making credit card payments work on your website is a pain!  
So to make everyones' life a bit easier we are sharing the  
Georgian TBC payment gateway sdk on GitHub.

### SMS / DMS

There are two types of transaction within this system: **SMS** and **DMS**.

SMS - is direct payment, where money is charged in 1 event.  
DMS - is delayed. Requires two events: first event blocks money on the card, second event takes money (second event can be carried out when product is shipped to the customer for example).

Every 24 hours, the merchant must send the close the business day request to TBC.

### Install

It is possible to simply include the library (see example), but you should use composer instead.

run in terminal:

```
composer require wearede/tbcpay-php:dev-master#0.9.0
```

### Autoload

```
require __DIR__ . '/vendor/autoload.php';
```

### Creating object

```php
$Payment = new TbcPayProcessor( '/cert/tbcpay.pem', '0DhJ4AdxVuPZmz3F4y', $_SERVER['REMOTE_ADDR'] );
```
1. Certificate absolute path
2. Certificate passphrase
3. Client ip address

### Methods

Method name
--- |
**sms_start_transaction()**
**dms_start_authorization()**
**dms_make_transaction($trans_id)**
**get_transaction_result($trans_id)**
**reverse_transaction($trans_id, $amount = '', $suspected_fraud = '')**
**refund_transaction($trans_id)**
**credit_transaction($trans_id, $amount = '')**
**close_day()**

### Getting started

You can start by looking at the [fully functional working prototype](https://github.com/wearede/tbcpay-php-example).

### Instructions

For a [simple example](https://github.com/wearede/tbcpay-php/tree/master/example).

#### Chores
1. Ask TBC to generate a certificate
2. Tell TBC your server IP so they can whitelist it
3. create `example.com/ok.php` and `example.com/fail.php` urls and communicate these to TBC
   * ok url - is used for redirecting back user in almost all situations (even when card has insuficient funds and transaction fails!)
   * fail url - is used for redirecting back user when technical error occurs (very rare)

#### Flow
1. `start.example` Here we start our process. We call TBC servers using `sms_start_transaction()` and get `$trans_id` in return.
   * We use returned $trans_id to redirect user to a TBC page, where credit card info can be entered.
   * After user fills out card info he is thrown back to our `ok.example` url on our server.
2. Take a look at `ok.example` We get `$trans_id` back from TBC, and we plug that in `get_transaction_result($trans_id)`
3. `get_transaction_result($trans_id)` tells us if transaction was success or not. `array('RESULT' => 'OK')` for example is success message, transaction went through.

### Common issues

- TBC bank provides SSL certificate in **.p12** format, we need it in .pem format, to transform use command: `openssl pkcs12 -in *.p12 -out tbcpay.pem`
- Move cert directory somewhere non public as a security meassure. Give it correct permissions so that php can read it.
