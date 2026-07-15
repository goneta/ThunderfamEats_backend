
<script type="text/x-template" id="xtemplate_order_details">

<q-card-section class="no-padding"> 
<q-tabs
v-model="tab_details"
dense
class="text-grey"
active-color="primary"
indicator-color="primary"
align="justify"
narrow-indicator                       
>
<q-tab name="order_details" label="Order details" no-caps ></q-tab>
<!-- <q-tab name="driver_info" label="Driver Information" no-caps ></q-tab> -->
<q-tab name="vehicle_info" label="Vehicle" no-caps ></q-tab>
<q-tab name="customer_info" label="Customer Information" no-caps ></q-tab>        
<q-tab name="order_timeline" label="Timeline" no-caps ></q-tab> 
<q-route-tab  no-caps icon="more_vert" >

    <q-popup-proxy ref="pop_menu">
        <q-banner class="q-pa-none">
        <q-list>
        <q-item clickable @click="showUpdateInfo" >
            <q-item-section>
                <q-item-label>Edit Delivery Information</q-item-label>
            </q-item-section>     
        </q-item>
        
        <q-item clickable v-if="hasDriver" @click="showAssign"  > 
            <q-item-section>
                <q-item-label>Regassign Driver</q-item-label>
            </q-item-section>     
        </q-item>

        <q-item clickable v-if="!hasDriver" @click="showAssign"  >
            <q-item-section>
                <q-item-label>Assign Driver</q-item-label>
            </q-item-section>     
        </q-item>

        <q-item clickable>
            <q-item-section @click="showChangeStatus">
                <q-item-label>Change Status</q-item-label>
            </q-item-section>     
        </q-item>
        </q-list>
        </q-banner>
    </q-popup-proxy>
    
</q-route-tab> 
</q-tabs>
<q-separator></q-separator>

