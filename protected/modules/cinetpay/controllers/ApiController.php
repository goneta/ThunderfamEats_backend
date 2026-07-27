<?php
/**
 * CinetPay (customer-facing "Mobile Money") checkout controller.
 *
 * Flow (mirrors the razorpay/vivawallet conventions):
 *  1. PlaceOrder builds payment_url = {site}/cinetpay/api/createcheckout?... and
 *     the app opens it in the browser (order exists, payment_status "unpaid").
 *  2. createcheckout initiates a CinetPay hosted checkout (fresh unique
 *     transaction reference per attempt) and redirects the payer there.
 *     CinetPay shows the mobile-money operators (Orange/MTN/Moov/Wave).
 *  3. The payer returns to verifypayment, which re-verifies server-side via
 *     the check API — the redirect itself proves NOTHING.
 *  4. CinetPay also POSTs the notify webhook: HMAC-checked, then verify-first
 *     against the check API, applied idempotently. Duplicate notifies are
 *     no-ops; an unreachable check API answers 503 so CinetPay retries.
 *
 * Failed/cancelled/pending payments leave the order in its draft/unpaid state:
 * nothing is confirmed, no stock moves, and the customer can retry — the same
 * guarantee the other online gateways rely on.
 */
class ApiController extends SiteCommon
{
	const CONSTANT_PAYMENTCODE = 'cinetpay';
	const MAX_PENDING_TRIES = 36; // ~3 minutes at the 5s meta-refresh below

	public function beforeAction($action)
	{
		Yii::app()->setImport(array(
			'application.modules.cinetpay.components.*',
		));

		$method = Yii::app()->getRequest()->getRequestType();

		Price_Formatter::init();
		if($method=="PUT"){
			$this->data = Yii::app()->input->xssClean(json_decode(file_get_contents('php://input'), true));
		} else $this->data = Yii::app()->input->xssClean($_POST);

		return true;
	}

	public function actionIndex()
	{
		//
	}

