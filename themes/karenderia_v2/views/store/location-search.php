
<?php if($location_searchtype==1):?>
    <el-row :gutter="10">
        <el-col :span="10">                        
        
            <components-city 
              ref="ref_city"
              v-model:city_id="city_id"
              location_searchtype="<?php echo $location_searchtype?>"
              :label="{
                    city: '<?php echo CJavaScript::quote(t('City'))?>',                           
                    no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
              }"
            ></components-city>            

        </el-col>
        <el-col :span="10">           
                           
           <components-area 
              ref="ref_area"
              v-model:area_id="area_id"
              location_searchtype="<?php echo $location_searchtype?>"
              :label="{
                    area: '<?php echo CJavaScript::quote(t('District/Area'))?>',                           
                    no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
              }"
              ></components-area>            

        </el-col>
        <el-col :span="4">            
           <el-button type="success" size="large" @click="onSearch" :disabled="!canSearch" >
           <i class="zmdi zmdi-arrow-right font25"></i>
           </el-button>
        </el-col>    
    </el-row>
<?php elseif ($location_searchtype==2):?>
    <el-row :gutter="10">
        <el-col :span="10">                        
            <components-state 
              ref="ref_state"
              v-model:state_id="state_id"
              country_id="<?php echo $country_id?>"
              :label="{
                    state: '<?php echo CJavaScript::quote(t('State'))?>',                           
                    no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
              }"
            ></components-state>
        </el-col>
        <el-col :span="10">           
                
            <components-city 
              ref="ref_city"
              v-model:city_id="city_id"    
              :label="{
                    city: '<?php echo CJavaScript::quote(t('City'))?>',                           
                    no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
              }"          
            ></components-city>            

        </el-col>
        <el-col :span="4">
           <el-button type="success" size="large" @click="onSearch" :disabled="!canSearch" >
           <i class="zmdi zmdi-arrow-right font25"></i>
           </el-button>
        </el-col>    
    </el-row>
<?php elseif ($location_searchtype==3):?>
    
    <el-row :gutter="10">
        <el-col :span="14">                        
                
            <components-postalcode 
              ref="ref_postalcode"
              v-model:postal_code="postal_code"       
              :label="{
                    postal_code: '<?php echo CJavaScript::quote(t('Postal Code/Zip Code'))?>',                           
                    no_data: '<?php echo CJavaScript::quote(t('No matching data'))?>', 
              }"       
            ></components-postalcode>      

        </el-col>        
        <el-col :span="10">
           <el-button type="success" size="large" class="w-100" @click="onSearch" :disabled="!canSearch" >
            <?php echo t("Search Restaurants")?>
           </el-button>
        </el-col>    
    </el-row>

<?php endif;?>