<q-tab-panels v-model="tab_details" animatedx
            transition-prev="fade"
            transition-next="fade"
            >
    <q-tab-panel name="order_details">
        <template v-if="loading_order">                   
            <div class="text-center q-pa-md flex flex-center">
                <div>
                <q-spinner
                color="orange-5"
                size="3em"
                />
                </div>
            </div>
        </template>
        <template v-else>
        <div class="row justify-center items-center">
            <div class="col">
            <div class="row items-center">
                <q-avatar>
                <img :src="customer_info.avatar">
                </q-avatar>                        
                <div class="col q-ml-sm">
                <h6 v-if="data_order.order_info" class="no-margin line-normal font-14">
                    {{data_order.order_info.customer_name}}
                </h6>
                <p class="no-margin text-grey">Customer</p>
                </div>
            </div>
            </div>
            <div class="col-3 text-right">                
            <q-badge v-if="delivery_status_data[data_order.order_info.delivery_status]" 
            :style="'background:'+ this.delivery_status_data[data_order.order_info.delivery_status].bg_color+';'" 
             text-color="white" rounded  class="q-pa-sm">                   
                <span class="ellipsis max-120">{{delivery_status_data[data_order.order_info.delivery_status].label}}</span>
            </q-badge>
            </div>
        </div>
        
        <div class="row justify-center q-mt-md">
           <div class="col-md-2">
              <div class="text-grey text-caption">Order ID</div>
              <div>{{data_order.order_info.order_id}}</div>
              <div class="text-grey text-caption">Order OTP</div>
              <div>{{data_order.order_info.order_otp}}</div>
           </div>
           <div class="col-md-4">
              <div class="text-grey">Address</div>
              <div class="ellipsis-2-lines full-width q-pr-md font-13">{{data_order.order_info.delivery_address}}</div>
           </div>
           <div class="col-md-3">
              <div class="text-grey text-caption">Delivery fee</div>
              <div>{{data_order.order_info.pretty_delivery_fee}}</div>
           </div>
           <div class="col-md-3">
              <div class="text-grey text-caption">Total</div>
              <div>{{data_order.order_info.pretty_total}}</div>
           </div>
        </div>
        <!-- row -->
        </template>

       
        
        <div class="border-top q-pt-md q-pb-md q-mt-md ">

        <template v-if="hasDriver">

            <div class="row justify-center items-center">
                <div class="col">
                <div class="row items-center">
                    <q-avatar>
                    <img :src="driver_info.photo">
                    </q-avatar>                        
                    <div class="col q-ml-sm">
                    <h6 class="no-margin line-normal font-14">{{driver_info.full_name}}</h6>
                    <p class="no-margin text-grey text-caption">Driver</p>
                    </div>
                </div>
                </div>
                <div class="text-right">                    
                    <q-btn :href="`tel:`+driver_info.phone_prefix + driver_info.phone" unelevated rounded color="green" label="Call" class="q-mr-md" icon="phone" no-caps ></q-btn>
                    <q-btn outline rounded  color="green" label="Chat" icon="chat" no-caps ></q-btn>                         
                </div>
                </div>
                
                <q-list class="q-mt-sm">
                <q-item>
                    <q-item-section>
                    <q-item-label >
                    <div class="text-grey text-caption">License number</div>
                    <div>{{driver_info.license_number}}</div>
                    </q-item-label>                            
                    </q-item-section>
                    <q-item-section>
                    <q-item-label >
                    <div class="text-grey text-caption">License Photo</div>
                    <q-btn @click="showLicensePhoto" flat  label="Click here to view" no-caps class="text-grey q-pl-none"></q-btn>
                    </q-item-label>                            
                    </q-item-section>                          
                </q-item>                      
            </q-list>

        </template>
        <template v-else>
        <div class="text-center q-pa-md flex flex-center">
            <div>
            <div class="text-h6 text-grey-4 q-mb-sm">
                No assign driver yet
            </div>
            <q-btn @click="showAssign" color="primary" unelevated label="Assign Driver" no-caps />
            </div>
        </div>
        </template>
       </div>

    </q-tab-panel>


    <q-tab-panel name="driver_info">

        <template v-if="hasDriver">
        
            <div class="row justify-center items-center">
                <div class="col">
                <div class="row items-center">
                    <q-avatar>
                    <img :src="driver_info.photo">
                    </q-avatar>                        
                    <div class="col q-ml-sm">
                    <h6 class="no-margin line-normal font-14">{{driver_info.full_name}}</h6>
                    <p class="no-margin text-grey">Driver</p>
                    </div>
                </div>
                </div>
                <div class="text-right">
                    <q-btn unelevated rounded color="green" label="Call" class="q-mr-md" icon="phone" no-caps ></q-btn>
                    <q-btn outline rounded  color="green" label="Chat" icon="chat" no-caps ></q-btn>                         
                </div>
                </div>
                
                <q-list class="q-mt-sm">
                <q-item>
                    <q-item-section>
                    <q-item-label >
                    <div class="text-grey">License number</div>
                    <div>{{driver_info.license_number}}</div>
                    </q-item-label>                            
                    </q-item-section>
                    <q-item-section>
                    <q-item-label >
                    <div class="text-grey">License Photo</div>
                    <div>Click here to view</div>
                    </q-item-label>                            
                    </q-item-section>                          
                </q-item>                      
            </q-list>

        </template>
        <template v-else>
        <div class="text-center q-pa-md flex flex-center">
            <div>
            <div class="text-h6 text-grey-4 q-mb-sm">
                No assign driver yet
            </div>
            <q-btn @click="showAssign" color="primary" unelevated label="Assign Driver" no-caps />
            </div>
        </div>
        </template>
            
    </q-tab-panel>

    <q-tab-panel name="vehicle_info">
        <template v-if="hasVehicle">                                   

        <div class="row justify-center items-center">
        <div class="col">
            <div class="row items-center">
            <q-avatar rounded>
                <img :src="vehicle_info.photo_url">
            </q-avatar>                        
            <div class="col q-ml-sm">
                <h6 class="no-margin line-normal font-14">{{vehicle_info.plate_number}}</h6>
                <p class="no-margin text-grey">Vehicle</p>
            </div>
            </div>
        </div>
        <div class="text-right">
            
        </div>
        </div>
        
        <q-list class="q-mt-sm">
            <q-item>
            <q-item-section>
                <q-item-label >
                <div class="text-grey">Maker</div>
                <div>{{vehicle_info.maker}}</div>
                </q-item-label>                            
            </q-item-section>
            <q-item-section>
                <q-item-label >
                <div class="text-grey">Model</div>
                <div>{{vehicle_info.model}}</div>
                </q-item-label>                            
            </q-item-section>                          
            <q-item-section>
                <q-item-label >
                <div class="text-grey">Color</div>
                <div>{{vehicle_info.color}}</div>
                </q-item-label>                            
            </q-item-section>                          
            </q-item>                      
        </q-list>

        </template>
        <template v-else>
        <div class="text-center q-pa-md flex flex-center">
            <div>
            <div class="text-h6 text-grey-4 q-mb-sm">
                <template v-if="!hasDriver">
                    No assign driver yet
                </template>                
                <template v-else>
                    No Vehicle assign
                </template>                
            </div>
            <q-btn v-if="!hasDriver" @click="showAssign" color="primary" unelevated label="Assign Driver" no-caps />
            <q-btn v-else @click="showAssign" color="primary" unelevated label="Assign Vehicle" no-caps />
            </div>
        </div>
        </template>               
    </q-tab-panel>

    <q-tab-panel name="customer_info">
        
        <template v-if="hasCustomerInfo">
        <div class="row justify-center items-center">
        <div class="col">
            <div class="row items-center">
            <q-avatar>
                <img :src="customer_info.avatar">
            </q-avatar>                        
            <div class="col q-ml-sm">
                <h6 class="no-margin line-normal font-14">{{customer_info.first_name}} {{customer_info.last_name}}</h6>
                <p class="no-margin text-grey">Customer</p>
            </div>
            </div>
        </div>
        <div class="text-right">
            <q-btn unelevated rounded color="green" label="Call" class="q-mr-md" icon="phone" no-caps ></q-btn>
            <q-btn outline rounded  color="green" label="Chat" icon="chat" no-caps ></q-btn>                         
        </div>
        </div>
    
        <q-list class="q-mt-sm">
            <q-item>
            <q-item-section>
                <q-item-label >
                <div class="text-grey">Email Address</div>
                <div>{{customer_info.email_address}}</div>
                </q-item-label>                            
            </q-item-section>
            <q-item-section>
                <q-item-label >
                <div class="text-grey">Member since</div>
                <div>{{customer_info.member_since}}</div>
                </q-item-label>                            
            </q-item-section>
            <q-item-section>
                <q-item-label >
                <div class="text-grey">Total Orders</div>
                <div>{{customer_info.order_count}}</div>
                </q-item-label>                            
            </q-item-section>                          
            </q-item>                      
        </q-list>
        </template>

    </q-tab-panel>

    <q-tab-panel name="order_timeline">
      <div class="q-pt-md q-pb-md">    

      <!-- <pre>{{order_proof}}</pre>
      <pre>{{delivery_status}}</pre>
      <pre>{{order_history}}</pre>
      <pre>{{order_history_status}}</pre> -->
      
      <q-timeline v-if="hasTimeline" color="primary" layout="comfortable">        
            <q-timeline-entry v-for="items in order_history">
            <template v-slot:title>
                
            </template>
            <template v-slot:subtitle>
                {{items.created_at}}
            </template>

            <div>
                <q-card class="q-card-light">
                <q-card-section>
                    <div class="row inline q-gutter-md">                          
                        
                        <template v-if="order_history_status[items.status]">
                          <q-badge class="column" outline color="orange" :label="order_history_status[items.status]" />
                        </template>
                        <template v-else>
                        <q-badge class="column" outline color="orange" :label="items.status" />
                        </template>
                        <div class="column">
                            <span class="ellipsis fit font-12">{{items.remarks}}</span>
                        </div>                                                
                    </div>          
                    
                    <div v-if="items.latitude!=''" class="q-mt-sm" >                        
                      <q-btn @click="locationOnMap(items)"  flat  label="Location on map" no-caps class="text-grey q-pl-none"></q-btn>
                    </div>

                    <div v-if="delivery_status==items.status && hasProof">
                    <template v-for="proof in order_proof">                        
                        <div>
                            <q-img
                            :src="proof.photo"
                            spinner-color="primary"
                            spinner-size="sm"
                            style="height: 60px; max-width: 80px"
                            >
                            </q-img>
                        </div>                        
                    </template>
                    </div>

                </q-card-section>
                </q-card>         
            </div>
            </q-timeline-entry>
        </q-timeline>       
        
        <div v-else class="text-center q-pa-md flex flex-center">
            <div>
                <div class="text-h6 text-grey-4 q-mb-sm">
                    No Data Available
                </div>                
            </div>
        </div>

      </div>
    </q-tab-panel>

    </q-tab-panels>                        
