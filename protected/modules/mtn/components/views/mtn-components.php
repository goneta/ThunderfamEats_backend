<components-mtn
ref="<?php echo $payment_code;?>"
title="<?php echo t("Add MTN Momo")?>"	 	  
payment_code="<?php echo $payment_code;?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
@set-paymentlist="SavedPaymentList"	 	
@after-cancel-payment="AfterCancelPayment"	
:label="{		    
 submit: '<?php echo CJavaScript::quote(t("Submit"))?>',
 notes : '',
 enter_phone: '<?php echo CJavaScript::quote(t("Enter your mobile number"))?>',
 mobile_number: '<?php echo CJavaScript::quote(t("Mobile number"))?>',
}"  
>
</components-mtn>