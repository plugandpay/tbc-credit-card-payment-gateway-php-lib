<?php

include '../src/WeAreDe/TbcPay/TbcPayProcessor.php';

use WeAreDe\TbcPay\TbcPayProcessor;


$Payment = new TbcPayProcessor( '/cert/tbcpay.pem', '0DhJ4AdxVuPZmz3F4y', $_SERVER['REMOTE_ADDR'] );

$Payment->amount      = 1; // 1 = 1 tetri
$Payment->currency    = 981; // 981 = GEL
$Payment->description = 'Your product description, will be shown to client on card processing page!';
$Payment->language    = 'GE'; // Interface language

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
        <input type="hidden" name="trans_id" value="<?php echo rawurlencode($trans_id); ?>">

        <noscript>
            <center>Please click the submit button below.<br>
            <input type="submit" name="submit" value="Submit"></center>
        </noscript>
    </form>
</body>

<?php } ?>

</html>
