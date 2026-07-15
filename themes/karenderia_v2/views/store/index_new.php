
<div id="vm_home_search">

  <div class="d-block d-lg-none">
    <div class="mobile-home-banner"></div>
  </div>

  <div class="container-fluid d-flex justify-content-center" id="main-search-banner">
      <div class="banner-center align-self-center">
         <h2 class="text-center mb-3"><?php echo t("Let's find best food for you")?></h2>
          <div class="home-search-wrap" >     
            
           <component-auto-complete
            ref="auto_complete"
            :label="{
                enter_address : '<?php echo CJavaScript::quote(t("Enter your street and house number"))?>', 
            }"
            formatted_address=""
            @after-choose="afterChoose"
            @after-getcurrentlocation="afterGetcurrentlocation"
            :enabled_locate="<?php echo true;?>"
            >
            </component-auto-complete>

          </div>
          <!-- home-search-wrap -->
      </div>
      <!-- banner-center -->
  </div>
  <!-- main-search-banner -->

  <?php $maps_config = CMaps::config();?>        
      <components-select-address
      ref="address_modal"
      :data="deliveryAddress"
      keys="<?php echo $maps_config['key']?>"
      provider="<?php echo $maps_config['provider']?>"
      zoom="<?php echo $maps_config['zoom']?>"
      :center="{
        lat: '<?php echo CJavaScript::quote($maps_config['default_lat'])?>',  
        lng: '<?php echo CJavaScript::quote($maps_config['default_lng'])?>',  
      }"        
      :label="{
          exact_location : '<?php echo CJavaScript::quote(t("What's your exact location?"))?>', 
          enter_address : '<?php echo CJavaScript::quote(t("Enter your street and house number"))?>', 
          submit : '<?php echo CJavaScript::quote(t("Submit"))?>', 
      }"
      @after-changeaddress="afterPointaddress"
      >
    </components-select-address>      
        
    <components-address-form
    ref="address_form"
    :location_data="location_data"
    @on-savelocation="onSavelocation"
    @after-closemodal="afterFormclose"
    >	
    </components-address-form>

</div>
<!-- vm_home_search -->

<script type="text/x-template" id="xtemplate_address_form">
<?php $this->renderPartial("//account/checkout-address")?>
</script>


<DIV id="vue-home-widgets" >

