
<div id="feed-locations" class="container-fluid" style="min-height:calc(80vh);"  >


<div class="row mt-2 mt-lg-4 row mb-4" v-cloak>

<div v-if="!hasQuery" class="col-lg-2 col-md-3 d-none d-lg-block column-1 affix-container">

   <el-affix target=".affix-container" :offset="80">          
    
     <el-scrollbar style="height:calc(80vh)" >
         <location-filters ref="ref_filters"
         @on-changefilter="onChangefilter"
         @on-changetransactions="onChangetransactions"
         @after-getfilters="afterGetfilters"
         >         
         </location-filters>
     </el-scrollbar>

    </el-affix>
</div>
<!-- col -->

<div class="col-lg-10 col-md-12 column-2" :class="{ 'col-lg-12 col-md-12' : hasQuery }">    


    <template v-if="!hasFilters">
    <div class="d-none d-lg-block">
      <el-breadcrumb separator="/">
      <el-breadcrumb-item >
         <a href="<?php echo Yii::app()->createUrl("/store/index")?>">
           <?php echo t("Home")?>
         </a>
      </el-breadcrumb-item>
      <el-breadcrumb-item>
         <?php echo t("Restaurants")?>
      </el-breadcrumb-item>    
      </el-breadcrumb>
    </div>
    </template>

    <div class="pt-2 pb-2 d-none d-lg-block"></div>
    <div class="pt-1 pb-1 d-none d-block d-lg-none"></div>

    <!-- MOBILE FILTERS -->
    <div class="d-block d-lg-none mb-2">              
        <location-mobile-filters 
         ref="ref_mobile_filters"
         :data="filters_data"
         @on-changefilter="onChangefilter"
         @on-changetransactions="onChangetransactions"
         @after-getfilters="afterGetfilters"
         >         
         </location-mobile-filters>
    </div>


    <template v-if="!hasFilters || hasFilters">      
     <components-search
     ref="ref_search"
     :cuisine_list="cuisineList"
     @after-clearsearch="afterClearsearch"
     @after-setsearch="afterSetsearch"
     >
     </components-search>
    </template>
     
    
    <template v-if="has_banner">
     <div class="mb-4 mt-4">
        <template v-if="!hasFilters">
        <components-banner
        ref="ref_banner"
        @after-getbanner="afterGetbanner"
        >
        </components-banner>     
        </template>
     </div>
   </template>

   
     <template v-if="!hasFilters">     
     <!-- postal_id=>{{postal_id}} -->
      <components-swiper-list
      ref="ref_swiperlist"
      :query="['popular']"     
      :filters_transactions="filters_transactions"
      :city_id="city_id"
      :area_id="area_id"
      :state_id="state_id"
      :postal_id="postal_id"
      title="<?php echo t("Popular Restaurants Near You")?>"
      @after-getfeatured="afterGetfeatured"
      >
      </components-swiper-list>

      
      <components-cuisine-list
      @after-getcuisine="afterGetcuisine"
      title="<?php echo t("Your favorite cuisines")?>"
      >
      </components-cuisine-list>
     </template>
          
     
     <components-restaurant-list
     ref="ref_restaurantlist"     
     :city_id="city_id"
     :area_id="area_id"
     :state_id="state_id"
     :postal_id="postal_id"
     :filters="Object.keys(filters).length > 0 ?filters:filters_transactions"
     :is_filters="hasFilters"
     :query="query"
     title="<?php echo t("All restaurants")?>"          
     >
     </components-restaurant-list>
     
     
     <el-backtop ref="ref_backtop" :right="50" :bottom="50" />

</div>
<!-- col -->

</div>
<!-- row -->


<components-current-address
ref="ref_current_address"
:search_type="search_type"
:is_guest="isGuest"
@after-changelocation="afterChangelocation"
>
</components-current-address>

</div>
<!-- feed-locations -->

<?php $this->renderPartial("//components/template_current_address");?>

<script type="text/x-template" id="xtemplate_location_filters">

