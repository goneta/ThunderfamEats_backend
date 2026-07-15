<?php
class CPromos
{
	private static $exchange_rate;

	public static function setExchangeRate($exchange_rate = 0)
	{
		if ($exchange_rate > 0) {
			self::$exchange_rate = $exchange_rate;
		} else {
			self::$exchange_rate = 1;
		}
	}

	public static function getExchangeRate()
	{
		return floatval(self::$exchange_rate > 0 ? self::$exchange_rate : 1);
	}

	public static function promo($merchant_id = '', $date_now = '')
	{
		$today = strtolower(date("l", strtotime($date_now)));
		$mtid = '"' . $merchant_id . '"';

		$stmt = "
    	SELECT 
    	'voucher' as promo_type,
    	a.voucher_id as promo_id,
    	a.merchant_id,
    	a.joining_merchant,
    	a.voucher_name,
    	a.voucher_type, 
    	a.amount, 
    	a.expiration, 
    	a.status, 
    	a.used_once, 
    	a.min_order,
    	a.expiration,
		a.max_order,
		a.max_discount_cap
    	
    	FROM {{voucher_new}} a
    	WHERE a.expiration >= " . q($date_now) . "
    	AND status in ('publish','published')
    	AND " . $today . "=1
		AND used_once <> 6
		AND visible=1
    	AND ( merchant_id =" . q($merchant_id) . " OR joining_merchant LIKE " . q("%$mtid%") . " )
    	
    	UNION ALL
    	
    	SELECT 
    	'offers' as promo_type,
    	offers_id as promo_id,
    	merchant_id,    	
    	applicable_to,
    	offer_percentage,
    	offer_price,
    	status,
    	'',
    	valid_from,
    	valid_to,
    	min_order,
    	'',
		offer_price as max_order,
		max_discount_cap
    	
    	FROM {{offers}}
    	WHERE merchant_id =" . q($merchant_id) . "
    	AND status in ('publish','published')
    	AND " . q($date_now) . " >= valid_from and " . q($date_now) . " <= valid_to
    	";
		if ($res = Yii::app()->db->createCommand($stmt)->queryAll()) {
			$data = array();
			$exchange_rate = self::getExchangeRate();

			foreach ($res as $val) {
				$name = '';
				$min_spend = '';
				$use_until = '';
				$max_spend = '';
				$max_cap = '';

				if ($val['promo_type'] == "voucher") {

					$pretty_expiration = Date_Formatter::date($val['expiration']);
					$pretty_amount = Price_Formatter::formatNumber(($val['amount'] * $exchange_rate));
					$pretty_min_order = Price_Formatter::formatNumber(($val['min_order'] * $exchange_rate));
					$pretty_max_order = Price_Formatter::formatNumber(($val['max_order'] * $exchange_rate));
					$pretty_max_discount_cap = Price_Formatter::formatNumber(($val['max_discount_cap'] * $exchange_rate));

					$use_until = t("Valid Until: {{date}}", array(
						'{{date}}' => $pretty_expiration
					));

					if ($val['voucher_type'] == "percentage") {
						$name = t("({{coupon_name}}) {{amount}}% off", array(
							'{{amount}}' => Price_Formatter::convertToRaw(($val['amount']), 0),
							'{{coupon_name}}' => $val['voucher_name'],
						));
					} else {
						$name = t("({{coupon_name}}) {{amount}} off", array(
							'{{amount}}' => $pretty_amount,
							'{{coupon_name}}' => $val['voucher_name'],
						));
					}

					if ($val['min_order'] > 0) {
						$min_spend = t("Minimum Spend: {amount}", array(
							'{amount}' => $pretty_min_order
						));
					}
					if ($val['max_order'] > 0) {
						$max_spend = t("Maximum Spend: {max_order}", array(
							'{max_order}' => $pretty_max_order
						));
					}
					if ($val['max_discount_cap'] > 0) {
						$max_cap = t("Maximum Discount Cap: {max_discount_cap}", array(
							'{max_discount_cap}' => $pretty_max_discount_cap
						));
					}

					$data[] = array(
						'promo_type' => $val['promo_type'],
						'promo_id' => $val['promo_id'],
						'title' => $name,
						'sub_title' => $min_spend,
						'max_spend' => $max_spend,
						'max_cap' => $max_cap,
						'valid_to' => $use_until,
					);
				} elseif ($val['promo_type'] == "offers") {

					$transaction_type = json_decode($val['joining_merchant'], true);
					$name = t("{{amount}}% off over {{order_over}} on {{transaction}}", array(
						'{{amount}}' => Price_Formatter::convertToRaw(($val['voucher_name']), 0),
						'{{order_over}}' => Price_Formatter::formatNumber(($val['voucher_type'] * $exchange_rate)),
						'{{transaction}}' => CommonUtility::arrayToString($transaction_type)
					));
					$valid_to = t("valid {{from}} to {{to}}", array(
						'{{from}}' => Date_Formatter::date($val['status']),
						'{{to}}' => Date_Formatter::date($val['used_once']),
					));

					$pretty_min_order = Price_Formatter::formatNumber(($val['min_order'] * $exchange_rate));
					$pretty_max_discount_cap = Price_Formatter::formatNumber(($val['max_discount_cap'] * $exchange_rate));

					if ($val['min_order'] > 0) {
						$min_spend = t("Minimum Spend: {amount}", array(
							'{amount}' => $pretty_min_order
						));
					}
					if ($val['max_discount_cap'] > 0) {
						$max_cap = t("Maximum Discount Cap: {max_discount_cap}", array(
							'{max_discount_cap}' => $pretty_max_discount_cap
						));
					}

					$data[] = array(
						'promo_type' => $val['promo_type'],
						'promo_id' => $val['promo_id'],
						'title' => $name,
						'sub_title' => $min_spend,
						'max_cap' => $max_cap,
						'valid_to' => $valid_to,
					);
				}
			}
			return $data;
		}
		return false;
	}

