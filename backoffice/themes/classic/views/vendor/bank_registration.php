
<?php  if(is_array($payment_provider) && count($payment_provider)>=1): ?>
<ul class="nav nav-tabs">
  <?php foreach ($payment_provider as $key => $items):?>  
  <li class="nav-item">
    <a class="nav-link <?php echo $provider_selected==$items['payment_code']?"active":'';?>" href="<?php 
    echo Yii::app()->createUrl("/vendor/bank_registration",[
      'id'=>$id,
      'payment_code'=>$items['payment_code']
    ])
    ?>"><?php echo $items['payment_name']?></a>
  </li>  
  <?php endforeach?>
</ul>

<div class="tab-content" >
<div class="tab-pane fade show active p-2 pt-4"  role="tabpanel">
    <div id="vue-bankregistration">             
        <?php CComponentsManager::renderComponents2($payment_provider,$payments_credentials,$this,'',[
          'merchant_id'=>$id,
          'merchant_type'=>$merchant_type,
          'provider_selected'=>$provider_selected
        ])?>   

        <components-loading-box
        ref="box"
        message="<?php echo t("Processing ...")?>"
        donnot_close="<?php echo t("don't close this window")?>"
        >
        </components-loading-box>
        
    </div>
</div>
</div>
<?php endif?>