<!--COMPONENTS NEW ORDER-->
<div id="components-modal-neworder">
<components-neworder 
	@new-order="onConfirm"
	@close-order="onClose"
	:title="title"
	:content="content"
	:new_order_label="new_order_label"
	:is_loading="is_loading"	
></components-neworder>
</div>

<?php $this->renderPartial("//components/vue-bootbox")?>

<?php $this->renderPartial("//store/merchant-top-header",[
	'data'=>$data,
	'open_end'=>$open_end,
	'distance'=>$distance,
	'enabled_review'=>$enabled_review,
	'home_search_mode'=>isset($home_search_mode)?$home_search_mode:''
])?>

<div class="container pt-2 pb-2">
   <h5 class="m-0"><?php echo $data['restaurant_name']?></h5> 
   
   <a href="#section-address" class="d-block chevron center position-relative no-hover">
	    <p class="font-weight-bolder m-0">
			<span class="mr-1"><i class="zmdi zmdi-star"></i></span>
			<span class="mr-1">(<?php echo t("{{rating}} ratings",array('{{rating}}'=>$data['review_count']))?>)</span>					
			<span>
			&bull; 
			<?php if(is_array($data['cuisine']) && count($data['cuisine'])>=1):?> 
			<?php foreach ($data['cuisine'] as $cuisine_key=> $item_cuisine):?>
				 <?php echo $item_cuisine;?>,
				 <?php 
				 if($cuisine_key>0){
					 break;
				 }
				 ?>
		    <?php endforeach?>
		    <?php endif?>
			&bull; <?php echo Price_Formatter::$number_format['currency_symbol'];?>
			<span>
		</p>		
		<p class="font-weight-light m-0"><?php echo t("Tap for hours,address, and more")?></p>
	</a>

	<div class="text-center">
		<?php if($home_search_mode=="address"):?>	
			<div class="pt-2">
			<component-merchant-services
			ref="ref_services"
			@after-update="afterUpdateServices"
			:label="{
				min:'<?php echo CJavaScript::quote(t("min"))?>', 			
			}"
			>
			</component-merchant-services>
			</div>
		<?php else : ?>
			<div class="pt-2">
			<components-location-estimation
			ref="ref_location_services"
			>
			</ref_current_address>
			</div>
		<?php endif;?>
    </div>

</div>
<!-- container-fluid -->
 
</div>
<!-- mobile view -->

</DIV>
<!-- vue-merchant-details -->


<!--SHOW CHANGE ADDRESS IF OUT OF COVERAGE-->
<?php 
if($home_search_mode=="address"){
	$this->renderPartial("//components/address-needed",[
		'maps_config'=>$maps_config
	]);
} else {
	$this->renderPartial("//components/location-address-needed");
	$this->renderPartial("//components/template_location_estimation");	
}
$this->renderPartial("//components/schedule-order",array(
	'show'=>true
));
$this->renderPartial("//components/template_age_verifications");
?>

