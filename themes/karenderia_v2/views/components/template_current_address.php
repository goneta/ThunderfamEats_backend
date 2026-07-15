<script type="text/x-template" id="xtemplate_current_address">
<el-dialog
    v-model="modal"
    :title="is_guest?'<?php echo t("Choose an address")?>':'<?php echo t("Add or choose an address")?>'"
    width="500"    
    @open="initgetAddress"
  >        
  <!-- search_type=>{{search_type}} -->
  <template v-if="search_type==1">
  <el-row :gutter="10">
        <el-col :span="10">                                         
            <components-city 
              ref="ref_city"
              v-model:city_id="city_id"
              :location_searchtype="search_type"
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
              :location_searchtype="search_type"
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
    </template>

    <template v-else-if="search_type==2">
    <el-row :gutter="10">
        <el-col :span="10">           
            <?php $country_id = Clocations::getDefaultCountry(); ?>
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
        </el-col>
        <el-col :span="10">                                      
            <components-city 
              ref="ref_city"
              v-model:city_id="city_id"
              :location_searchtype="search_type"
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
    </template>

    <template v-else-if="search_type==3">    
      <el-row :gutter="10">
        <el-col :span="20">                     
             <components-postalcode 
              ref="ref_postalcode"
              v-model:postal_code="postal_id"              
            ></components-postalcode>             
        </el-col>
        <el-col :span="4">                          
           <el-button type="success" size="large" @click="onSearch" :disabled="!canSearch" >
           <i class="zmdi zmdi-arrow-right font25"></i>
           </el-button>
        </el-col>    
     </el-row>
    </template>
    
    <div v-if="!is_guest" class="mt-3">
       <template v-if="loading">
          <el-skeleton animated  >
            <template #template>
             <el-skeleton-item variant="rect"  style="height:70px;" ></el-skeleton-item>
            </template>
          </el-skeleton>
       </template>
       <template v-else>        
        <div v-if="hasAddress" class="custom-element-radio">  
          <el-scrollbar max-height="280px">
              <el-radio-group v-model="address_uuid">
                <template v-for="items in data">
                    <el-radio :value="items.address_uuid" size="large" border class="w-100">
                        <div class="text-dark font-weight-bold">{{items.address_label}}</div>
                        <div>{{items.complete_address}}</div>
                    </el-radio>                      
                </template>
              </el-radio-group>
          </el-scrollbar>        
          <div class="text-right mt-2">
              <el-button size="large"  @click="modal = false">
                <?php echo t("Cancel")?>
              </el-button>
              <el-button type="success" size="large" @click="setSelectAddress()">
                <?php echo t("Select")?>
              </el-button>
          </div>                            
        </div>       
       </template>
    </div>

</el-dialog>
</script>