</q-card-section>    


<q-dialog v-model="modal_update_info" class="q-mb-md" persistent >             
    <q-card style="width: 500px; max-width: 80vw;"  class="b_radius15" >      
    <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Update Delivery Information</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
    </q-card-section>

    <q-form
        @submit="UpdateDeliveryInformation"                     
        >
    <q-card-section class="q-gutter-md">

       <q-input v-model="customer_name" label="Customer name" filled 
       :rules="[ val => val && val.length > 0 || 'This is required']"
        ></q-input>

       <q-input v-model="contact_number" label="Contact number" filled  
       :rules="[ val => val && val.length > 0 || 'This is required']"
       ></q-input>

       <q-input v-model="delivery_address" label="Address" filled
       :rules="[ val => val && val.length > 0 || 'This is required']"
        ></q-input>

       <q-input v-model="latitude" label="Latitude" filled
       :rules="[ val => val && val.length > 0 || 'This is required']"
        ></q-input>

       <q-input v-model="longitude" label="Longitude" filled 
       :rules="[ val => val && val.length > 0 || 'This is required']"
       ></q-input>

    </q-card-section>   

    <q-separator />

    <q-card-actions align="right">
        <q-btn color="dark" unelevated no-caps v-close-popup rounded >
            <span class="q-pl-md q-pr-md">Cancel</span>
        </q-btn>
        <q-btn type="submit" color="green-6" unelevated no-caps rounded >
            <span class="q-pl-md q-pr-md">Submit</span>
        </q-btn>
    </q-card-actions>
    </q-form>
    
    </q-card>                  