	public static function applyVoucher($merchant_id = '', $voucher_id = '', $client_id = '', $date = '', $sub_total = 0, $transaction_type = '')
	{
		$days = date("l", strtotime($date));
		$status_delivered = AOrderSettings::getStatus(array(
			'status_delivered',
			'status_completed',
			'status_new_order',
			'tracking_status_process',
			'tracking_status_ready',
			'tracking_status_in_transit'
		));

		$in_status = CommonUtility::arrayToQueryParameters($status_delivered);
		$in_status = !empty($in_status) ? $in_status : CommonUtility::arrayToQueryParameters(['complete', 'delivered']);

		$stmt = "
		SELECT a.voucher_id,a.voucher_owner,a.merchant_id,a.joining_merchant,a.voucher_name,
		a.voucher_type,a.amount,a.min_order,
		a.used_once,a.max_number_use,a.selected_customer,
		a.max_order,a.max_discount_cap,
		
		(
		  select count(*) from {{ordernew}}
		  where promo_code = a.voucher_name
		  and
		  client_id=" . q($client_id) . "
		  and status IN ($in_status)
		) as customer_use_count,
		
		(
		  select count(*) from {{ordernew}}
		  where promo_code = a.voucher_name
		) as all_use_count,
		
	    (
	      select count(*) from {{ordernew}}
	      where client_id=" . q($client_id) . "
	      and status not in ('initial_order','cancel','cancelled')
	    ) as first_order_count,

		(
			select GROUP_CONCAT(meta_value1) 
			from {{merchant_meta}}
			where merchant_id = a.merchant_id
			and meta_name='coupon'
			and meta_value = a.voucher_id
		) as transaction_list
		
		FROM {{voucher_new}} a
		WHERE voucher_id = " . q($voucher_id) . "
		AND expiration >= " . q($date) . "
		AND " . strtolower($days) . "=1			    
		AND status in ('publish','published')
		";
		if ($res = Yii::app()->db->createCommand($stmt)->queryRow()) {

			$exchange_rate = self::getExchangeRate();
			//$sub_total = ($sub_total*$exchange_rate);
			$transaction_list = !empty($res['transaction_list']) ? explode(",", $res['transaction_list']) : '';
			$voucher_options = (int)$res['used_once'];
			$max_number_use = (int)$res['max_number_use'];
			$voucher_type = $res['voucher_type'];
			$min_order = floatval($res['min_order']) * $exchange_rate;
			$max_order = floatval($res['max_order']) * $exchange_rate;
			$max_discount_cap = floatval($res['max_discount_cap']) * $exchange_rate;

			$less_amount = floatval($res['amount']);
			$less_amount_original = floatval($res['amount']);

			if (is_array($transaction_list) && count($transaction_list) >= 1 && !empty($transaction_type)) {
				if (!in_array($transaction_type, (array)$transaction_list)) {
					if ($transaction_translation = AttributesTools::getTransactionTypeDetails($transaction_type, Yii::app()->language)) {
						$transaction_type = $transaction_translation['service_name'];
					}
					throw new Exception(t("Voucher code not applicable for {transaction_type}", [
						'{transaction_type}' => $transaction_type
					]));
				}
			}

			if ($res['voucher_owner'] == "admin") {
				$joining_merchant = !empty($res['joining_merchant']) ? json_decode($res['joining_merchant'], true) : '';
				if (is_array($joining_merchant) && count($joining_merchant) >= 1) {
					if (!in_array($merchant_id, (array)$joining_merchant)) {
						throw new Exception("Voucher code not applicable to this merchant");
					}
				}
			} else if ($res['voucher_owner'] == "merchant") {
				if ($res['merchant_id'] != $merchant_id) {
					throw new Exception("Voucher code not applicable to this merchant");
				}
			} else {
				throw new Exception("Voucher code not found");
			}

			if ($min_order > 0) {
				if ($sub_total < $min_order) {
					throw new Exception(
						t("Minimum order for this voucher is [min_order]", array(
							'[min_order]' => Price_Formatter::formatNumber(($res['min_order'] * $exchange_rate))
						))
					);
				}
			}
			if ($max_order > 0) {
				if ($sub_total > $max_order) {
					throw new Exception(
						t("Maximum order for this voucher is [max_order]", array(
							'[max_order]' => Price_Formatter::formatNumber(($res['max_order'] * $exchange_rate))
						))
					);
				}
			}

			$less_discount = 0;
			if ($voucher_type == "percentage") {
				$less_discount = $sub_total * ($less_amount / 100);
			} else $less_discount = $less_amount * $exchange_rate;


			$total = floatval($sub_total) - floatval($less_discount);
			if ($total <= 0) {
				throw new Exception("Discount cannot be applied due to total less than zero after discount");
			}

			switch ($voucher_options) {
				case 2:
					if ($res['all_use_count'] > 0) {
						throw new Exception("This voucher code has already been used");
					}
					break;

				case 3:
					if ($res['customer_use_count'] > 0) {
						throw new Exception("Sorry but you have already use this voucher code");
					}
					break;

				case 4:
					if ($res['first_order_count'] > 0) {
						throw new Exception("This voucher can be use only in your first order");
					}
					break;

				case 5:
					if ($res['customer_use_count'] >= $max_number_use) {

						$error_msg = '';
						if ($res['customer_use_count'] <= 1) {
							$error_msg = "You already used this voucher [count] time and cannot be use again";
						} else $error_msg = "You already used this voucher [count] times and cannot be use again";

						throw new Exception(
							Yii::t("default", $error_msg, array(
								'[count]' => $max_number_use
							))
						);
					}
					break;

				case 6:
					if ($res['customer_use_count'] > 0) {
						throw new Exception("Sorry but you have already use this voucher code");
					}

					$selected_customer = !empty($res['selected_customer']) ? json_decode($res['selected_customer'], true) : false;
					if (is_array($selected_customer) && count($selected_customer) >= 1) {
						if (!in_array($client_id, (array)$selected_customer)) {
							throw new Exception("This voucher cannot be use in your account");
						}
					} else throw new Exception("Voucher code not found");

					break;

				default:
					break;
			}

			$final_less_discount = ($max_discount_cap > 0 && $less_discount > $max_discount_cap) ? $max_discount_cap : $less_discount;

			return array(
				'promo_type' => "voucher",
				'less_amount' => $final_less_discount,
				'voucher_id' => $res['voucher_id'],
				'voucher_name' => $res['voucher_name'],
				'max_discount_cap' => $res['max_discount_cap'],
				'voucher_owner' => $res['voucher_owner']
			);
		}
		throw new Exception("Voucher code not found");
	}

