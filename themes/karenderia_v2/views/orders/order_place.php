
<div id="vue-orders-track" class="container" v-cloak  >

 <component-order-tracking
 ref="tracking" 
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
   channel : '<?php echo CJavaScript::quote( Yii::app()->user->client_uuid  )?>',  			   
   event : '<?php echo CJavaScript::quote( Yii::app()->params->realtime['event_tracking_order'] )?>',  
 }"  			      
 @after-receive="afterProgress"
 >		      
 </component-order-tracking>
 
 <!--COMPONENTS REVIEW-->
<components-review
ref="ReviewRef"
accepted_files = "image/jpeg,image/png,image/gif/mage/webp"
:max_file = "2"
:label="{
  write_review: '<?php echo CJavaScript::quote(t("Write A Review"))?>',
  what_did_you_like: '<?php echo CJavaScript::quote(t("What did you like?"))?>',  
  what_did_you_not_like: '<?php echo CJavaScript::quote(t("What did you not like?"))?>',
  add_photo: '<?php echo CJavaScript::quote(t("Add Photos"))?>',
  write_your_review: '<?php echo CJavaScript::quote(t("Write your review"))?>',
  post_review_anonymous: '<?php echo CJavaScript::quote(t("post review as anonymous"))?>',
  review_helps: '<?php echo CJavaScript::quote(t("Your review helps us to make better choices"))?>',  
  drop_files_here: '<?php echo CJavaScript::quote(t("Drop files here to upload"))?>', 
  add_review: '<?php echo CJavaScript::quote(t("Add Review"))?>',  
  max_file_exceeded : '<?php echo CJavaScript::quote(t("Maximum files exceeded"))?>',  
  dictDefaultMessage : '<?php echo CJavaScript::quote(t("Drop files here to upload"))?>',  
  dictFallbackMessage : '<?php echo CJavaScript::quote(t("Your browser does not support drag'n'drop file uploads."))?>',  dictFallbackText : '<?php echo CJavaScript::quote(t("Please use the fallback form below to upload your files like in the olden days."))?>',  
  dictFileTooBig: '<?php echo CJavaScript::quote(t("File is too big ({{filesize}}MiB). Max filesize: {{maxFilesize}}MiB.w"))?>',  
  dictInvalidFileType: '<?php echo CJavaScript::quote(t("You can't upload files of this type."))?>',  
  dictResponseError: '<?php echo CJavaScript::quote(t("Server responded with {{statusCode}} code."))?>',  
  dictCancelUpload: '<?php echo CJavaScript::quote(t("Cancel upload"))?>',  
  dictCancelUploadConfirmation: '<?php echo CJavaScript::quote(t("Are you sure you want to cancel this upload?"))?>',  
  dictRemoveFile: '<?php echo CJavaScript::quote(t("Remove file"))?>',  
  dictMaxFilesExceeded: '<?php echo CJavaScript::quote(t("You can not upload any more files."))?>',   
  search_tag: '<?php echo CJavaScript::quote(t("Search tag"))?>',   
}"
:rating-value="rating_value"
@update:rating-value="rating_value = $event"
@after-addreview="afterAddreview"
>
</components-review>

<!-- <pre>{{order_info.order_id}}</pre>
<pre>{{driver_info.driver_id}}</pre> -->
<components-reviewdriver ref="ref_driverreview"
:order_id="order_info?order_info.order_id:0"
:driver_id="driver_info?driver_info.driver_id:0"
:driver_info="driver_info?driver_info:null"
>
</components-reviewdriver>

