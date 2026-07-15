

<div id="vue-delivery-management" v-cloak >

<div v-loading="loading" >

<h6 class="mb-3">Delivery Charge Type</h6>


<el-form :model="form" label-width="auto" label-position="top" >
<el-form-item >
     <el-select
      v-model="delivery_charges_type"
      placeholder="Select"
      size="large"
      style="width: 240px"
    >
       <el-option label="Fixed Charge" value="fixed" ></el-option>
       <el-option label="Base Distance" value="base-distance" ></el-option>
       <el-option label="Range-Based" value="range-based" ></el-option>
    </el-select>
</el-form-item>

<el-form-item >
   <el-checkbox v-model="opt_contact_delivery" label="Enabled Opt in for no contact delivery" size="large" ></el-checkbox>
   <el-checkbox v-model="free_delivery_on_first_order" label="Free Delivery On First Order " size="large" ></el-checkbox>   
</el-form-item>


<el-form-item>
    <el-button type="success" size="large" @click="onSubmit" :loading="loading_submit">Save Settings</el-button>      
</el-form-item>
</el-form>


<hr class="mt-4 mb-4" />

<el-tabs v-model="delivery_charges_type" class="demo-tabs" @tab-change="tabClick">
    <el-tab-pane label="Fixed Charge" name="fixed">

    <el-form :model="form"  label-width="auto" label-position="top"  >
        <el-row :gutter="20">
        <el-col :span="11">
            <el-form-item label="Price" >
            <el-input-number v-model="fixed_price" :min="0" :max="999999999"  size="large" controls-position="right" class="w-100">
            </el-input-number>
            </el-form-item>		  
        </el-col>
        <el-col :span="11">
            <el-form-item label="Delivery estimation" >              
            <el-input 
            v-model="fixed_estimation" 
            size="large" 
            :formatter="(value) => value.replace(/\D/g, '').replace(/(\d{2})(\d{2})/, '$1-$2')"
            :parser="(value) => value.replace(/[^\d-]/g, '')"
            >
            <template #append>Minutes</template>
          </el-input>            
            </el-form-item>		  
        </el-col>
        </el-row>

        <el-row :gutter="20">
        <el-col :span="11">
            <el-form-item label="Minimum Order Value" >
            <el-input-number v-model="fixed_minimum_order" :min="0" :max="999999999"  size="large" controls-position="right" class="w-100">
            </el-form-item>		  
        </el-col>
        <el-col :span="11">
            <el-form-item label="Maximum Order Value" >
            <el-input-number v-model="fixed_maximum_order" :min="0" :max="999999999"  size="large" controls-position="right" class="w-100">
            </el-form-item>		  
        </el-col>
        </el-row>

        <el-row :gutter="20" >
        <el-col :span="11">                  
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Minimum Order for Free Delivery
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Minimum order value for waiving delivery charges"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input-number v-model="fixed_free_delivery_threshold" :min="0" :max="999999999"  size="large" controls-position="right" class="w-100">
            </el-col>
        </el-col>
        </el-row>

        <div class="p-3"></div>

        <el-form-item>
          <el-button type="success" size="large" class="w-100 p-3" @click="saveFixedRate" >Save</el-button>          
       </el-form-item>
    </el-form>

    </el-tab-pane>


    <el-tab-pane label="Base Distance" name="base-distance">
       <el-form :model="form"  label-width="auto" label-position="top"  >

       
        <el-row :gutter="20" >
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                       Base Distance
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Initial distance for free or minimum delivery charge"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_base_distance"  size="large" >
                    <template #append><?php echo $distance_pretty_unit?></template>
                 </el-input>  
            </el-col>
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Base Delivery Fee
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Standard fee applied within the base distance"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_base_delivery_fee"  size="large" placeholder="<?php echo $eg_fee?>" ></el-input>  
            </el-col>
        </el-row>

        <div class="p-2"></div>

        <el-row :gutter="20" >
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Price per <?php echo $distance_pretty_unit?>
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Additional cost per mile/kilometer beyond the base distance"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_price_extra_distance"  size="large" placeholder="<?php echo $eg_fee?>" ></el-input>  
            </el-col>
            <el-col :span="11">
                 <!-- <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Delivery Radius
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Maximum distance for delivery"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_delivery_radius" size="large" >
                    <template #append><?php echo $distance_pretty_unit?></template>
                 </el-input>   -->
            </el-col>
        </el-row>

        <div class="p-2"></div>

        <el-row :gutter="20" >
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Minimum Order Value
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Minimum order amount required for delivery service to be available"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_minimum_order"  size="large" placeholder="<?php echo $eg_fee?>" ></el-input>  
            </el-col>
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Maximum Order Value
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Maximum order amount allowed for delivery service"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_maximum_order"  size="large" placeholder="<?php echo $eg_fee?>" ></el-input>  
            </el-col>
        </el-row>

        <div class="p-2"></div>

        <el-row :gutter="20" >
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Minimum Order for Free Delivery
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Minimum order value for waiving delivery charges"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_free_delivery_threshold"  size="large" placeholder="<?php echo $eg_fee?>" ></el-input>  
            </el-col>
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Cap on Delivery Charge
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Maximum delivery charge regardless of distance"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_cap_delivery_charge"  size="large" placeholder="<?php echo $eg_fee?>" ></el-input>  
            </el-col>
        </el-row>

        <div class="p-2"></div>

        <el-row :gutter="20" >
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Base Delivery Time Estimate
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Estimated time for the base distance"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_base_time_estimate"  size="large" >
                    <template #append>Minutes</template>
                 </el-input>  
            </el-col>
            <el-col :span="11">
                 <div class="d-flex align-items-center">
                   <label class="el-form-item__label line-normal height-auto">
                   Time per Additional Mile/Kilometer
                   </label>
                   <div>
                     <el-popover
                        placement="bottom"                        
                        :width="200"
                        trigger="click"
                        content="Additional delivery time per mile beyond the base distance"
                    >
                        <template #reference>
                          <el-button circle size="small" link ><i class="zmdi zmdi-info-outline font16"></i></el-button>
                        </template>
                    </el-popover>
                   </div>
                 </div>
                 <el-input v-model="bd_base_time_estimate_additional"  size="large"  >
                    <template #append>Minutes</template>
                 </el-input>  
            </el-col>
        </el-row>

      
        <el-form-item class="mt-4">
          <el-button type="success" size="large" class="w-100" @click="saveBasedistance" >Save</el-button>          
       </el-form-item>

      </el-form>
    </el-tab-pane>

    <el-tab-pane label="Range-Based" name="range-based">

        <div class="text-right">
        <el-button type="primary" @click="showAddrate" size="large">
            <?php echo t("Add new")?>
        </el-button>
        </div>

        <components-datatable
        ref="datatable"
        ajax_url="<?php echo isset($ajax_url)?$ajax_url:'' ?>" 
        actions="rangeDistanceList"
        :table_col='<?php echo json_encode($table_col)?>'
        :columns='<?php echo json_encode($columns)?>'
        :date_filter='<?php echo false;?>'
        :settings="{
            auto_load : '<?php echo false;?>',
            filter : '<?php echo false;?>',   
            ordering :'<?php echo true;?>',  
            order_col :'<?php echo intval($order_col);?>',   
            sortby :'<?php echo $sortby;?>',         
            placeholder : '<?php echo t("Start date -- End date")?>',  
            separator : '<?php echo t("to")?>',
            all_transaction : '<?php echo t("All transactions")?>',
            delete_confirmation : '<?php echo CJavaScript::quote(t("Delete Confirmation"));?>',    
            delete_warning : '<?php echo CJavaScript::quote(t("Are you sure you want to permanently delete the selected item?"));?>',        
            cancel : '<?php echo CJavaScript::quote(t("Cancel"));?>',        
            delete : '<?php echo CJavaScript::quote(t("Delete"));?>',        
        }"  
        page_limit = "<?php echo Yii::app()->params->list_limit?>"  
        @edit-records="editRecords"        
        >
        </components-datatable>
    </el-tab-pane>    