	public static function applyOffers($merchant_id = '', $offer_id = '', $date = '', $sub_total = 0, $transaction_type = '')
	{
		$stmt = "
    	SELECT * FROM {{offers}}    	
    	WHERE offers_id = " . q($offer_id) . "
    	AND merchant_id =" . q($merchant_id) . "
    	AND status in ('publish','published')
    	AND " . q($date) . " >= valid_from and " . q($date) . " <= valid_to
    	";
		if ($res = Yii::app()->db->createCommand($stmt)->queryRow()) {

			$exchange_rate = self::getExchangeRate();

			$less = floatval($res['offer_percentage']);
			$min_order = floatval($res['min_order']) * $exchange_rate;
			$offer_price = floatval($res['offer_price']) * $exchange_rate;
			$transaction = json_decode($res['applicable_to'], true);

			if ($min_order > 0) {
				if ($min_order > $sub_total) {
					throw new Exception(t("Your order does not meet the minimum spend of {min_order}. Please add more items to qualify for this offer.", array('{min_order}' => Price_Formatter::formatNumber(($min_order * $exchange_rate)))));
				}
			}

			if ($offer_price > 0) {
				if ($offer_price > $sub_total) {
					throw new Exception(t("This offer is valid for orders over {offer_price}. Please increase your order amount to qualify.", array('{offer_price}' => Price_Formatter::formatNumber(($offer_price * $exchange_rate)))));
				}
			}

			if (!in_array($transaction_type, (array)$transaction)) {
				throw new Exception(t("this offer is not valid for [transaction_type]", array('[transaction_type]' => t($transaction_type))));
			}

			return array(
				'promo_type' => "offers",
				'less_amount' => $less,
				'offers_id' => $offer_id,
				'max_discount_cap' => $res['max_discount_cap'],
			);
		}
		throw new Exception("Offers not valid");
	}

