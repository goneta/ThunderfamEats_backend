<components-paydelivery
ref="<?php echo $payment_code;?>"
title="<?php echo t("Add Payment")?>"	 	  
payment_code="<?php echo $payment_code;?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
:cart_uuid="cart_uuid"
ajax_url = "<?php echo Yii::app()->createAbsoluteUrl("$payment_code/api")?>"
@set-paymentlist="SavedPaymentList"
:label="{    
    submit: '<?php echo CJavaScript::quote(t("Saved"))?>',
}"
>
</components-paydelivery>