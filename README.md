![face_post_tbc_payment](https://cloud.githubusercontent.com/assets/8479569/7435079/5aebe7cc-f051-11e4-8ee1-d85b0e36a8a9.jpg)

# Tbcpay - online payments php SDK

Making credit card payments work on your website is a pain!  
So to make everyones' life a bit easier we are sharing the  
Georgian TBC payment gateway sdk on GitHub.  

Have fun :)

### SMS / DMS

There are two types of transaction within this system: **SMS** and **DMS**.

SMS - is direct payment, where money is charged in 1 event.  
DMS - is delayed. Requires two events: first event blocks money on the card, second event takes money (second event can be carried out when product is shipped to the customer for example).

Every 24 hours, the merchant must send the close the business day request to bank server.

### Install

It is possible to simply include the library (check examples dir), but you should use composer instead.

run in terminal:

```
composer require wearede/tbcpay-php:dev-master
```

or

Add in composer.json
```
"require": {
		"wearede/tbcpay-php": "dev-master"
}
```

and run in terminal
```
composer update wearede/tbcpay-php
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

Method name | Return Value | Description
--- | --- | ---
**sms_start_transaction()** | `array( 'TRANSACTION_ID' => 'AX23x...' )` | 
**dms_start_authorization()** |
**dms_make_transaction( $trans_id )** |
**get_transaction_result( $trans_id )** |
**reverse_transaction( $trans_id, $amount = '', $suspected_fraud = '' )** |
**refund_transaction( $trans_id )** |
**credit_transaction( $trans_id, $amount = '' )** |
**close_day()** |

### Instructions

1. Ask bank to generate certificate
2. Tell them your server IP so they whitelist it
3. create `example.com/ok.php` and `example.com/fail.php` urls and tell them about it
   * ok url - is used for redirecting back user in almost all situations
   * fail ulr - is used for redirecting back user when technical error occurs

1. `start.example` Here we start our process. We call bank servers using `sms_start_transaction()` and get `$trans_id` in return.
   * We use returned $trans_id to redirect user to a bank page, where credit card info can be entered.
   * After user fills out card info he is thrown back to our `ok.example` url on our server.
2. Take a look at `ok.example` We get `$trans_id` back from bank, and we plug that in `get_transaction_result( $trans_id )`
3. `get_transaction_result( $trans_id )` tells us if transaction was success or not. `array( 'RESULT' => 'OK' )` for example is success message, transaction went through.

### TODO

Help needed with [#3](/../../issues/3) !

### API

###### ECOMM Integrated Merchant Agent

This API besides **TBCBANK** should support **BANK OF GEORGIA**, **LIBERTY BANK**, and some other minor banks. But since I've only tested this with *TBCBANK*, I'm releasing this as such.

### SSL

TBC bank provides SSL certificate in **.p12** format  
To Trasnform it into .pem format Run: `openssl pkcs12 -in *.p12 -out tbcpay.pem`

**!** Move cert directory somewhere non accessible to web server as a security meassure.  
