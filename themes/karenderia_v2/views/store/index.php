
<!-- ThunderfamEats redesign: mark page for scoped theming -->
<script>document.documentElement.classList.add('tf-home');</script>

<div id="vm_home_search">

  <div class="d-block d-lg-none">
    <div class="mobile-home-banner"></div>
  </div>
<!-- KEN MODIFICATIONS  -->
  <?php $tfFr = (stripos((string)Yii::app()->language, 'fr') === 0); ?>
  <div class="container-fluid" id="main-search-banner">
    <div class="tf-banner">

      <div class="tf-wordmark"><span class="tf-tf">Thunderfam</span><span class="tf-e1">E</span><span class="tf-e2">a</span><span class="tf-e3">t</span><span class="tf-e4">s</span></div>

      <h1 class="tf-banner-title"><?php if($tfFr): ?>Tous vos besoins quotidiens, <span class="g">à portée</span> <span class="b">de main</span> <span class="r">!</span><?php else: ?>All Your Daily Needs, <span class="g">One</span> <span class="b">Tap</span> <span class="r">Away!</span><?php endif; ?></h1>
      <p class="tf-banner-sub"><?php echo $tfFr ? "Services, réservations, commandes et livraisons ; — tout est réuni au même endroit." : "Services, booking, ordering and delivery; — everything in one place."; ?></p>

      <div class="home-search-wrap" >

           <component-auto-complete
            ref="auto_complete"
            :label="{
                enter_address : '<?php echo CJavaScript::quote(t("Locate Your Location"))?>',
            }"
            formatted_address=""
            @after-choose="afterChoose"
            @after-getcurrentlocation="afterGetcurrentlocation"
            @after-pointaddress="afterPointaddress"
            :enabled_locate="<?php echo true;?>"
            >
            </component-auto-complete>

      </div>
      <!-- home-search-wrap -->

      <div class="tf-cat-grid">
        <a href="javascript:;" class="tf-cat tf-cat-green"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M3 11l9-7 9 7"/><path d="M5 10v10h14V10"/></svg></span><b><?php echo $tfFr?'Services à domicile':'Home Services'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-blue"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><circle cx="6" cy="6" r="2.6"/><circle cx="6" cy="18" r="2.6"/><path d="M20 4L8.7 15.3M20 20L8.7 8.7"/></svg></span><b><?php echo $tfFr?'Barber':'Barber'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-orange"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M2 7.5h10a4 4 0 0 1 0 8H8"/><path d="M8 15.5L7 22"/><path d="M2 7.5v8h6"/></svg></span><b><?php echo $tfFr?'Coiffeuse':'Hairdresser'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-blue"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M4 13l1.8-5h12.4L20 13"/><path d="M3 13h18v5H3z"/><circle cx="7.5" cy="18" r="1.4"/><circle cx="16.5" cy="18" r="1.4"/></svg></span><b><?php echo $tfFr?'Réserver un taxi':'Book Taxi'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-orange"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M3 18V7"/><path d="M3 11h13a4 4 0 0 1 4 4v3"/><path d="M3 14h18"/><path d="M8 11V9h4v2"/></svg></span><b><?php echo $tfFr?'Réserver un hôtel':'Hotel'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-orange"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M3.5 16a8.5 8.5 0 0 1 17 0"/><path d="M2 16h20"/><path d="M12 4.5V7"/></svg></span><b><?php echo $tfFr?'Réserver un restaurant':'Book Restaurants'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-orange"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M7 3v6a2 2 0 0 0 4 0V3"/><path d="M9 9v12"/><path d="M17 3c-1.5 1-2.5 3-2.5 6S16 15 17 15v6"/></svg></span><b><?php echo $tfFr?'Commander des repas':'Order Food'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-green"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M3 4h2l2.2 11h10L20 7H6"/><circle cx="9" cy="19.5" r="1.4"/><circle cx="17" cy="19.5" r="1.4"/></svg></span><b><?php echo $tfFr?'Courses':'Grocery'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-green"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M12 3l8 4v10l-8 4-8-4V7z"/><path d="M4 7l8 4 8-4"/><path d="M12 11v10"/></svg></span><b><?php echo $tfFr?"Livraison de biens & d'articles":'Delivery Good & Items'?></b></a>
        <a href="javascript:;" class="tf-cat tf-cat-green"><span class="tf-cat-ico"><svg viewBox="0 0 24 24"><path d="M6 8h12l-1 12H7z"/><path d="M9 8V6.5a3 3 0 0 1 6 0V8"/></svg></span><b><?php echo $tfFr?'À emporter':'Takeout'?></b></a>
      </div>
      <!-- tf-cat-grid -->

      <div class="tf-trust">
        <div class="tf-trust-item tf-trust-green"><span class="tf-trust-ico"><svg viewBox="0 0 24 24"><path d="M12 3l7 3v6c0 5-3.5 7.5-7 9-3.5-1.5-7-4-7-9V6z"/><path d="M8.5 12l2.3 2.3L15.5 10"/></svg></span><div><h6><?php echo $tfFr?'Professionnels fiables et vérifiés':'Trusted &amp; Verified'?></h6><p><?php echo $tfFr?'Des experts de confiance à votre service':'Reliable professionals you can trust'?></p></div></div>
        <div class="tf-trust-item tf-trust-blue"><span class="tf-trust-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg></span><div><h6><?php echo $tfFr?'Rapide &amp; pratique':'Fast &amp; Convenient'?></h6><p><?php echo $tfFr?'Gagnez du temps, nous nous occupons du reste':"Save time, we've got you"?></p></div></div>
        <div class="tf-trust-item tf-trust-orange"><span class="tf-trust-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="9" r="5.5"/><path d="M8.5 13.5L7 21l5-2.8L17 21l-1.5-7.5"/></svg></span><div><h6><?php echo $tfFr?'Service de qualité':'Quality Service'?></h6><p><?php echo $tfFr?'La satisfaction client est notre priorité':'Top-notch service, every time'?></p></div></div>
        <div class="tf-trust-item tf-trust-red"><span class="tf-trust-ico"><svg viewBox="0 0 24 24"><path d="M12 22s7-6 7-12a7 7 0 0 0-14 0c0 6 7 12 7 12z"/><circle cx="12" cy="10" r="2.4"/></svg></span><div><h6><?php echo $tfFr?'Disponible près de chez vous':'Available Near You'?></h6><p><?php echo $tfFr?'Nous sommes dans votre zone, prêts à vous aider':"We're in your area, ready to help"?></p></div></div>
      </div>
      <!-- tf-trust -->

    </div>
    <!-- tf-banner -->
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
    >	
    </components-address-form>

