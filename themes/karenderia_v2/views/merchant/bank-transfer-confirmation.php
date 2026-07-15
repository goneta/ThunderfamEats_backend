<div class="container mt-3 mb-3">


<div class="receipt-section mb-4 mt-5">
  <img src="<?php echo Yii::app()->theme->baseUrl."/assets/images/receipt.png"?>" />
</div>

<div class="text-center mb-5">
  <h3><?php echo t("Bank Transfer Instructions Sent")?></h3>  
</div>
<div style="max-width: 500px;margin:auto;">
    <p><?php echo t("Dear {name}",[
        '{name}'=>isset($name)?$name:''
    ])?>,</p>
    <p>
        <?php echo t("Thank you for choosing to pay via bank transfer. We have sent an email to {email} with the bank transfer instructions.",[
            '{email}'=>isset($email_address)?$email_address:''
        ])?>
    </p>
    <p><?php echo t("Please follow the instructions in the email to complete your payment. Once we receive your payment, we will activate your subscription.")?></p>
    <p><?php echo t("If you have any questions, please contact our support team.")?></p>
    <p><?php echo t("Best regards")?>,<br><?php echo isset($title)?$title:''?></p>
</div>
 
</div> <!--container-->