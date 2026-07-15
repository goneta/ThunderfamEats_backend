<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>

  
  <?php if(isset($link)):?>
  <?php if(!empty($link)):?> 
    <div class="d-flex flex-row justify-content-end align-items-center">
	  <div class="p-2">  
	  <a type="button" class="btn btn-black btn-circle" 
	  href="<?php echo $link?>">
	    <i class="zmdi zmdi-edit"></i>
	  </a>  
	  </div>
	  <div class="p-2"><h5 class="m-0"><?php echo t("Edit")?></h5></div>
      </div> <!--flex-->     
  <?php endif;?>
 <?php endif;?>	 

</nav>


<div id="app-holiday" class="card" v-cloak>        
</div>
<!-- app-holiday -->

<script type="text/x-template" id="xtemplate_holiday">
     <div class="row align-items-center">
        <div class="col">
            <h5><?php echo CommonUtility::safeTranslate("Plan Ahead: Set Special Hours for Holidays")?></h5>
            <p><?php echo CommonUtility::safeTranslate("Planning a holiday closure or special hours? Easily update your schedule for specific holidays or events. View and manage all holiday dates in one place.")?>
              <space><el-button type="text" @click="modal_events=true" ><?php echo CommonUtility::safeTranslate("View all events")?></el-button></space>
            </p>            

        </div>
        <div class="col-3">
           <el-button type="success" size="large" round plain @click="form_modal = true">
            <?php echo CommonUtility::safeTranslate("Edit availability")?>
           </el-button>
        </div>
    </div>   

    <template v-if="label">
        <el-dialog v-model="form_modal"
        :title="label.edit_availability"
        width="500"		
        id="footer-none-bg"
        :close-on-click-modal="false"
        :close-on-press-escape="false"        				
        >

        <el-form label-position="left" label-width="auto"  :model="formData" :rules="rules" ref="form" >
            <el-form-item :label="label.holiday_name" label-position="top" prop="holiday_name" >
                <el-input v-model="formData.holiday_name" size="large"  ></el-input>
            </el-form-item>
            <el-form-item  :label="label.date" label-position="top" prop="date" >                
                <el-date-picker
                    v-model="formData.holiday_date"
                    type="date"
                    :placeholder="label.select_date"
                    size="large"
                    class="w-100"
                     format="YYYY/MM/DD"
                     value-format="YYYY-MM-DD"
                >
                </el-date-picker>
            </el-form-item>

            <el-form-item :label="label.reason" label-position="top" prop="reason" >
                <el-input v-model="formData.reason" size="large"  ></el-input>
            </el-form-item>

        </el-form>

        <template #footer>
            <div class="dialog-footer" >		
                <el-button size="large" @click="form_modal=false" :disabled="loading" >
                {{label.cancel}}
                </el-button>
                <el-button type="primary" size="large" @click="submitForms" :loading="loading" >
                {{label.save}}
                </el-button>
            </div>
        </template>	
        </el-dialog>
    </template>

    <el-dialog v-model="modal_events"
        title="View Events"
        width="500"		
        id="footer-none-bg"
        :close-on-click-modal="false"
        :close-on-press-escape="false"        	
        @open="fetchHolidays"			
     >
     <p><?php echo CommonUtility::safeTranslate("Schedule for upcoming events, holidays and operational changes to your sites.")?></p>
     

     <div class="custom-dialog-content" v-loading="loading_list">
        <h5><?php echo CommonUtility::safeTranslate("Upcoming events")?></h5>

        <template v-if="!list">
            <p class="text-muted"><?php echo CommonUtility::safeTranslate("You don't have upcoming events")?></p>
        </template>
        <template v-else> 
        <template v-for="items in list" :keys="items">
            <el-descriptions>
                <el-descriptions-item border >            
                    <div class="d-flex align-items-center">
                        <div class="mr-2"><i class="zmdi zmdi-calendar-alt" style="font-size:18px;"></i></div>
                        <div>
                        <div>{{ items.holiday_name  }}</div>
                        <div class="text-muted font12">{{ items.holiday_date }}</div>
                        <div class="truncate-3-lines font11 line-normal" style="width:150px;">
                            {{ items.reason }}
                        </div>
                        </div>
                    </div>            
                </el-descriptions-item>        
                <el-descriptions-item >          
                    <el-button type="danger" round plain @click="deleteEvent(items.id)">
                        <?php echo CommonUtility::safeTranslate("Delete")?>
                    </el-button>
                </el-descriptions-item>        
            </el-descriptions>
            <hr style="margin:0;" />
        </template>
        </template>
     </div>

     <template #footer>
            <div class="dialog-footer" >		
                <el-button size="large" @click="modal_events=false" :disabled="loading" >
                {{label.close}}
                </el-button>                
            </div>
        </template>	

    </el-dialog>        
</script>


<p><?php echo t("These are the hours your store is available")?></p>
<table class="table">
    <?php foreach ($days as $day_code => $day_name):?>
    <?php if(isset($data[$day_code])):?>
        <?php $x=0?>
        <?php foreach ($data[$day_code] as $items):?>
        <tr>
            <td width="15%" class="text-capitalize">                
                <?php echo $x<=0?t($day_name):''?>
            </td>
            <td width="20%"><?php echo Date_Formatter::Time($items['start_time'])?> - <?php echo Date_Formatter::Time($items['end_time'])?></td>
            <td width="10%">
                <span class="badge p-1 store_hours_<?php echo $items['status']?>"><?php echo t($items['status'])?></span>
            </td>
            <td width="15%">
                <?php echo !empty($items['custom_text'])?$items['custom_text']:'&nbsp;'?>                
            </td>
        </tr>
        <?php $x++?>
        <?php endforeach;?>
    <?php else :?>
        <tr>
           <td width="15%" class="text-capitalize">                
                <?php echo t($day_name);?>
           </td>
           <td width="20%" class="text-muted"><?php echo t("No opening hours")?></td>
           <td width="10%"></td>
           <td width="15%"></td>
        </tr>
    <?php endif;?>    
    <?php endforeach;?>
</table>