<div class="container mt-4 mb-3" v-cloak >
  
  <h6 class="mb-3"><?php echo t("Cuisine type")?>:</h6>  

  <!-- cuisine list -->
  <div class="d-none d-lg-block">
  <div class="row no-gutters list-inline"> 
  
    <template v-for="(cuisine, index) in data_cuisine" >
    <div v-if="index<=7" class="col">
      <a :href="cuisine.url"  v-html="cuisine.cuisine_name"></a>
    </div>    
    </template>
    
    <template v-if="data_cuisine.length" >
    <template v-if="data_cuisine[8]" >
    <div class="col">          
      <a class="btn btn-sm dropdown-toggle text-truncate shadow-none" 
      href="javascript:;" id="dropdownCuisine" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    <?php echo t("More")?>
	  </a>	  
	  
	  <div class="dropdown-menu" aria-labelledby="dropdownCuisine">
	     <template v-for="(cuisine, index) in data_cuisine.slice(8)" >
	     <a  :href="cuisine.url" class="p-2 pl-2 d-block "  v-html="cuisine.cuisine_name" >	       
	     </a>	     
	     </template>
	  </div>	  
    </div> <!--col-->   
    </template>
    </template>
    
  </div> <!--row-->
  </div>
  <!-- cuisine list -->

  <div class="d-block d-lg-none">
     <component-cuisine
     :data="data_cuisine"
     :responsive='<?php echo json_encode($responsive);?>'
     ></component>
  </div>
 
    
  <!--COMPONENTS FEATURED LOCATION-->
  <component-carousel
  title="<?php echo t("Popular nearby")?>"
  featured_name="popular"
  :settings="{
      theme: '<?php echo CJavaScript::quote('rounded')?>',       
      items: '<?php echo CJavaScript::quote(5)?>', 
      lazyLoad: '<?php echo CJavaScript::quote(true)?>', 
      loop: '<?php echo CJavaScript::quote(true)?>', 
      margin: '<?php echo CJavaScript::quote(15)?>', 
      nav: '<?php echo CJavaScript::quote(false)?>', 
      dots: '<?php echo CJavaScript::quote(false)?>', 
      stagePadding: '<?php echo CJavaScript::quote(10)?>', 
      free_delivery: '<?php echo CJavaScript::quote( t("Free delivery") )?>',       
  }"
  :responsive='<?php echo json_encode($responsive);?>'
  />
  </component-carousel>  
  <!--COMPONENTS FEATURED LOCATION-->
    
    
  <!-- order 3 steps -->
  <?php if(isset(Yii::app()->params['settings']['enabled_home_steps'])):?>
  <?php if(Yii::app()->params['settings']['enabled_home_steps']==1):?>
  <div class="order-three-steps d-none d-lg-block">  
  <div class="section-addons row mt-4 mb-0">      
      <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
        <div class="addons addons-1">
	        <div class="inner">
	        <!-- <h1>01</h1> -->
          <br> <br>
	        <h5 style="text-align:right; color: white; font-size: 20px;"><?php echo t("No Minimum Order")?></h5>  <!--No Minimum Order-->
	        <p style="text-align:right; color: white; font-size: 180px;"><?php echo t("Order in for yourself or for the group, with no restrictions on order value")?></p> <!--Order in for yourself or for the group, with no restrictions on order value -->
	        </div>
        </div>
      </div>
      
       <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
        <div class="addons addons-2">
	        <div class="inner">
	        <!-- <h1>02</h1> -->
          <br> <br>
          <h5 style="text-align:right; color: white; font-size: 20px;"><?php echo t("Live Order Tracking")?></h5><!-- Live Order Tracking -->
	        <p style="text-align:right; color: white; font-size: 180px;"><?php echo t("Know where your order is at all times, from the restaurant to your doorstep")?></p><!-- Know where your order is at all times, from the restaurant to your doorstep -->
	        </div>
        </div>
      </div>
      
       <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
        <div class="addons addons-3">
	        <div class="inner">
	        <!-- <h1>03</h1> -->
           <br> <br>
          <h5 style="text-align:right; color: white; font-size: 20px;"><?php echo t("Lightning-Fast Deliver")?></h5>
	        <p style="text-align:right; color: white; font-size: 180px;"><?php echo t("Experience karenderia superfast delivery for food delivered fresh & on time")?></p> <!-- Experience karenderia superfast delivery for food delivered fresh & on time -->
	        </div>
        </div>
      </div>            
   </div> <!--ordering-steps-->
   </div> <!-- order 3 steps -->      

    <!-- order 3 steps mobile -->
   <div class="d-block d-lg-none">
     <component-three-steps      
     >      
     </component-three-steps>
   </div>
   <!-- order 3 steps mobile -->
   <?php endif?>
   <?php endif?>
   
   <!-- section-benefits -->
   <?php if(isset(Yii::app()->params['settings']['enabled_home_promotional'])):?>
   <?php if(Yii::app()->params['settings']['enabled_home_promotional']==1):?>
   <div class="section-benefits mt-3 mb-0 row">   
   
      <div class="col-lg-3 col-md-3 col-sm-6 mb-4 mb-lg-3">
        <div class="benefits benefits-1">
           <div class="inner">  
             <div class="d-flex align-items-start flex-column">
               <div class="mb-auto"><h4><?php echo t("Best promotions in your area")?></h4></div>
               <div>
               <p class="m-0"><?php echo t("")?></p><!-- Up to -->
               <!-- <h4>50%</h4> -->
               </div>
               <!-- <div class="mt-auto"><div class="btn-white-parent"><a  class="btn btn-link"><?php echo t("Check")?></a></div></div> -->
             </div>
           </div> <!--inner-->
        </div> <!--benefits-->
      </div> <!--col-->
      
      <div class="col-lg-3 col-md-3 col-sm-6 mb-4 mb-lg-3">
        <div class="benefits benefits-2">
           <div class="inner">  
             <div class="d-flex align-items-start flex-column">
               <div class="mb-auto"><h4><?php echo t("Rising stars Business")?></h4></div>
               <div>
               <p class="m-0"><?php echo t("")?></p> <!-- Try something -->
               <h4><?php echo t("")?></h4> <!-- New -->
               </div>
               <!-- <div class="mt-auto"><div class="btn-white-parent"><a class="btn btn-link"><?php echo t("Check")?></a></div></div> -->
             </div>
           </div> <!--inner-->
        </div> <!--benefits-->
      </div> <!--col-->
      
      <div class="col-lg-3 col-md-3  col-sm-6 mb-4 mb-lg-3">
        <div class="benefits benefits-3">
           <div class="inner">  
             <div class="d-flex align-items-start flex-column">
               <div class="mb-auto"><h4><?php echo t("Fastest delivery for you!")?></h4></div>
               <div>
               <p class="m-0"><?php echo t("")?></p><!-- Best quick -->
               <h4><?php echo t("")?></h4><!-- Lunch -->
               </div>
               <!-- <div class="mt-auto"><div class="btn-white-parent"><a class="btn btn-link"><?php echo t("Check")?></a></div></div> -->
             </div>
           </div> <!--inner-->
        </div> <!--benefits-->
      </div> <!--col-->
      
      <div class="col-lg-3 col-md-3  col-sm-6 mb-4 mb-lg-3">
        <div class="benefits benefits-4">
           <div class="inner">  
             <div class="d-flex align-items-start flex-column">
               <div class="mb-auto"><h4><?php echo t("Party night?")?></h4></div>
               <div>
               <p class="m-0"><?php echo t("")?></p> <!-- Maybe -->
               <h4><?php echo t("?")?></h4> <!-- Snacks -->
               </div>
               <!-- <div class="mt-auto"><div class="btn-white-parent"><a  class="btn btn-link"><?php echo t("Check")?></a></div></div> -->
             </div>
           </div> <!--inner-->
        </div> <!--benefits-->
      </div> <!--col--> 
            
   </div> <!--section-benefits-->
   <?php endif?>
   <?php endif?>
      
   <!--COMPONENTS FEATURED LOCATION-->
  <component-carousel
  title="<?php echo t("New restaurant")?>"
  featured_name="new"
  :settings="{
      theme: '<?php echo CJavaScript::quote('rounded-circle')?>',      
      items: '<?php echo CJavaScript::quote(6)?>',      
      lazyLoad: '<?php echo CJavaScript::quote(true)?>', 
      loop: '<?php echo CJavaScript::quote(true)?>', 
      margin: '<?php echo CJavaScript::quote(15)?>', 
      nav: '<?php echo CJavaScript::quote(false)?>', 
      dots: '<?php echo CJavaScript::quote(false)?>', 
      stagePadding: '<?php echo CJavaScript::quote(10)?>', 
      free_delivery: '<?php echo CJavaScript::quote( t("Free delivery") )?>', 
  }"
  :responsive='<?php echo json_encode($responsive);?>'
  />
  </component-carousel>  
  <!--COMPONENTS FEATURED LOCATION-->
  
  <!--JOIN US-->
  <?php if(isset(Yii::app()->params['settings']['enabled_signup_section'])):?>
  <?php if(Yii::app()->params['settings']['enabled_signup_section']==1):?>
  <div class="mt-4">
  <?php $this->renderPartial("//store/join-us")?>
  </div>
  <?php endif?>
  <?php endif?>
  <!--END JOIN US-->
      
