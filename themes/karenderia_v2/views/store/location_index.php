<div id="vm_home_search_location">
      <div class="d-block d-lg-none">
        <div class="mobile-home-banner"></div>
      </div>    

      <div class="container-fluid d-flex justify-content-center" id="main-search-banner">
      <div class="banner-center align-self-center">
         <h2 class="text-center mb-3"><?php echo t("Let's find best food for you")?></h2>
          <div class="home-search-wrap" v-cloak >             
             <?php 
             $this->renderPartial("//store/location-search",[
                'location_searchtype'=>$location_searchtype,
                'country_id'=>$country_id
             ]);
             ?>
          </div>
          <!-- home-search-wrap -->
      </div>
      <!-- banner-center -->
  </div>
  <!-- main-search-banner -->  

<div class="container mt-4 mb-3" v-cloak >

     <components-cuisine-list
      @after-getcuisine="afterGetcuisine"
      title="<?php echo t("Your favorite cuisines")?>"
      >
      </components-cuisine-list>     
      
      <components-swiper-list
      ref="ref_swiperlist"
      :query="['popular']"     
      :filters_transactions="filters_transactions"
      :city_id="city_id"
      :area_id="area_id"
      title="<?php echo t("Popular Restaurants Near You")?>"
      @after-getfeatured="afterGetfeatured"
      >
      </components-swiper-list>    

      <components-swiper-list  
      ref="ref_swiperlist"
      :query="['new']"     
      :filters_transactions="filters_transactions"
      :city_id="city_id"
      :area_id="area_id"
      title="<?php echo t("New Restaurants")?>"
      @after-getfeatured="afterGetfeatured"
      >
      </components-swiper-list>    
     
      <components-swiper-list  
      ref="ref_swiperlist"
      :query="['best_seller']"     
      :filters_transactions="filters_transactions"
      :city_id="city_id"
      :area_id="area_id"
      title="<?php echo t("Best Seller Restaurants")?>"
      @after-getfeatured="afterGetfeatured"
      >
      </components-swiper-list>    

      <components-swiper-list  
      ref="ref_swiperlist"
      :query="['recommended']"     
      :filters_transactions="filters_transactions"
      :city_id="city_id"
      :area_id="area_id"
      title="<?php echo t("Recommended Restaurants")?>"
      @after-getfeatured="afterGetfeatured"
      >
      </components-swiper-list>    
    
      
      <components-join      
      >
      </components-join>     


</div>
<!-- container -->


<?php if(isset(Yii::app()->params['settings']['enabled_mobileapp_section'])):?>
<?php if(Yii::app()->params['settings']['enabled_mobileapp_section']==1):?>
<div class="section-mobileapp section-newmobileapp tree-columns-center d-none d-md-block" > 
<div class="container">
   <div class="mb-0 row">
   
   <div class="col-lg-4 col-md-4 mb-4 mb-lg-3">
      <div class="d-flex align-items-center">
       <div class="w-100 text-center text-md-left">
         <h5><?php echo t("Best restaurants")?></h5>
         <h1 class="mb-4"><?php echo t("In your pocket")?></h1>
         <p class=""><?php echo t("Order from your favorite restaurants & track on the go.")?></p>
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
         <h1 class="mb-4"><?php echo $website_title?></h1>
         
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
  <div class="section-mobileapp section-newmobileapp"> 
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


<location-recent-address
ref="ref_location_recent_address"
>
</location-recent-address>

</div>
<!-- vm_home_search_location -->

<?php 
$this->renderPartial("//components/template_cuisine_list");
$this->renderPartial("//components/template_swiper_list");
$this->renderPartial("//components/template_join",[
  'website_title'=>$website_title
]);
$this->renderPartial("//components/template_recent_address");
?>