	public static function findVoucherByID($voucher_id = '')
	{
		$model = AR_voucher_new::model()->findByPk($voucher_id);
		if ($model) {
			return $model;
		}
		return false;
	}

	public static function findOffers($offers_id = '')
	{
		$model = AR_offers::model()->findByPk($offers_id);
		if ($model) {
			return $model;
		}
		return false;
	}

	public static function getAvaialblePromo($merchant_ids = array(), $date_now = '')
	{
		$today = strtolower(date("l", strtotime($date_now)));
		$stmt = "
		SELECT * FROM {{view_offers}}
		WHERE merchant_id IN (" . implode(',', $merchant_ids) . ")
		AND valid_from<=" . q($date_now) . " and valid_to>" . q($date_now) . "
		AND status='publish'
		AND visible=1
		AND " . $today . "=1
		";
		if ($res = Yii::app()->db->createCommand($stmt)->queryAll()) {
			$data = [];
			foreach ($res as $items) {
				$discount_name = '';
				if ($items['discount_type'] == "voucher") {
					$offer_amount = $items['offer_type'] == "fixed amount" ? Price_Formatter::formatNumber($items['offer_amount']) :
						Price_Formatter::convertToRaw($items['offer_amount'], 0) . "%";
					$discount_name = t("{offer_amount} off w/ code {discount_name}", [
						'{offer_amount}' => $offer_amount,
						'{discount_name}' => $items['discount_name']
					]);
				} else {
					$offer_amount = $items['offer_type'] == "fixed amount" ? Price_Formatter::formatNumber($items['offer_amount']) :
						Price_Formatter::convertToRaw($items['discount_name'], 0) . "%";
					$discount_name = t("{offer_amount} off min. {min_order}", [
						'{offer_amount}' => $offer_amount,
						'{min_order}' => Price_Formatter::formatNumber($items['min_order'])
					]);
				}
				$data[$items['merchant_id']][] = [
					'discount_type' => $items['discount_type'],
					'discount_name' => $discount_name
				];
			}
			return $data;
		}
		throw new Exception("No available promos");
	}

