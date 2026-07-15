<ul id="vue-cart-preview" class="top-menu list-unstyled " v-cloak> 

  <li class="d-none d-lg-inline">   
      <?php $this->widget('application.components.WidgetCurrencySelection');?>
  </li>

  <li class="d-none d-lg-inline">      
      <?php $this->widget('application.components.WidgetLangselection');?>
  </li>

 <li class="d-none d-lg-inline">
 
 <a href="<?php echo $cart_preview==true?'javascript:;':'#vue-cart'?>" 
   class="<?php echo $cart_preview==true?'ssm-toggle-navx':''?>"
   <?php if($cart_preview):?>
    @click="showCartPreview"  
    <?php endif?>
   >
   <?php echo t("Cart")?>
 </a>
 
 </li>
 <li class="d-inline pr-2">     
 <a href="<?php echo $cart_preview==true?'javascript:;':'#vue-cart'?>" 
    class="cart-handle <?php echo $cart_preview==true?'ssm-toggle-navx':''?>"
    <?php if($cart_preview):?>
    @click="showCartPreview"  
    <?php endif?>
    >
    <img src="<?php echo Yii::app()->theme->baseUrl."/assets/images/shopping-bag.svg"?>" />
    <span class="badge small badge-dark rounded-pill">
    {{items_count}}
    </span>
 </a>
 
 </li>
<li class="d-none d-lg-inline line-left">
   <a href="<?php echo Yii::app()->createUrl("/account/login")?>"><?php echo t("Sign in")?></a>  
 </li>

 <li class="ml-3 ml-xs-1  d-inline d-lg-none">
  <div @click="drawer=true" class="hamburger hamburger--3dx">
   <div class="hamburger-box">
      <div class="hamburger-inner"></div>
   </div>
  </div> 
 </li>
 
<?php Yii::app()->controller->renderPartial("//components/cart-preview",array(
 'cart_preview'=>$cart_preview
))?>

<component-notification-cart
 ref="notification_cart" 
 ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 		      
 :realtime="{
   enabled : '<?php echo Yii::app()->params['realtime_settings']['enabled']==1?true:false ;?>',  
   provider : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['provider'] )?>',  			   
   key : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['key'] )?>',  			   
   cluster : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['cluster'] )?>', 
   ably_apikey : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['ably_apikey'] )?>', 
   piesocket_api_key : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['piesocket_api_key'] )?>', 
   piesocket_websocket_api : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['piesocket_websocket_api'] )?>', 
   piesocket_clusterid : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['piesocket_clusterid'] )?>',    
   event : '<?php echo CJavaScript::quote( Yii::app()->params->realtime['event_cart'] )?>',  
 }"  			       
 @after-receiveitemupdate="afterReceiveitemupdate"
 >		      
 </component-notification-cart>

<el-drawer v-model="drawer"
 direction="ltr" 
 custom-class="drawer-menu"
 :with-header="false"
 size="60%"
 >
 
 <template #default>
 <a href="<?php echo Yii::app()->createUrl("/account/login")?>" class="btn btn-black text-white w-100 rounded-0"><?php echo t("Sign in")?></a>
 <div class="mt-4">    
   <ul class="list-unstyled">
      <li><a href="<?php echo Yii::app()->createUrl("/merchant")?>"><?php echo t("Add your restaurant")?></a></li>
      <li><a href="<?php echo Yii::app()->createUrl("/deliveryboy/signup")?>"><?php echo t("Sign up to deliver")?></a></li>
   </ul>

   <hr/>

   <components-language
   ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
   >
   </components-language>
 

 </div>
 </template>

 <template #footer >
   <div class="text-left">
      <!-- <div class="d-flex align-items-center">
         <div class="mr-2">
            <?php 
               $this->widget('application.components.WidgetSiteLogo',array(
                  'class_name'=>'img-fluid'
               ));
            ?>
         </div>
         <div class="">
            <p class="m-0 font-weight-bold"><?php echo t("Best restaurants In your pocket")?></p>
         </div>
      </div>
      <a class="btn btn-light mt-2 rounded-pill"><?php echo t("Get the app")?></a> -->
   </div>
 </template>

</el-drawer>
 
</ul>