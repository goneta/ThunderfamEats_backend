<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>$links,
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>


<DIV id="vue-migration" >
<?php
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'upload-form',
		'enableAjaxValidation' => false,		
	)
);
?>

<div class="card" id="vue-migration" v-cloak>
  <div class="card-body" v-loading="loading">  

  
  <el-collapse v-model="activeNames" >
    <el-collapse-item title="<?php echo t("Instructions")?> :" name="1">
        <ol>
          <li class="mb-2">
            <b><?php echo t("Export Tables from Old Database")?>:</b>
            <ul>
              <li class="mb-1"><?php echo t("Use PhpMyAdmin to export the following tables:")?>
               <?php foreach ($tables as $items):?>
                 <span class="text-dark"><b><?php echo $items;?></b></span>
                 <span class="mr-2 ">,</span>
               <?php endforeach;?>
               </li>
              <li class="mb-1"><?php echo t("If the table is large, consider using the mysqldump command for faster data exporting.")?></li>
            </ul>
          </li>
          <li class="mb-2">
            <b><?php echo t("Import Tables into New Database")?>:</b>
            <ul>
              <li><?php echo t("Utilize either PhpMyAdmin or the MySQL command to import the tables.")?></li>
            </ul>
          </li>
          <li class="mb-2">
            <b><?php echo t("Warning: Clean Database Table")?>:</b>
            <ul>
              <li><?php echo t("Before importing, ensure that the target database table is clean, as the existing data will be deleted during the import process.")?></li>
            </ul>
          </li>
          <li class="mb-2">
            <b><?php echo t("Select Data for Import")?>:</b>
            <ul>
              <li class="mb-1"><?php echo t("Below, choose the data to import and input your old table prefix (e.g., mt).")?></li>
              <li class="mb-1"><?php echo t('Click "Submit."')?></li>
            </ul>
          </li>
          <li class="mb-2">
            <b><?php echo t("Continue Sequential Import")?>:</b>
            <ul>
              <li><?php echo t("Import the selected data in sequence until you have completed the import process.")?></li>
            </ul>
          </li>
          <li class="mb-2">
            <b><?php echo t("Upload Images to New Server")?>:</b>
            <ul>
              <li><?php echo t("Transfer all images from the /upload folder in your old server to the corresponding /upload folder in your new server.")?></li>
            </ul>
          </li>
        </ol>
      </el-collapse-item>
  </el-collapse>

  <h6 class="mb-2 mt-4"><?php echo t("Select Data to import")?></h6>
  <el-select v-model="data_type" class="w-100" placeholder="<?php echo t("Select")?>" size="large">
    <el-option
      v-for="item in data_type_list"
      :key="item.value"
      :label="item.label"
      :value="item.value"
    >
    </el-option>
  </el-select>


  <h6 class="mb-2 mt-4"><?php echo t("Table Prefix")?></h6>
  <el-input v-model="table_prefix"  size="large" ></el-input>
  <p class="text-muted mt-1"><?php echo t("The table prefix for your old tables")?></p>


  <div class="mt-4">
  <el-button type="primary" size="large" class="w-100 pb-4 pt-4" :loading="loading" @click="onSubmit" >
	<?php echo t("Submit")?>
  </el-button>
  </div>

  </div> <!--body-->
</div> <!--card-->

  



<?php $this->endWidget(); ?>  
</DIV>