<div class="section-menu" >
	<div class="container">
	  <div class="row">	 
        	    	    
	    <div class="col-lg-9 col-md-12 mb-3 mb-lg-3 menu-center position-relative">	    	    			    
        
           <div id="sticky-sidebar" class="sticky-sidebar d-none d-lg-block" v-cloak></div>

            <div id="vue-merchant-category" >    

			    <div ref="search_result"></div>

                <component-topcategory
                :data="category_data"
				@after-search="afterSearch"
				@clear-search="clearSearch"
                >
                </component-topcategory>				

                <!-- mobile view category -->
                <div class="d-block d-lg-none">	      					
                    <components-category-carousel
                    :data="category_data"
                    restaurant_name="<?php echo CHtml::encode($data['restaurant_name'])?>"
                    >
                    </components-category-carousel>
					
					<components-search-menu-mobile
					@after-search="afterSearch"
				    @clear-search="clearSearch"
					>
					</components-search-menu-mobile>

					<div class="p-2"></div>
                </div>
                <!-- mobile view category -->
								
				<component-menu-search-result
				:q="q"
				:data="search_menu_data"
				>					
				</component-menu-search-result>

				<components-age-verification
				ref="ref_age_verifications"
				:enabled="<?php echo isset($data['age_verifications'])?$data['age_verifications']:false;?>"
				:merchant_id="<?php echo isset($data['merchant_id'])?$data['merchant_id']:false;?>"
				:label="<?php echo CommonUtility::safeJsonEncode([
					'title'=>t("You must be at least 18 years old to enter this section."),
					'subtitle'=>t("Please confirm your age."),
					'over'=>t("Over 18"),
					'under'=>t("Under 18"),
					'home_url'=>Yii::app()->createAbsoluteUrl("/store/index")
				])?>"
				>
				</components-age-verification>

            </div>
            <!-- vue-merchant-category -->            
			
			<!-- SEARCH RESULTS -->
			<script type="text/x-template" id="xtemplate_menusearch">
			  <?php $this->renderPartial("//store/menu-search-data")?>
            </script>
			<!-- SEARCH RESULTS -->

            <div id="vue-merchant-menu"> 

            <!--CHANGE ADDRRESS-->      
            <component-change-address
            ref="address"
            @set-location="afterChangeAddress"
            @after-close="afterCloseAddress"	
            @set-placeid="afterSetAddress"	
            @set-edit="editAddress"
            @after-delete="afterDeleteAddress"
            :label="{
                title:'<?php echo CJavaScript::quote(t("Delivery Address"))?>', 
                enter_address: '<?php echo CJavaScript::quote(t("Enter your address"))?>',	    	    
            }"
            :addresses="addresses"
            :location_data=""
            >
            </component-change-address>

			<component-allergens
			 ref="ref_allergens"			 
			 :label="{
                title:'<?php echo CJavaScript::quote(t("More product information"))?>', 
                sub_title: '<?php echo CJavaScript::quote(t("Allergen"))?>',	    	    
            }"
			:merchant_id="<?php echo intval($data['merchant_id'])?>"
			>
			</component-allergens>
                                 
            
            <el-skeleton :count="12" :loading="menu_loading" animated>
		      <template #template>
			      <div class="row m-0">  
				      <div class="col-lg-3 col-md-3 p-0 mb-2">
			             <el-skeleton-item variant="image" style="width: 95%; height: 140px" />
	                  </div> <!-- col -->
					  <div class=" col-lg-9 col-md-9 p-0">					  
					    <div class="row m-0 p-0">
						    <div class="col-lg-12">							
							<el-skeleton :rows="2" ></el-skeleton>
	                        </div>							
	                    </div>
						<!-- row -->
	                  </div> <!-- col --> 					  
 	              </div> <!--  row -->
	          </template>

			  <template #default>
			     <?php 					 
				  switch ($menu_layout) {
					case 'left_image':
						$this->renderPartial("//store/menu-data",[
							'disabled_inline_addtocart'=>isset($disabled_inline_addtocart)?$disabled_inline_addtocart:false,
						]);
						break;
					case 'right_image':
						$this->renderPartial("//store/menu-data-right",[
							'disabled_inline_addtocart'=>isset($disabled_inline_addtocart)?$disabled_inline_addtocart:false,
						 ]);
						break;
					case 'no_image':
						$this->renderPartial("//store/menu-data-noimage",[
							'disabled_inline_addtocart'=>isset($disabled_inline_addtocart)?$disabled_inline_addtocart:false,
						]);
						break;
					case 'two_column':
						$this->renderPartial("//store/menu-data-two-column",[
							'disabled_inline_addtocart'=>isset($disabled_inline_addtocart)?$disabled_inline_addtocart:false,
						]);
						break;
				  }
				  ?>
			  </template>

		   </el-skeleton>
	    
		   <?php $this->renderPartial("//store/item-details",array(
			   'is_mobile'=>Yii::app()->params['isMobile']
		   ))?>
           
                    
            <el-affix 
            position="bottom" :offset="20" v-if="item_in_cart>0" 
            z-index="9"
            v-cloak >
                <div class="floating-cart d-block d-md-none">				  
                <button @click="showDrawerCart" class="btn btn-black small rounded w-100 position-relative">				  
                    <p class="m-0"><?php echo t("View order")?></p>
                    <h5 class="m-0">{{merchant_data.restaurant_name}}</h5>
                    <count>{{item_in_cart}}</count>
                </button>			  
                </div>
            </el-affix>

			<el-backtop :right="10" :bottom="20" />
            </div> 
            <!-- vue-merchant-menu -->
		   
	    </div> <!--col menu center-->
	    
	    <div class="col-lg-3 col-md-12 mb-3 mb-lg-3 menu-right p-0 d-none d-lg-block mt-4">
		  		  
	      <?php $this->renderPartial("//store/cart",array(
	        'checkout'=>false,
	        'checkout_link'=>$checkout_link
	      ))?>	   
		  
	    </div> <!--col menu right-->
	    
	  </div> <!--row-->
	</div> <!--container-->