<template v-if="loading">
<el-skeleton :rows="13" animated  />
</template>
<template v-else >

<div class="d-flex align-items-center justify-content-between mb-3">
   <div>
      <h5 class="m-auto"><?php echo t("Filters")?></h5>
   </div>
   <div v-if="hasFilter">
      <el-button type="plain" link @click="clearFilters" >
         <?php echo t("Clear all")?>
      </el-button>
   </div>
</div>
<!-- d-flex -->


<el-radio-group v-model="filter_transaction" class="block" @change="onChangeTransaction">
   <template v-for="items in services_list">
      <el-radio :value="items.service_code" size="large">{{items.service_name}}</el-radio>
   </template>   
</el-radio-group>

<div class="pt-3"></div>

<h6 class="font-weight-normal">
   <?php echo t("Quick filters")?>
</h6>

<template v-for="(items,index) in quick_filters_list">
<div class="mb-2">
<el-button round @click="setQuickFilters(index)" :type="ifFilterIncludes(index) ? 'primary' :'default' " >   
  <el-icon class="el-icon--left gold-color">
     <i :class="filter_icons[index]"></i>
  </el-icon>
   {{ items }}
</el-button>
</div>
</template>

<div class="pt-3"></div>

<h6 class="font-weight-normal">
   <?php echo t("Offers")?>
</h6>
<template v-for="(items,offers_index) in offers_filters_list">       
   <div class="mb-1">            
      <el-checkbox 
      v-model="offers_filters" 
      :label="items" 
      size="large" 
      :value="offers_index"
      @change="onChangeFilters"
      >
      </el-checkbox>         
   </div>                         
   </template>       

<div class="pt-4"></div>

<div :class="{ 'min-filter':!more_cuisine  }">
   <h6 class="font-weight-normal">
      <?php echo t("Cuisines")?>
   </h6>
   <div class="mb-2" style="width:90%;">
     <el-input 
     v-model="search_cuisine" 
     placeholder="<?php echo t("Search Cuisine")?>"
     clearable
     class="rounded"
     @input="searchInput"     
      >
     </el-input>
   </div>
   
   <template v-if="!filterCuisineFound && search_cuisine">
      <div class="font-weight-bold"><?php echo t("No results found for")?> "{{ search_cuisine }}"</div>
   </template>
   <template v-for="items in filteredCuisine">       
   <div class="mb-1">            
      <el-checkbox 
      v-model="cuisine" 
      :label="items.cuisine_name" 
      size="large" 
      :value="items.cuisine_id"
      @change="onChangeFilters"
      >
      </el-checkbox>         
   </div>                         
   </template>       
</div>

<template v-if="filterCuisineFound && !search_cuisine">
<el-button type="plain" link @click="more_cuisine=!more_cuisine">
   <div class="d-flex align-items-center">
      <div class="mr-2">
         <template v-if="more_cuisine">
            <?php echo t("Show less")?>
         </template>
         <template v-else>            
            <?php echo t("Show more")?>
         </template>
      </div>
      <div><i class="fas fa-chevron-down"></i></div>
   </div>   
</el-button>
</template>

<div class="pt-3"></div>
<h6 class="font-weight-normal"><?php echo t("Price")?></h6>
<el-radio-group v-model="price_range" size="large" @change="onChangeFilters" >
<template v-for="(items,index) in price_range_list">
  <el-radio-button :label="items" :value="index" ></el-radio-button>
</template>
</el-radio-group>

<div class="pt-3"></div>

</template>
</script>


<script type="text/x-template" id="xtemplate_banner">
   
   <template v-if="loading">
      <el-skeleton animated >
         <template #template>
            <el-skeleton-item variant="rect" style="height: 180px;width:100%;"  />                           
         </template>
      </el-skeleton>
   </template>

   <div class="swiper swiperBanner" ref="refSwiper">
      <div class="swiper-wrapper">  
         <template v-for="items in data">           
            <div class="swiper-slide swiperSlide" type="button" @click="onBannerClick(items)">
               <el-image :fit="cover" class="radius10 none" :src="items.image" ></el-image>
            </div>
         </template>
      </div>      
      <div class="swiper-pagination"></div>
   </div> 
