<components-bank
ref="<?php echo $payment_code;?>"
title="<?php echo t("Add Bank Transfer")?>"	 	  
payment_code="<?php echo $payment_code;?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
@set-paymentlist="SavedPaymentList"	 	
:label="{		    
submit: '<?php echo CJavaScript::quote(t("Submit"))?>',
notes : '<?php echo CJavaScript::quote(t("Pay using bank Transfer."))?>'
}"  
>
</components-bank>