</div> <!--section-menu-->
<!--SECTION MENU-->


<!--SECTION BOOKING RESERVATION-->
<?php if($booking_enabled):?>
<div id="vue-booking-reservation" class="container mt-0 mt-lg-5">

  <section id="section-booking" class="mb-3 p-2 p-lg-0">
	 <div class="row">
	 <div class="col-lg-3 col-md-3 p-0 mb-2 mb-lg-0">	     
		 <div class="d-flex">
          <div class="mr-3"><img class="img-30" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/chair.png"?>"/></div>
          <div><h5><?php echo t("Table reservations")?></h5></div>
         </div> <!--d-flex-->
	 </div>
	 <div class="col-lg-9 col-md-9">
		<component-reservation ref="booking"
		ajax_url="<?php echo Yii::app()->createUrl("/Apibooking")?>" 
		api_url="<?php echo Yii::app()->createUrl("/api")?>" 
		merchant_uuid="<?php echo $merchant_uuid?>"		
		booking_enabled_capcha="<?php echo $booking_enabled_capcha?>"		
		captcha_site_key="<?php echo $captcha_site_key?>"		
		:label="{		    
			guest: '<?php echo CJavaScript::quote(t("Guest"))?>', 
			date: '<?php echo CJavaScript::quote(t("Date"))?>', 		    
			time: '<?php echo CJavaScript::quote(t("Time"))?>',	
			no_results: '<?php echo CJavaScript::quote(t("We do not have any slots available for given criteria, please view the next available date"))?>',	
			terms: '<?php echo CJavaScript::quote(t("Restaurant Terms & Conditions"))?>',	
			continue: '<?php echo CJavaScript::quote(t("Continue"))?>',	
			reservation_details: '<?php echo CJavaScript::quote(t("Reservation details"))?>',	
			personal_details: '<?php echo CJavaScript::quote(t("Personal details"))?>',	
			first_name: '<?php echo CJavaScript::quote(t("First name"))?>',	
			last_name: '<?php echo CJavaScript::quote(t("Last name"))?>',	
			email_address: '<?php echo CJavaScript::quote(t("Email address"))?>',	
			special_request: '<?php echo CJavaScript::quote(t("Special requests"))?>',	
			agree: '<?php echo CJavaScript::quote(t("By continuing, you agree to Terms of Service and Privacy Policy."))?>',	
			back: '<?php echo CJavaScript::quote(t("Back"))?>',	
			reserve: '<?php echo CJavaScript::quote(t("Reserve"))?>',	
			reservation_id: '<?php echo CJavaScript::quote(t("Reservation ID"))?>',							
			reservation_succesful: '<?php echo CJavaScript::quote(t("Your reservation succesfully placed."))?>',	
			reservation_succesful_notes: '<?php echo CJavaScript::quote(t("You will receive another email once your reservation is confirm."))?>',	
			reserved_table_again: '<?php echo CJavaScript::quote(t("Reserved table again"))?>',	
			track_your_reservation: '<?php echo CJavaScript::quote(t("Track your reservation"))?>',	
			room_name: '<?php echo CJavaScript::quote(t("Room name"))?>',	
			table_name: '<?php echo CJavaScript::quote(t("Table name"))?>',	
		}"	    
		>
		</component-reservation>
	 </div>
	 </div>
  </section>
    