</div> <!--container-->


<?php if(isset(Yii::app()->params['settings']['enabled_mobileapp_section'])):?>
<?php if(Yii::app()->params['settings']['enabled_mobileapp_section']==1):?>
<div class="section-mobileapp tree-columns-center d-none d-md-block"> 
<div class="container">
   <div class="mb-0 row">
   
   <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
      <div class="d-flex align-items-center">
       <div class="w-100 text-center text-md-left">
         <h5><?php echo t("Best restaurants")?></h5>
         <h1 class="mb-4"><?php echo t("In your pocket")?></h1>
         <p class=""><?php echo t("Order from your favorite restaurants & track on the go, with the all-new K app.")?></p>
       </div>
      </div>
   </div>  
   
   <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
      <div class="d-flex align-items-center">
       <div class="w-100 text-center">
          <img class="mobileapp" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/mobileapp.png"?>" />
       </div>
      </div>
   </div>
   
    <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
      <div class="d-flex align-items-center">
       <div class="w-100 text-center text-md-right">
         <h5><?php echo t("Download")?></h5>
         <h1 class="mb-4"><?php echo t("K mobile app")?></h1>
         
         <div class="app-store-wrap">
           <a href="<?php echo  !empty($ios_download_url)?$ios_download_url:'#' ?>" class="d-inline mr-2" 
           <?php echo !empty($ios_download_url)?'target="_blank"':""; ?>
           >
		        <img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/app-store@2x.png">
		       </a>
          <a href="<?php echo !empty($android_download_url)?$android_download_url:'#' ?>" class="d-inline" 
          <?php echo !empty($android_download_url)?'target="_blank"':""; ?>
          >
            <img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/google-play@2x.png">
          </a>
         </div>
         
       </div>
      </div>
   </div>
   
   </div> <!--row-->
