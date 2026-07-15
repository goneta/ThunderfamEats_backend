<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>$links,
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>

<div class="row mb-1">
    <div class="col">
        <h4 class="m-0"><?php echo t("Details")?></h4>
    </div>
    <div class="col text-right">

    <div class="dropdown dropleft">
        <a class="rounded-pill rounded-button-icon d-inline-block bg-primary" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="zmdi zmdi-more" style="color: #fff;"></i>
        </a>
    
        <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">			  
           <a class="dropdown-item" href="<?php echo Yii::app()->CreateUrl("/invoicemerchant/pdf",['invoice_uuid'=>$model->invoice_uuid]);?>" ><?php echo t("Download PDF")?></a>           
           <a class="dropdown-item" href="<?php echo Yii::app()->CreateUrl("/invoicemerchant/uploaddeposit",['invoice_uuid'=>$model->invoice_uuid]);?>" ><?php echo t("Upload Deposit")?></a>           
        </div>
    </div>

    </div>
</div>
<!-- row -->

<div class="row align-items-start">
    <div class="col">
<div class="card">
    <div class="card-body">
    
     <div class="row">
        <div class="col">
            <p class="m-0"><?php echo t("Invoice No#: {invoice_number}",['{invoice_number}'=>$model->invoice_number])?></p>
            <p class="m-0"><?php echo t("Invoice Date : {invoice_created}",['{invoice_created}'=> Date_Formatter::date($model->invoice_created) ])?></p>
            <p class="m-0"><?php echo t("Due Date : {due_date}",['{due_date}'=> Date_Formatter::date($model->due_date) ])?></p>            
        </div>
        <div class="col text-right">
            <h5 class="m-0 p-0"><span class="badge payment <?php echo $model->payment_status;?>"><?php echo strtoupper($model->payment_status)?></span></h5>
            <h4 class="m-0"><?php echo Price_Formatter::formatNumber( ($model->invoice_total-$model->amount_paid) ) ?></h4>            
            <?php if($is_due):?>
              <div class="text-warning bold"><i class="zmdi zmdi-info"></i> <?php echo t("OVERDUE")?></div>
            <?php else :?>
              <div class="text-warning bold"><?php echo t("AMOUNT DUE")?></div>
            <?php endif?>
        </div>
     </div>
     <!-- row -->

     <div>
       <h5 class="m-0"><?php echo t("BILL TO")?></h5>
       <p class="m-0"><?php echo CHtml::encode($model->restaurant_name)?></p>
       <p class="m-0"><?php echo CHtml::encode($model->business_address)?></p>
     </div>

     <table class="table mt-3">
        <thead class="thead-light">
            <tr>
                <th><?php echo t("Description")?></th>
                <th class="text-right pr-4"><?php echo t("Total")?></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo t("Commission ({from} - {to})",[
                            '{from}'=>Date_Formatter::date($model->date_from,"dd MMM yyyy",true),
                            '{to}'=>Date_Formatter::date($model->date_to,"dd MMM yyyy",true),
                ]);?></td>
                <td class="text-right pr-4"><?php echo Price_Formatter::formatNumber($model->invoice_total)?></td>
            </tr>
            <tr>
                <td colspan="2" class="text-right"> 
                    <table class="table" style="width: 50%;margin-left:auto;padding:0;">
                        <tbody>
                            <tr>
                                <td style="border-top: 0px;"><?php echo t("Sub total")?></td>
                                <td style="border-top: 0px;"><?php echo Price_Formatter::formatNumber($model->invoice_total)?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold"><?php echo t("TOTAL")?></td>
                                <td><?php echo Price_Formatter::formatNumber($model->invoice_total)?></td>
                            </tr>
                            <tr>
                                <td><?php echo t("Amount paid")?></td>
                                <td><?php echo Price_Formatter::formatNumber($model->amount_paid)?></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold"><?php echo t("AMOUNT DUE")?></td>
                                <td><?php echo Price_Formatter::formatNumber( ($model->invoice_total-$model->amount_paid) ) ?></td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
     </table>

    </div>
    <!-- card-body -->
</div>
<!-- card -->
</div> 
<!-- col -->

<div class="col-3" style="padding-left:5px;padding-right:10px;">
   <div class="card border">
     <div class="card-body">
        <h5 class="card-title row">
            <div class="col"><?php echo t("Balance Due")?></div>
            <div class="col-5 text-right">
              <?php echo Price_Formatter::formatNumber( ($model->invoice_total-$model->amount_paid) ) ?>
            </div>
        </h5>        
     </div>
   </div>
   <!-- card -->

   <div class="p-2"></div>

   <?php if($payment_info):?>
   <div class="card border"> 
      <div class="card-body">             
          <p class="m-1">
            <?php echo t("The amount {balance_due} should be deposited before {due_date} into our account",[
                '{due_date}'=>Date_Formatter::date($model->due_date),
                '{balance_due}'=>Price_Formatter::formatNumber( ($model->invoice_total-$model->amount_paid) )
            ])?>
          </p>
          <h6>Payment information</h6>
          <div class="pre-scrollable" style="max-height: 40vh">
            <ul class="m-0 p-1">            
                <li class="mb-1">
                    <div class="badge pl-0"><?php echo t("Bank name")?></div>
                    <p class="m-0"><?php echo $payment_info['bank_name']?></p>
                </li>                        
                <li class="mb-1">
                    <div class="badge pl-0"><?php echo t("Account name")?></div>
                    <p class="m-0"><?php echo $payment_info['account_name']?></p>
                </li>                        
                <li class="mb-1">
                    <div class="badge pl-0"><?php echo t("Account number")?></div>
                    <p class="m-0"><?php echo $payment_info['account_number']?></p>
                </li>                        
            </ul>
         </div>
      </div>
   </div>
   <?php endif;?>
   <!-- card -->

   <div class="p-2"></div>

   <div class="card border">
     <div class="card-body">
        <h5 class="card-title"><?php echo t("Invoice activity")?></h5>

        <div class="pre-scrollable" style="max-height: 75vh">
        <?php if(is_array($history) && count($history)>=1):?>
        <ul class="m-0 p-1">
            <?php foreach ($history as $items):?>
            <li class="mb-2">
                <div class="badge"><?php echo Date_Formatter::dateTime($items->meta_value2,"dd MMM yyyy h:mm a",true)?></div>
                <p class="m-0"><?php echo $items->meta_value1?></p>
            </li>            
            <?php endforeach;?>
        </ul>
        <?php endif?>
        </div>
     </div>
   </div>
   <!-- card -->

</div>        
<!-- col -->
</div> 
<!-- row -->