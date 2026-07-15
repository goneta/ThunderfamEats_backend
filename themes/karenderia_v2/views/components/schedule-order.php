<div id="vue-schedule-order">

<?php if($show):?>         
<div class="container-fluid" v-cloak v-if="store_close || time_required" >
    <div class="container">
       <div class="d-flex justify-content-center">
              
       <div class="w-100 text-center mt-3 mb-3">                 
         <template v-if="time_required">
            <h4><?php echo t("Please Select Date/Time")?></h4>	      
            <p class="m-0"><?php echo t("Please choose a date and time, as it is essential for completing your request.")?></p>
	         <a href="javascript:;" @click="show" class="font-weight-bold"><?php echo t("Pick a time")?></a>
         </template>
         <template v-else>
            <template v-if="time_selection==3"></template>
            <template v-else>               
               <h4><?php echo t("Store is close")?></h4>	      
               <template v-if="store_open_data.holiday_id>0 && store_open_data.holiday_reason">
                   <p class="m-0">{{ store_open_data.holiday_reason }}</p>
               </template>
               <template v-else>
                   <p class="m-0"><?php echo t("This store is close right now, but you can schedulean order later.")?></p>
               </template>
               <a href="javascript:;" @click="show" class="font-weight-bold"><?php echo t("Schedule Order")?></a>
            </template>
         </template>	      	      
	   </div>
       
       </div>
    </div>
</div>
<?php endif;?>

<component-select-time
ref="select_time" 
:label="{
title:'<?php echo CJavaScript::quote(t("Pick a time"))?>', 
save: '<?php echo CJavaScript::quote(t("Save"))?>',	    	    
}"
@after-save="afterSaveTransOptions"
@after-close="afterCloseAddress"
>
</component-select-time>

</div>
<!--vue-schedule-order-->