</div>
<?php endif?>
<!--SECTION BOOKING RESERVATION-->

<!--SECTION RESTAURANT DETAILS-->

<div class="container mt-0 mt-lg-5" >

  <section id="section-about" class="mb-3 p-2 p-lg-0">
  <div class="row">
    <div class="col-lg-3 col-md-3 p-0 mb-2 mb-lg-0">
        <div class="d-flex">
          <div class="mr-3"><img class="img-20" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/comment-more.png"?>"/></div>
          <div><h5><?php echo t("Few words about {{restaurant_name}}",array('{{restaurant_name}}'=>$data['restaurant_name']))?></h5></div>
       </div> <!--d-flex-->
    </div> <!--col-->
    <div class="col-lg-9 col-md-9">
       <p><?php echo Yii::app()->input->xssClean(nl2br($data['short_description']))?></p>
    </div> <!--col-->
  </div> <!--row-->
  </section>
  
  
  <section id="section-gallery" class="mb-5 p-2 p-lg-0" >
  <div class="row">
    <div class="col-lg-3 col-md-3 p-0 mb-2 mb-lg-0">
        <div class="d-flex">
          <div class="mr-3"><img class="img-20" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/image-gallery.png"?>"/></div>
          <div><h5><?php echo t("Gallery")?></h5></div>
       </div> <!--d-flex-->
    </div> <!--col-->
    <div class="col-lg-9 col-md-9">

    <?php if($gallery):?>
    <div class="gallery gallery_magnific row w-50 hover13">
       <?php $x=1;?>
       <?php foreach ($gallery as $gallery_item):?>
           <?php if($x<=5):?>           
	       <div class="col-lg-4 col-md-5 col-sm-6 col-6 mb-0 mb-lg-0  p-1">
	         <div class="position-relative"> 
	           <figure>
		       <div class="skeleton-placeholder"></div>
		       <a href="<?php echo $gallery_item['image_url']?>">
		       <img class="rounded lazy" data-src="<?php echo $gallery_item['thumbnail']?>"/>
		       </a>
		       </figure>
		     </div>  
	       </div>   
	       <?php endif;?>
	       
	       <?php if($x>5):?>
	          <div class="col-lg-4 col-md-5 col-sm-6 col-6 mb-0 mb-lg-0  p-1">
		         <div class="position-relative"> 
			       <div class="skeleton-placeholder"></div>
			       <a href="<?php echo $gallery_item['image_url']?>">
			       <div class="gallery-more d-flex align-items-center justify-content-center">+<?php echo count($gallery)-5;?></div>	       
			       <img class="rounded lazy" data-src="<?php echo $gallery_item['image_url']?>"/>
			       </a>
			     </div>  
		       </div>
	          <?php break;?>
	       <?php endif;?>
	       
       <?php $x++;?>
       <?php endforeach;?>
    </div> <!--gallery-->
    <?php endif;?>
    
    </div> <!--col-->
  </div> <!--row-->
  </section>
  
  <section id="section-address" class="mb-4 p-2 p-lg-0">
   <div class="row">
    <div class="col-lg-3 col-md-12 p-0 mb-3 mb-lg-0">
        <div class="d-flex">
          <div class="mr-3"><img class="img-20 contain" style="height:28px;" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/location.png"?>"/></div>
          <div>
            <h5><?php echo t("Address")?>:</h5>
            <div class="mb-3">
			    <?php if(!empty($data['contact_phone'])):?>
				<p class="m-0"><?php echo t("Contact#")?> : <?php echo $data['contact_phone']?></p>
				<?php endif?>
	            <p class="m-0"><?php echo $data['merchant_address']?></p>
				<?php if(!empty($tax_number)):?>
				<p class="m-0"><?php echo t("Tax number")?> : <?php echo $tax_number?></p>
				<?php endif?>
	            <?php if($map_direction):?>
	            <a href="<?php echo $map_direction;?>" target="_blank" class="a-12"><u><?php echo t("Get direction")?></u></a>
	            <?php endif;?>
            </div>			
            
          </div>
       </div> <!--d-flex-->
       
       <div class="d-flex">
          <div class="mr-3"><img class="img-20" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/clock.png"?>"/></div>
          <div class="flex-fill">
             <h5><?php echo t("Opening hours")?>:</h5>
             <?php if(is_array($opening_hours) && count($opening_hours)>=1):?>
             <table class="w-100">              
             <?php foreach ($opening_hours as $opening_hours_val):?>
                <tr >
                <td class="align-top pb-1"><?php echo ucwords(t($opening_hours_val['value']))?></td>
                 <td class="bold align-top pb-1">
                  <p class="m-0">
                  <?php echo t("[start] - [end]",
                       array(
                       '[start]'=>$opening_hours_val['start_time'],
                       '[end]'=>$opening_hours_val['end_time']) )
                  ?>
                  </p>
                  <?php if(!empty($opening_hours_val['start_time_pm'])):?>
                  
	                  <p class="m-0">
	                  <?php echo t("[start] - [end]",
	                       array(
	                       '[start]'=>$opening_hours_val['start_time_pm'],
	                       '[end]'=>$opening_hours_val['end_time_pm']) )
	                  ?>
	                  </p>  
                  
                  <?php endif;?>
                  
                  <?php if(!empty($opening_hours_val['custom_text'])):?>
                  <p class="m-0"><?php echo $opening_hours_val['custom_text'];?></p>
                  <?php endif;?>
                  
                 </td>
                </tr>
             <?php endforeach;?>
             </table>
             <?php endif;?>
          </div>
       </div> <!--d-flex-->
       
       
    </div> <!--col-->
    
    <div class="col-lg-9 col-md-12">
      <?php if(!empty($static_maps)):?>
      <img class="rounded w-100"  src="<?php echo $static_maps?>" alt="<?php echo $data['restaurant_name']?>">
      <?php endif;?>     
    </div> <!--col-->
    
  </div> <!--row-->
  </section>
  
