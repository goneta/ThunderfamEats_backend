<div class="container-fluid page-grey">

<div class="container">
   <div class="row">
     <div class="col-lg-8 col-md-12 mb-4 mb-lg-3 p-0 p-lg-2">
        <div class="card">

		  <div id="vue-notification-cart">			
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
		  </div>
		  <!-- vue-notification-cart -->
                       
          <!--Delivery method and time-->
          <div class="card-body">
          
          <?php $this->renderPartial("//components/schedule-order",array(
            'show'=>false
          ))?>
          
          <div class="row mb-3" >    
			     <div class="col d-flex justify-content-start align-items-center" >
			         <span class="badge badge-dark rounded-pill">1</span>
			         <h5 class="m-0 ml-2 section-title"><?php echo t("Order type and time")?></h5>			         
			     </div>		     
 		   </div> <!--row-->  
 		   
 		  
 		   <!--promo-section--> 	
 		   <DIV v-cloak id="vue-promo">  
			 
			<el-skeleton :loading="is_loading" animated>
			<template #template>
			<div class="border rounded p-1 mb-2">
				<el-skeleton :rows="1" />
			</div>
			</template>		   
			<template #default>

			<template v-if="data.length>0">
			  <a @click="show" href="javascript:;" class="d-block chevron-section promo-section d-flex align-items-center rounded mb-2">
				<div class="flexcol mr-2"><i class="zmdi zmdi-label"></i></div>
				<div class="flexcol"> 		     		    
				<template v-if="promo_selected.length<=0">
				<span class="bold">{{ data.length }}</span> <?php echo t("Promotion available")?>
				</template> 		       
				<template v-if="promo_selected.length>0">
					<?php echo t("Promotion applied")?>
				</template>
							
				<p v-if="promo_selected.length>0" class="m-0 text-success">{{promo_selected[2]}}</p>
				
				</div> 		    		   
			</a>  		  
			<?php $this->renderPartial("//account/checkout-promo")?>
			</template>

			</template>
			</el-skeleton>
			 		 
 		   
 		    <!--COMPONENT PROMO CODE--> 		   
 		   <component-promocode 
 		   ref="childref"
 		   title="<?php echo t("Have a promo code?")?>"
 		   add_promo_code="<?php echo t("Add promo code")?>"
 		   apply_text="<?php echo t("Apply")?>"
 		   @back="show"
 		   @set-loadpromo="loadPromo"
			is_mobile="<?php echo Yii::app()->params['isMobile'];?>"
 		   >
 		   </component-promocode>
 		   <!--END COMPONENT PROMO CODE-->
 		   
 		   </DIV>	   		   
 		   <!--promo-section-->
 		   
 		   
 		   <!--add promo code manually--> 	
 		   <DIV v-cloak id="vue-add-promocode">   		   
 		   <template v-if="enabled"> 		   
 		   <a @click="show" href="javascript:;" class="d-block chevron-section promo-section d-flex align-items-center rounded mb-2">
 		    <div class="flexcol mr-2"><i class="zmdi zmdi-label"></i></div>
 		    <div class="flexcol"> 	 		       
 		       <span v-if="has_promocode===false"><?php echo t("Add promo code")?></span>
 		       <span v-else><?php echo t("Remove promo code")?></span>
 		       
 		       <p v-if="has_promocode" class="m-0 text-success">{{saving}}</p>
 		       
 		    </div> 		    		   
 		   </a>  		   		   
 		   </template>
 		   
 		    <!--COMPONENT PROMO CODE--> 		   
 		   <component-apply-promocode 
 		   ref="childref"
 		   title="<?php echo t("Have a promo code?")?>"
 		   add_promo_code="<?php echo t("Add promo code")?>"
 		   apply_text="<?php echo t("Apply")?>" 		   
 		   @back="show"
 		   @set-loadpromo="loadPromo"
 		   >
 		   </component-apply-promocode>
 		   <!--END COMPONENT PROMO CODE-->
 		   
 		   </DIV>	   		   
 		   <!--end add promo code-->


		   <!-- POINTS -->
		   <?php if($points_enabled && $loyalty_points_activated):?>
		   <DIV id="vue-checkout-points">  						    
			    <el-skeleton :loading="loading" animated>
				<template #template>
				<div class="border rounded p-1 mb-2">
					<el-skeleton :rows="1" />
				</div>
				</template>		   
				<template #default>			
				    				    
					<template v-if="data.total>0">
						<template v-if="data.discount>0">
						<div class="d-block chevron-section d-flex align-items-center rounded mb-2" > 		     
							<div class="flexcol mr-2"><i class="fas fa-gift"></i></div> 
							<div class="flexcol">
								<?php echo t("Points discount")?>
								<p class="m-0 text-success">{{data.discount_label}}</p>
								<div class="d-flex">
									<div class="mr-2">
										<el-button			
										size="small"																
											type="primary"
											link
											@click="showApplyPoints"
											>
											<?php echo t("Change")?>
										</el-button>
									</div>
									<div >
										<el-button			
										size="small"																
											type="primary"
											link
											@click="removePoints"
											:loading="loading_remove"
											>										
											<?php echo t("Remove")?>
										</el-button>
									</div>
								</div>
							</div>						  
						</div>
						</template>
						<template v-else>
						<a @click="showApplyPoints" href="javascript:;" class="d-block chevron-section promo-section d-flex align-items-center rounded mb-2">
						<div class="flexcol mr-2"><i class="fas fa-gift"></i></div> 
						<div class="flexcol">
							<?php echo t("Points discount")?>
							<?php if(!$points_use_thresholds):?>
							<p class="m-0 text-muted">{{data.redeem_discount}}</p>
							<p class="m-0 text-muted">{{data.redeem_label}}</p>
							<?php endif;?>
						</div>
						</a>
						</template>
					</template>

				</template>
				</el-skeleton>		
				
				<components-apply-points
				  ref="apply_points"
				  is_mobile="<?php echo Yii::app()->params['isMobile'];?>"
				  title="<?php echo t("Apply Points discount")?>"
				  apply_text="<?php echo t("Apply")?>" 		   				  
				  enter_points="<?php echo t("Enter points to convert to discount")?>" 
				  points_value="1"	   
				  @after-applypoints="afterApplypoints"
				  :data="data"
				  use_thresholds="<?php echo isset($points_use_thresholds)?$points_use_thresholds:false;?>"
				>
				</components-apply-points>
		   </DIV>
		   <?php endif;?>
		   <!-- POINTS -->
 		   
 		   <!--tips-->
 		   <DIV v-cloak id="vue-tips">   					
			<template v-if="enabled_tips">
			<el-skeleton :loading="is_loading" animated>
			<template #template>
			<div class="border rounded p-1 mb-2">
				<el-skeleton :rows="1" />
			</div>
			</template>		   
			<template #default>

			 <!-- TIPS -->			
		   <template v-if="ifDelivery">
 		   <div class="d-block chevron-section d-flex align-items-center justify-content-between rounded mb-2"> 		    		    
 		    <div class="flexcol">
 		      <?php echo t("Tip the courier")?>
 		      <p class="m-0 mb-2"><?php echo t("Optional tip for the courier")?></p>
 		      
 		       <!--tips-->			   
		        <div class="btn-group btn-group-toggle input-group-small mb-3" >
		        
		           <label  class="btn" v-for="tip in data" :class="{ active: tips==tip.value }"  >
		             <input type="radio" :value="tip.value" v-model="tips" @click="checkoutAddTips(tip.value,false)"> 
		             {{ tip.name }}
		           </label>		        
		           
		        </div>
		        <!--tips-->
		        
		       <!--tips-other-->				    
		       <div v-if="ifOthers">
		       <div class="d-flex align-items-center">
		         <div class="flexcol mr-2">
		         
		           <input type="text" class="form-control form-control-text text-center" 
 		            type="text" v-model="manual_tip"  maxlength="10" style="width:80px;">   
		         
		         </div> <!--flexcol-->
		         <div class="flexcol">
		         <button @click="checkoutAddTips(manual_tip,true)" class="btn btn-green" :class="{ loading: is_loading }" >
		           <span class="label" ><?php echo t("Add tip")?></span>
		           <div class="m-auto circle-loader" data-loader="circle-side"></div>
		         </button>
		         </div>
		       </div>   
		       </div> 
		       <!--tips-other-->
		        
 		      
 		    </div> <!--flexcol-->	    
 		   </div>
 		   </template>

			 <!-- TIPS -->

			</template>
			</el-skeleton>
			</template> 		
 		   </DIV>
 		   <!--tips-->
 		   
 		    
 		   
          </div>
          <!--Delivery method and time-->
          
          <div class="divider p-0"></div>
         
          <!--vue-manage-address-->		
          <?php 
		  if($home_search_mode=="location"){
			  $this->renderPartial("//account/checkout-location-address",[
				'country_id'=>$country_id,
				'location_searchtype'=>$location_searchtype,
				'delivery_option'=>$delivery_option,
				'address_label'=>$address_label,
				'delivery_option_first_value'=>$delivery_option_first_value,
				'address_label_first_value'=>$address_label_first_value,
				'enabled_map_selection'=>$enabled_map_selection
			  ]);
		  } else {
			  $this->renderPartial("//account/checkout-delivery-address",[
				'redirect_to'=>'',
				'maps_config'=>CMaps::config()
			  ]);
		  }		  
		  ?>
          <!--vue-manage-address-->
          
          <!--PAYMENT METHOD-->
          <div class="card-body">
          <DIV id="vue-payment-list" v-cloak>
             <div class="row mb-3" >              
              <div class="col d-flex justify-content-start align-items-center" >              
		         <span class="badge badge-dark rounded-pill">
		         <template v-if="transaction_type==='delivery'">
		         3
		         </template>
		         <template v-else>
		         2
		         </template>
		         </span>
		         <h5 class="m-0 ml-2 section-title"><?php echo t("Payment Methods")?></h5>			         
			  </div>	             
             </div> <!--row-->
             
			 <!-- DIGITAL WALLET -->
			<components-digital-wallet 
			ref="digital_wallet" 
			ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 	
			@after-applywallet="afterApplywallet"	      
			:amount_to_pay="amount_to_pay"
			:enabled_digital_wallet="<?php echo isset(Yii::app()->params['settings']['digitalwallet_enabled'])?Yii::app()->params['settings']['digitalwallet_enabled']:false?>"
			></components-digital-wallet>

             <!--SAVE PAYMENT METHOD-->        			 
			<el-skeleton :count="3" :loading="saved_payment_loading" animated>
			<template #template>
			<div class="border rounded p-1 mb-2">
				<el-skeleton :rows="1" />
			</div>
			</template>		   
			<template #default>
						
			<template v-if="hasSavedPayment && !isWalletFullPayment">
             <h5 class="mb-3"><?php echo t("Saved Payment Methods")?></h5> 
                          
             <div v-for="saved_payment in data_saved_payment" class="row no-gutters align-items-center chevron-section medium rounded mb-2"  :class="{ selected: saved_payment.as_default==1 }" >
             
              <div class="col-lg-8 col-md-8 col-10 d-flex align-items-center">
                <div class="flexcol mr-0 mr-lg-2 payment-logo-wrap">
	 		      <i v-if="saved_payment.logo_type=='icon'" :class="saved_payment.logo_class"></i>
	 		      <img v-else class="img-35 contain" :src="saved_payment.logo_image" /> 		      
	 		    </div> <!--flex-col-->
	 		    <div class="flexcol" > 		     		     		      
	 		       <span class=" mr-1">{{saved_payment.attr1}}</span>       
				   <span class="text-grey font11">
				   <template v-if="saved_payment.card_fee_percent && saved_payment.card_fee_fixed">
						({{saved_payment.card_fee_percent}}%+{{saved_payment.card_fee_fixed}})
					</template>
					<template v-else-if="saved_payment.card_fee_percent">
					    ({{saved_payment.card_fee_percent}}%)
					</template>
					<template v-else-if="saved_payment.card_fee_fixed">
					    ({{saved_payment.card_fee_fixed}})
					</template>
				   </span>
	 		       <p class="m-0 text-muted">{{saved_payment.attr2}}</p>   
				   <p class="m-0 text-muted" v-if="usePartialPayment && saved_payment.as_default==1">{{getPayRemaining}}</p>   
	 		    </div> 		    		    		    
              </div> <!--col-->
              <div class="col-lg-4 col-md-4 col-2  d-flex align-items-center justify-content-end">
              			     

                 <template v-if="saved_payment.as_default==1">
                 <div class="mr-1 d-none d-md-block"><i class="zmdi zmdi-check text-success"></i></div>
                 <div class="mr-3 d-none d-md-block"><p class="m-0"><?php echo t("Default")?></p></div>
                 </template>
                 
	             <div class="dropdown">
	             <a href="javascript:;" class="rounded-pill rounded-button-icon d-inline-block" 
	             id="dropdownMenuLink" data-toggle="dropdown" >
	               <i class="zmdi zmdi-more" style="font-size: inherit;"></i>
	             </a>
	                 <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
					    <a  v-if="saved_payment.as_default!=1" 
	             @click="setDefaultPayment(saved_payment.payment_uuid)"
	             class="dropdown-item a-12" href="javascript:;"><?php echo t("Set Default")?></a>
					    
					    <a @click="deleteSavedPaymentMethod(saved_payment.payment_uuid,saved_payment.payment_code)" class="dropdown-item a-12" href="javascript:;"><?php echo t("Delete")?></a>				    
					  </div>
	             </div> <!--dropdown-->
	              
              </div> <!--col-->
             </div> <!--row-->
             
             </template>
             <!--END SAVE PAYMENT METHOD-->

			</template>
			</el-skeleton>


			<!-- COD AMOUNT CHANGE -->
			<div v-if="!isWalletFullPayment">								
				<template v-for="items_payment in data_saved_payment">
					<template v-if="items_payment.payment_uuid==default_payment_uuid">
						<template v-if="items_payment.attr_required==1">
						    <h5 class="mb-3 mt-4"><?php echo t("Change for how much?")?></h5>
							<div>
							   <input v-model="payment_change" v-on:keypress="NumbersOnly" type="number" class="form-control form-control-text" type="text">							   
							   <p v-if="!validatePaymentChange" class="text-danger mt-1"><?php echo t("Change is required")?></p>
							   <template v-else>
							   <p v-if="!validatePaymentChangeValue" class="text-danger mt-1"><?php echo t("Change must not lower than total amount")?></p>
							   </template>
							</div>
						</template>
					</template>
				</template>
			</div>

             
             <h5 class="mb-3 mt-4"><?php echo t("Add New Payment Method")?></h5>

			<el-skeleton :count="3" :loading="payment_list_loading" animated>
			<template #template>
			<div class="border rounded p-1 mb-2">
				<el-skeleton :rows="1" />
			</div>
			</template>		   
			<template #default>

			<template v-if="hasData">
             <a v-for="payment in data" @click="showPayment(payment.payment_code)" class="d-block chevron-section medium d-flex align-items-center rounded mb-2">
	 		    <div class="flexcol mr-0 mr-lg-2  payment-logo-wrap">
	 		      <i v-if="payment.logo_type=='icon'" :class="payment.logo_class"></i>
	 		      <img v-else class="img-35 contain" :src="payment.logo_image" />
	 		    </div>
	 		    
	 		    <div class="flexcol" > 		     		     		      
	 		       <span class="mr-1">{{payment.payment_name}}</span>
				   <span class="text-grey font11">
				   <template v-if="payment.card_fee_percent && payment.card_fee_fixed">
						({{payment.card_fee_percent}}%+{{payment.card_fee_fixed}})
					</template>
					<template v-else-if="payment.card_fee_percent">
					    ({{payment.card_fee_percent}}%)
					</template>
					<template v-else-if="payment.card_fee_fixed">
					    ({{payment.card_fee_fixed}})
					</template>
				   </span>          
	 		    </div> 		    		    		    
	 		 </a> 
             </template>            

			</template>
			</el-skeleton>
			 
                   
             <!--RENDER PAYMENT COMPONENTS-->       
             <?php CComponentsManager::renderComponents($payments,$payments_credentials,$this)?>                          
          	 
          </DIV> <!-- vue-payment-list-->
          </div> <!--card-body-->                               
          <!--END PAYMENT METHOD-->
           		 
          
        </div> <!--card-->
     </div> <!--col-->
     
     
     <!--RIGHT SIDE PANEL-->
     <div class="col-lg-4 col-md-12 mb-4 mb-lg-3  p-0 p-lg-2">
     
      <!--vue-cart-->       
      <div id="vue-cart" class="sticky-cart" v-cloak >
		 <div class="card">     		  
		   <div class="card-body pb-3"   v-if="cart_items.length>0" >      
		     <div class="items d-flex justify-content-between">
		        <div>		       		            
					 <el-image
						style="width: 50px; height: 50px"
						class="rounded-pill"
						:src="cart_merchant.logo"
						fit="cover"
						lazy
					></el-image>
		        </div> <!--col-->
		        <div class=" flex-fill pl-2">
		          		          
		          <a :href="cart_merchant.restaurant_slug" class="m-0 p-0">
                  <h5 class="m-0 chevron d-inline position-relative">{{ cart_merchant.restaurant_name }}</h5>
                  </a>  
                  
                  <template v-for="(cuisine, index) in cart_merchant.cuisine"  >
                  <div>
		          <span v-if="index <= 0" class="badge mr-1" 
	             :style="'background:'+cuisine.bgcolor+';font-color:'+cuisine.fncolor" >
		            {{ cuisine.cuisine_name }}
		          </span>
		          </div>
		          </template>
                  
		          <p class="m-0">{{ cart_merchant.merchant_address }}</p>
		        </div> <!--col-->
		     </div> <!--items-->                
		   </div> <!--card body-->
		   		   
		   <div class="divider p-0"></div>
		   
		   <div class="card-body">		     
		     <?php $this->renderPartial("//store/cart-qr",array(
		      'checkout'=>true
		     ))?>	      
		   </div> <!--card body-->
		   
		 </div> <!--card-->       
		 </div> <!--sticky-sidebar-->
     <!--end vue-cart-->
     
     </div> <!--col-->
     <!--END RIGHT SIDE PANEL-->
     
   </div> <!--row-->
</div><!-- container-->

</div> <!--container-fluid--> 

<?php $this->renderPartial("//components/loading-box")?>
<?php $this->renderPartial("//components/vue-bootbox")?>
<?php $this->renderPartial("//components/digital-wallet")?>

<script src=https://touchpay.gutouch.net/touchpayv2/script/touchpaynr/prod_touchpay-0.0.1.js
type="text/javascript"></script>