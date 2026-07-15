<components-paypal
ref="<?php echo $payment_code?>"
title="<?php echo t("Add Paypal")?>"	 	  
payment_code="<?php echo $payment_code?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
client_id="<?php echo isset($credentials['attr1'])?$credentials['attr1']:''; ?>"
:amount="amount_to_pay"
:cart_uuid="cart_uuid"
currency_code="<?php echo Price_Formatter::$number_format['currency_code'];?>"
ajax_url = "<?php echo Yii::app()->createAbsoluteUrl("$payment_code/paypalapi")?>"
@set-paymentlist="SavedPaymentList"	 	
@after-cancel-payment="AfterCancelPayment"	
@after-successfulpayment="afterSuccessfulpayment"	
@after-failedpayment="afterFailedpayment"	
@close-topup="closeTopup"
:label="{		    
submit: '<?php echo CJavaScript::quote(t("Add Paypal"))?>',
notes : '<?php echo CJavaScript::quote(t("Pay using your paypal account"))?>',
payment_title : '<?php echo CJavaScript::quote(t("Pay using Paypal"))?>',
payment_notes : '<?php echo CJavaScript::quote(t("You will re-direct to paypal account to login to your account."))?>',
}"  
:on_error="{		    
error: '<?php echo CJavaScript::quote(t("An error has occured"))?>',
}"  
>
</components-paypal>