	public function actioncreatecheckout()
	{
		$message = null;
		$request_from = 'web'; $return_url = null;
		try {

			$order_uuid = Yii::app()->request->getQuery('order_uuid', '');
			$cart_uuid = Yii::app()->request->getQuery('cart_uuid', '');
			$payment_uuid = Yii::app()->request->getQuery('payment_uuid', '');
			$request_from = Yii::app()->request->getQuery('request_from', 'web');
			$return_url = Yii::app()->request->getQuery('return_url', null);
			$return_url = rtrim((string)$return_url, "/");
			$return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url;

			$order = AR_ordernew::model()->find('order_uuid=:order_uuid',array(':order_uuid'=>$order_uuid));
			if(!$order){
				$message = t("Order not found");
				$this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
				Yii::app()->end();
			}

			// Re-entry with an already-paid order (double tap, refreshed tab,
			// webhook won the race): never initiate twice.
			if($order->payment_status == CPayments::paidStatus()){
				$this->redirect($this->successRedirectUrl($request_from,$return_url,$order->order_uuid));
				Yii::app()->end();
			}

			$credentials = $this->getCredentials($order->merchant_id);
			$apikey = isset($credentials['attr1'])?trim($credentials['attr1']):'';
			$site_id = isset($credentials['attr2'])?trim($credentials['attr2']):'';
			$channels = isset($credentials['attr5'])&&!empty(trim($credentials['attr5']))?trim($credentials['attr5']):'MOBILE_MONEY';
			$currency_override = isset($credentials['attr6'])?trim($credentials['attr6']):'';

			$merchant = CMerchantListingV1::getMerchant($order->merchant_id);
			$customer = ACustomer::get($order->client_id);

			/* Amount in the order's checkout currency (same block as razorpay). */
			$exchange_rate = $order->exchange_rate>0?$order->exchange_rate:1;
			if($order->amount_due>0){
				$total = floatval(Price_Formatter::convertToRaw( ($order->amount_due*$exchange_rate) ));
			} else $total = floatval(Price_Formatter::convertToRaw( ($order->total*$exchange_rate) ));

			$options = OptionsTools::find(['multicurrency_enabled','multicurrency_enabled_checkout_currency']);
			$multicurrency_enabled = $options['multicurrency_enabled'] ?? false;
			$multicurrency_enabled = $multicurrency_enabled==1?true:false;
			$enabled_checkout_currency = $options['multicurrency_enabled_checkout_currency'] ?? false;
			$enabled_force = $multicurrency_enabled==true? ($enabled_checkout_currency==1?true:false) :false;
			$use_currency_code = $order->use_currency_code;
			if($enabled_force){
				if($force_result = CMulticurrency::getForceCheckoutCurrency($order->payment_code,$use_currency_code)){
					$use_currency_code = $force_result['to_currency'];
					$total = Price_Formatter::convertToRaw($total*$force_result['exchange_rate'],2);
				}
			}

			$currency = PaymentCinetpay::mapCurrency(!empty($currency_override)?$currency_override:$use_currency_code);
			$amount = PaymentCinetpay::normalizeAmount($total, $currency);

			/* Fresh unique reference per attempt — CinetPay transaction_ids are
			   single-use, so a retry after a refused payment needs a new one. */
			$reference = "TFE".$order->order_id."T".time();

			$payment_description = t("Payment to merchant [merchant]. Order#[order_id]",
			array('[merchant]'=>$merchant->restaurant_name,'[order_id]'=>$order->order_id ));

			$verify_params = array(
				'reference'=>$reference,
				'request_from'=>$request_from,
				'return_url'=>$return_url,
			);
			$verify_url = Yii::app()->createAbsoluteUrl(self::CONSTANT_PAYMENTCODE."/api/verifypayment", $verify_params);
			$notify_url = Yii::app()->createAbsoluteUrl(self::CONSTANT_PAYMENTCODE."/api/notify");

			$lang = strtolower(substr((string)Yii::app()->language,0,2));
			$lang = in_array($lang,array('fr','en'))?$lang:'fr';

			$params = array(
				'transaction_id' => $reference,
				'amount'         => $amount,
				'currency'       => $currency,
				'description'    => mb_substr($payment_description,0,180),
				'return_url'     => $verify_url,
				'cancel_url'     => $verify_url,
				'notify_url'     => $notify_url,
				'channels'       => $channels,
				'metadata'       => $reference,
				'lang'           => $lang,
				// Non-empty customer identity keeps every channel available.
				'customer_name'    => !empty($customer->first_name)?$customer->first_name:'App',
				'customer_surname' => !empty($customer->last_name)?$customer->last_name:'Customer',
				'customer_email'   => !empty($customer->email_address)?$customer->email_address:'',
				'customer_phone_number' => !empty($customer->contact_phone)?$customer->contact_phone:'',
			);

			$checkout = PaymentCinetpay::createCheckout($apikey,$site_id,$params);

			/* Persist the attempt: reference row (webhook lookup) + context row
			   (cart/payment uuids, redirect targets, amount sent — used by both
			   verifypayment and notify, and kept for reconciliation). */
			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $order->order_id;
			$order_model->meta_name = "cinetpay_reference";
			$order_model->meta_value = $reference;
			$order_model->save();

			$order_model = new AR_ordernew_meta();
			$order_model->order_id = $order->order_id;
			$order_model->meta_name = "cinetpay_ctx_".$reference;
			$order_model->meta_value = json_encode(array(
				'cart_uuid'=>$cart_uuid,
				'payment_uuid'=>$payment_uuid,
				'request_from'=>$request_from,
				'return_url'=>$return_url,
				'sent_amount'=>$amount,
				'sent_currency'=>$currency,
				'payment_token'=>$checkout['payment_token'],
			));
			$order_model->save();

			Yii::log("CinetPay checkout initiated ref=$reference order=".$order->order_id, CLogger::LEVEL_INFO, 'application.cinetpay');

			$this->redirect($checkout['payment_url']);
			Yii::app()->end();

		} catch (Exception $e) {
			$message = $e->getMessage();
			Yii::log("CinetPay createcheckout error: $message", CLogger::LEVEL_ERROR, 'application.cinetpay');
		}
		$this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
	}

