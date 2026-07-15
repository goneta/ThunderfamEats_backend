
<div class="container p-3">
<div class="row">
    <div class="col-md-6">
        <H5><?php echo t("Upload Proof of Payment")?></H5>
        
        <p class="m-0">Please enter the details of your bank deposit payment below.</p>
        <p>Failure to provide accurate information may cause delays in processing or invalidation of your payment</p>


                
        <?php
        $form = $this->beginWidget(
            'CActiveForm',
            array(
                'id' => 'upload-form',
                'enableAjaxValidation' => false,
                'htmlOptions' => array('enctype' => 'multipart/form-data'),
            )
        );
        ?>

        <?php if(Yii::app()->user->hasFlash('success')): ?>
            <div class="alert alert-success">
                <?php echo Yii::app()->user->getFlash('success'); ?>
            </div>
        <?php endif;?>

        <?php if(Yii::app()->user->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <?php echo Yii::app()->user->getFlash('error'); ?>
            </div>
        <?php endif;?>


        <div class="row pb-2">    
           <div class="col-2"><?php echo t("Order")?>#:</div>
           <div class="col"><?php echo $order->order_id?></div>
        </div>    

        <div class="row pb-2">    
           <div class="col-2"><?php echo t("Amount")?></div>
           <div class="col"><?php echo Price_Formatter::formatNumber( ($order->total*$order->exchange_rate) )?></div>
        </div>    
                
        <div class="form-label-group">    
        <?php echo $form->textField($model,'account_name',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'account_name')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'account_name'); ?>
        <?php echo $form->error($model,'account_name'); ?>
        </div>

        <div class="form-label-group">    
        <?php echo $form->textField($model,'amount',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'amount')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'amount'); ?>
        <?php echo $form->error($model,'amount'); ?>
        </div>

        <div class="form-label-group">    
        <?php echo $form->textField($model,'reference_number',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'reference_number')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'reference_number'); ?>
        <?php echo $form->error($model,'reference_number'); ?>
        </div>
        
        
        <div class="form-label-group">    
        <?php echo $form->fileField($model,'proof_image',array(
            'class'=>"form-control form-control-text",
            'placeholder'=>$form->label($model,'proof_image')     
        )); ?>   
        <?php    
            echo $form->labelEx($model,'proof_image'); ?>
        <?php echo $form->error($model,'proof_image'); ?>
        </div>

                
        <?php         
        echo CHtml::submitButton('submit',array(
        'class'=>"btn btn-green btn-full",
        'value'=>$model->isNewRecord?t("Submit"):t("Update")
        )); ?>

        
        <?php $this->endWidget(); ?>

        
    </div>
</div>
</div>