</div> <!--container-->

<!--END SECTION RESTAURANT DETAILS-->



<!--SECTION REVIEW-->
<?php if($enabled_review):?>
<section id="section-review" class="container mb-4" >


 <div class="row mb-4">
	 <div class="col-3 p-lg-0">
	    <div class="d-flex align-items-center" style="height:28px;">
          <div class="m-0 mr-3"><img class="img-20" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/star.png"?>"/></div>
          <div><h5 class="m-0"><?php echo t("Reviews")?></h5></div>
        </div> <!--d-flex-->
	 </div> <!--col-->
	 
	 <div class="col-9">
	     <div class="d-flex justify-content-between align-items-center" style="height:28px;">
	       <div class="flex-fill">
	         <!--<a href="javascript:;" @click="openFormReview" class="a-12"><u><?php echo t("Add your opinion")?></u></a>-->
	       </div>
	       <div class=""><p class="m-0 mr-5"><?php echo t("Based on")?> <u><?php echo t("{{review_count}} reviews",array('{{review_count}}'=>$data['review_count']))?></u></p></div>
	       <div><span class="badge badge-yellow rounded-pill"><?php echo Price_Formatter::convertToRaw($data['ratings'],1)?></span></div>
	     </div> <!--flex-->
	 </div> <!--col-->
 </div> <!--row-->
  
 
 <el-skeleton :count="4" :loading="review_loading" animated>
 <template #template>
    <div class="row items-review mb-4"  >
	  <div class="col-lg-3 col-md-3 p-lg-0 mb-2 mb-lg-0">
	      <div class="d-flex align-items-center">
		    <div class="mr-3"><el-skeleton-item variant="circle" style="width: 60px; height: 60px" /></div>
			<div class="flex-grow-1">				
				<el-skeleton-item variant="h3" style="width: 50%" />				
			</div>
	      </div>

	  </div>
	  <div class="col-lg-9 col-md-9">
	       <el-skeleton :rows="2" />
	  </div>
	</div>
 </template>
 <template #default>

 <!--items-review-->
 <template v-for="data in review_data" >
 <div class="row items-review mb-4" v-for="reviews in data" >
	 <div class="col-lg-3 col-md-3 p-lg-0 mb-2 mb-lg-0">
	    <div class="d-flex align-items-center">
          <div class="mr-3"><img class="img-60 rounded rounded-pill" :src="reviews.url_image" /></div>
          <div>
            
            <h6 class="m-0" v-if="reviews.as_anonymous==0">{{ reviews.fullname }}</h6>
            <h6 class="m-0" v-if="reviews.as_anonymous==1">{{ reviews.hidden_fullname }}</h6>
                        
            <div class="star-rating"
            data-totalstars="5"
            :data-initialrating="reviews.rating"
            data-strokecolor="#fedc79"
            data-ratedcolor="#fedc79"
            data-strokewidth="10"
            data-starsize="15"
            data-readonly="true"
            ></div>            
            
          </div>
        </div> <!--d-flex-->
	 </div> <!--col-->
	 
	 <div class="col-lg-9 col-md-9">
	     <div class="d-flex justify-content-between ">
	       <div class="flex-fill mr-4" >
			 		     
	         <p class="d-none d-lg-block" v-html="reviews.review" ></p>
			 <div class="d-block d-lg-none"> 
				 <div class="row no-gutters">
				   <div class="col pr-2"><p v-html="reviews.review" ></p></div>
				   <div class="col-1"><span class="badge  rounded-pill">{{ reviews.rating }}</span></div>
				 </div>
			 </div>
	         	         
	         <div v-if="reviews.meta.tags_like" class="d-flex flex-row mb-3">
	           <div v-for="tags_like in reviews.meta.tags_like" class="mr-2">
	             <span v-if="tags_like" class="rounded-pill bg-lighter p-1 a-12 pl-2 pr-2">{{ tags_like }}</span>
	           </div>	           
	         </div>  
	         
	         <div v-if="reviews.meta.upload_images" class="gallery review_magnific row m-0">
	           <div v-for="upload_images in reviews.meta.upload_images" class="col-lg-2 col-md-3 col-sm-6 col-6 mb-0 mb-lg-0 p-1">
	             <figure class="m-0">
	                <a :href="upload_images">
		             <img class="rounded" :src="upload_images">
		           </a>	  	       
	             </figure>
	           </div>	           	           
	         </div> <!--gallery-->
	         
	       </div>	       
	       <div class="d-none d-lg-block"><span class="badge badge-yellow rounded-pill">{{ reviews.rating }}</span></div>
	     </div> <!--flex-->
	 </div> <!--col-->
 </div> 
 </template>
 <!--items-review-->

 </template>
 </el-skeleton>
 

 
 <div class="row mb-3" v-if="review_loadmore" >
	 <div class="col-lg-3 col-md-3 p-0"></div>
	 <div class="col-lg-9 col-md-9 ">
	    <a href="javascript:;" @click="loadMore" class="btn btn-black m-auto w25"><?php echo t("Load more")?></a>
	 </div>
</div><!-- row-->	 


</section>
<?php endif;?>
<!--END SECTION REVIEW-->


<!--COMPONENTS REVIEW -->
<div id="components-modal-review">
<components-review 
	@add-review="onConfirm"
	@close-order="onClose"	
	@remove-upload="onRemove"	
	
	:title="title"
	:is_loading="is_loading"	
	:required_message="required_message"
	:upload_images="upload_images"
	
	:review-value="review_content"
    @update:review-value="review_content = $event"
    
    :rating-value="rating_value"
    @update:rating-value="rating_value = $event"
    
></components-review>
</div>


<div class="container-fluid m-0 p-0 full-width">
 <?php $this->renderPartial("//store/join-us")?>
</div>
