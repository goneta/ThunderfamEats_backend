<components-touchpay
ref="<?php echo $payment_code;?>"
title="<?php echo t("Add TouchPay")?>"	 	  
payment_code="<?php echo $payment_code;?>"
merchant_id="<?php echo isset($credentials['merchant_id'])?$credentials['merchant_id']:0;?>"
redirect="<?php echo $redirect;?>"
ajaxurl="<?php echo $ajaxurl;?>"
:cart_uuid="cart_uuid"
@set-paymentlist="SavedPaymentList"	 	
@after-cancel-payment="AfterCancelPayment"	
@alert="Alert"	
@show-loader="showLoadingBox"	
@close-loader="closeLoadingBox"
:label="{		    
    submit: '<?php echo t("Saved")?>',
    notes : '',    
}"  
>
</components-touchpay>