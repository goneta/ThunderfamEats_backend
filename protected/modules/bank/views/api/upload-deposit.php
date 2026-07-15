<html>
<head>
<title><?php echo $this->pageTitle?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
</head>
<body>

<div id="q-app" >

<q-layout view="hHh lpR fFf">

    <q-page-container>
      <q-page >
        <q-header class="bg-white">
          <q-toolbar>
            <q-btn                
                flat
                round
                dense
                icon="arrow_back"
                color="dark"
                href="<?php echo $redirect_url;?>"
            >            
            </q-btn>
            <q-toolbar-title class="text-subtitle2 text-weight-bold text-dark">
              <?php echo $page_title ?? '' ?>
            </q-toolbar-title>
            </q-toolbar>
        </q-header>

      
        <div class="absolute-center full-width q-pa-md" >
            <div class="text-weight-bold text-subtitle2 q-mb-md">
            You've selected Bank Deposit as your payment method.
            </div>
            <div class="text-body2">
            <?php echo CommonUtility::safeTranslate("To complete your wallet load, please transfer the amount to our bank account using the instructions we will send to your email.")?>
            <br/>
            <?php echo t("An email with full bank deposit instructions and a secure link to upload your payment proof has been sent to: {email_address}",[
                '{email_address}'=> $email_address ?? ''
            ])?>
            <br/><br/>
            <?php echo CommonUtility::safeTranslate("Once your payment is verified, your wallet will be updated.")?>
            <br/>
            <?php echo CommonUtility::safeTranslate("Verification may take up to 24 hours.")?>
            </div>            
            <div class="q-mt-md">
              <q-btn color="blue-grey-6" 
              href="<?php echo $redirect_url;?>"
              label="<?php echo CommonUtility::safeTranslate("Return to Wallet")?>" unelevated no-caps icon="arrow_back" ></q-btn>
            </div>
        </div>

      </q-page>
    </q-page-container>

</q-layout>

</div>

</body>
</html>