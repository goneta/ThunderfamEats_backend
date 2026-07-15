<el-form
    label-position="top"
    label-width="auto"    
    style="max-width: 600px"
>  

<el-row :gutter="10">
   <el-col :span="12">    
       <el-form-item label="<?php echo t("House number")?>" label-position="top">
          <el-input v-model="house_number" size="large" ></el-input>
       </el-form-item>
   </el-col>         
   <el-col :span="12">    
       <el-form-item label="<?php echo t("Street")?>" label-position="top">
          <el-input v-model="formatted_address" size="large" ></el-input>
       </el-form-item>
   </el-col>                       
</el-row>

<el-row :gutter="10">   
   <el-col :span="12">    
      <el-form-item label="<?php echo t("Street Number")?>" label-position="top">
          <el-input v-model="address1" size="large" ></el-input>
       </el-form-item>
   </el-col>         
   <el-col :span="12"> 
     <el-form-item label="<?php echo t("State/Region")?>" label-position="top">             
     <components-state 
        ref="ref_state"
        v-model:state_id="state_id"
        country_id="<?php echo $country_id?>"        
        @after-selectstate="afterSelectstate"
        :label="{
               state: '<?php echo CJavaScript::quote(t('State'))?>',                           
               no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
         }"
     ></components-state>
     </el-form-item>
   </el-col>                       
</el-row>

<el-row :gutter="10">   
   <el-col :span="12">    
     <el-form-item label="<?php echo t("City")?>" label-position="top">       
     <components-city 
        ref="ref_city"
        v-model:city_id="city_id"       
        @after-selectcity="afterSelectcity"      
        :label="{
               city: '<?php echo CJavaScript::quote(t('City'))?>',                           
               no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
         }" 
     ></components-city>      
     </el-form-item>            
   </el-col>         
   <el-col :span="12">    
       <el-form-item label="<?php echo t("Distric/Area/neighborhood")?>" label-position="top"> 
       <components-area 
            ref="ref_area"
            v-model:area_id="area_id"
            location_searchtype="<?php echo $location_searchtype?>"
            @after-selectarea="afterSelectarea"       
            :label="{
                  area: '<?php echo CJavaScript::quote(t('District/Area'))?>',                           
                  no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
            }"
        ></components-area>           
        </el-form-item>      
   </el-col>                
</el-row>

<el-row :gutter="10">         
   <el-col :span="12">          
       <el-form-item label="<?php echo t("Zip Code")?>" label-position="top">
          <el-input v-model="zip_code" size="large" ></el-input>
       </el-form-item>      
   </el-col>                
   <el-col :span="12">    
       <el-form-item label="<?php echo t("Location Name")?>" label-position="top">
          <el-input v-model="location_name" size="large" placeholder="<?php echo t("Aparment, suite or floor")?>" ></el-input>
       </el-form-item>
   </el-col>
</el-row>

<template v-if="enabled_map_selection">
   <div class="mb-3" v-loading="loading_getlocation" >
     <el-space fill class="w-100">  
      <el-alert type="info" show-icon :closable="false">
      <p><?php echo t("To ensure accurate and timely delivery, we need the latitude and longitude of your delivery address. This helps us pinpoint your exact location and avoid any delivery delays")?>.</p>      
      </el-alert>      
     </el-space>       
     <div class="position-relative">
     <components-maps 
      ref="ref_maps"
      :keys="cmaps_config?cmaps_config.key:''"
      :provider="cmaps_config?cmaps_config.provider:''"
      :zoom="cmaps_config?cmaps_config.zoom:''"
      :markers="[{
         lat : latitude ? latitude : cmaps_config.lat ,
         lng : longitude ? longitude : cmaps_config.lng ,
         draggable : true
      }]"
      :center="{
         lat : cmaps_config?cmaps_config.lat:'',
         lng : cmaps_config?cmaps_config.lng:'',
      }"
      @after-selectmap="afterSelectmap"
      @locate-location="locateLocation"
     >
     </components-maps>
     </div> 
     <div class="text-right">
        <el-button @click="getCurrentLocation" type="text" plain>
         <?php echo CommonUtility::safeTranslate("Use my current location")?>
        </el-button>
     </div>
   </div>
</template>
<template v-else>
<el-space fill class="w-100">  
   <el-alert type="info" show-icon :closable="false">
   <p>
      <?php echo t("To ensure accurate and timely delivery, we need the latitude and longitude of your delivery address. This helps us pinpoint your exact location and avoid any delivery delays")?>.
      <br/>
      <?php echo t("You can easily find your coordinates by visiting")?> <a target="_blank" href="https://www.maps.ie/coordinates.html">https://www.maps.ie/coordinates.html</a>
   </p>      
   </el-alert>      
</el-space>
</template>

<el-row :gutter="10">
   <el-col :span="12">               
       <el-form-item label="<?php echo t("Latitude")?>" label-position="top">
          <el-input v-model="latitude" size="large" placeholder="<?php echo t("Latitude")?>" ></el-input>
       </el-form-item>
       
   </el-col>                
   <el-col :span="12">    

      <el-form-item label="<?php echo t("Longitude")?>" label-position="top">
          <el-input v-model="longitude" size="large" placeholder="<?php echo t("Longitude")?>" ></el-input>
       </el-form-item>
      
   </el-col>                
</el-row>

<el-row :gutter="10">
   <el-col :span="24">    

   <el-form-item label="<?php echo t("Delivery options")?>" label-position="top">
   <el-select
      v-model="delivery_options"      
      size="large"      
    >
      <el-option
        v-for="item in delivery_option_list"
        :key="item.value"
        :label="item.label"
        :value="item.value"
      >
      </el-option>
    </el-select>
    </el-form-item>
       
   </el-col>                   
</el-row>

<el-row :gutter="10">
   <el-col :span="24">    

   <el-form-item label="<?php echo t("Add delivery instructions")?>" label-position="top">
   <el-input
    v-model="delivery_instructions"    
    :rows="2"
    type="textarea"    
    placeholder="<?php echo t("eg. ring the bell after dropoff, leave next to the porch, call upon arrival, etc")?>"
   >
   </el-input>  
   </el-form-item>
       
   </el-col>                   
</el-row>

<el-row :gutter="10">
   <el-col :span="24">    
      
   <el-form-item label="<?php echo t("Address label")?>" label-position="top">
   <el-radio-group v-model="address_label" size="large">
      <template v-for="items in address_label_list">
         <el-radio-button :label="items.label" :value="items.value" />
      </template>      
    </el-radio-group>
    </el-form-item>

   </el-col>                   
</el-row>

</el-form>