	/**
	 * Return/cancel landing. The payer's browser arriving here proves nothing:
	 * the ONLY source of truth is the check API.
	 */
	public function actionverifypayment()
	{
		$message = null;
		$request_from = 'web'; $return_url = null;
		try {

			$reference = Yii::app()->request->getQuery('reference', '');
			// CinetPay appends ?transaction_id=<reference> to the return URL —
			// accept it as fallback so a stripped query still verifies.
			if(empty($reference)){
				$reference = Yii::app()->request->getQuery('transaction_id', '');
			}
			$request_from = Yii::app()->request->getQuery('request_from', 'web');
			$return_url = Yii::app()->request->getQuery('return_url', null);
			$return_url = rtrim((string)$return_url, "/");
			$return_url = (empty($return_url) || $return_url === 'null') ? '' : $return_url;
			$tries = intval(Yii::app()->request->getQuery('tries', 0));

			if(empty($reference)){
				$message = t("Transaction is empty");
				$this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
				Yii::app()->end();
			}

			$found = $this->findByReference($reference);
			if(!$found){
				$message = t("Order code not found");
				$this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
				Yii::app()->end();
			}
			list($order,$ctx) = $found;

			// Already confirmed (webhook won the race) — straight to success.
			if($order->payment_status == CPayments::paidStatus()){
				$this->redirect($this->successRedirectUrl($request_from,$return_url,$order->order_uuid));
				Yii::app()->end();
			}

			$credentials = $this->getCredentials($order->merchant_id);
			$apikey = isset($credentials['attr1'])?trim($credentials['attr1']):'';
			$site_id = isset($credentials['attr2'])?trim($credentials['attr2']):'';

			$check = PaymentCinetpay::checkTransaction($apikey,$site_id,$reference);
			$this->storeCheckPayload($order->order_id,$reference,$check['payload']);

			if($check['status']=='successful'){
				$this->applyPaidStatus($order,$reference,$ctx,$check['payload']);
				$this->redirect($this->successRedirectUrl($request_from,$return_url,$order->order_uuid));
				Yii::app()->end();
			}

			if($check['status']=='failed'){
				$message = t("Payment has failed or was cancelled. You have not been charged.");
				$this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
				Yii::app()->end();
			}

			/* pending / unknown: the payer may still be confirming on their
			   phone. Show a light auto-refreshing wait page, capped. */
			if($tries < self::MAX_PENDING_TRIES){
				$refresh_url = Yii::app()->createAbsoluteUrl(self::CONSTANT_PAYMENTCODE."/api/verifypayment", array(
					'reference'=>$reference,
					'request_from'=>$request_from,
					'return_url'=>$return_url,
					'tries'=>$tries+1,
				));
				$this->renderPendingPage($refresh_url);
				Yii::app()->end();
			}

			$message = t("Payment is still pending. If you validated it on your phone it will be confirmed automatically — check My Orders in a moment.");

		} catch (Exception $e) {
			$message = $e->getMessage();
			Yii::log("CinetPay verifypayment error: $message", CLogger::LEVEL_ERROR, 'application.cinetpay');
		}
		$this->redirect(CommonUtility::failedRedirect($request_from,$return_url,$message));
	}

	/**
	 * Public notify webhook (registered as notify_url on every initiation and
	 * in the CinetPay panel). Verify-first + idempotent; 503 when the check
	 * API is unreachable so CinetPay retries the delivery.
	 */
	public function actionnotify()
	{
		$logs = '';
		try {

			$form = $_POST;
			if(empty($form)){
				$raw = file_get_contents("php://input");
				$json = !empty($raw)?json_decode($raw,true):null;
				if(is_array($json)){
					$form = $json;
				}
			}

			$reference = isset($form['cpm_trans_id'])?trim((string)$form['cpm_trans_id']):'';
			if(empty($reference)){
				$this->endNotify(400, array('message'=>'missing cpm_trans_id'));
			}

			$found = $this->findByReference($reference);
			if(!$found){
				$this->endNotify(404, array('reference'=>$reference,'message'=>'unknown reference'));
			}
			list($order,$ctx) = $found;

			$credentials = $this->getCredentials($order->merchant_id);
			$apikey = isset($credentials['attr1'])?trim($credentials['attr1']):'';
			$site_id = isset($credentials['attr2'])?trim($credentials['attr2']):'';
			$secret = isset($credentials['attr3'])?trim($credentials['attr3']):'';

			$x_token = isset($_SERVER['HTTP_X_TOKEN'])?$_SERVER['HTTP_X_TOKEN']:null;
			if(!PaymentCinetpay::verifyXToken($secret,$x_token,$form)){
				Yii::log("CinetPay notify FORGED token ref=$reference", CLogger::LEVEL_ERROR, 'application.cinetpay');
				$this->endNotify(403, array('reference'=>$reference,'message'=>'invalid token'));
			}

			// Verify-first: the notify body is untrusted — ask the gateway.
			$check = PaymentCinetpay::checkTransaction($apikey,$site_id,$reference);
			$this->storeCheckPayload($order->order_id,$reference,$check['payload']);

			if($check['status']=='unknown'){
				// Apply NOTHING; CinetPay retries the notification later.
				Yii::log("CinetPay notify check unavailable ref=$reference -> 503", CLogger::LEVEL_ERROR, 'application.cinetpay');
				$this->endNotify(503, array('reference'=>$reference,'message'=>'verification unavailable, retry'));
			}

			if($check['status']=='successful'){
				$applied = $this->applyPaidStatus($order,$reference,$ctx,$check['payload']);
				$logs = "CinetPay notify ref=$reference verified successful applied=".($applied?'1':'0 (replay no-op)');
				Yii::log($logs, CLogger::LEVEL_INFO, 'application.cinetpay');
				$this->endNotify(200, array('reference'=>$reference,'status'=>'successful','applied'=>$applied));
			}

			// failed / pending: record nothing on the order (it stays unpaid
			// and retryable); acknowledge so CinetPay stops re-sending.
			$logs = "CinetPay notify ref=$reference verified status=".$check['status'];
			Yii::log($logs, CLogger::LEVEL_INFO, 'application.cinetpay');
			$this->endNotify(200, array('reference'=>$reference,'status'=>$check['status'],'applied'=>false));

		} catch (Exception $e) {
			Yii::log("CinetPay notify error: ".$e->getMessage(), CLogger::LEVEL_ERROR, 'application.cinetpay');
			$this->endNotify(500, array('message'=>'internal error'));
		}
	}

