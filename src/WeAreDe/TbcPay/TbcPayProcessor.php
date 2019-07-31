<?php namespace WeAreDe\TbcPay;

/**
*
* Tbcpay - online payments php SDK
*
*
* There are two types of transaction within this system: SMS and DMS.
* SMS - is direct payment, where money is charged in 1 event.
* DMS - is delayed. Requires two events: first event blocks money on the card, second event takes this money.
* Second event can be carried out when product is shipped to the customer for example.
*
* Every 24 hours, the merchant must send a request to server to close the business day.
*
* Detailed instructions can be found in README.md or online
* https://github.com/wearede/tbcpay-php
*
* Written and maintained by sandro@weare.de.com
*
*/
class TbcPayProcessor
{

    /**
     * gateway endpoint
     * @var string
     */
    public $submit_url = 'https://securepay.ufc.ge:18443/ecomm2/MerchantHandler';

    /**
     * absolute path to certificate
     * @var string
     */
    private $cert_path;

    /**
     * certificate passphrase
     * @var string
     */
    private $cert_pass;

    /**
     * client IP address, mandatory (15 characters)
     * @var string
     */
    private $client_ip_addr;

    /**
     * transaction amount in fractional units, mandatory (up to 12 digits)
     * 100 = 1 unit of currency. e.g. 1 gel = 100.
     * @var numeric
     */
    public $amount;

    /**
     * transaction currency code (ISO 4217), mandatory, (3 digits)
     * http://en.wikipedia.org/wiki/ISO_4217
     * GEL = 981 e.g.
     * @var numeric
     */
    public $currency;

    /**
     * transaction details, optional (up to 125 characters)
     * @var string
     */
    public $description;

    /**
     * authorization language identifier, optional (up to 32 characters)
             * EN, GE e.g,
     * @var string
     */
    public $language;

    /**
     * visible on account statement, optional (up to 99 latin characters)
     * @var string
     */
    public $biller;

    /**
     * So far used only when paying with "Ertguli" points. Must be 80|0000
     * @var string
     */
    public $account;

    /**
     * ? this seems to be ignored by tbcbank
     * @var string
     * private $property_name;
     */

    /**
     * ? this seems to be ignored by tbcbank
     * @var string
     * private $property_value;
     */


    /**
     * @param string  $cert_path
     * @param string  $cert_pass
     * @param string  $client_ip_addr
     */
    public function __construct($cert_path, $cert_pass, $client_ip_addr)
    {
        $this->cert_path      = $cert_path;
        $this->cert_pass      = $cert_pass;

        $this->client_ip_addr = $client_ip_addr;
    }

