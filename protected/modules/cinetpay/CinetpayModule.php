<?php
/**
 * CinetPay payment module — customer-facing label is "Mobile Money".
 *
 * CinetPay is the technical provider aggregating Orange Money, MTN Mobile
 * Money, Moov Money and Wave behind one hosted checkout. The gateway row in
 * {{payment_gateway}} (payment_code 'cinetpay') carries the credentials:
 *   attr1 = API Key (apikey)      attr2 = Site ID
 *   attr3 = Secret Key (HMAC secret used to validate the notify x-token)
 *   attr5 = Channels (default MOBILE_MONEY; ALL exposes cards too)
 *   attr6 = Currency override (optional, e.g. XOF — else the order currency)
 * There is no separate sandbox host: CinetPay test keys on the same endpoints
 * ARE the sandbox, so the standard is_live flag is informational only.
 *
 * See docs/cinetpay-mobile-money.md for deploy/config/test instructions.
 */
class CinetpayModule extends CWebModule
{
	public function init()
	{
		$this->setImport(array(
			'cinetpay.components.*',
			'cinetpay.models.*'
		));
	}

	public static function paymentCode()
	{
		return 'cinetpay';
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			return true;
		}
		else
			return false;
	}

	public function paymentInstructions()
	{
		return array(
		  'method'=>"online",
		  'redirect'=>''
		);
	}

	public function delete($data)
	{
		AR_payment_method_meta::model()->deleteAll("payment_method_id=:payment_method_id",array(
		  ':payment_method_id'=>$data->payment_method_id
		));
	}

	/**
	 * Offline-style transaction hook. Not used for CinetPay (method is
	 * "online": the order is only confirmed after server-side verification in
	 * ApiController), but kept for interface parity with the other modules.
	 */
	public function savedTransaction($data)
	{
		$order = AR_ordernew::model()->find('order_id=:order_id',
		array(':order_id'=>$data->order_id));
		if($order){
			$order->scenario = "new_order";
			$order->status = COrders::newOrderStatus();
			$order->cart_uuid = $data->cart_uuid;
			$order->save();
		}

		$payment_ref = CommonUtility::generateToken("{{ordernew_transaction}}",'payment_reference',
		CommonUtility::generateAplhaCode(10) );

		$model = new AR_ordernew_transaction;
		$model->order_id = $data->order_id;
		$model->merchant_id = $data->merchant_id;
		$model->client_id = $data->client_id;
		$model->payment_code = $data->payment_code;
		$model->trans_amount = $data->total;
		$model->currency_code = Price_Formatter::$number_format['currency_code'];
		$model->payment_reference = $payment_ref;
		$model->save();
	}

	public function refund($credentials, $transaction, $payment )
	{
		// CinetPay refunds are initiated from the CinetPay merchant back-office,
		// not through the checkout API. Surface a clear message instead of
		// pretending to support it.
		throw new Exception( t("Refunds for Mobile Money payments must be issued from the CinetPay merchant dashboard") );
	}

}
/*end class*/
