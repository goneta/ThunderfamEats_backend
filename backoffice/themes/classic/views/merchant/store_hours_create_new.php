<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>array(
    t("Store Hours")=>array(Yii::app()->controller->id.'/store_hours'), 
    $this->pageTitle,           
),
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>

<div id="vue-opening-hours">
    <div v-loading="loading" style="min-height:300px;" >
        <components-opening-hours
        merchant_id="<?php echo $model->merchant_id?>"
        :days="days"
        :time_range="time_range"
        :data="data"
        :data_loading="loading"
        @after-update="afterUpdate"
        >
        </components-opening-hours>
    </div>
</div>

<script type="text/x-template" id="xtemplate_opening_hours">     
 

 <template v-if="hasError">
    <template v-for="items in errors">
        <div><el-text class="mx-1" type="danger">{{items}}</el-text></div>
    </template>
 </template>

 <!-- <pre>{{datas}}</pre>
 <pre>{{time_range}}</pre> -->
 <template v-for="(day,days_code) in days">
 <div class="row align-items-start pt-1 pb-1">
    <div class="col-2 " class="text-capitalize">
        <div class="pt-2">{{day}}</div>
    </div>
    <div class="col">
        <template v-for="(items,index) in datas[days_code]">                    
        <div class="d-flex flex-row align-items-center">
          <div class="pb-1">                    
                <el-select v-model="items.start_time"  placeholder="Select" style="width:120px;" >
                    <el-option
                    v-for="item in time_range"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                    />
                </el-select>                
          </div>
           <div class="p-2">&ndash;</div>
          <div class="p-2">               
               <el-select v-model="items.end_time" placeholder="Select" style="width:120px;" >
                    <el-option
                    v-for="item in time_range"
                    :key="item.value"
                    :label="item.label"
                    :value="item.value"
                    />
                </el-select>      
          </div>

          <div class="p-2">
           <template v-if="index>0">
                <el-button type="danger" @click="removeRow(days_code,index)">
                    <i class="zmdi zmdi-minus"></i>
                </el-button> 
            </template>         
            <template v-else>
                <el-button type="primary" @click="addRow(days_code,index)">
                    <i class="zmdi zmdi-plus"></i>
                </el-button> 
            </template>             
          </div>        

          <div class="p-2">
             <el-checkbox v-model="items.close" label="<?php echo t("Closed")?>" style="height:auto;" ></el-checkbox>
          </div>

          <div class="p-2">
            <el-input v-model="items.custom_text" placeholder="<?php echo t("Close message")?>" />
          </div>


        </div>
        <!-- flex -->
        </template>
    </div>
    <!-- col -->
   
 </div>
 <!-- row -->
</template>

<!-- <pre>{{datas}}</pre> -->

<div v-if="!data_loading" class="row align-items-start pt-1 pb-1">
    <div class="col-2"> </div>
    <div class="col">

    <el-form-item>
        <el-button color="#3ecf8e" @click="onSubmit" size="large" style="color:#fff;" :loading="loading" class="w-100" >
         <?php echo t("Update")?>
        </el-button>       
    </el-form-item>

    </div>
</div>
<!-- row -->

</script>