</q-dialog>        

<components-locationonmap
ref="location_on_map"
:markers="location_data"
zoom="15"
:center='<?php echo json_encode([
    'lat'=>$maps_config['default_lat'],
    'lng'=>$maps_config['default_lng'],
])?>'
:maps_config='<?php echo json_encode($maps_config)?>'
>
</components-locationonmap>

<components-carousel
ref="carousel"
:data="driver_info.license_photo"
>
</components-carousel>
    
</script>


<!-- ASSIGN DRIVER MODAL -->
<script type="text/x-template" id="xtemplate_assign">
    <q-card-section class="row items-center q-pb-none">
            <div class="text-h6">Assign</div>
            <q-space />
            <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        
        <q-form
        @submit="AssignDriver"
        @reset="onReset"                
        >
        <q-card-section class="q-gutter-md">
        

        <q-select
        filled
        v-model="zone_id"
        use-input
        input-debounce="0"
        label="Filter by zone"
        :options="zone_list"
        @filter="filterZone"
        @update:model-value="afterSelectZone"
        emit-value            
        map-options                
        >
        <template v-slot:no-option>
        <q-item>
            <q-item-section class="text-grey">
            No results
            </q-item-section>
        </q-item>
        </template>
        </q-select>
                        
        <q-select
        filled
        v-model="group_id"
        use-input
        input-debounce="0"
        label="Select Groups"
        :options="group_list"
        @filter="filterGroup"    
        @update:model-value="afterSelectGroup"
        emit-value            
        map-options                
        >
        <template v-slot:no-option>
        <q-item>
            <q-item-section class="text-grey">
            No results
            </q-item-section>
        </q-item>
        </template>
        </q-select>
        
        <q-select
        filled
        v-model="driver_id"
        use-input
        input-debounce="0"
        label="Select Driver"
        :options="driver_list"
        @filter="filterDriver"    
        emit-value            
        map-options
        :rules="[value => !!value || 'Selection is required']"
        >
        <template v-slot:no-option>
        <q-item>
            <q-item-section class="text-grey">
            No results
            </q-item-section>
        </q-item>
        </template>
        </q-select>                            

    </q-card-section>

    <q-separator />

    <q-card-actions align="right">
        <q-btn color="dark" unelevated no-caps v-close-popup rounded >
            <span class="q-pl-md q-pr-md">Cancel</span>
        </q-btn>        
        <q-btn type="submit" color="green-6" unelevated no-caps rounded :loading="loading_assign" >
            <span class="q-pl-md q-pr-md">Submit</span>
        </q-btn>
    </q-card-actions>
    </q-form>