	public function actionsuccessful()
	{
		$message = Yii::app()->input->get('message');
		echo !empty($message)?$message:t("Payment successful");
	}

	public function actionfailed()
	{
		$message = Yii::app()->input->get('message');
		if(empty($message)){
			$message = Yii::app()->input->get('error');
		}
		echo !empty($message)?$message:t("Payment has failed");
	}

	public function actioncancel()
	{
		echo t("Payment cancelled");
	}

	/* ------------------------------------------------------------------ */

	/** Admin-level gateway credentials for this deployment. */
	private function getCredentials($merchant_id)
	{
		$payment_code = self::CONSTANT_PAYMENTCODE;
		$credentials = CPayments::getPaymentCredentials($merchant_id,$payment_code,2);
		$credentials = isset($credentials[$payment_code])?$credentials[$payment_code]:array();
		if(empty($credentials)){
			throw new Exception(t("Mobile Money payment is not configured"));
		}
		return $credentials;
	}

	/**
	 * Resolve an order + attempt context from a CinetPay reference.
	 * @return array|false [AR_ordernew, array ctx]
	 */
	private function findByReference($reference)
	{
		$meta = AR_ordernew_meta::model()->find("meta_name=:meta_name AND meta_value=:meta_value",array(
			':meta_name'=>"cinetpay_reference",
			':meta_value'=>$reference
		));
		if(!$meta){
			return false;
		}
		$order = AR_ordernew::model()->find('order_id=:order_id',array(':order_id'=>$meta->order_id));
		if(!$order){
			return false;
		}
		$ctx = array();
		$ctx_meta = AR_ordernew_meta::model()->find("order_id=:order_id AND meta_name=:meta_name",array(
			':order_id'=>$meta->order_id,
			':meta_name'=>"cinetpay_ctx_".$reference
		));
		if($ctx_meta){
			$decoded = json_decode($ctx_meta->meta_value,true);
			if(is_array($decoded)){
				$ctx = $decoded;
			}
		}
		return array($order,$ctx);
	}

