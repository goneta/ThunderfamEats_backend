<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'form',
		'enableAjaxValidation' => false,		
	)
);
?>

<div id="vue-tax-new" class="card" v-cloak >
  <div class="card-body">
 
<?php if(Yii::app()->user->hasFlash('success')): ?>
	<div class="alert alert-success">
		<?php echo Yii::app()->user->getFlash('success'); ?>
	</div>
<?php endif;?>

<?php if(Yii::app()->user->hasFlash('error')): ?>
	<div class="alert alert-danger">
		<?php echo Yii::app()->user->getFlash('error'); ?>
	</div>
<?php endif;?>


<div class="custom-control custom-switch custom-switch-md mr-4">  
  <?php echo $form->checkBox($model,"tax_enabled",array(
     'class'=>"custom-control-input",     
     'value'=>1,
     'id'=>"tax_enabled",     
     'checked'=>$model->tax_enabled==1?true:false,     
   )); ?>   
  <label class="custom-control-label" for="tax_enabled">
   <?php echo t("Tax enabled")?>
  </label>
</div>    

<hr/>

<div class="custom-control custom-switch custom-switch-md mr-4">  
      <?php echo $form->checkBox($model,"tax_service_fee",array(
        'class'=>"custom-control-input",     
        'value'=>1,
        'id'=>"tax_service_fee",     
        'checked'=>$model->tax_service_fee==1?true:false,     
      )); ?>   
      <label class="custom-control-label" for="tax_service_fee">
      <?php echo t("Tax on service fee")?>
      </label>
    </div>    

    <div class="custom-control custom-switch custom-switch-md mr-4">  
      <?php echo $form->checkBox($model,"tax_small_order_fee",array(
        'class'=>"custom-control-input",     
        'value'=>1,
        'id'=>"tax_small_order_fee",     
        'checked'=>$model->tax_small_order_fee==1?true:false,     
      )); ?>   
      <label class="custom-control-label" for="tax_small_order_fee">
      <?php echo t("Tax on small order fee")?>
      </label>
    </div>    

    <div class="custom-control custom-switch custom-switch-md mr-4">  
      <?php echo $form->checkBox($model,"tax_on_delivery_fee",array(
        'class'=>"custom-control-input",     
        'value'=>1,
        'id'=>"tax_on_delivery_fee",     
        'checked'=>$model->tax_on_delivery_fee==1?true:false,     
      )); ?>   
      <label class="custom-control-label" for="tax_on_delivery_fee">
      <?php echo t("Tax on delivery fee")?>
      </label>
    </div>    

    <div class="custom-control custom-switch custom-switch-md mr-4">  
      <?php echo $form->checkBox($model,"tax_packaging",array(
        'class'=>"custom-control-input",     
        'value'=>1,
        'id'=>"tax_packaging",     
        'checked'=>$model->tax_packaging==1?true:false,     
      )); ?>   
      <label class="custom-control-label" for="tax_packaging">
      <?php echo t("Tax on packaging fee")?>
      </label>
    </div>    
    
    <hr/>    

<h6 class="mb-3 mt-3"><?php echo t("Tax Calculation Method")?></h6>


