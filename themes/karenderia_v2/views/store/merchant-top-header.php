<DIV id="vue-merchant-details">

<div class="merchant-top-header d-none d-lg-block" v-cloak>
   <div class="container pt-2 pb-3">
	
		<div class="pt-1 pb-3" >
			<el-breadcrumb>
				<el-breadcrumb-item ><a href="<?php echo Yii::app()->createUrl("/store/index")?>"><?php echo t("Home")?></a></el-breadcrumb-item>
				<el-breadcrumb-item><a href="<?php echo Yii::app()->createUrl("/store/restaurants")?>"><?php echo t("Restaurant")?></a></el-breadcrumb-item>
				<el-breadcrumb-item>
				<span class="d-inline-block text-truncate" style="max-width: 300px;">
				<?php echo CommonUtility::safeDecode($data['restaurant_name'])?> -        
				<?php echo CommonUtility::safeDecode($data['address'])?>
				</span>
				</el-breadcrumb-item>    
			</el-breadcrumb>
		</div>


		<div class="row" v-cloak>
		   <div class="col-6 left-info p-3">
			
		      <div class="d-flex align-items-start">			  
				<div>
				    <el-image
						style="width: 130px; max-height: 130px;min-width: 130px; min-height: 100px"
						src="<?php echo $data['url_logo'];?>"
						fit="contain"
						lazy
						class="img-thumbnail"
					>
					</el-image>												
				</div>
				<!-- left -->
				<div class="pl-3" style="min-width:300px;">					
					<template v-if="is_loading" >
						<el-skeleton animated :loading="is_loading"  :rows="1">
							<el-skeleton-item variant="text" style="margin-right: 16px" ></el-skeleton-item>
							<el-skeleton-item variant="text" style="width: 30%"></el-skeleton-item>
						</el-skeleton>
					</template>
					<template v-else>

						<?php if(is_array($data['cuisine']) && count($data['cuisine'])>=1):?> 		
						<ul class="p-0 m-0 mb-1 text-grey">
							<?php foreach ($data['cuisine'] as $cuisine_key=> $item_cuisine):?> 
							<li><?php echo $item_cuisine;?></li>
							<?php if($cuisine_key>2){break;}?>
							<div class="info-items-dot-separator"></div>						
							<?php endforeach?>
						</ul>
						<?php endif?>

						<h4><?php echo CommonUtility::safeDecode($data['restaurant_name'])?></h4>

						<ul class="p-0 m-0 mb-1">
							<?php if($enabled_review):?>
							<li class="mr-1"><i class="zmdi zmdi-star"></i></li>
							<li>
							<a href="#section-review">
							<b><?php echo Price_Formatter::convertToRaw($data['ratings'],1)?></b> 
							<span>
							<?php echo t("+{rating} ratings",[
								'{rating}'=>isset($data['review_count'])?intval($data['review_count']):0
							])?>
							</span>
							</a>
							</li>			
							<?php endif;?>			
							<?php if(!empty($open_end)):?>
							<div class="info-items-dot-separator"></div>		
							<li class="mr-1"><i class="zmdi zmdi-time"></i></li>
							<li>
								<?php echo t("Open until {ends}",[
									'{ends}'=>$open_end
								])?>
							</li>						
							<?php endif;?>						

							<?php if($home_search_mode=="address"):?>
							<div class="info-items-dot-separator"></div>
							<li class="mr-1"><i class="zmdi zmdi-pin"></i></li>
							<li><?php echo isset($distance['label'])?$distance['label']:''?></li>						
							<?php endif;?>
						</ul>					

					</template>
					<component-promo-details
						:merchant_id="<?php echo intval($data['merchant_id'])?>"
						:label="{
						title:'<?php echo CJavaScript::quote(t("Promotions"))?>', 
						enjoy:'<?php echo CJavaScript::quote(t("Enjoy discounts on items"))?>', 		
						see_details:'<?php echo CJavaScript::quote(t("See details"))?>', 														
						}"
					>
					</component-promo-details>		
					
					<?php if($home_search_mode=="address"):?>
						<component-merchant-services
						ref="ref_services"
						@after-update="afterUpdateServices"
						:label="{
							min:'<?php echo CJavaScript::quote(t("min"))?>', 			
						}"
						>
						</component-merchant-services>					
					<?php else :?>										
						<components-location-estimation
						ref="ref_location_services"
						>
						</ref_current_address>
					<?php endif;?>									
					
				</div>	
				<!-- right-->
			  </div>
			  <!-- d-flex -->

			

		   </div>
		   <!-- col -->
		   <div class="col-6 right-infox position-relative rounded" >               			   
			   <div class="fav-wrap" style="z-index: 99;">
					<component-save-store
					:active="found"
					:merchant_id="<?php echo intval($data['merchant_id'])?>"
					@after-save="getSaveStore"
					:is_guest="<?php echo Yii::app()->user->isGuest;?>"
					please_login="<?php echo CHtml::encode(t("Login to save it to your favorites"))?>"
					/>
					</component-save-store>	        
				</div>	     
				<?php if(!empty($data['url_header']) && $data['has_header'] ):?>
				<el-image class="w-100" style="max-height: 250px;" src="<?php echo $data['url_header']?>" fit="contain" lazy ></el-image>
				<?php endif;?>
		   </div>
		   <!-- col -->
		</div>
		<!-- row -->

   </div>
   <!-- container -->
</div>
<!-- merchant-top-header -->

<!-- </DIV> -->
<!-- vue-merchant-details -->

<!-- mobile view -->
<div class="d-block d-lg-none">
 <div class="top-merchant-details mobile-merchant-details position-relative">

 
 <div class="sub">
	 <div class="container p-4">
     <div class="d-flex justify-content-end">		
		<template v-if="!is_loading"> 	          
		<component-save-store
			:active="found"
			:merchant_id="<?php echo intval($data['merchant_id'])?>"
			@after-save="getSaveStore"
		/>
		</component-save-store>	        
		</template>
	</div>  <!-- d-flex -->	
	</div> <!--  container -->
 </div> 
 <!-- sub -->
</div>   
<!-- top-merchant-details -->