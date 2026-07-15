<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', $links);
?>
</nav>

<div class="card">

  <div class="card-body">
  <?php 

    require 'php-jwt/vendor/autoload.php';
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    ?>
  <?php if($model->isNewRecord):?>
    <?php 
			$user_token = [
				'iss'=>Yii::app()->request->getServerName(),
				'sub'=>$model->merchant_uuid,
				'iat'=>time(),
			];
			$user_token = JWT::encode($user_token, CRON_KEY, 'HS256');
				
			$payload_pickup = [		
				'user_token'=>$user_token,
				'merchant_id'=>$model->merchant_id,
				'transaction_type'=>'pickup',
			];					
			$payload_dinein = [		
				'user_token'=>$user_token,
				'merchant_id'=>$model->merchant_id,
				'transaction_type'=>'dinein',
			];					
						
			$qrcode_pickup = JWT::encode($payload_pickup, CRON_KEY, 'HS256');						
			$qrcode_dinein = JWT::encode($payload_dinein, CRON_KEY, 'HS256');						
			
			
			$download_qrcode_pickup = Yii::app()->CreateUrl("/merchant/view_qrcode",[
				'data'=>$qrcode_pickup
			]);	
			$download_qrcode_dinein = Yii::app()->CreateUrl("/merchant/view_qrcode",[
				'data'=>$qrcode_dinein
			]);	?>
    <?php echo $this->renderPartial('merchant_info', array(
      'model'=>$model,
      'status'=>$status,
	  'cuisine'=>$cuisine,
	  'tags'=>$tags,
	  'services'=>$services,
	  'unit'=>$unit,
	  'featured'=>$featured,
	  'upload_path'=>$upload_path,
    'download_qrcode_pickup'=>isset($download_qrcode_pickup)?$download_qrcode_pickup:"",
    'download_qrcode_dinein'=>isset($download_qrcode_dinein)?$download_qrcode_dinein:"",
    'zone_type'=>AttributesTools::zoneType(),
	  'show_status'=>true
    )); ?>
  <?php else :?>
  <div class="row">
    <div class="col-md-3">
         
    <div class="attributes-menu-wrap">
	<?php $this->widget('application.components.WidgetMerchantAttMenu',array(
	 'id'=>$model->merchant_id
	));?>
	</div>
    
    </div> <!--col-->
    <div class="col-md-9">
    
     <?php echo $this->renderPartial('merchant_info', array(
      'model'=>$model,
      'status'=>$status,
	  'cuisine'=>$cuisine,
	  'tags'=>$tags,
	  'services'=>$services,
	  'unit'=>$unit,
	  'featured'=>$featured,
	  'show_status'=>true
    )); ?>  
    
    </div>
  </div> <!--row-->
  <?php endif;?>
  
  </div> <!--card-body-->

</div> <!--card-->