</script>

<?php 
$this->renderPartial("//components/template_swiper_list");
$this->renderPartial("//components/template_cuisine_list");
?>

<script type="text/x-template" id="xtemplate_restaurant_list">
<template v-if="loading && page<=1">
   <el-skeleton animated >
      <template #template>
         <el-skeleton-item variant="rect" style="height: 180px;width:100%;"  />                           
      </template>
   </el-skeleton>
</template>

<template v-if="hasData">
   <div class="mb-4 mt-4">
       <h3 class="font-weight-bold">
         <template v-if="is_filters">
            {{total_pretty}}
         </template>
         <template v-else>
            {{title}}
         </template>
       </h3>
   </div> 
</template>

<div class="row no-gutters">
   <template v-for="items in data" v-if="items !== null && items !== undefined">
      <div class="col-lg-3 col-md-3 col-sm-6 p-1 mb-3 ">
         <div class="rounded-box" type="button"  @click="onBannerClick(items)" >
             <div class="position-relative"> 
               
              <div v-if="items && items.merchant_open_status=='0'" class="layer-grey"></div>
               <div v-else-if="items.close_store == '1' || items.disabled_ordering == '1' || items.pause_ordering=='1' ||
               items.holiday_status=='1'  " 
               class="layer-black d-flex align-items-center justify-content-center" >
               </div>

               <div v-if="items.close_store == '1' || items.disabled_ordering=='1'" 
                  class="layer-content d-flex align-items-center justify-content-center">
                  <p class="bold"><?php echo t("Currently unavailable")?></p>
               </div>
               
               <div v-if="items.pause_ordering=='1' && items.disabled_ordering!='1' && items.close_store!='1' " 
                  class="layer-content d-flex align-items-center justify-content-center">
                     <p class="bold" v-if="items.pause_reason" >{{ items.pause_reason }}</p>
                     <p class="bold" v-else><?php echo t("Currently unavailable")?></p>
               </div>

               <div v-if="items.holiday_status == '1'" 
                  class="layer-content d-flex align-items-center justify-content-center">
                  <p class="bold text-capitalize">{{ items.holiday_reason }}</p>
               </div>

                <div class="position-absolute p-2" style="z-index: 9;">
                     <template v-for="promos_items in items.vouchers">
                        <div class="mb-1"><el-tag type="danger">{{promos_items.title}}</el-tag></div>
                     </template>
                     <template v-for="promos_items in items.promos">
                        <div class="mb-1"><el-tag type="warning">{{promos_items.title}}</el-tag></div>
                     </template>
                </div>
                <el-image :src="items.logo" lazy ></el-image>                
             </div>          
             <div class="p-2">

                <div v-if="items.merchant_open_status=='0'" class="make-grey">
                     <h6 class="m-0">
                        {{items.next_opening}}
                     </h6> 
                </div>
                  
                <div class="font-weight-bold mb-2 truncate">{{ items.restaurant_name }}</div>
                <div class="font-weight-light truncate mb-1">{{ items.cuisines }}</div>
                <div class="row align-items-center no-gutters">
                     <div class="col">
                        <div class="d-flex">
                           <div class="mr-1"><i class="fas fa-star gold-color"></i></div>
                           <div>{{items.ratings}} ({{items.review_count}}+ <?php echo t("Ratings")?>)</div>
                        </div>                        
                     </div>
                     <div class="col-5">
                        <div class="d-flex">
                           <div class="mr-2"><i class="zmdi zmdi-time"></i></div>
                           <div class="truncate">{{items.estimated_time_min}}</div>
                        </div>                        
                     </div>   
                </div> 
                <!-- row -->
             </div>            
         </div>
         <!-- rounded -->
     </div>
     <!-- col -->
   </template>
