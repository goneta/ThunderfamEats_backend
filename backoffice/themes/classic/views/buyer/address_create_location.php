

<div class="card" id="vue-location-address">
  <div class="card-body" v-cloak>

  
<el-form
    label-position="top"
    label-width="auto"        
	v-loading="loading_getaddress"
>  


<div class="row">
    <div class="col-md-6">
	     <el-form-item label="<?php echo t("House number")?>" label-position="top">
		  <el-input v-model="house_number" size="large"></el-input>
		 </el-form-item>
	</div>
	<div class="col-md-6">
	     <el-form-item label="<?php echo t("Street")?>" label-position="top">
		  <el-input v-model="formatted_address" size="large"></el-input>
		 </el-form-item>
	</div>
</div>

<div class="row">
	<div class="col-12">
	    <el-form-item label="<?php echo t("Street number")?>" label-position="top">
		  <el-input v-model="address1" size="large"></el-input>
		 </el-form-item>
	</div>
</div>

<div class="row">
    <div class="col-md-6">
          <el-form-item label="<?php echo t("Country")?>" label-position="top">
            <el-select
              v-model="country_id"        
              filterable
              remote
              reserve-keyword
              placeholder="<?php echo t("Please enter a keyword")?>"
              remote-show-suffix        
              :loading="loading"        
              size="large"
              class="w-100"
              :automatic-dropdown="true"       
              @change="OnselectCountry" 
            >
              <el-option
                v-for="item in country_list"
                :key="item.value"
                :label="item.label"
                :value="item.value"
              />
            </el-select>
        </el-form-item>
    </div>
    <div class="col-md-6">      	
       <el-form-item label="<?php echo t("State/Region")?>" label-position="top">
          <el-select
            v-model="state_id"        
            filterable
            remote
            reserve-keyword
            placeholder="<?php echo t("Please select")?>"
            remote-show-suffix        
            :loading="loading_state"        
            size="large"
            class="w-100"
            :automatic-dropdown="true"        
            @change="OnselectState" 
          >
            <el-option
              v-for="item in state_list"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
        </el-form-item>
    </div>
  </div>
  
  <div class="row">
    <div class="col-md-6">

      <el-form-item label="<?php echo t("City")?>" label-position="top">
          <el-select
            v-model="city_id"        
            filterable
            remote
            reserve-keyword
            placeholder="<?php echo t("Please select")?>"
            remote-show-suffix        
            :loading="loading_city"        
            size="large"
        class="w-100"
            :automatic-dropdown="true"        
            @change="OnselectCity" 
          >
            <el-option
              v-for="item in city_list"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
      </el-form-item>

    </div>
    <div class="col-md-6">
    
        <el-form-item label="<?php echo t("Distric/Area/neighborhood")?>" label-position="top">
          <el-select
            v-model="area_id"        
            filterable
            remote
            reserve-keyword
            placeholder="<?php echo t("Please select")?>"
            remote-show-suffix        
            :loading="loading_area"        
            size="large"
            class="w-100"
            :automatic-dropdown="true"        
          >
            <el-option
              v-for="item in area_list"
              :key="item.value"
              :label="item.label"
              :value="item.value"
            />
          </el-select>
      </el-form-item>

    </div>
  </div>

<div class="row">
    <div class="col-md-6">
	   <el-form-item label="<?php echo t("Zip Code")?>" label-position="top">
          <el-input v-model="zip_code" size="large" ></el-input>
       </el-form-item>      
	</div>
	<div class="col-md-6">
	   <el-form-item label="<?php echo t("Location Name")?>" label-position="top">
          <el-input v-model="location_name" size="large" placeholder="<?php echo t("Aparment, suite or floor")?>" ></el-input>
       </el-form-item>
	</div>
</div>
  
  <el-space fill class="w-100">  
   <el-alert type="info" show-icon :closable="false">
   <p>      
      <?php echo t("You can easily find your coordinates by visiting")?> <a target="_blank" href="https://www.maps.ie/coordinates.html">https://www.maps.ie/coordinates.html</a>
   </p>      
   </el-alert>      
  </el-space>

  <div class="row">
    <div class="col-md-6">
	     <el-form-item label="<?php echo t("Latitude")?>" label-position="top">
		  <el-input v-model="latitude" size="large"></el-input>
		 </el-form-item>
	</div>
	<div class="col-md-6">
	     <el-form-item label="<?php echo t("Longitude")?>" label-position="top">
		  <el-input v-model="longitude" size="large"></el-input>
		 </el-form-item>
	</div>
</div>


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

  <el-button 
        @click="onSubmit" 
        type="primary" 
        size="large"
        :loading="loading"
		class="w-100"		
        >
            <?php echo t("Submit")?>
        </el-button>

</el-form>

  </div>
</div>