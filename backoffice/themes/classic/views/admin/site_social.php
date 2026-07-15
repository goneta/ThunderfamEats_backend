
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

<!-- GOOGLE  -->
<div class="card" style="border:1px solid rgba(0,0,0,.125)">
  <div class="card-header font-weight-bold"><?php echo t("Google Login")?></div>
  <div class="card-body">
  
     <div class="row">
        <div class="col">

        <div class="custom-control custom-switch custom-switch-md">  
          <?php echo $form->checkBox($model,"google_login_enabled",array(
            'class'=>"custom-control-input checkbox_child",     
            'value'=>1,
            'id'=>"google_login_enabled",
            'checked'=>$model->google_login_enabled==1?true:false
          )); ?>   
          <label class="custom-control-label" for="google_login_enabled">
          <?php echo t("Web Login Enabled")?>
          </label>
        </div>    

        </div>
        <div class="col">
        
        
          <div class="custom-control custom-switch custom-switch-md">  
            <?php echo $form->checkBox($model,"app_enabled_google_login",array(
              'class'=>"custom-control-input checkbox_child",     
              'value'=>1,
              'id'=>"app_enabled_google_login",
              'checked'=>$model->app_enabled_google_login==1?true:false
            )); ?>   
            <label class="custom-control-label" for="app_enabled_google_login">
            <?php echo t("App Login Enabled")?>
            </label>
          </div>    

        </div>
     </div>
   
    
    <div class="form-group">
      <?php echo $form->labelEx($model,'google_client_id');?>
      <span class="ml-2 font14"><i data-toggle="tooltip" title="<?php echo t("Used for Google login on your website. Obtain this from your Google Cloud Console under OAuth 2.0 Client IDs with application type = Web")?>" class="zmdi zmdi-help"></i></span>
      <?php echo $form->textField($model,'google_client_id',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

    <div class="form-group">
      <?php echo $form->labelEx($model,'app_google_client_id');?>
      <span class="ml-2 font14"><i data-toggle="tooltip" title="<?php echo t("Used for Google login in your Android or iOS mobile apps. Must be created as a separate OAuth 2.0 Client ID with application type = Web")?>" class="zmdi zmdi-help"></i></span>
      <?php echo $form->textField($model,'app_google_client_id',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

    <div class="form-group">
      <?php echo $form->labelEx($model,'google_client_secret');?>
      <?php echo $form->textField($model,'google_client_secret',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>


  </div>  
</div>
<!-- GOOGLE -->

<div class="pb-3"></div>

<!-- FACEBOOK  -->
<div class="card" style="border:1px solid rgba(0,0,0,.125)">
  <div class="card-header font-weight-bold"><?php echo t("Facebook Login")?></div>
  <div class="card-body">
  
     <div class="row">
        <div class="col">

        <div class="custom-control custom-switch custom-switch-md">  
          <?php echo $form->checkBox($model,"fb_flag",array(
            'class'=>"custom-control-input checkbox_child",     
            'value'=>1,
            'id'=>"fb_flag",
            'checked'=>$model->fb_flag==1?true:false
          )); ?>   
          <label class="custom-control-label" for="fb_flag">
          <?php echo t("Web Login Enabled")?>
          </label>
        </div>    

        </div>
        <div class="col">
        
        
          <div class="custom-control custom-switch custom-switch-md">  
            <?php echo $form->checkBox($model,"app_enabled_fb_login",array(
              'class'=>"custom-control-input checkbox_child",     
              'value'=>1,
              'id'=>"app_enabled_fb_login",
              'checked'=>$model->app_enabled_fb_login==1?true:false
            )); ?>   
            <label class="custom-control-label" for="app_enabled_fb_login">
            <?php echo t("App Login Enabled")?>
            </label>
          </div>    

        </div>
     </div>
   
    
    <div class="form-group">
      <?php echo $form->labelEx($model,'fb_app_id');?>
      <?php echo $form->textField($model,'fb_app_id',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

    <div class="form-group">
      <?php echo $form->labelEx($model,'fb_app_secret');?>
      <?php echo $form->textField($model,'fb_app_secret',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

    <div class="form-group">
      <?php echo $form->labelEx($model,'app_facebook_client_token');?>
      <?php echo $form->textField($model,'app_facebook_client_token',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>


  </div>  
</div>
<!-- FACEBOOK -->

<div class="pb-3"></div>

<!-- APPLE  -->
<div class="card" style="border:1px solid rgba(0,0,0,.125)">
  <div class="card-header font-weight-bold"><?php echo t("Apple Login")?></div>
  <div class="card-body">
  
     <div class="row">
        <div class="col">

        <div class="custom-control custom-switch custom-switch-md">  
          <?php echo $form->checkBox($model,"web_enabled_apple_login",array(
            'class'=>"custom-control-input checkbox_child",     
            'value'=>1,
            'id'=>"web_enabled_apple_login",
            'checked'=>$model->web_enabled_apple_login==1?true:false
          )); ?>   
          <label class="custom-control-label" for="web_enabled_apple_login">
          <?php echo t("Web Login Enabled")?>
          </label>
        </div>    

        </div>
        <div class="col">
        
        
          <div class="custom-control custom-switch custom-switch-md">  
            <?php echo $form->checkBox($model,"app_enabled_apple_login",array(
              'class'=>"custom-control-input checkbox_child",     
              'value'=>1,
              'id'=>"app_enabled_apple_login",
              'checked'=>$model->app_enabled_apple_login==1?true:false
            )); ?>   
            <label class="custom-control-label" for="app_enabled_apple_login">
            <?php echo t("App Login Enabled")?>
            </label>
          </div>    

        </div>
     </div>

    <div class="form-group">
      <label for="website_redirect_uri" class="col-sm-3 col-form-label"><?php echo t("Website Redirect URI")?></label>
      <div class="col-sm-9">
        <input type="text" readonly class="form-control-plaintext border p-2" id="website_redirect_uri" value="<?php echo $website_redirect_uri ?? ''?>">
      </div>
    </div>
   
    <div class="form-group">
      <label for="web_redirect_uri" class="col-sm-3 col-form-label"><?php echo t("App Web Redirect URI")?></label>
      <div class="col-sm-9">
        <input type="text" readonly class="form-control-plaintext border p-2" id="web_redirect_uri" value="<?php echo $web_redirect_uri ?? ''?>">
      </div>
    </div>

    <div class="form-group">
      <label for="app_redirect_uri" class="col-sm-3 col-form-label"><?php echo t("App Redirect URI")?></label>
      <div class="col-sm-9">
        <input type="text" readonly class="form-control-plaintext border p-2" id="app_redirect_uri" value="<?php echo $app_redirect_uri ?? ''?>">
      </div>
    </div>
    
    <div class="form-group">
      <?php echo $form->labelEx($model,'app_apple_app_id');?>
      <?php echo $form->textField($model,'app_apple_app_id',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

    <div class="form-group">
      <?php echo $form->labelEx($model,'app_apple_team_id');?>
      <?php echo $form->textField($model,'app_apple_team_id',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

     <div class="form-group">
      <?php echo $form->labelEx($model,'app_apple_key_id');?>
      <?php echo $form->textField($model,'app_apple_key_id',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
    </div>

     <h5 class="pb-1"><?php echo t("Service file")?></h5>
      <?php if(!empty($model->app_apple_key_crt)): ?>
      <div class="pb-4"><b><?php echo t("Current Uploaded File")?></b> : <?php echo $model->app_apple_key_crt?></div>
      <?php endif?>
      <div class="form-group">
      <?php 
      echo $form->fileField($model, 'file_crt',array(
          'class'=>"form-control-file"
      ));
      echo $form->error($model, 'file_crt');
      ?>
    </div>


  </div>  
</div>
<!-- APPLE -->

<div class="pb-3"></div>

<!-- SOCIAL URI -->
<div class="card" style="border:1px solid rgba(0,0,0,.125)">
  <div class="card-header font-weight-bold"><?php echo t("Social URL Page")?></div>
  <div class="card-body">
  
     <div class="form-group">
      <?php echo $form->labelEx($model,'facebook_page');?>
      <?php echo $form->textField($model,'facebook_page',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
     </div>

      <div class="form-group">
      <?php echo $form->labelEx($model,'twitter_page');?>
      <?php echo $form->textField($model,'twitter_page',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
     </div>

    <div class="form-group">
      <?php echo $form->labelEx($model,'google_page');?>
      <?php echo $form->textField($model,'google_page',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
     </div>

     <div class="form-group">
      <?php echo $form->labelEx($model,'instagram_page');?>
      <?php echo $form->textField($model,'instagram_page',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
     </div>

      <div class="form-group">
      <?php echo $form->labelEx($model,'linkedin_page');?>
      <?php echo $form->textField($model,'linkedin_page',array(
        'class'=>"form-control form-control-lg",        
      )); ?>         
     </div>

  </div>
</div>
<!-- SOCIAL URI -->


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>