</div> <!--container-->
</div> <!--sections-->

<!-- section mobile app view -->
<div class="d-block d-md-none">
  <div class="section-mobileapp border"> 
     <div class="container text-center"> 
     
         <h5><?php echo t("Best restaurants")?></h5>
         <h1 class="mb-3"><?php echo t("In your pocket")?></h1>
         <p class=""><?php echo t("Order from your favorite restaurants & track on the go, with the all-new K app.")?></p>

         <div class="d-flex justify-content-center app-store-wrap mb-5 mt-4">
           <div class="mr-2">
           <a href="<?php echo  !empty($ios_download_url)?$ios_download_url:'#' ?>" class="d-inline mr-2" 
           <?php echo !empty($ios_download_url)?'target="_blank"':""; ?>
           >
              <img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/app-store@2x.png">
            </a>
           </div>
           <div class="">
           <a href="<?php echo !empty($android_download_url)?$android_download_url:'#' ?>" class="d-inline" 
          <?php echo !empty($android_download_url)?'target="_blank"':""; ?>
          >
              <img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/google-play@2x.png">
            </a>
           </div>
         </div>

         <img class="mobileapp" src="<?php echo Yii::app()->theme->baseUrl."/assets/images/mobileapp-half.png"?>" />

     </div>
   </div>
</div>
<!-- section mobile app view -->
<?php endif?>
<?php endif?>


<div class="container">

   <!--COMPONENTS FEATURED LOCATION-->
  <component-carousel
  title="<?php echo t("Try something new in")?>"
  featured_name="best_seller"
  :settings="{
      theme: '<?php echo CJavaScript::quote('rounded')?>',       
      items: '<?php echo CJavaScript::quote(5)?>', 
      lazyLoad: '<?php echo CJavaScript::quote(true)?>', 
      loop: '<?php echo CJavaScript::quote(true)?>', 
      margin: '<?php echo CJavaScript::quote(15)?>', 
      nav: '<?php echo CJavaScript::quote(false)?>', 
      dots: '<?php echo CJavaScript::quote(false)?>', 
      stagePadding: '<?php echo CJavaScript::quote(10)?>', 
      free_delivery: '<?php echo CJavaScript::quote( t("Free delivery") )?>', 
  }"
  :responsive='<?php echo json_encode($responsive);?>'
  />
  </component-carousel>  
  <!--COMPONENTS FEATURED LOCATION-->

</div> <!--container-->

</DIV>
<!--vue-home-widgets-->


<script type="text/x-template" id="three-steps-ordering">

  <div ref="carousel_three_steps" class="section-addons carousel-three-steps owl-carousel owl-theme">

    <div class="mr-2">
       <div class="addons addons-1">
	        <div class="inner">
	        <h1>01</h1>
	        <h5><?php echo t("No Minimum Order")?></h5>
	        <p><?php echo t("Order in for yourself or for the group, with no restrictions on order value")?></p>
	        </div>
        </div>
    </div> 
    <!-- item -->

    <div class="mr-2">
       <div class="addons addons-2">
	        <div class="inner">
	        <h1>02</h1>
	        <h5><?php echo t("Live Order Tracking")?></h5>
	        <p><?php echo t("Know where your order is at all times, from the restaurant to your doorstep")?></p>
	        </div>
        </div>
    </div>
    <!-- item -->

    <div class="">
       <div class="addons addons-3">
	        <div class="inner">
	        <h1>03</h1>
	        <h5><?php echo t("Lightning-Fast Delivery")?></h5>
	        <p><?php echo t("Experience karenderia superfast delivery for food delivered fresh & on time")?></p>
	        </div>
        </div>
    </div>
    <!-- item -->

  </div> 
  <!-- carousel -->
</script>