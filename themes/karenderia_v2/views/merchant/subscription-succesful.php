<div class="container mt-3 mb-3">


<div class="receipt-section mb-4 mt-5">
  <img class="img-fluid" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/receipt.png"?>" />
</div>

<div class="text-center mb-5">
  <p class="m-0"><?php echo t("THANKS FOR JOINING")?></p>
  <h3 class="mb-4"><?php echo t("Thank You for Subscribing!")?></h3>

  <p>
    <?php echo t("Your subscription plan has been successfully activated")?>.
    <br/>
    <?php echo t("We've sent a confirmation to your email with the details of your subscription")?>.
    <br/>
    <?php echo t("If you have any questions or need assistance, feel free to contact our support team")?>.
  </p>  
  <p>
  <?php echo t('You can login to merchant portal by clicking {{start_link}}here{{end_link}}',array(
   '{{start_link}}'=>'<a href="'.Yii::app()->createUrl('/backoffice/merchant').'" target="_blank" class="text-green">',
   '{{end_link}}'=>'</a>'
  ))?>
  <a href="{{merchant_dashboard_url}}">Go to Dashboard</a>
  </p>  
</div>
 
</div> <!--container-->