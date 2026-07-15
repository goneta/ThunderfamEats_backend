<components-mercadopago
ref="<?php echo $payment_code?>"
title="<?php echo t("Add mercadopago")?>"	 	  
payment_code="<?php echo $payment_code?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
merchant_type="<?php echo isset($credentials['merchant_type'])?$credentials['merchant_type']:2;?>"
public_key="<?php echo isset($credentials['attr1'])?$credentials['attr1']:''; ?>"
:amount="amount_to_pay"
:cart_uuid="cart_uuid"
currency_code="<?php echo Price_Formatter::$number_format['currency_code'];?>"
ajax_url = "<?php echo Yii::app()->createAbsoluteUrl("$payment_code/Mercadopago")?>"

@set-paymentlist="SavedPaymentList"	 	
@after-cancel-payment="AfterCancelPayment"	
@after-successfulpayment="afterSuccessfulpayment"	
@after-failedpayment="afterFailedpayment"	
@close-topup="closeTopup"
@alert="Alert"	
@show-loader="showLoadingBox"	
@close-loader="closeLoadingBox"

:label="{		    
submit: '<?php echo CJavaScript::quote(t("Submit"))?>',
notes : '<?php echo CJavaScript::quote(t("Pay using your mercadopago account"))?>',
card_name: '<?php echo CJavaScript::quote(t("Card Name"))?>',
credit_card_number: '<?php echo CJavaScript::quote(t("Card Number"))?>',
expiry_date: '<?php echo CJavaScript::quote(t("Exp. Date MM/YYYY"))?>',
cvv: '<?php echo CJavaScript::quote(t("Security Code"))?>',
card_name: '<?php echo CJavaScript::quote(t("Card Name"))?>',
email_address: '<?php echo CJavaScript::quote(t("Email Address"))?>',
identification_type: '<?php echo CJavaScript::quote(t("Identification"))?>',
identification_number: '<?php echo CJavaScript::quote(t("Identification Number"))?>',
getting_payment : '<?php echo CJavaScript::quote(t("Getting payment information...."))?>',
enter_cvv : '<?php echo CJavaScript::quote(t("Enter CVV for card"))?>',
cvv_verification : '<?php echo CJavaScript::quote(t("Verification"))?>',
}"  
:on_error="{		    
error: '<?php echo CJavaScript::quote(t("An error has occured"))?>',
}"  
>
</components-mercadopago>