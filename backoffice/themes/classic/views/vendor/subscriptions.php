<div id="merchant-subscriptions" v-cloak>

<el-card  shadow="never" v-loading="loading" >    
   <template v-if="hasData">
   <h5>Current Plan: {{data.plan_title}} 
    (<el-text class="mx-1" :type="statusColor(data.status)" >{{data.status_pretty}}</el-text>)    
   </h5>
   

   <div class="mt-3">      
      <div class="mb-3">
        <div>Item Limit</div>
        <el-progress :percentage="data.remaining_items_percentage" ></el-progress>
        <div>Credits Used: <b>{{  data.remaining_items_display }}</b></div>
      </div>      

      <div class="mb-3">
        <div>Order Limit</div>
        <el-progress :percentage="data.remaining_orders_percentage" ></el-progress>
        <div>Credits Used: <b>{{ data.remaining_orders_display }}</b></div>
      </div>      
   </div>

   
   <div v-if="features_list" class="mt-3 mb-2 row align-items-center">  
     <template v-for="(feature_name,feature_id) in features_list">
        <div class="col-3 mb-2">            
            <template v-if="subscription_features">
                <template v-if="subscription_features[feature_id]">
                    <i class="zmdi zmdi-check text-green mr-1 font16"></i> 
                </template>
                <template v-else>
                    <i class="zmdi zmdi-close debit mr-1 font16"></i> 
                </template>
            </template>        
            <template v-else>
                <i class="zmdi zmdi-close debit mr-1 font16"></i> 
            </template>    
            {{feature_name}}
        </div>     
     </template>
   </div>


   <el-button type="primary" plain size="large" @click="modal=true" >
          <b>Adjust Credits & Access</b>
    </el-button>    

          
   </template>
   <template v-else>
      <template v-if="!loading">
         <h5><?php echo t("No active subscriptions")?></h5>         
      </template>      
   </template>
</el-card>


<div class="m-3"></div>

<template v-if="hasData">
<el-card shadow="never" v-loading="loading" >           
  <el-descriptions title="Current Subscription"  border>
     <el-descriptions-item label="Plan">{{ data.plan_title }}</el-descriptions-item>
     <el-descriptions-item label=" Billing Price">{{ data.price }}</el-descriptions-item>
     <el-descriptions-item label=" Billing Cycle"> {{ data.package_period }} </el-descriptions-item>
     <el-descriptions-item label="Created At">{{ data.created_at }}</el-descriptions-item>
     <el-descriptions-item label="Expiration">{{ data.expiration }}</el-descriptions-item>
     <el-descriptions-item label="Next Due on">{{ data.next_due }}</el-descriptions-item>
  </el-descriptions>  
</el-card>
</template>

<div class="m-3"></div>


<template v-if="hasHistory">
<el-card shadow="never" v-loading="loading_history" >             
  <el-descriptions title="Subscription History">     
  </el-descriptions>


  <el-table :data="history_data" style="width: 100%">
    <el-table-column label="Plan Name">
      <template #default="scope">
          {{ scope.row.plan_title }}
      </template>
    </el-table-column>
    <el-table-column label="Amount">
      <template #default="scope">
         {{ scope.row.amount }}
      </template>
    </el-table-column>    
    <el-table-column label="Start Date">
      <template #default="scope">
         {{ scope.row.current_start }}
      </template>
    </el-table-column>    
    <el-table-column label="End Date">
      <template #default="scope">
         {{ scope.row.current_end }}
      </template>
    </el-table-column>    

    <el-table-column label="Payment">
      <template #default="scope">
         {{ scope.row.payment_code }}
      </template>
    </el-table-column>    

    <el-table-column label="Status">
      <template #default="scope">         
         <el-button
          size="small"
          :type="statusColor(scope.row.status_raw)"          
         >
           {{ scope.row.status }}
         </el-button>
      </template>
    </el-table-column>    
  </el-table>  
</el-card>
</template>


<div class="m-4"></div>

<el-dialog v-model="modal"  title="Adjust Credits" :show-close="true"  align-center @open="setLimitData"  >

    <div v-loading="submit">
    <el-form :model="form" label-width="auto" style="max-width: 600px">
        <h5>Item Limit</h5>
        <el-form-item label="Item Use">
        <el-input v-model="items_added" ></el-input>
        </el-form-item>
        <el-form-item label="Item Limit">
        <el-input v-model="item_limit" ></el-input>
        </el-form-item>

        <h5>Order Limit</h5>
        <el-form-item label="Order Count">
        <el-input v-model="orders_added" ></el-input>
        </el-form-item>
        <el-form-item label="Order Limit">
        <el-input v-model="order_limit" ></el-input>
        </el-form-item>

    </el-form>

    <h5>Features</h5>       
    <template v-for="(feature_name,feature_id) in features_list">            
        <div class="form-check form-check-inline mb-2">        
                <input v-model="plan_features" class="form-check-input" type="checkbox" :id="feature_id" :value="feature_id">
                <label class="form-check-label" :for="feature_id">
                    {{feature_name}}
                </label>               
        </div>
    </template> 
    </div>


    <template #footer>
      <div class="dialog-footer" style="padding-top: 0px;">
        <el-button size="large" @click="modal = false" :disabled="submit">Cancel</el-button>
        <el-button size="large" type="primary" @click="susbcriptionsUpdateLimits" :loading="submit">
          Confirm
        </el-button>
      </div>
    </template>
</el-dialog>

</div>
<!-- merchant-subscriptions -->

