![face_post_tbc_payment](https://cloud.githubusercontent.com/assets/8479569/7435079/5aebe7cc-f051-11e4-8ee1-d85b0e36a8a9.jpg)

# Tbcpay - online payments php SDK

Making payment work on your website is a pain!  
So to make everyones' life a bit easier we are sharing the  
Georgian TBC payment gateway sdk on GitHub.  

Have fun :)

### SMS / DMS

There are two types of transaction within this system: **SMS** and **DMS**.

SMS - is direct payment, where money is charged in 1 event.  
DMS - is delayed. Requires two events: first event blocks money on the card, second event takes this money. Second event can be carried out when product is shipped to the customer for example.

Every 24 hours, the merchant must send a request to server to close the business day.

### Creating object

```php
$Payment = new tbcpay( 'https://securepay.ufc.ge:18443/ecomm2/MerchantHandler', __DIR__ . '/cert/tbcpay.pem', '0DhJ4AdxVuPZmz3F4y', $_SERVER['REMOTE_ADDR'], 1, 981, 'product_id:1234567', 'GE' );
```

1. Submit url
2. Certificate absolute path
3. Certificate passphrase
4. Client ip address
5. Amount 1 = 0.01 gel
6. 981 = GEL http://en.wikipedia.org/wiki/ISO_4217
7. Description
8. Interface language

### Methods

Method name | Return Value | Description
--- | --- | ---
**sms_start_transaction()** | `array( 'TRANSACTION_ID' => 'AX23x...' )` | start SMS transaction. This is simplest form that charges amount to customer instantly.
**dms_start_authorization()** |
**dms_make_transaction( $trans_id )** |
**get_transaction_result( $trans_id )** |
**reverse_transaction( $trans_id, $amount = '', $suspected_fraud = '' )** |
**refund_transaction( $trans_id )** |
**credit_transaction( $trans_id, $amount = '' )** |
**close_day()** |


### TODO

1. Regular payments
2. **DMS** example
3. CRON for `close_day()`
4. Make composer compatible

### API

###### ECOMM Integrated Merchant Agent

This API besides **TBCBANK** should support **BANK OF GEORGIA**, **LIBERTY BANK**, and some other minor banks. But since I've only tested this with *TBCBANK*, I'm releasing this as such.

### SSL

TBC bank provides SSL certificate in **.p12** format  
To Trasnform it into .pem format Run: `openssl pkcs12 -in *.p12 -out tbcpay.pem`

**!** Move cert directory somewhere non accessible to web server as a security meassure.  
