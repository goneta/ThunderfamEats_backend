
<div id="location-restaurant-list" class="container p-2" style="min-height: calc(65vh)" >
      

   <components-restaurant-list
     ref="ref_restaurantlist"     
     :city_id="city_id"
     :area_id="area_id"
     :state_id="state_id"
     :postal_id="postal_id"
     :filters="quick_filters"
     :is_filters="true"
     :query="query"
     title="<?php echo isset($title)?$title:''?>"          
     >
    </components-restaurant-list>
  
</div>
<!-- location-featured -->

<?php $this->renderPartial("//components/template_restaurant_list");?>