</el-tabs>


<components-addrate 
ref="ref_addrate"
save_action="saveRangeRate"
merchant_id="0"
@after-saverate="afterSaverate"
></components-addrate>

</div>
</div>
<!-- vue-delivery-management -->


<!-- TEMPLATE -->

<script type="text/x-template" id="xtemplate_range_based">
<el-dialog
    v-model="modal"
    title="Add Rate"
    width="500"     
    @open="close"  
>

<el-form :model="form"  label-width="auto" label-position="top"  >
    <el-row :gutter="20">
    <el-col :span="11">
        <el-form-item label="From" >
           <el-input-number
            v-model="rd_from"
            :min="0"
            :max="999999999"    
            size="large"
            controls-position="right"
            
            class="w-100"
            >
            </el-input-number>
        </el-form-item>		  
    </el-col>
    <el-col :span="11">
        <el-form-item label="To" >
           <el-input-number
            v-model="rd_to"
            :min="0"
            :max="999999999"    
            size="large"
            controls-position="right"
            
            class="w-100"
            >
            </el-input-number>
        </el-form-item>		  
    </el-col>
    </el-row>

    <el-row :gutter="20">
    <el-col :span="11">
        <el-form-item label="Price" >
           <el-input-number
            v-model="rd_price"
            :min="0"
            :max="999999999"    
            size="large"
            controls-position="right"
            
            class="w-100"
            >
            </el-input-number>
        </el-form-item>		  
    </el-col>
    <el-col :span="11">      
        <el-form-item label="Delivery estimation" >
        <el-input v-model="rd_estimation"  size="large" 
        :formatter="(value) => value.replace(/\D/g, '').replace(/(\d{2})(\d{2})/, '$1-$2')"
        :parser="(value) => value.replace(/[^\d-]/g, '')"
         >
         <template #append>Minutes</template>
        </el-input>  
        </el-form-item>		  
    </el-col>
    </el-row>

    <el-row :gutter="20">
    <el-col :span="11">
        <el-form-item label="Minimum Order" >
           <el-input-number
            v-model="rd_minimum_order"
            :min="0"
            :max="999999999"    
            size="large"
            controls-position="right"
            
            class="w-100"
            >
            </el-input-number>
        </el-form-item>		  
    </el-col>
    <el-col :span="11">
        <el-form-item label="Maximum Order" >
        <el-input-number
            v-model="rd_maximum_order"
            :min="0"
            :max="999999999"    
            size="large"
            controls-position="right"
            
            class="w-100"
            >
            </el-input-number>
        </el-form-item>		  
    </el-col>
    </el-row>

    <el-row :gutter="20">
    <el-col :span="11">
        <el-form-item label="Min. Order for Free Delivery" >
           <el-input-number
            v-model="rd_free_delivery_threshold"
            :min="0"
            :max="999999999"    
            size="large"
            controls-position="right"            
            class="w-100"
            >
            </el-input-number>
        </el-form-item>		  
    </el-col>
    <el-col :span="11">      
    </el-col>
    </el-row>
    
</el-form>

<template #footer>
      <div class="dialog-footer">
        <el-button 
        @click="onSubmit" 
        type="primary" 
        size="large"
        :loading="loading_submit"        
        >
            Submit
        </el-button>
      </div>
</template>

</el-dialog>
</script>