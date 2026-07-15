<components-cod
ref="<?php echo $payment_code;?>"
title="<?php echo t("Add Cash On delivery")?>"	 	  
payment_code="<?php echo $payment_code;?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
@set-paymentlist="SavedPaymentList"	 	
:label="{		    
submit: '<?php echo CJavaScript::quote(t("Add Cash"))?>',
notes : '<?php echo CJavaScript::quote(t("Cash on Delivery or COD is a payment method that allows pay for the items you have ordered only when it gets delivered."))?>'
}"  
>
</components-cod>