# TBC bank provides SSL certificate in .p12 format
# Trasnform it into .pem format
# Run: openssl pkcs12 -in *.p12 -out tbcpay.pem
# Move cert directory somewhere non accessible to web server as a security meassure.