

<div id="vue-feed" class="container p-2">
  <h4 class="m-0"><?php echo $model->cuisine_name?></h4>
  <h6 class="m-0" v-cloak >{{this.total_message}}</h6>
  <div class="p-2"></div>  
  
  <template v-if="!hasData && !is_loading">
	<div v-if="hasFilter" style="min-height:400px;">
		<h3><?php echo t("0 Result(s)")?></h3>
		<p class="m-0 text-muted"><?php echo t("No available restaurant with your selected filters")?>.</p>
	</div>
	<div v-else>
	  <h3><?php echo t("Sorry! We're not there yet")?></h3>
      <p><?php echo t("We're working hard to expand our area. However, we're not in this location yet. So sorry about this, we'd still love to have you as a customer.")?></p>
	</div>
 </template>


 <el-skeleton :loading="is_loading" animated :count="4" >
	<template #template>
	   <div class="row equal align-items-center">	  
			<div v-for="dummy_index in 3" class="col-lg-4  col-md-6 mb-3 list-items">
				<div><el-skeleton-item variant="image" style="width: 100%; height: 170px" /></div>
				<div><el-skeleton-item style="width: 50%;" variant="text" /></div>
				<div><el-skeleton-item  variant="text" /></div>
			</div>
       </div>
	</template>  
	<template #default>

    <div class="row equal align-items-center position-relative">

    <template v-for="(data,data_index) in datas"  >
	   <div class="col-lg-4 mb-3 col-md-6 list-items"  v-for="item in data" 
	   :class="{ 'make-grey': item.merchant_open_status=='0' || item.close_store=='1' || item.disabled_ordering=='1' }"  >  
	   	   
	     <!--IMAGE-->
	     <div class="position-relative"> 
	       <!-- <div class="skeleton-placeholder"></div> -->
	       <a :href="item.merchant_url">
			   <!-- <img class="rounded lazy" :data-src="item.url_logo"/> -->
			   <el-image
					style="width: 100%; height: 170px"
					:src="item.url_logo"
					:fit="cover"
					lazy
				></el-image>
			</a>
	       
	       <a :href="item.merchant_url">
	       
	         <div v-if="item.merchant_open_status=='0'" class="layer-grey"></div>
	         <div v-else-if="item.close_store == '1' || item.disabled_ordering == '1' || item.disabled_ordering=='1' || item.pause_ordering=='1'  " 
	          class="layer-black d-flex align-items-center justify-content-center" >
	         </div>
	         
	         <div v-if="item.close_store == '1' || item.disabled_ordering=='1'" 
	          class="layer-content d-flex align-items-center justify-content-center">
	           <p class="bold"><?php echo t("Currently unavailable")?></p>
	         </div>
	         
	         <div v-if="item.pause_ordering=='1' && item.disabled_ordering!='1' && item.close_store!='1' " 
	          class="layer-content d-flex align-items-center justify-content-center">
	             <p class="bold" v-if="pause_reason_data[item.merchant_id]">{{pause_reason_data[item.merchant_id]}}</p>
	             <p class="bold" v-else><?php echo t("Currently unavailable")?></p>
	         </div>
	         
	       </a>
	     </div>  
	     <!--END IMAGE-->
	     
	     <div class="row align-items-center mt-2" >
	      <div class="col text-truncate">
	       <h6 v-if="item.merchant_open_status=='0'" class="m-0">
	       {{item.next_opening}}
	       </h6> 
	       <a :href="item.merchant_url">
	         <h5 class="m-0 text-truncate">{{item.restaurant_name}}</h5>
	       </a>
	      </div>
	      <div class="col col-md-auto text-right">
	           	      	      
	        <!--COMPONENTS-->
	        <component-save-store
	         :active="item.saved_store=='1'?true:false"
	         :merchant_id="item.merchant_id"
	         @after-save="afterSaveStore(item)"
	        />
	        </component-save-store>
	        <!--COMPONENTS-->
	        
	      </div>
	     </div> <!--flex-->
	     
	     
	     <div class="row align-items-center" >
	      <div class="col text-truncate">
       
	        <template  v-for="(cuisine,index) in item.cuisine_name"  >	        
	         <span class="a-12 mr-1">{{cuisine.cuisine_name}},</span>	      	         
	        </template>
	        
	      </div>
	      <div class="col col-md-auto text-right">
	       <p class="m-0 bold">
	         <template v-if="estimation[item.merchant_id]">
	           <template v-if="services[item.merchant_id]">
	             <template v-for="(service_name,index_service) in services[item.merchant_id]"  >
	               <template v-if="index_service<=0">
	               
				   <template v-if="estimation[item.merchant_id][service_name]">    
						<template v-if=" estimation[item.merchant_id][service_name][item.charge_type] "> 
						{{ estimation[item.merchant_id][service_name][item.charge_type].estimation }} <?php echo t("min")?>
						</template>
					</template>
	                   
	               </template>
	             </template>
	           </template>
	         </template>
	       </p>
	      </div>
	    </div> <!--flex-->
	     
	    
	    <div class="row align-items-center">
	      <div class="col text-truncate">
	      <p class="m-0" v-if="enabled_review">
	      <b class="mr-1">{{item.ratings.rating}}</b> 
	      <i class="zmdi zmdi-star mr-1 text-grey"></i>
	        
	       <u v-if="item.ratings.review_count>0">{{item.ratings.review_count}}+ <?php echo t("Ratings")?></u>
	       <u v-else>{{item.ratings.review_count}} <?php echo t("rating")?></u>
	       
	      </p>	      
	      </div>
	      
	      <div class="col-md-auto text-right">
	        <p class="m-0" v-if="item.free_delivery==='1'" ><?php echo t("Free delivery")?></p>
	      </div>
	    </div> <!--flex-->
	   
	   </div> <!--col-->
	   </template>

    </div>
    <!-- row -->

  </template>
	</el-skeleton>

</div>