</div>
<!-- row -->

<infinite-loading  @infinite="getData" distance="50" :identifier="identifier" >
   <template #spinner>
     <div class="p-2 justify-center">
        <div class="typing_loader"></div>
     </div>
   </template>
   <template #complete>      
     <template v-if="hasData">
     <div class="p-2 justify-center d-none">        
        <?php echo t("No more items to display")?>
     </div>
     </template>
     <div v-else class="mb-4 mt-4">       
         <h3 class="font-weight-bold"><?php echo t("No Restaurants found")?></h3>
         <p class="text-muted"><?php echo t("We couldn't find any results with the current filters. Try changing the filters or reset them to explore more options.")?></p>
      </div>     
   </template>
</infinite-loading> 


</script>


<script type="text/x-template" id="xtemplate_search">
<div class="position-relative" :class="{ 'search-show' : search_modal }" >        
   <el-input 
   v-model="search_filters" 
   placeholder="<?php echo t("Search for restaurants, cuisines,and dishes")?>"
   clearable
   class="rounded"
   @input="searchInput"     
   size="large"
   @click="search_modal=true"
   @clear="searchInputClear"
   :loading="loading"
   >
    <template #suffix>
      <template v-if="loading">
          <div class="typing_loader"></div> 
      </template>
    </template>
   </el-input>  
   <template v-if="search_modal">
   <div class="suggestion-results p-2">
      <el-scrollbar max-height="500px" >          
               
         <template v-if="hasInput">                           
            <div><?php echo t("Search for")?> "{{search_filters}}"</div>

            <ul class="suggestion-search-list">
            <template v-for="items in data">
               <li>
                  <div class="d-flex">
                  <div class="mr-auto">
                        <el-button link type="plain" size="large" @click="setSearchFilters(items.name)">
                           <el-icon class="el-icon--left"><i class="zmdi zmdi-search font20 mr-3"></i></el-icon> {{items.name}}
                        </el-button>
                     </div>
                     <div class="">
                        <el-button link type="plain" size="large" @click="setSearchFilters(items.name)" >
                           <el-icon class="el-icon--left"><i class="zmdi zmdi-arrow-right-top font20 rotate-180"></i></el-icon>
                        </el-button>
                     </div>
                  </div>
               </li>
            </template>
            </ul>
         </template>
         <template v-else>
               <template v-if="hasSearchHistory">
               <div><h5><?php echo t("Recent searches")?></h5></div>
               <ul class="suggestion-search-list">
                  <template v-for="(items,index) in searchHistory">
                  <li>
                  <div class="d-flex">
                     <div class="mr-auto">
                        <el-button link type="plain" size="large" @click="setSearchFilters(items.searchTerm)">
                           <el-icon class="el-icon--left"><i class="zmdi zmdi-time-restore font20 mr-3"></i></el-icon> {{items.searchTerm}}
                        </el-button>
                     </div>
                     <div class="">
                        <el-button link type="plain" size="large" @click="deleteSearch(index)">
                           <el-icon class="el-icon--left"><i class="zmdi zmdi-close font20"></i></el-icon>
                        </el-button>
                     </div>
                  </div>                
                  </li>
                  </template>
               </ul>
               </template>  
               
               <div class="p-2 pb-3">
                  <h5><?php echo t("Popular searches")?></h5>              
                  <div class="flex-content w-100">
                  <template v-for="items in cuisine_list">
                     <div class="flex-content-item">
                     <el-button round  @click="setSearchFilters(items.cuisine_name)" >
                        {{items.cuisine_name	}}
                     </el-button>
                     </div>
                  </template>                  
                  </div>    
               </div>
         </template>
      </el-scrollbar>
   </div>
   </template>
   </div>    
   
   <div v-if="search_modal" class="search-suggestions-overlay" @click.stop="search_modal=false">
   <div class="bds-c-modal__backdrop">        
   </div>
</div>   
</script>