</script>


<script type="text/x-template" id="xtemplate_change_status">
    <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">Change Status</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
    </q-card-section>

    <q-form
        @submit="changeStatus"                     
    >
    
    <q-card-section class="q-gutter-md">

        <q-select
        filled
        v-model="delivery_status"
        use-input
        input-debounce="0"
        label="Select Status"
        :options="delivery_status_list"        
        emit-value            
        map-options                
        :rules="[ val => val && val.length > 0 || 'This is required']"
        >
        <template v-slot:option="scope">

          <template v-if="order_delivery_status==scope.opt.value">
            <q-item>   
                <q-item-section disabled>
                <q-item-label>{{ scope.opt.label }}</q-item-label>
                <q-item-label caption>{{ scope.opt.description }}</q-item-label>
                </q-item-section>
            </q-item>
          </template>
          <template v-else >
            <q-item v-bind="scope.itemProps">            
                <q-item-section>
                <q-item-label>{{ scope.opt.label }}</q-item-label>
                <q-item-label caption>{{ scope.opt.description }}</q-item-label>
                </q-item-section>
            </q-item>
          </template>

        </template>
        <template v-slot:no-option>
        <q-item>
            <q-item-section class="text-grey">
            No results
            </q-item-section>
        </q-item>
        </template>
        </q-select>

    </q-card-section>   

    <q-separator />

    <q-card-actions align="right">
        <q-btn color="dark" unelevated no-caps v-close-popup rounded >
            <span class="q-pl-md q-pr-md">Cancel</span>
        </q-btn>
        <q-btn type="submit" color="green-6" unelevated no-caps rounded >
            <span class="q-pl-md q-pr-md">Submit</span>
        </q-btn>
    </q-card-actions>
    </q-form>

</script>



<script type="text/x-template" id="xtemplate_driver_information">
<q-card-section class="no-padding" > 

<q-tabs
v-model="tabs"
dense
class="text-grey"
active-color="primary"
indicator-color="primary"
align="justify"
narrow-indicator                 
>
<q-tab name="details" label="Details" no-caps ></q-tab>
<q-tab name="orders" label="Orders" no-caps ></q-tab>
<q-tab name="activities" label="Activities" no-caps ></q-tab>
</q-tabs>
<q-separator></q-separator>

<q-tab-panels v-model="tabs" animatedx
            transition-prev="fade"
            transition-next="fade"
            >
            
<q-tab-panel name="details">

<template v-if="loading">                   
    <div class="text-center q-pa-md flex flex-center">
        <div>
        <q-spinner
        color="orange-5"
        size="3em"
        />
        </div>
    </div>
</template>
<template v-else>
<div class="row justify-center items-center">
    <div class="col">
    <div class="row items-center">
        <q-avatar>
        <img :src="driver_info.photo">
        </q-avatar>                        
        <div class="col q-ml-sm">
        <h6 class="no-margin line-normal font-14">{{driver_info.full_name}}</h6>
        <p class="no-margin text-grey">Driver</p>
        </div>
    </div>
    </div>
    <div class="text-right">        
        <q-btn :href="`tel:`+driver_info.phone_prefix + driver_info.phone"  unelevated rounded color="green" label="Call" class="q-mr-md" icon="phone" no-caps ></q-btn>
        <q-btn outline rounded  color="green" label="Chat" icon="chat" no-caps ></q-btn>                         
    </div>
    </div>
    
    <q-list class="q-mt-sm">
    <q-item>
        <q-item-section>
        <q-item-label >
        <div class="text-grey">License number</div>
        <div>{{driver_info.license_number}}</div>
        </q-item-label>                            
        </q-item-section>
        <q-item-section>
        <q-item-label >
        <div class="text-grey">License Photo</div>
        <q-btn @click="showCarousel(driver_info.license_photo)" flat  label="Click here to view" no-caps class="text-grey q-pl-none"></q-btn>
        </q-item-label>                            
        </q-item-section>                          
    </q-item>                      
</q-list>
</template>

</q-tab-panel>            

