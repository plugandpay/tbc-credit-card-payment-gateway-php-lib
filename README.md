![face_post_tbc_payment](https://cloud.githubusercontent.com/assets/8479569/7435079/5aebe7cc-f051-11e4-8ee1-d85b0e36a8a9.jpg)

# Tbcpay - online payments php SDK

### SSL

TBC bank provides SSL certificate in .p12 format
Trasnform it into .pem format
Run: `openssl pkcs12 -in *.p12 -out tbcpay.pem`
Move cert directory somewhere non accessible to web server as a security meassure.