<script type="text/x-template" id="xtemplate_mobile_location_filters">
<el-scrollbar class="pb-2">
   <div class="scrollbar-flex-content">
      
      <div class="scrollbar-item">
         <el-button round @click="modal=!modal">          
            <el-icon class="el-icon--left warm-pink">
               <i class="fas fa-filter"></i>
            </el-icon>
         </el-button>
      </div>
            
      <template v-if="hasData">
         <template v-for="(items,index) in data.quick_filters">
            <div class="scrollbar-item">
            <el-button round @click="setQuickFilters(index,true)" :type="ifFilterIncludes(index) ? 'success' :'default' "  >          
               <el-icon class="el-icon--left gold-color">
                  <i :class="filter_icons[index]"></i>
               </el-icon>
               {{items}}
            </el-button>
            </div>
         </template>      
      </template>

   </div>
</el-scrollbar>

<el-drawer
v-model="modal"        
direction="btt"
size="100%"
modal-class="drawer-footer-center"
@open="onOpened"
>
<template #header>
   <h4 class="font-weight-bold"><?php echo t("Filters")?></h4>
</template>

<template #default>      
   
   <div class="flex-content mb-4">
      <template v-for="items in getServices">         
          <div class="flex-content-item">
              <el-button round  @click="setTransactiontype(items.service_code)" :type="filter_transaction==items.service_code ? 'success' :'default' "   >               
                  {{ items.service_name }}
              </el-button>
          </div>
      </template>
   </div>

   <div class="mb-3">
     <h6 class="font-weight-normal"><?php echo t("Quick filters")?></h6>
     <div class="flex-content">
     <template v-for="(items,index) in getQuickFilters">
         <div class="flex-content-item">
            <el-button round @click="setQuickFilters(index,false)" :type="ifFilterIncludes(index) ? 'success' :'default' "  >   
            <el-icon class="el-icon--left gold-color">
               <i :class="filter_icons[index]"></i>
            </el-icon>
               {{ items }}
            </el-button>
         </div>
      </template>
     </div>
   </div>
      
   <div class="mb-3">
     <h6 class="font-weight-normal"><?php echo t("Offers")?></h6>
     <div class="flex-content">
       <template v-for="(items,index) in getOfferFilters">     
         <div class="flex-content-item">              
            <el-button round   @click="setOffers(index)" :type="offersIncludes(index) ? 'success' :'default' "   >               
               {{ items }}
            </el-button>
         </div>
     </template>
     </div>                         
   </div>
   
   <div class="mb-3">      
      <h6 class="font-weight-normal"><?php echo t("Cuisines")?></h6>
      <div class="flex-content">
         <template v-for="(items,index) in getCuisine">
            <div class="flex-content-item">
               <el-button round @click="setCuisine(items.cuisine_id)" :type="cuisineIncludes(items.cuisine_id) ? 'success' :'default' "   >               
                  {{ items.cuisine_name }}
               </el-button>
            </div>
         </template>
      </div>
   </div>

   <div class="mb-3">      
      <h6 class="font-weight-normal"><?php echo t("Price")?></h6>
      <div class="flex-content">
         <template v-for="(items,index) in getPrices">
            <div class="flex-content-item">
               <el-button round  @click="price_range=index" :type="price_range==index ? 'success' :'default' "   >               
                  {{ items }}
               </el-button>
            </div>
         </template>
      </div>
   </div>
   

</template>

<template #footer>

<div class="mb-2">
<el-button @click="onChangeFilters" type="success" round size="large" class="w-100">
  <?php echo t("Apply")?>
</el-button>
</div>
<div>
   <template v-if="hasFilters">
       <el-button @click="clearFilters" round size="large" class="w-100"><?php echo t("Clear Filters")?></el-button>
   </template>
   <template v-else>
       <el-button @click="closeDrawer" round size="large" class="w-100"><?php echo t("Cancel")?></el-button>
   </template>
</div>

</template>
</el-drawer>

</script>