<q-tab-panel name="orders">   
 
    <template v-if="loading_orders">                   
        <div class="text-center q-pa-md flex flex-center">
            <div>
            <q-spinner
            color="orange-5"
            size="3em"
            />
            </div>
        </div>
    </template>
    
   <q-table      
      v-else
      :rows="orders_data"
      :columns="columns"
      row-key="name"
      flat
      :hide-pagination="true"      
    >
    <template v-slot:body="props">
       <q-tr :props="props">
          <q-td key="order_id" :props="props">
            {{ props.row.order_id }}
          </q-td>
          <q-td key="full_name" :props="props">
            <div class="row items-center"> 
                <q-avatar class="col q-ma-none">
                <img :src="props.row.avatar">
                </q-avatar>
                <div class="col">{{ props.row.full_name }}</div>
            </div>
          </q-td>
          <q-td key="address" :props="props">
            <div class="ellipsis" style="max-width:150px;">{{ props.row.address }}</div>
          </q-td>
          <q-td key="delivery_status" :props="props">
            <q-badge  text-color="white" rounded class="q-pa-sm" :class="'custom_'+props.row.delivery_status_raw"
            :style="'background:'+ this.order_status[ props.row.delivery_status].bg_color+';'"
            > 
             <span class="ellipsis max-120">{{ props.row.delivery_status }}</span>
            </q-badge>   
          </q-td>
       </q-tr>
    </template>   
    </q-table>
</q-tab-panel>            

<q-tab-panel name="activities">
<template v-if="!loading_activities">                   
    <div class="text-center q-pa-md flex flex-center">
        <div>
        <q-spinner
        color="orange-5"
        size="3em"
        />
        </div>
    </div>
</template>

<q-card  flat>
  <q-card-section>

  <template v-if="loadin_activity">                   
    <div class="text-center q-pa-md flex flex-center">
        <div>
        <q-spinner
        color="orange-5"
        size="3em"
        />
        </div>
    </div>
</template>
    
<template v-else>
 
<q-timeline v-if="hasActivity" color="primary" layout="comfortable">        
    <q-timeline-entry v-for="items in data_activity">
    <template v-slot:title>
        
    </template>
    <template v-slot:subtitle>
        {{items.created_at}}
    </template>

    <div>
         <q-card class="q-card-light">
         <q-card-section>
            <div class="row inline q-gutter-md">                          
              <q-badge class="column" outline color="orange" :label="items.status" />
              <div class="column">{{items.remarks}}</div>
            </div>           

            <div class="q-mt-sm row items-center justify-between">
                <div class="col">
                   <q-btn @click="locationOnMap(items)"  flat  label="Location on map" no-caps class="text-grey q-pl-none"></q-btn>            
                </div>
                <div v-if="items.order_id>0" class="col text-right text-weight-bold">#{{items.order_id}}</div>
            </div>

            <template v-if="items.reference_id>0">                
                <template v-if="meta_activity[items.reference_id]">
                    <div @click="showActivityPhoto(meta_activity[items.reference_id])" class="row inline q-gutter-sm cursor-pointer">
                        <div v-for="itema in meta_activity[items.reference_id]" class="column">                            
                           <q-img
                            :src="itema.document"
                            spinner-color="primary"
                            spinner-size="sm"
                            style="height: 50px; width: 50px"
                            >
                            </q-img>
                        </div>
                    </div>                    
                </template>
            </template>

         </q-card-section>
         </q-card>         
    </div>
    </q-timeline-entry>
</q-timeline>

<div v-else class="text-center q-pa-md flex flex-center">
    <div>
    <div class="text-h6 text-grey-4 q-mb-sm">
        No Data Available
    </div>                
    </div>
</div>


</template>

</q-card-section>
</q-card>

</q-tab-panel>            

</q-tab-panels>   

</q-card-section>

<components-locationonmap
ref="location_on_map"
:markers="location_data"
zoom="15"
:center='<?php echo json_encode([
    'lat'=>$maps_config['default_lat'],
    'lng'=>$maps_config['default_lng'],
])?>'
:maps_config='<?php echo json_encode($maps_config)?>'
>
</components-locationonmap>

<components-carousel
ref="carousel"
:data="carousel_data"
>
</components-carousel>

<components-carousel-activity
ref="carousel_activity"
:data="activity_photo"
>
</components-carousel-activity>

</script>