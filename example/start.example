<?php

include 'tbcpay.lib.php';

$Payment = new tbcpay( 'https://securepay.ufc.ge:18443/ecomm2/MerchantHandler', __DIR__ . '/cert/tbcpay.pem', '0DhJ4AdxVuPZmz3F4y', $_SERVER['REMOTE_ADDR'], 1, 981, 'product_id:1234567', 'GE' );

$start = $Payment->sms_start_transaction();

if ( isset($start['TRANSACTION_ID']) AND !isset($start['error']) ) {
	$trans_id = $start['TRANSACTION_ID'];
}

?>


<html>
<head>
    <title>TBCPAY</title>
    <script type="text/javascript" language="javascript">
        function redirect() {
          document.returnform.submit();
        }
    </script>
</head>

<?php if ( isset($start['error']) ) { ?>

<body>
    <h2>Error:</h2>
    <h1><?php echo $start['error']; ?></h1>
</body>

<?php } elseif (isset($start['TRANSACTION_ID'])) { ?>

<body onLoad="javascript:redirect()">
    <form name="returnform" action="https://securepay.ufc.ge/ecomm2/ClientHandler" method="POST">
        <input type="hidden" name="trans_id" value="<?php echo $trans_id; ?>">

        <noscript>
            <center>Please click the submit button below.<br>
            <input type="submit" name="submit" value="Submit"></center>
        </noscript>
    </form>
</body>

<?php } ?>

</html>
