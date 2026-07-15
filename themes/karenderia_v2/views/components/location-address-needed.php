<div id="feed-locations"  v-cloak>
    <div class="container">
        
    <div class="d-flex justify-content-center">
        <div class="w-100 text-center mt-3 mb-3">   
            <template v-if="isAddressNeeded">
                <h4><?php echo t("Enter your address")?></h4>
                <p><?php echo t("We'll confirm that you can have this restaurant delivered.")?></p>
                <el-button @click="showCurrentaddress" round><?php echo t("Add Address")?></el-button>
            </template>
            
            <components-check-location 
            ref="ref_check_location"
            :search_type="search_type"
            :city_id="city_id"
            :area_id="area_id"
            :state_id="state_id"
            :postal_id="postal_id"
            @show-currentaddress="showCurrentaddress"
            ></components-check-location>
        </div>
    </div>
    <!-- center -->
    

    </div>
    <!-- container-fluid -->
    
    <components-current-address
    ref="ref_current_address"
    :search_type="search_type"
    :is_guest="isGuest"
    @after-changelocation="afterChangelocation"
    >
    </components-current-address>

</div>
<!-- feed-locations -->

<?php 
$this->renderPartial("//components/template_current_address");
$this->renderPartial("//components/template_check_locations");
?>