    /**
     * Curl is responsible for sending data to remote server, using certificate for ssl connection
     * @param  string $query_string created from an array using method build_query_string
     * @return string returns tbc server response in the form of key: value \n key: value. OR error: value.
     */
    private function curl($query_string)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_POSTFIELDS, $query_string);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($curl, CURLOPT_CAINFO, $this->cert_path); // because of Self-Signed certificate at payment server.
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->cert_path);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->cert_path);
        curl_setopt($curl, CURLOPT_SSLKEYPASSWD, $this->cert_pass);
        curl_setopt($curl, CURLOPT_URL, $this->submit_url);

        $result = curl_exec($curl);

        return $result;
    }

    /**
     * Building string from array
     * @param  array  $post_fields
     * @return string
     */
    private function build_query_string($post_fields)
    {
        return http_build_query($post_fields);
    }

    /**
     * Parse tbcbank server response string into an array
     * @param  string $string
     * @return array
     */
    private function parse_result($string)
    {
        $array1 = explode(PHP_EOL, trim($string));
        $result = array();
        foreach ($array1 as $key => $value) {
            $array2 = explode(':', $value);
            if (!empty($array2[1])) {
                $result[ $array2[0] ] = trim($array2[1]);
            }
        }

        return $result;
    }

    /**
     * Takes array and transforms into a POST string
     * curls it to tbc server
     * parses results
     * returns parsed results
     * @param  array $post_fields
     * @return array
     */
    private function process($post_fields)
    {
        $string = $this->build_query_string($post_fields);
        $result = $this->curl($string);
        $parsed = $this->parse_result($result);

        return $parsed;
    }

    /**
     * Registering transactions
     * start SMS transaction. This is simplest form that charges amount to customer instantly.
     * @return array  TRANSACTION_ID
     * TRANSACTION_ID - transaction identifier (28 characters in base64 encoding)
     * error          - in case of an error
     */
    public function sms_start_transaction()
    {
        $post_fields = array(
            'command'        => 'v', // identifies a request for transaction registration
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'client_ip_addr' => $this->client_ip_addr,
            'description'    => $this->description,
            'language'       => $this->language,
            'biller'         => $this->biller,
            'msg_type'       => 'SMS'
        );

        if ($this->account) {
            $post_fields['account'] = '80|0000';
        }

        return $this->process($post_fields);
    }

    /**
     * Registering DMS authorization
     * DMS is different from SMS, dms_start_authorization blocks amount,
     * and than we use dms_make_transaction to charge customer.
     * @return array  TRANSACTION_ID
     * TRANSACTION_ID - transaction identifier (28 characters in base64 encoding)
     * error          - in case of an error
     */
    public function dms_start_authorization()
    {
        $post_fields = array(
            'command'        => 'a', // identifies a request for transaction registration
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'client_ip_addr' => $this->client_ip_addr,
            'description'    => $this->description,
            'language'       => $this->language,
            'biller'         => $this->biller,
            'msg_type'       => 'DMS'
        );

        if ($this->account) {
            $post_fields['account'] = '80|0000';
        }

        return $this->process($post_fields);
    }

    /**
     * Executing a DMS transaction
     * @param  string $trans_id
     * @return array  RESULT, RESULT_CODE, BRN, APPROVAL_CODE, CARD_NUMBER, error
     * RESULT         - transaction results: OK - successful transaction, FAILED - failed transaction
     * RESULT_CODE    - transaction result code returned from Card Suite Processing RTPS (3 digits)
     * BRN            - retrieval reference number returned from Card Suite Processing RTPS (12 characters)
     * APPROVAL_CODE  - approval code returned from Card Suite Processing RTPS (max 6 characters)
     * CARD_NUMBER    - masked card number
     * error          - in case of an error
     */
    public function dms_make_transaction($trans_id)
    {
        $post_fields = array(
            'command'        => 't', // identifies a request for transaction registration
            'trans_id'       => $trans_id,
            'amount'         => $this->amount,
            'currency'       => $this->currency,
            'client_ip_addr' => $this->client_ip_addr,
            'description'    => $this->description,
            'language'       => $this->language,
            'msg_type'       => 'DMS'
        );

        return $this->process($post_fields);
    }

    /**
     * Transaction result
     * @param  string $trans_id
     * @return array  RESULT, RESULT_PS, RESULT_CODE, 3DSECURE, RRN, APPROVAL_CODE, CARD_NUMBER, AAV, RECC_PMNT_ID, RECC_PMNT_EXPIRY, MRCH_TRANSACTION_ID
     * RESULT              - OK              - successfully completed transaction,
     *                       FAILED          - transaction has failed,
     *                       CREATED         - transaction just registered in the system,
     *                       PENDING         - transaction is not accomplished yet,
     *                       DECLINED        - transaction declined by ECOMM,
     *                       REVERSED        - transaction is reversed,
     *                       AUTOREVERSED    - transaction is reversed by autoreversal,
     *                       TIMEOUT         - transaction was timed out
     * RESULT_PS           - transaction result, Payment Server interpretation (shown only if configured to return ECOMM2 specific details
     *                       FINISHED        - successfully completed payment,
     *                       CANCELLED       - cancelled payment,
     *                       RETURNED        - returned payment,
     *                       ACTIVE          - registered and not yet completed payment.
     * RESULT_CODE         - transaction result code returned from Card Suite Processing RTPS (3 digits)
     * 3DSECURE            - AUTHENTICATED   - successful 3D Secure authorization
     *                       DECLINED        - failed 3D Secure authorization
     *                       NOTPARTICIPATED - cardholder is not a member of 3D Secure scheme
     *                       NO_RANGE        - card is not in 3D secure card range defined by issuer
     *                       ATTEMPTED       - cardholder 3D secure authorization using attempts ACS server
     *                       UNAVAILABLE     - cardholder 3D secure authorization is unavailable
     *                       ERROR           - error message received from ACS server
     *                       SYSERROR        - 3D secure authorization ended with system error
     *                       UNKNOWNSCHEME   - 3D secure authorization was attempted by wrong card scheme (Dinners club, American Express)
     * RRN                 - retrieval reference number returned from Card Suite Processing RTPS
     * APPROVAL_CODE       - approval code returned from Card Suite Processing RTPS (max 6 characters)
     * CARD_NUMBER         - Masked card number
     * AAV                 - FAILED the results of the verification of hash value in AAV merchant name (only if failed)
     * RECC_PMNT_ID            - Reoccurring payment (if available) identification in Payment Server.
     * RECC_PMNT_EXPIRY        - Reoccurring payment (if available) expiry date in Payment Server in form of YYMM
     * MRCH_TRANSACTION_ID     - Merchant Transaction Identifier (if available) for Payment - shown if it was sent as additional parameter  on Payment registration.
     * The RESULT_CODE and 3DSECURE fields are informative only and can be not shown. The fields RRN and APPROVAL_CODE appear for successful transactions only, for informative purposes, and they facilitate tracking the transactions in Card Suite Processing RTPS system.
     * error                   - In case of an error
     * warning                 - In case of warning (reserved for future use).
     */
    public function get_transaction_result($trans_id)
    {
        $post_fields = array(
            'command'        => 'c', // identifies a request for transaction registration
            'trans_id'       => $trans_id,
            'client_ip_addr' => $this->client_ip_addr
        );

        return $this->process($post_fields);
    }

    /**
     * Transaction reversal
     * @param  string $trans_id
     * @param  string $amount          reversal amount in fractional units (up to 12 characters). For DMS authorizations only full amount can be reversed, i.e., the reversal and authorization amounts have to match. In other cases partial reversal is also available.
     * @param  string $suspected_fraud flag, indicating that transaction is being reversed because of suspected fraud. In such cases this parameter value should be set to yes. If this parameter is used, then only full reversals are allowed.
     * @return array  RESULT, RESULT_CODE
     * RESULT         - OK              - successful reversal transaction
     *                  REVERSED        - transaction has already been reversed
     *          FAILED          - failed to reverse transaction (transaction status remains as it was)
     * RESULT_CODE    - reversal result code returned from Card Suite Processing RTPS (3 digits)
     * error          - In case of an error
     * warning        - In case of warning (reserved for future use).
     */
    public function reverse_transaction($trans_id, $amount = '', $suspected_fraud = '')
    {
        $post_fields = array(
            'command'         => 'r', // identifies a request for transaction registration
            'trans_id'        => $trans_id,
            'amount'          => $amount,
            'suspected_fraud' => $suspected_fraud
        );

        return $this->process($post_fields);
    }

    /**
     * Transaction refund
     * full original amount is always refunded
     * @param  string $trans_id
     * @return array  RESULT, RESULT_CODE, REFUND_TRANS_ID
     * RESULT          - OK     - successful refund transaction
     *               FAILED - failed refund transaction
     * RESULT_CODE     - result code returned from Card Suite Processing RTPS (3 digits)
     * REFUND_TRANS_ID - refund transaction identifier - applicable for obtaining refund payment details or to request refund payment reversal.
     * error           - In case of an error
     * warning         - In case of warning (reserved for future use).
     */
    public function refund_transaction($trans_id)
    {
        $post_fields = array(
            'command'         => 'k', // identifies a request for transaction registration
            'trans_id'        => $trans_id
        );

        return $this->process($post_fields);
    }

    /**
     * Credit transaction
     * @param  string  $trans_id original transaction identifier, mandatory (28 characters)
     * @param  string  $amount   credit transaction amount in fractional units (up to 12 characters)
     * @return array   RESULT, RESULT_CODE, REFUND_TRANS_ID
     * RESULT          - OK     - successful credit transaction
     *           FAILED - failed credit transaction
     * RESULT_CODE     - result code returned from Card Suite Processing RTPS (3 digits)
     * REFUND_TRANS_ID - credit transaction identifier - applicable for obtaining credit payment details or to request credit payment reversal.
     * error           - In case of an error
     * warning         - In case of warning (reserved for future use).
     */
    public function credit_transaction($trans_id, $amount = '')
    {
        $post_fields = array(
            'command'         => 'g', // identifies a request for transaction registration
            'trans_id'        => $trans_id,
            'amount'          => $amount
        );

        return $this->process($post_fields);
    }

    /**
     * needs to be run once every 24 hours.
     * this tells bank to process all transactions of that day SMS or DMS that were success
     * in case of DMS only confirmed and sucessful transactions will be processed
     * @return array RESULT, RESULT_CODE, FLD_075, FLD_076, FLD_087, FLD_088
     * RESULT        - OK     - successful end of business day
     *                 FAILED - failed end of business day
     * RESULT_CODE   - end-of-business-day code returned from Card Suite Processing RTPS (3 digits)
     * FLD_075       - the number of credit reversals (up to 10 digits), shown only if result_code begins with 5
     * FLD_076       - the number of debit transactions (up to 10 digits), shown only if result_code begins with 5
     * FLD_087       - total amount of credit reversals (up to 16 digits), shown only if result_code begins with 5
     * FLD_088       - total amount of debit transactions (up to 16 digits), shown only if result_code begins with 5
     */
    public function close_day()
    {
        $post_fields = array(
            'command'         => 'b' // identifies a request for transaction registration
        );

        return $this->process($post_fields);
    }

    // Regular payments need to be implemented!
}