<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'tax_type', array(
    'value'=>'standard',
    'uncheckValue'=>null,
    'id'=>'standard',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="standard">
    <h6 style="margin-top: 4px;" class="font-weight-normal"><?php echo t("Standard Tax")?></h6>
  </label>  
</div>

<div class="custom-control custom-radio mb-2">  
  <?php 
  echo $form->radioButton($model, 'tax_type', array(
    'value'=>'multiple',
    'uncheckValue'=>null,
    'id'=>'multiple',
    'class'=>"custom-control-input"
  ));
  ?>
  <label class="custom-control-label" for="multiple">
    <h6 style="margin-top: 4px;" class="font-weight-normal"><?php echo t("Item-specific Tax Rates")?></h6>
  </label>  
</div>


<el-tabs v-model="tabs" class="demo-tabs" @tab-click="handleClick">
   <el-tab-pane label="<?php echo CommonUtility::safeTranslate('Standard Tax')?>" name="standard">   

    <div class="form-group">
      <label for="standard_tax_label"><?php echo t("Tax name")?></label>
      <?php echo $form->textField($model,'standard_tax_label',array(
        'class'=>"form-control", 
        'id'=>'standard_tax_label'
      )); ?>   
    </div>

    <div class="form-group">
      <label for="standard_tax_label"><?php echo t("Tax Rate %")?></label>
      <?php echo $form->textField($model,'standard_tax_value',array(
        'class'=>"form-control ", 
        'id'=>'standard_tax_value'
      )); ?>   
    </div>

    <div class="custom-control custom-switch custom-switch-md mr-4">  
      <?php echo $form->checkBox($model,"standard_tax_inclusive",array(
        'class'=>"custom-control-input",     
        'value'=>1,
        'id'=>"standard_tax_inclusive",     
        'checked'=>$model->standard_tax_inclusive==1?true:false,     
      )); ?>   
      <label class="custom-control-label" for="standard_tax_inclusive">
      <?php echo t("Prices Include Tax")?>
      </label>
    </div>    

    

   </el-tab-pane>

   <el-tab-pane label="<?php echo CommonUtility::safeTranslate('Item-specific Tax Rates')?>" name="multiple">
       

      <div class="custom-control custom-switch custom-switch-md mr-4">  
        <?php echo $form->checkBox($model,"price_included_tax",array(
          'class'=>"custom-control-input",     
          'value'=>1,
          'id'=>"price_included_tax",     
          'checked'=>$model->price_included_tax==1?true:false,     
        )); ?>   
        <label class="custom-control-label" for="price_included_tax">
        <?php echo t("Prices Include Tax")?>
        </label>
      </div>    


      <h6 class="mt-3"><?php echo t("Tax for delivery,service,small order fee and packaging fee.")?></h6>
      <div class="form-label-group">    
        <?php echo $form->dropDownList($model,'tax_for_delivery', (array)$mutilple_tax_list,array(
          'class'=>"form-control custom-select form-control-select select_two",
          'multiple'=>true,
          'placeholder'=>$form->label($model,'tax_for_delivery'),
          'style'=>"width:100%;"
        )); ?>         
        <?php echo $form->error($model,'tax_for_delivery'); ?>
      </div>

      <tax-table
      :label="<?php echo CommonUtility::safeJsonEncode([
        'title'=>t("Add new tax"),
        'tax_name'=>t("Tax name"),
        'tax_rate'=>t("Rate %"),
        'active'=>t("Active"),
        'save'=>t("Save"),    
        'cancel'=>t("Cancel"),    
        'edit'=>t("Edit"),
        'delete'=>t("Delete"),
        'are_you_sure'=>t("Are you sure?"),
        'confirm'=>t("Confirm"),
        'ok'=>t("Ok"),        
        'active'=>t('Active'),
        'inactive'=>t('Inactive'),
      ])?>"
      @after-addtax="afterAddtax"
      >
      </tax-table>

   </el-tab-pane>

</el-tabs>

<div style="height: 10px;"></div>
<?php echo CHtml::submitButton('submit',array(
      'class'=>"btn btn-green btn-full mt-3",
      'value'=>t("Save")
)); ?>

<?php $this->endWidget(); ?>

  </div>
</div>


<div>
<script type="text/x-template" id="xtemplate_tax_table">

<div class="text-right mb-2">
  <el-button @click="showTaxform" type="primary" size="large">
    <?php echo CommonUtility::safeTranslate("Add new tax")?>
  </el-button>
</div>

<el-table :data="getItems" 
    style="width: 100%" 
    empty-text="<?php echo CommonUtility::safeTranslate("No available data")?>"    
    v-loading="loading_fetch"
    >      
    <!-- <el-table-column prop="tax_id" label="<?php echo CommonUtility::safeTranslate('ID')?>" sortable  ></el-table-column>      -->
    <el-table-column prop="tax_name" label="<?php echo CommonUtility::safeTranslate('Tax name')?>" sortable  ></el-table-column>     
    <el-table-column prop="tax_rate" label="<?php echo CommonUtility::safeTranslate('Tax rate')?>" sortable  ></el-table-column>     
    <el-table-column prop="active" label="<?php echo CommonUtility::safeTranslate('Status')?>" sortable  >
        <template #default="scope">               
            <el-tag :type="scope.row.active?'success':'danger'">
               {{  scope.row.active ? label.active : label.inactive }}
            </el-tag>          
        </template>
    </el-table-column>    
    <el-table-column prop="tax_uuid" label="<?php echo CommonUtility::safeTranslate('Actions')?>" width="150" >
        <template #default="scope">          
          <el-button size="small" @click="handleEdit(scope.row.tax_uuid)">
            {{label.edit}}
          </el-button>
          <el-button
            size="small"
            type="danger"            
            @click="handleDelete(scope.row.tax_uuid)"
          >
             {{label.delete}}
          </el-button>
        </template>
    </el-table-column>
</el-table>


<el-dialog v-model="modal"
    :title="label.title"
    :width="500"		
    id="footer-none-bg"
    :close-on-click-modal="false"
    :close-on-press-escape="false"      
    :close="onDialogClose" 
>


<el-form
label-position="top"       
 label-width="auto"
>   

<el-form-item :label="label.tax_name" label-position="top">
    <el-input v-model="tax_name" ></el-input>
</el-form-item>

<el-form-item :label="label.tax_rate" label-position="top">
    <el-input v-model="tax_rate" ></el-input>
</el-form-item>

<el-checkbox v-model="active" :label="label.active" size="large" />


<el-form-item>
  <el-button type="primary" color="#626aef"  @click="submitForm" size="large" :loading="loading" >
    {{ label.save }}
  </el-button>
  <el-button @click="modal = false" size="large" :disabled="loading" >
    {{ label.cancel }}
  </el-button>
</el-form-item>

</el-form>

</el-dialog>

</script>
</div>