	/**
	 * Idempotent money side-effect: confirm the order exactly once.
	 * Replays (duplicate notifies, return/notify race) are safe no-ops.
	 * @return bool true when this call performed the transition.
	 */
	private function applyPaidStatus($order,$reference,$ctx,$check_payload)
	{
		if($order->payment_status == CPayments::paidStatus()){
			return false; // already confirmed — no-op
		}

		/* Belt-and-braces: the verified amount must match what we initiated.
		   (Guards against transaction-id confusion, not against CinetPay.) */
		$sent_amount = isset($ctx['sent_amount'])?intval($ctx['sent_amount']):0;
		$paid_amount = isset($check_payload['data']['amount'])?intval($check_payload['data']['amount']):0;
		if($sent_amount>0 && $paid_amount>0 && $sent_amount!==$paid_amount){
			Yii::log("CinetPay AMOUNT MISMATCH ref=$reference sent=$sent_amount paid=$paid_amount — not applying", CLogger::LEVEL_ERROR, 'application.cinetpay');
			throw new Exception(t("Payment amount mismatch"));
		}

		$operator_id = isset($check_payload['data']['operator_id'])?(string)$check_payload['data']['operator_id']:'';
		$payment_method = isset($check_payload['data']['payment_method'])?(string)$check_payload['data']['payment_method']:'';
		$cart_uuid = isset($ctx['cart_uuid'])?$ctx['cart_uuid']:'';
		$payment_uuid = isset($ctx['payment_uuid'])?$ctx['payment_uuid']:'';

		$order->scenario = "new_order";
		$order->status = COrders::newOrderStatus();
		$order->payment_status = CPayments::paidStatus();
		$order->cart_uuid = $cart_uuid;
		$order->save();

		/* One transaction row per reference — replays update, never duplicate. */
		$model = AR_ordernew_transaction::model()->find("payment_reference=:payment_reference",array(
			':payment_reference'=>$reference
		));
		if(!$model){
			$model = new AR_ordernew_transaction;
			$model->order_id = $order->order_id;
			$model->merchant_id = $order->merchant_id;
			$model->client_id = $order->client_id;
			$model->payment_code = $order->payment_code;
			$model->trans_amount = $order->amount_due>0? $order->amount_due: $order->total;
			$model->currency_code = $order->use_currency_code;
			$model->payment_reference = $reference;
			$model->payment_uuid = $payment_uuid;
		}
		$model->status = CPayments::paidStatus();
		$model->reason = '';
		if($model->save()){
			$params = array();
			if(!empty($operator_id)){
				$params[] = array('transaction_id'=>$model->transaction_id,'order_id'=>$order->order_id,
				'meta_name'=>'cinetpay_operator_id','meta_value'=>$operator_id);
			}
			if(!empty($payment_method)){
				$params[] = array('transaction_id'=>$model->transaction_id,'order_id'=>$order->order_id,
				'meta_name'=>'cinetpay_payment_method','meta_value'=>$payment_method);
			}
			if(count($params)>0){
				$builder=Yii::app()->db->schema->commandBuilder;
				$command=$builder->createMultipleInsertCommand('{{ordernew_trans_meta}}',$params);
				$command->execute();
			}
		}

		Yii::log("CinetPay payment CONFIRMED ref=$reference order=".$order->order_id." method=$payment_method", CLogger::LEVEL_INFO, 'application.cinetpay');
		return true;
	}

	/** Keep the last verified gateway answer on the order for reconciliation. */
	private function storeCheckPayload($order_id,$reference,$payload)
	{
		try {
			$meta_name = "cinetpay_check_".$reference;
			$model = AR_ordernew_meta::model()->find("order_id=:order_id AND meta_name=:meta_name",array(
				':order_id'=>$order_id,
				':meta_name'=>$meta_name
			));
			if(!$model){
				$model = new AR_ordernew_meta();
				$model->order_id = $order_id;
				$model->meta_name = $meta_name;
			}
			$model->meta_value = json_encode($payload);
			$model->save();
		} catch (Exception $e) {
			// reconciliation data must never break the payment path
			Yii::log("CinetPay storeCheckPayload: ".$e->getMessage(), CLogger::LEVEL_ERROR, 'application.cinetpay');
		}
	}

	/** Where a confirmed payer lands (same contract as razorpay). */
	private function successRedirectUrl($request_from,$return_url,$order_uuid)
	{
		if($request_from=="app"){
			if(!empty($return_url)){
				return $return_url."/account/trackorder?".http_build_query(array('order_uuid'=>$order_uuid));
			}
			return APP_CUSTOM_URL_SCHEME."://payment-callback?".http_build_query(array('status'=>'successful','order_id'=>$order_uuid));
		}
		return Yii::app()->createAbsoluteUrl("orders/index",array('order_uuid'=>$order_uuid));
	}

	/** Minimal self-refreshing wait page (payer confirming on their phone). */
	private function renderPendingPage($refresh_url)
	{
		$title = CHtml::encode(t("Waiting for payment confirmation"));
		$body = CHtml::encode(t("Validate the payment on your phone. This page refreshes automatically — please don't close it."));
		$url = CHtml::encode($refresh_url);
		header('Content-Type: text/html; charset=utf-8');
		echo "<!DOCTYPE html><html><head><meta charset=\"utf-8\">"
			."<meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">"
			."<meta http-equiv=\"refresh\" content=\"5;url={$url}\">"
			."<title>{$title}</title>"
			."<style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:90vh;margin:0;background:#0d1117;color:#fff;text-align:center;padding:24px}"
			.".card{max-width:420px}.spin{width:42px;height:42px;border:4px solid #4285F4;border-top-color:transparent;border-radius:50%;margin:0 auto 20px;animation:s 1s linear infinite}@keyframes s{to{transform:rotate(360deg)}}</style>"
			."</head><body><div class=\"card\"><div class=\"spin\"></div><h3>{$title}</h3><p>{$body}</p></div></body></html>";
	}

	/** Webhook responder: explicit HTTP status + JSON body, then end. */
	private function endNotify($http_code,$body=array())
	{
		http_response_code($http_code);
		header('Content-type: application/json');
		echo CJSON::encode($body);
		Yii::app()->end();
	}

}
// end class
