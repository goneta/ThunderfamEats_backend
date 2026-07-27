<?php
/**
 * Thin CinetPay checkout-v2 REST client.
 *
 * Two endpoints + one HMAC check — deliberately no SDK (the official PHP SDK
 * is a 2020, PHP-5.6-era package). Pure static functions with no Yii
 * dependency so the class is unit-testable standalone (see tests/).
 *
 * Security doctrine implemented here and enforced by the callers:
 *  - verify-first: a notify body is NEVER trusted; every status applied to an
 *    order comes from checkTransaction() against the check API.
 *  - checkTransaction() never throws on transport problems: it returns status
 *    'unknown', and the webhook answers 503 so CinetPay retries later.
 *  - the x-token HMAC is compared constant-time (hash_equals).
 *  - credentials are never logged; callers log references and statuses only.
 */
class PaymentCinetpay
{
    const API_URL   = 'https://api-checkout.cinetpay.com/v2/payment';
    const CHECK_URL = 'https://api-checkout.cinetpay.com/v2/payment/check';

    /** Gateway -> internal status map (data.status from the check API). */
    public static $status_map = array(
        'ACCEPTED'             => 'successful',
        'REFUSED'              => 'failed',
        'CANCELLED'            => 'failed',
        'WAITING_FOR_CUSTOMER' => 'pending',
        'PENDING'              => 'pending',
    );

    /**
     * Exact field order of the notify x-token HMAC
     * (HMAC-SHA256 over the concatenation, empty string when a field is absent).
     */
    public static $hmac_fields = array(
        'cpm_site_id', 'cpm_trans_id', 'cpm_trans_date', 'cpm_amount', 'cpm_currency',
        'signature', 'payment_method', 'cel_phone_num', 'cpm_phone_prefixe',
        'cpm_language', 'cpm_version', 'cpm_payment_config', 'cpm_page_action',
        'cpm_custom', 'cpm_designation', 'cpm_error_message',
    );

    /** Map local currency labels to CinetPay codes. */
    public static function mapCurrency($currency_code='')
    {
        $currency_code = strtoupper(trim((string)$currency_code));
        if($currency_code=='FCFA' || $currency_code=='CFA'){
            return 'XOF';
        }
        return $currency_code;
    }

    /**
     * CinetPay amounts are integers >= 5 and, for XOF/XAF, multiples of 5.
     */
    public static function normalizeAmount($amount, $currency_code='')
    {
        $currency_code = self::mapCurrency($currency_code);
        $amount = floatval($amount);
        if($currency_code=='XOF' || $currency_code=='XAF'){
            $normalized = intval(round($amount / 5) * 5);
        } else {
            $normalized = intval(round($amount));
        }
        return max(5, $normalized);
    }

    /**
     * Initiate a hosted checkout.
     * @return array ['payment_url'=>..., 'payment_token'=>..., 'payload'=>full response]
     * @throws Exception on any failure (missing url, error code, transport).
     */
    public static function createCheckout($apikey, $site_id, $params=array())
    {
        if(empty($apikey) || empty($site_id)){
            throw new Exception("CinetPay credentials are not configured");
        }
        $body = array_merge(array(
            'apikey'  => $apikey,
            'site_id' => $site_id,
        ), $params);

        $payload = self::post(self::API_URL, $body);
        $payment_url = isset($payload['data']['payment_url'])?$payload['data']['payment_url']:'';
        if(!empty($payment_url)){
            return array(
                'payment_url'   => $payment_url,
                'payment_token' => isset($payload['data']['payment_token'])?$payload['data']['payment_token']:'',
                'payload'       => $payload,
            );
        }
        $code = isset($payload['code'])?$payload['code']:'';
        $desc = isset($payload['description'])?$payload['description']:(isset($payload['message'])?$payload['message']:'');
        throw new Exception("CinetPay initiation failed [$code] $desc");
    }

    /**
     * Verify a transaction server-side. NEVER throws on transport problems.
     * @return array ['status'=>'successful'|'failed'|'pending'|'unknown', 'payload'=>array]
     */
    public static function checkTransaction($apikey, $site_id, $transaction_id)
    {
        if(empty($apikey) || empty($site_id) || empty($transaction_id)){
            return array('status'=>'unknown','payload'=>array('message'=>'credentials or reference missing'));
        }
        try {
            $payload = self::post(self::CHECK_URL, array(
                'apikey'         => $apikey,
                'site_id'        => $site_id,
                'transaction_id' => $transaction_id,
            ));
        } catch (Exception $e) {
            return array('status'=>'unknown','payload'=>array('message'=>$e->getMessage()));
        }
        return self::parseCheckPayload($payload);
    }

    /**
     * Pure mapping of a check-API payload to an internal status
     * (split out of checkTransaction so it is unit-testable without network).
     */
    public static function parseCheckPayload($payload)
    {
        if(!is_array($payload)){
            return array('status'=>'unknown','payload'=>array('message'=>'unparseable payload'));
        }
        $raw = isset($payload['data']['status'])?strtoupper(trim((string)$payload['data']['status'])):'';
        if(isset(self::$status_map[$raw])){
            return array('status'=>self::$status_map[$raw],'payload'=>$payload);
        }
        $code = isset($payload['code'])?(string)$payload['code']:'';
        if($code==='662'){ // WAITING_CUSTOMER_TO_VALIDATE
            return array('status'=>'pending','payload'=>$payload);
        }
        if($code==='600' || $code===''){ // transport / unparseable
            return array('status'=>'unknown','payload'=>$payload);
        }
        // Any other explicit gateway answer without an ACCEPTED status
        // (e.g. 404 transaction not found, 608 minimum) is a non-success.
        return array('status'=>'failed','payload'=>$payload);
    }

    /**
     * Validate the notify x-token header (constant time).
     * With no secret configured the check API remains the authoritative gate,
     * so an absent secret returns true by design.
     */
    public static function verifyXToken($secret, $x_token, $form=array())
    {
        if(empty($secret)){
            return true;
        }
        if(empty($x_token)){
            return false;
        }
        $data = '';
        foreach(self::$hmac_fields as $field){
            $data .= isset($form[$field])?(string)$form[$field]:'';
        }
        $expected = hash_hmac('sha256', $data, $secret);
        return hash_equals($expected, trim((string)$x_token));
    }

    /** Internal JSON POST via cURL. @throws Exception on transport errors. */
    private static function post($url, $body=array())
    {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($body),
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
        ));
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("CinetPay gateway unreachable: $error");
        }
        curl_close($ch);
        if(empty($result)){
            throw new Exception("Empty response from CinetPay");
        }
        $json = json_decode($result, true);
        if(!is_array($json)){
            throw new Exception("Invalid response from CinetPay");
        }
        return $json;
    }

}
// end class
