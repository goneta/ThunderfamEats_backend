<?php
/**
 * Standalone unit tests for the CinetPay gateway client (no Yii, no network).
 *
 * The backend has no test framework, so this is a plain CLI runner:
 *   php protected/modules/cinetpay/tests/run-tests.php
 * Exit code 0 = all green; non-zero = failures (listed on stdout).
 *
 * Covers the security-critical pure logic:
 *  - x-token HMAC: accept (real vector), reject forged, reject missing,
 *    pass-through when no secret configured
 *  - verify-first status mapping: ACCEPTED/REFUSED/CANCELLED/WAITING/PENDING,
 *    code 662 -> pending, code 600/empty -> unknown (webhook answers 503),
 *    other explicit codes -> failed, garbage -> unknown
 *  - amount normalization: XOF/XAF multiples of 5, minimum 5, integer rounding
 *  - currency mapping: FCFA/CFA -> XOF
 *
 * The controller flows (createcheckout redirect, idempotent applyPaidStatus,
 * webhook 403/503 paths) depend on the Yii runtime + database and are
 * validated by php -l plus the staging curl matrix in
 * docs/cinetpay-mobile-money.md — documented there, not silently skipped.
 */

require __DIR__ . '/../components/PaymentCinetpay.php';

$failures = 0; $passed = 0;
function check($label, $cond){
    global $failures, $passed;
    if($cond){ $passed++; echo "  ok  $label\n"; }
    else { $failures++; echo "FAIL  $label\n"; }
}

echo "== HMAC x-token ==\n";
$secret = 'test-secret-key-123';
$form = array(
    'cpm_site_id' => '445160', 'cpm_trans_id' => 'TFE12T1785072880',
    'cpm_trans_date' => '2026-07-27 12:00:00', 'cpm_amount' => '5000',
    'cpm_currency' => 'XOF', 'signature' => 'sig-abc', 'payment_method' => 'OMCIV2',
    'cel_phone_num' => '0102030405', 'cpm_phone_prefixe' => '225',
    'cpm_language' => 'fr', 'cpm_version' => 'V2', 'cpm_payment_config' => 'SINGLE',
    'cpm_page_action' => 'PAYMENT', 'cpm_custom' => 'TFE12T1785072880',
    'cpm_designation' => 'Order #12', 'cpm_error_message' => 'SUCCES',
);
$data = '';
foreach(PaymentCinetpay::$hmac_fields as $f){ $data .= isset($form[$f])?(string)$form[$f]:''; }
$valid_token = hash_hmac('sha256', $data, $secret);

check('valid token accepted', PaymentCinetpay::verifyXToken($secret, $valid_token, $form) === true);
check('forged token rejected', PaymentCinetpay::verifyXToken($secret, 'deadbeef'.$valid_token, $form) === false);
check('missing token rejected when secret set', PaymentCinetpay::verifyXToken($secret, null, $form) === false);
check('tampered body rejected', PaymentCinetpay::verifyXToken($secret, $valid_token, array_merge($form, array('cpm_amount'=>'999999'))) === false);
check('no secret configured -> pass-through (check API stays the gate)', PaymentCinetpay::verifyXToken('', null, $form) === true);
$absent_field_form = $form; unset($absent_field_form['cel_phone_num']);
$data2 = '';
foreach(PaymentCinetpay::$hmac_fields as $f){ $data2 .= isset($absent_field_form[$f])?(string)$absent_field_form[$f]:''; }
check('absent field = empty string in concatenation', PaymentCinetpay::verifyXToken($secret, hash_hmac('sha256',$data2,$secret), $absent_field_form) === true);

echo "== status mapping (verify-first) ==\n";
$r = PaymentCinetpay::parseCheckPayload(array('code'=>'00','data'=>array('status'=>'ACCEPTED','amount'=>5000)));
check('ACCEPTED -> successful', $r['status']==='successful');
$r = PaymentCinetpay::parseCheckPayload(array('code'=>'00','data'=>array('status'=>'REFUSED')));
check('REFUSED -> failed', $r['status']==='failed');
$r = PaymentCinetpay::parseCheckPayload(array('code'=>'627','data'=>array('status'=>'CANCELLED')));
check('CANCELLED -> failed', $r['status']==='failed');
$r = PaymentCinetpay::parseCheckPayload(array('data'=>array('status'=>'WAITING_FOR_CUSTOMER')));
check('WAITING_FOR_CUSTOMER -> pending', $r['status']==='pending');
$r = PaymentCinetpay::parseCheckPayload(array('data'=>array('status'=>'PENDING')));
check('PENDING -> pending', $r['status']==='pending');
$r = PaymentCinetpay::parseCheckPayload(array('code'=>'662','message'=>'WAITING_CUSTOMER_TO_VALIDATE'));
check('code 662 -> pending', $r['status']==='pending');
$r = PaymentCinetpay::parseCheckPayload(array('code'=>'600','message'=>'erreur'));
check('code 600 -> unknown (webhook must answer 503)', $r['status']==='unknown');
$r = PaymentCinetpay::parseCheckPayload(array('message'=>'no code at all'));
check('no code/status -> unknown', $r['status']==='unknown');
$r = PaymentCinetpay::parseCheckPayload(array('code'=>'404','message'=>'transaction not found'));
check('explicit non-success code -> failed', $r['status']==='failed');
$r = PaymentCinetpay::parseCheckPayload('garbage');
check('non-array payload -> unknown', $r['status']==='unknown');
$r = PaymentCinetpay::parseCheckPayload(array('data'=>array('status'=>'accepted ')));
check('status is case/space normalized', $r['status']==='successful');

echo "== amount normalization ==\n";
check('XOF rounds to multiple of 5 (5003 -> 5005)', PaymentCinetpay::normalizeAmount(5003, 'XOF') === 5005);
check('XOF rounds down (5002 -> 5000)', PaymentCinetpay::normalizeAmount(5002, 'XOF') === 5000);
check('XAF multiple of 5', PaymentCinetpay::normalizeAmount(101, 'XAF') === 100);
check('minimum is 5', PaymentCinetpay::normalizeAmount(1, 'XOF') === 5);
check('minimum applies to zero', PaymentCinetpay::normalizeAmount(0, 'USD') === 5);
check('non-XOF integer rounding (10.6 USD -> 11)', PaymentCinetpay::normalizeAmount(10.6, 'USD') === 11);
check('FCFA treated as XOF for rounding', PaymentCinetpay::normalizeAmount(5003, 'FCFA') === 5005);

echo "== currency mapping ==\n";
check('FCFA -> XOF', PaymentCinetpay::mapCurrency('FCFA') === 'XOF');
check('CFA -> XOF', PaymentCinetpay::mapCurrency('CFA') === 'XOF');
check('xof -> XOF (case)', PaymentCinetpay::mapCurrency('xof') === 'XOF');
check('USD passes through', PaymentCinetpay::mapCurrency('USD') === 'USD');

echo "\n$passed passed, $failures failed\n";
exit($failures > 0 ? 1 : 0);