<div class="row no-gutters" v-if="merchant_info" >
  <div class="col-lg-3 col-md-12 pt-4 pb-4 pr-2">
          
    <!-- order_progress=>{{order_progress}}   
    <br/> 
    is_order_ongoing=>{{is_order_ongoing}}
    <br/> 
    order_type=>{{order_type}} -->
    <h5 class="m-0 mb-1">{{getEstimatetime}}</h5>  
    <p class="m-0 mb-1 font-weight-bold">{{order_status}}</p>    
    <p class="m-0">{{order_status_details}}</p>      
    
    <template v-if="is_order_need_cancellation">
       <div class="pt-2">
         <el-button type="danger" :loading="cancel_loading" style="font-size:12px" @click="CancelOrder" plain>
          <?php echo t("Cancel Order")?>
         </el-button>
       </div>
    </template>
        
    <template v-if="order_info.order_type=='delivery'">       
    <div class="mt-3 mb-5">
	    <ul id="progressbar" class="text-center">
	        <li class="step-order" :class="{ active: order_progress>='1', 'progressing': order_progress=='1' , 'order_failed': order_progress=='0'  }" >
	          <div class="progress-value"></div>
	        </li>  
	        <li class="step-merchant" :class="{ active: order_progress>='2', 'progressing': order_progress=='2' , 'order_failed': order_progress=='0'  }"  >
	           <div class="progress-value"></div>
	        </li>        
	        <li class="step-car " :class="{ active: order_progress>='3' , 'progressing': order_progress=='3' , 'order_failed': order_progress=='0'   }" >
	           <div class="progress-value"></div>
	        </li>
	        <li class="step-home" :class="{ active: order_progress>='4', 'order_failed': order_progress=='0' }" ></li>
	    </ul>
    </div>
    </template>
    
    <template v-else>       
       <div class="mt-3 mb-5">
	    <ul id="progressbar" class="text-center three-column">
	        <li class="step-order" :class="{ active: order_progress>='1', 'progressing': order_progress=='1' , 'order_failed': order_progress=='0'  }" >
	          <div class="progress-value"></div>
	        </li>  
	        <li class="step-merchant" :class="{ active: order_progress>='2', 'progressing': order_progress=='2' , 'order_failed': order_progress=='0'  }"  >
	           <div class="progress-value"></div>
	        </li>        	        
	        <li class="step-home" :class="{ active: order_progress>='3', 'order_failed': order_progress=='0' }" ></li>
	    </ul>
    </div>
    </template>
    
    <div class="card body mt-5"  >      
     <div class="items d-flex justify-content-between">
        <div>
            <div class="position-relative"> 
			   <div class="skeleton-placeholder"></div>
			   <img class="rounded-pill lazy" :data-src="merchant_info.url_logo"/>
			 </div>
        </div> <!--col-->
        <div class=" flex-fill pl-2">
          <a target="_blank"  :href="merchant_info.restaurant_url"><h6 class="d-inline mr-1">{{merchant_info.restaurant_name}}</h6></a>
          <template v-for="(cuisine, index) in merchant_info.cuisine"  >
          <span v-if="index <= 0" class="badge mr-1" :style="'background:'+cuisine.bgcolor+';font-color:'+cuisine.fncolor" >
            {{ cuisine.cuisine_name }}
          </span>
          </template>
          <p class="m-0">{{ merchant_info.merchant_address }}</p>
          
          <!--DIRECTIONS -->
          <div v-if="order_info.order_type=='pickup'" class="mt-2">            
            <a :href="'tel:'+ merchant_info.contact_phone"  class="btn btn-circle btn-white border mr-2"><i class="zmdi zmdi-phone"></i></a>
            <a :href="merchant_info.restaurant_direction" target="_blank" class="btn btn-circle btn-white border"><i class="zmdi zmdi-turning-sign"></i></a>
          </div>          
          <div v-else-if="order_info.order_type=='dinein'" class="mt-2">            
            <a :href="'tel:'+ merchant_info.contact_phone"  class="btn btn-circle btn-white border mr-2"><i class="zmdi zmdi-phone"></i></a>
            <a :href="merchant_info.restaurant_direction" target="_blank" class="btn btn-circle btn-white border"><i class="zmdi zmdi-turning-sign"></i></a>
          </div>
          
        </div> <!--col-->
     </div> <!--items-->                
   </div> <!--card body-->
    
    <div class="divider p-0 mt-2 mb-2"></div>
                
    <!-- <template v-if="order_progress===4 && enabled_review"> -->
    <template v-if="showReview">
    <div class="mt-3 mb-3">      
        <h6><?php echo t("HOWS WAS YOUR ORDER?")?></h6>
        <p><?php echo t("let us know how your delivery wen and how you liked your order!")?></p>
        <div class="d-flex justify-content-end">
          <div><a @click="writeReview(order_uuid)" class="btn link text-green"><?php echo t("Rate Your Order")?></a></div>
        </div>
    </div> <!--review-->     
    <div  class="divider p-0 mt-2 mb-2"></div>
    </template>
        
    <template v-if="hasInstructions">   
    <div class="mt-3 mb-3" >
      <h6><?php echo t("UPON ARRIVAL")?></h6>
      <p>{{instructions.text}}</p>
    </div>
    <div class="divider p-0 mt-2 mb-2"></div>
    </template>

      
    <div class="mt-3 mb-3" v-if="points_label">
      <h6><?php echo t("Congratulations")?>! <i class="fas fa-gift text-green"></i></h6>
      <p class="m-0">{{points_label}}</p>
    </div>
            
    <!-- <div v-if="driver_info && order_progress>2" class="mt-3 mb-3"> -->
    <div v-if="driver_info" class="mt-3 mb-3">
        <div class="d-flex align-items-center">
          <div class="mr-2"> <el-avatar :size="40" :src="driver_info.photo" ></el-avatar></div>
          <div>
             <div class="d-flex align-items-center">
                 <h6 class="m-0 mr-1">{{driver_info.full_name}}</h6>
                 <div class="font11 text-muted mr-1">{{driver_info.average_rating}}</div>
                 <div><i class="zmdi zmdi-star font14 color-yellow"></i></div>
             </div>             
             <div class="font11 text-muted">{{driver_info.car_maker}} &bull; {{driver_info.plate_number}}</div>
          </div>
        </div>
                
        <div class="d-flex">          
            <?php if($chat_enabled):?>
              <el-button color="#f6f7f8" @click="openChatDriver" >
                <div class="d-flex align-items-center">
                  <div class="mr-1"><img class="img-20" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/live-chat2.png"?>" /></div>
                  <div class="font11"><?php echo t("Chat with your driver")?></div>
                </div>
              </el-button>            
            <?php endif;?>
            <el-button @click="callDriver">
                <i class="zmdi zmdi-phone font14"></i>
            </el-button>            
        </div>        
    </div>
    
    <div class="d-flex justify-content-end" v-if="order_progress===4 && enabled_review && driver_info" >        
        <a @click="this.$refs.ref_driverreview.modal=true" class="btn link text-green"><?php echo t("Rate Delivery Experience")?></a>
    </div>

    <div class="mt-3 mb-3" >
     <div class="row align-items-center">
        <div class="col">
           <a target="_blank" :href="merchant_info.restaurant_url"><h6 class="m-0">{{merchant_info.restaurant_name}}</h6></a>
        </div>
        <?php if($chat_enabled):?>
        <div class="col text-right">
           <a href="<?php echo isset($chat_link)?$chat_link:''?>">             
              <img class="img-20" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/live-chat2.png"?>" />
           </a>
        </div>        
        <?php endif;?>
     </div>
     <p class="m-0 mb-1 bold"><?php echo t("Order #")?>{{order_info.order_id}}</p>     
     <p class="m-0 mb-1 bold">{{order_info.total_items1}}</p>
     <p class="m-0 mb-1">{{order_info.pretty_total}} <?php echo t("Total")?></p>
     <ul class="list-unstyled m-0 p-0" v-if="items.length>0" >
      <template v-for="(item, index) in items"  >
      <li><p class="m-0">
       {{item.qty}}x <span v-html="item.item_name"></span>
       <template v-if=" item.size_name!='' "> 
          (<span v-html="item.size_name"></span>)          
       </template>      
      </p></li>      
      </template>
     </ul>
    </div>
                      
    <template v-if="order_info.order_type=='delivery'">
    <div class="divider p-0 mt-2 mb-2"></div>
    <div class="mt-3 mb-3" v-if="meta">      
      <h6><?php echo t("Delivery Address")?></h6>            
      <p class="m-0">{{order_info.complete_delivery_address}}</p>
      <p v-if="order_info?.order_version==1" class="m-0">&bull; {{order_info?.address1}} {{order_info?.delivery_address}}  </p>
      <p>{{order_info.location_name}}</p>
    </div>
    </template>
    
    
  </div> <!--col-->
    
  <div class="col-lg-9 col-md-12 page-grey p-0 track-map-div" >     
     <div v-if="is_order_ongoing" class="position-bottom">
        <el-button circle size="large" @click="FitBounds();" >
          <i class="zmdi zmdi-gps-dot font20"></i>
        </el-button>
     </div>     
     <div ref="cmaps" id="cmaps" class="map-fullscreen" ></div>      
  </div> <!--col-->
</div> <!--row-->


<template v-if="driver_info && chat_link">
<el-dialog v-model="modal_chat" width="400" title="<?php echo t("Chat with rider")?>" :lock-scroll="true"	>
  <iframe style="height: calc(60vh);width:100%;border:none;" :src="chat_link"></iframe>
</el-dialog>
</template>


</div> <!--container-->

<div id="app-track-order">
<!-- driver_uuid=>{{driver_uuid}} -->
 <!-- <pre>{{lastSentPosition}}</pre> -->
</div>

<?php $this->renderPartial('//components/review_driver');?>