</div>
<!-- vm_home_search -->

<script type="text/x-template" id="xtemplate_address_form">
<?php $this->renderPartial("//account/checkout-address")?>
</script>

<!-- Mobile search  -->
<div class="d-block d-lg-none container mt-3">  
 <DIV id="vue-home-search-mobile" class="position-relative">  
	  <!-- <component-home-search
	  ref="childref"
	  next_url="< ?php echo Yii::app()->createAbsoluteUrl('store/restaurants')?>"
	  auto_generate_uuid = "true"
	  :label="{		    
		 enter_address: '< ?php echo CJavaScript::quote(t("Enter delivery address"))?>', 		    
	  }"	    
    :enabled_auto_detect_address="< ?php echo $enabled_auto_detect_address?>"
	  />
	  </component-home-search>    -->
  </DIV>
</div>
<!-- mobile search -->

<DIV id="vue-home-widgets" >

<div class="container mt-4 mb-3" v-cloak >
  
  <h6 class="mb-3"><?php echo t("Business type")?>:</h6>  <!-- cuisine type -->

  <!-- cuisine list -->
  <div class="d-none d-lg-block">
  <div class="row no-gutters list-inline"> 
  
    <template v-for="(cuisine, index) in data_cuisine" >
    <div v-if="index<=7" class="col">
      <a :href="cuisine.url" > {{ cuisine.cuisine_name }}</a>
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
	     <a  :href="cuisine.url" class="p-2 pl-2 d-block "  >
	       {{ cuisine.cuisine_name }}
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
    
  <!-- /* KEN MODIFICATIONS */ -->
  <!-- order 3 steps -->
  <?php if(isset(Yii::app()->params['settings']['enabled_home_steps'])):?>
  <?php if(Yii::app()->params['settings']['enabled_home_steps']==1):?>
  <div class="order-three-steps d-none d-lg-block">
  <h2 class="tf-section-title"><?php echo t("Our exclusive advantages")?></h2>
  <div class="tf-advantages">
      <div class="tf-adv-card tf-adv-1">
        <div class="tf-adv-img"><img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/addons-1_new.png" alt=""></div>
        <div class="tf-adv-body">
          <h5><?php echo t("No Minimum Order")?></h5>
          <p><?php echo t("Order in for yourself or for the group, with no restrictions on order value")?></p>
        </div>
      </div>

      <div class="tf-adv-card tf-adv-2">
        <div class="tf-adv-img"><img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/addons-2_new.png" alt=""></div>
        <div class="tf-adv-body">
          <h5><?php echo t("Live Order Tracking")?></h5>
          <p><?php echo t("Know where your order is at all times, from the restaurant to your doorstep")?></p>
        </div>
      </div>

      <div class="tf-adv-card tf-adv-3">
        <div class="tf-adv-img"><img src="<?php echo Yii::app()->theme->baseUrl?>/assets/images/addons-3_new.png" alt=""></div>
        <div class="tf-adv-body">
          <h5><?php echo t("Lightning-Fast Deliver")?></h5>
          <p><?php echo t("Experience karenderia superfast delivery for food delivered fresh & on time")?></p>
        </div>
      </div>
   </div> <!--tf-advantages-->
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
   <!-- KEN MODIFICATIONS -->
   <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
      <div class="d-flex align-items-center">
       <div class="w-100 text-center text-md-left">
         <h5><?php echo t("Local Businesses")?></h5>
         <h3 class="mb-4"><?php echo t("In your pocket")?></h3>
         <p class=""><?php echo t("Order from your favorite Businesses & track on the go, with ThunderfamEats app.")?></p>
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
   <!-- KEN MODIFICATIONS -->
    <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
      <div class="d-flex align-items-center">
       <div class="w-100 text-center text-md-right">
         <h5><?php echo t("Download")?></h5>
         <h3 class="mb-4"><?php echo t("ThunderfamEats app")?></h3>
         
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