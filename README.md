![face_post_tbc_payment](https://cloud.githubusercontent.com/assets/8479569/7435079/5aebe7cc-f051-11e4-8ee1-d85b0e36a8a9.jpg)

# Tbcpay - online payments php SDK

### SMS / DMS

There are two types of transaction within this system: **SMS** and **DMS**.

### TODO

1. Regular payments
2. **DMS** example
3. CRON for `close_day()`

### API

###### ECOMM Integrated Merchant Agent

This API besides **TBCBANK** should support **BANK OF GEORGIA**, **LIBERTY BANK**, and some other minor banks. But since I've only tested this with *TBCBANK*, I'm releasing this as such.

### SSL

TBC bank provides SSL certificate in **.p12** format

To Trasnform it into .pem format Run: `openssl pkcs12 -in *.p12 -out tbcpay.pem`

**!** Move cert directory somewhere non accessible to web server as a security meassure.
