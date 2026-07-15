<div id="app-task" v-cloak>


    <q-layout view="hHh Lpr lff" >
      <q-header class="bg-white text-dark" style="border-bottom:1px solid #dee2e6;">
        <q-toolbar>
           <q-btn href="<?php echo Yii::app()->createUrl("/driver/schedule")?>"  flat round dense icon="menu" class="q-mr-sm"></q-btn>
           <q-btn  @click="drawerLeft = !drawerLeft"  flat round dense icon="chevron_left" class="q-mr-sm"></q-btn>
           <q-toolbar-title>
              <?php echo t("Map view")?>
           </q-toolbar-title>              

          <!-- <component-grouplist 
          ref="grouplist"
          ajax_url="<?php echo $ajax_url;?>" 
          :label="{
            all_groups : '<?php echo CJavaScript::quote(t("All Groups"));?>',                   
          }"  
          >
          </component-grouplist> -->

          <q-btn-group outline class="q-mr-md">
             <q-btn href="<?php echo Yii::app()->createUrl("/driver/mapview")?>" color="dark" label="<?php echo t("Map")?>" icon="map" ></q-btn>
             <q-btn href="<?php echo Yii::app()->createUrl("/driver/orders")?>" outline color="dark" label="<?php echo t("List")?>" icon="format_list_bulleted" ></q-btn>
          </q-btn-group>

          <q-spacer></q-spacer>        

          <!-- 
            <q-btn round icon="notifications" size="sm" class="q-mr-sm" >
            <q-popup-proxy>
              <q-banner>
              <q-list style="max-width: 350px">
              
              <template v-for="items in 5">
              <q-item clickable v-ripple>
                <q-item-section avatar>
                  <q-avatar>
                    <img src="https://cdn.quasar.dev/img/avatar2.jpg">
                  </q-avatar>
                </q-item-section>

                <q-item-section>
                  <q-item-label lines="1">Brunch this weekend?</q-item-label>
                  <q-item-label caption lines="2">
                    <span class="text-weight-bold">Janet</span>
                    -- I'll be in your neighborhood doing errands this
                    weekend. Do you want to grab brunch?
                  </q-item-label>
                </q-item-section>

                <q-item-section side top>
                  1 min ago
                </q-item-section>
              </q-item>
              <q-separator inset="item" ></q-separator>
              </template>

              </q-list>
              </q-banner>
            </q-popup-proxy>
            <q-badge floating color="red" rounded />
          </q-btn> 
          -->
          <components-notifications
          ref="notifications"
          ajax_url="<?php echo $ajax_url;?>"
          view_url="<?php echo Yii::app()->createUrl("/notifications/all_notification")?>" 
          :realtime="{
            enabled : '<?php echo Yii::app()->params['realtime_settings']['enabled']==1?true:false ;?>',  
            provider : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['provider'] )?>',  			   
            key : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['key'] )?>',  			   
            cluster : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['cluster'] )?>', 
            ably_apikey : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['ably_apikey'] )?>', 
            piesocket_api_key : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['piesocket_api_key'] )?>', 
            piesocket_websocket_api : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['piesocket_websocket_api'] )?>', 
            piesocket_clusterid : '<?php echo CJavaScript::quote( Yii::app()->params['realtime_settings']['piesocket_clusterid'] )?>', 
            channel : '<?php echo CJavaScript::quote( Yii::app()->params->realtime['admin_channel'] )?>',  			   
            event : '<?php echo CJavaScript::quote( Yii::app()->params->realtime['notification_event'] )?>',  
          }"  			
          @after-receivenotifications="afterReceivenotifications"
          >
          </components-notifications>

          <q-spacer spaced  ></q-spacer>

          <q-btn  @click="drawerRight = !drawerRight"  flat round dense icon="chevron_right" class="q-mr-sm"></q-btn>

        </q-toolbar>
      </q-header>

      <q-drawer
        v-model="drawerLeft"
        show-if-above
        :width="380"
        :breakpoint="700"        
        class="bg-grey-1 text-dark"    
        style="border-right:1px solid #dee2e6;"          
      >
        <q-scroll-area class="fit">     

            <div class="bg-brandgreen text-white q-pr-sm">    
            <q-form
              @submit="applyOrderFilter"              
            >                       
            <q-toolbar>                                  
                <q-input borderless color="white"                
                dense
                label-color="white" v-model="order_q" label="<?php echo t("Search Orders")?>" class="full-width text-color">                  
                  <template v-slot:append>                           
                    <template v-if="has_order_filter">
                       <q-btn round @click="resetOrderFilter" :loading="order_apply_filter" color="white" icon="restart_alt" size="md" unelevated flat></q-btn>
                    </template>                                        
                    <template v-else>
                        <q-btn round @click="applyOrderFilter" :loading="order_apply_filter" color="white" icon="search" size="md" unelevated flat ></q-btn> 
                    </template>                                        
                    
                  </template>
                </q-input>                 
              </q-toolbar>           
              </q-form>
            </div>        

            <components-orders-total
            ref="orders_total"
            task_url="<?php echo $task_url;?>" 
            :order_q="order_q"
            @after-total="afterTotal"              
            >
            </components-orders-total>
            
            <q-tabs
              v-model="tab"
              dense
              class="text-grey"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              narrow-indicator
              @update:model-value="orderChangeTab"
            >
              <q-tab name="unassigned" :label="total_data.uunassigned_group+ ' ' +'unassigned'" ></q-tab>
              <q-tab name="assigned" :label="total_data.assigned_group+ ' ' +'assigned'" ></q-tab>
              <q-tab name="completed" :label="total_data.completed_group+ ' ' +'completed'" ></q-tab>
            </q-tabs>  
            <q-separator></q-separator>

          <q-tab-panels v-model="tab" animated>
            <q-tab-panel name="unassigned" class="q-pa-none">              
               <components-orders
               ref="tab_unassigned"               
               :status='<?php echo json_encode($uunassigned_group)?>'
               :order_q="order_q"
               tab="unassigned"
               ajax_url="<?php echo $ajax_url;?>"
               task_url="<?php echo $task_url;?>" 
               @view-details="viewDetails"
               @show-assign="showAssign"     
               @after-getorders="afterGetorders"      
               @location-focus="locationFocus"    
               >
               </components-orders>
            </q-tab-panel>

            <q-tab-panel name="assigned" class="q-pa-none" >          
               <components-orders
               ref="tab_assigned"               
               :status='<?php echo json_encode($assigned_group)?>'
               :order_q="order_q"
               tab="assigned"
               ajax_url="<?php echo $ajax_url;?>"
               task_url="<?php echo $task_url;?>" 
               @view-details="viewDetails"
               @view-assign="viewAssign"   
               @after-getorders="afterGetorders"       
               @location-focus="locationFocus"                
               >
               </components-orders>
            </q-tab-panel>

            <q-tab-panel name="completed" class="q-pa-none">
               <components-orders
               ref="tab_completed"               
               :status='<?php echo json_encode($completed_group)?>'
               :order_q="order_q"
               tab="completed"
               ajax_url="<?php echo $ajax_url;?>"
               task_url="<?php echo $task_url;?>" 
               @view-details="viewDetails"
               @view-assign="viewAssign"  
               @after-getorders="afterGetorders"
               @location-focus="locationFocus"                       
               >
               </components-orders>              
            </q-tab-panel>
          </q-tab-panels> 

        </q-scroll-area>
      </q-drawer>

      <q-drawer
        side="right"
        v-model="drawerRight"
        show-if-above
        bordered
        :width="380"
        :breakpoint="1000"
        class="bg-grey-1"
      >
        <q-scroll-area class="fit">
           <div class="bg-brandgreen text-white q-pr-sm">          
           
             <q-toolbar>                               
                <q-input borderless color="white"
                dense
                label-color="white" v-model="driver_q" label="<?php echo t("Search Drivers")?>" class="full-width">                  
                  <template v-slot:append>                    
                    <template v-if="has_driver_filter">
                       <q-btn  round @click="resetDriverFilter" color="white" icon="restart_alt" size="md" unelevated flat></q-btn>
                    </template>
                    <template v-else>
                      <q-btn @click="applyDriverFilter" round color="white" icon="search" size="md" unelevated flat />
                    </template>
                  </template>
                </q-input>                 
              </q-toolbar>           
           
           </div>        

           <components-drivers-total
            ref="drivers_total"
            :driver_q="driver_q"
            task_url="<?php echo $task_url;?>"             
            @after-totaldriver="afterTotaldriver"              
            >
            </components-drivers-total>

           <q-tabs
              v-model="tab_agent"
              dense
              class="text-grey"
              active-color="primary"
              indicator-color="primary"
              align="justify"
              narrow-indicator              
            >
              <q-tab name="duty" :label="driver_onduty+` Duty`" ></q-tab>
              <q-tab name="busy" :label="driver_busy+` Busy`" ></q-tab>              
            </q-tabs>  
            <q-separator></q-separator>
            <q-tab-panels v-model="tab_agent" animated  >
              <q-tab-panel name="duty" class="q-pa-none"> 
                 <components-driver 
                 ref="driver"
                 status='duty'
                 ajax_url="<?php echo $ajax_url;?>"       
                 task_url="<?php echo $task_url;?>" 
                 :driver_q="driver_q"
                 @set-onduty="setOnduty"        
                 @show-driverinfo="showDriverinfo"  
                 @refresh-list="refreshList"
                 @set-Datamarkerdriver="setDatamarkerdriver"
                 @set-Locationdriver="setLocationdriver"
                 date_now="<?php echo date("c")?>"
                 timezone="<?php echo Yii::app()->timeZone;?>"
                 @location-focus="locationFocus"    
                 >
                 </components-driver>              
              </q-tab-panel>
              <q-tab-panel name="busy" class="q-pa-none" >                 
                <components-driver 
                 ref="driver_busy"
                 status='busy'
                 ajax_url="<?php echo $ajax_url;?>"       
                 task_url="<?php echo $task_url;?>" 
                 @set-onduty="setOnduty"        
                 @show-driverinfo="showDriverinfo"  
                 @refresh-list="refreshList"
                 @set-Datamarkerdriver="setDatamarkerdriver"
                 @set-Locationdriver="setLocationdriver"
                 date_now="<?php echo date("c")?>"
                 timezone="<?php echo Yii::app()->timeZone;?>"
                 @location-focus="locationFocus"    
                 >
                </components-driver>               
              </q-tab-panel>        
            </q-tab-panels> 

        </q-scroll-area>
      </q-drawer>

      <q-page-container>
        <q-page class="bg-grey-4">          
       
         <!-- <components-map 
         ref="map_view"
         :maps_config='<?php echo json_encode($maps_config)?>'
         task_url="<?php echo $task_url;?>" 
         :status='<?php echo json_encode($status_merge)?>'
         >
         </components-map> -->

         <components-allorder
         ref="all_order"
         task_url="<?php echo $task_url;?>" 
         :status='<?php echo json_encode($status_merge)?>'
         @set-datamarker="setDatamarker"
         >
         </components-allorder>              
         
         <components-alldriver
         ref="all_driver"
         task_url="<?php echo $task_url;?>"      
         date_now="<?php echo date("c")?>"    
         >
         </components-alldriver>
                        
         <components-map
         ref="map_view"
         :maps_config='<?php echo json_encode($maps_config)?>'
         :marker="data_marker"
         :driver_markers="driver_markers"
         :driver_locations="driver_locations"
         :marker_focus="marker_focus"
         :drawer_left="drawerLeft"
         :drawer_right="drawerRight"
         :enabled_cluster="<?php echo isset(Yii::app()->params['settings']['driver_map_enabled_cluster'])?Yii::app()->params['settings']['driver_map_enabled_cluster']:0;?>"         
         >
         </components-map>
        
        </q-page>
      </q-page-container>
    </q-layout>

    <q-dialog v-model="show_details" position="bottom" seamless class="q-mb-md" @show="orderModalShow" >       
       <q-card style="width: 700px; max-width: 80vw;" class="b_radius15" >      
       <div class="q-pa-sm text-right">
         <q-btn @click="show_details = !show_details"  round  icon="close" dense unelevated size="sm" />
       </div>
       <components-details
       ref="details"               
        ajax_url="<?php echo $ajax_url;?>" 
        task_url="<?php echo $task_url;?>" 
        apibackend="<?php echo $apibackend;?>" 
        :order_uuid="order_uuid"
        @show-assign="showAssign"
        @show-changestatus="showChangestatus"
        >
         </components-details>   
       </q-card>
    </q-dialog>


    <q-dialog v-model="modal_assign" class="q-mb-md"  persistent>             
      <q-card style="width: 600px; max-width: 80vw;" class="b_radius15">            
      <components-assign
      ref="assign"      
      task_url="<?php echo $task_url;?>"     
      :order_uuid="order_uuid"
      merchant_id="0"
      @after-assign="afterAssign"
      >
      </components-assign>
      </q-card>                  
    </q-dialog>

    <q-dialog v-model="modal_change_status" class="q-mb-md"  persistent>             
    <q-card style="width: 500px; max-width: 80vw;" class="b_radius15"  >           
      <components-change-status
      ref="change_status"      
      task_url="<?php echo $task_url;?>"     
      :order_uuid="order_uuid"
      @after-changestatus="afterChangestatus"
      >
      </components-change-status>
      </q-card>                  
    </q-dialog>


    <q-dialog v-model="show_driverinfo" position="bottom" seamless class="q-mb-md" @show="onShowDriverInfo" >       
       <q-card style="width: 700px; max-width: 80vw;" class="b_radius15" >      
       <div class="q-pa-sm text-right">
         <q-btn @click="show_driverinfo = !show_driverinfo"  round  icon="close" dense unelevated size="sm" />
       </div>
       <components-driver-information
       ref="driver_information"               
        ajax_url="<?php echo $ajax_url;?>" 
        task_url="<?php echo $task_url;?>" 
        apibackend="<?php echo $apibackend;?>" 
        :driver_id="driver_id"        
        date_now="<?php echo date("Y-m-d");?>" 
        >
        </components-driver-information>   
       </q-card>
    </q-dialog>


</div>
<!-- app-task -->


<?php $this->renderPartial("/driver/tpl_order_details",[
  'maps_config'=>$maps_config
]);?>