	public static function getAvaialblePromoNew($merchant_ids = array(), $date_now = '', $long_desc = false, $return_once = false)
	{
		$today = strtolower(date("l", strtotime($date_now)));
		$stmt = "
		SELECT * FROM 
		{{promos}}
		WHERE 
		  merchant_id IN (" . implode(',', $merchant_ids) . ")
		AND valid_from<=" . q($date_now) . " and valid_to>" . q($date_now) . "
		AND status='publish'
		AND visible=1
		AND " . $today . "=1
		";
		if ($res = Yii::app()->db->createCommand($stmt)->queryAll()) {
			$data = [];
			$exchange_rate = self::getExchangeRate();
			foreach ($res as $items) {
				$discount_name = '';
				$title = '';
				$discount = '';
				$applicable_to = !empty($items['applicable_to']) ? json_decode($items['applicable_to'], true) : null;
				if ($items['offer_type'] == "voucher") {

					$offer_amount = 0;
					if ($items['discount_type'] == "fixed amount") {
						$offer_amount = Price_Formatter::formatNumber(($items['offer_amount'] * $exchange_rate));
						$discount = $offer_amount;
					} else {
						$offer_amount = Price_Formatter::convertToRaw($items['offer_amount'], 0) . "%";
						$discount = $offer_amount;
					}

					if (!$long_desc) {
						$discountLabel = $items['max_discount_cap'] > 0 ? 'Get {offer_amount} off with {discount_name}! Cap: {max_discount_cap}, Min: {min_order}. Offer expires on {valid_to}' :
							'Enjoy {offer_amount} off with {discount_name}! Min {min_order}, Max {max_order}. Offer expires on {valid_to}';
					} else {
						$discountLabel = $items['max_discount_cap'] > 0 ?
							'Use coupon code {discount_name} to get {offer_amount} off your order. Valid for orders between {min_order} and {max_order}. Maximum discount: {max_discount_cap}. Offer expires on {valid_to}' :
							'Use coupon code {discount_name} to get {offer_amount} off your order. Valid for orders between {min_order} and {max_order}. Offer expires on {valid_to}';
					}

					$title = t("({coupon_name}) {amount}% off", [
						'{coupon_name}' => $items['discount_name'],
						'{amount}' => $items['discount_type'] == 'percentage' ? Price_Formatter::convertToRaw($items['offer_amount'], 0)  : Price_Formatter::formatNumber(($items['offer_amount'] * $exchange_rate))
					]);

					$discount_name = t($discountLabel, [
						'{offer_amount}' => $offer_amount,
						'{discount_name}' => $items['discount_name'],
						'{valid_to}' => Date_Formatter::date($items['valid_to'], "MMMM dd, yyyy", true),
						'{min_order}' => Price_Formatter::formatNumber(($items['min_order'] * $exchange_rate)),
						'{max_order}' => Price_Formatter::formatNumber(($items['max_order'] * $exchange_rate)),
						'{max_discount_cap}' => Price_Formatter::formatNumber(($items['max_discount_cap'] * $exchange_rate)),
					]);
				} else {

					$title = t("{amount}% off over {max_order}", [
						'{amount}' => Price_Formatter::convertToRaw($items['offer_amount'], 0),
						'{max_order}' => Price_Formatter::formatNumber(($items['max_order'] * $exchange_rate))
					]);

					$offer_amount = $items['discount_type'] == "fixed amount" ? Price_Formatter::formatNumber($items['offer_amount']) : Price_Formatter::convertToRaw($items['offer_amount'], 0);
					$discount = $items['discount_type'] == "fixed amount" ? Price_Formatter::formatNumber($items['offer_amount']) : Price_Formatter::convertToRaw($items['offer_amount'], 0) . "%";

					$discountLabel = $items['max_discount_cap'] > 0 ? "Get {offer_amount}% off! orders over {max_order}, Max savings: {max_discount_cap} on {transaction_type}. Offer valid until {valid_to}" :
						"{offer_amount}% off orders over {max_order}! Offer valid until {valid_to}";
					$discount_name = t($discountLabel, [
						'{offer_amount}' => $offer_amount,
						'{valid_to}' => Date_Formatter::date($items['valid_to'], "MMMM dd, yyyy", true),
						'{min_order}' => Price_Formatter::formatNumber(($items['min_order'] * $exchange_rate)),
						'{max_order}' => Price_Formatter::formatNumber(($items['max_order'] * $exchange_rate)),
						'{max_discount_cap}' => Price_Formatter::formatNumber(($items['max_discount_cap'] * $exchange_rate)),
						'{transaction_type}' => CommonUtility::arrayToString($applicable_to)
					]);
				}


				if ($return_once) {
					$data[] = [
						'discount_type' => $items['offer_type'],
						'discount_name' => $discount_name,
						'title' => $title,
						'min_order' => t("Minimum Spend: {amount}", [
							'{amount}' => Price_Formatter::formatNumber(($items['min_order'] * $exchange_rate))
						]),
						'max_order' => t("Maximum Spend: {amount}", [
							'{amount}' => Price_Formatter::formatNumber(($items['max_order'] * $exchange_rate))
						]),
						'max_discount_cap' => t("Maximum Discount Cap: {amount}", [
							'{amount}' => Price_Formatter::formatNumber(($items['max_discount_cap'] * $exchange_rate))
						]),
						'valid_to' => t("Valid Until: {date}", [
							'{date}' => Date_Formatter::date($items['valid_to'])
						]),
						'applicable_to' => t("Applicable to {transaction_type}", [
							'{transaction_type}' => CommonUtility::arrayToString($applicable_to)
						])
					];
				} else {
					$data[$items['merchant_id']][] = [
						'discount_type' => $items['offer_type'],
						'promo_type' => $items['offer_type'],
						'discount_name' => $discount_name,
						'title' => $title,
						'discount' => t("{discount} off", [
							'{discount}' => $discount
						]),
						'min_order' => t("Minimum Spend: {amount}", [
							'{amount}' => $items['min_order']
						]),
					];
				}
			}
			return $data;
		}
		throw new Exception("No available promos");
	}

	public static function fetchHolidays($merchant_id = 0)
	{
		$criteria = new CDbCriteria();
		$criteria->condition = "merchant_id=:merchant_id";
		$criteria->params = [
			':merchant_id' => $merchant_id
		];
		$criteria->order = "id DESC";
		if ($model = AR_holidays::model()->findAll($criteria)) {
			$data = [];
			foreach ($model as $items) {
				$data[] = [
					'id' => $items->id,
					'holiday_name' => $items->holiday_name,
					'holiday_date' => $items->holiday_date,
					'reason' => $items->reason,
				];
			}
			return $data;
		}
		throw new Exception(t(HELPER_NO_RESULTS));
	}
}
/*end class*/