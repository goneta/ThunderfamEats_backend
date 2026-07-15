
<div class="row mb-4 d-none d-lg-block">
  <div class="col d-flex justify-content-start align-items-center"> 
   <h6 class="m-0 p-2 pd-5 with-icon-account with-icon"><?php echo t("Profile")?></h6>
  </div> <!--col-->     
</div> <!--row-->


<div id="upload-profile" class="d-block d-lg-none text-center mb-3 mt-3">  
  <!-- <img ref="refavatar" class="lazy img-fluid mb-2 rounded-circle img-60" data-src="<?php echo $avatar;?>"/>   -->
        
  <div v-loading="loading">
    <el-upload
      class="avatar-uploader mb-2"
      :action="upload_url"
      :show-file-list="false"
      :on-success="handleAvatarSuccess"
      :before-upload="beforeAvatarUpload"    
      :data="data"    
    >    
      <img v-if="imageUrl" :src="imageUrl" class="avatar" />
      <el-icon v-else class="avatar-uploader-icon">
        <i class="fas fa-plus"></i>
      </el-icon>
    </el-upload>
  </div>

  <h5 class="m-0 mb-1"><?php echo $model->first_name?> <?php echo $model->last_name?></h5>
  <p class="m-0 text-muted"><?php echo t("M. {{mobile}}",array('{{mobile}}'=>$model->contact_phone))?></p>
  <p class="m-0 text-muted"><?php echo t("E. {{email}}",array('{{email}}'=>$model->email_address))?></p>
</div>

<?php 
$index=0;
switch (Yii::app()->controller->action->id) {
    case 'profile':
    $index = 0;
    break;
    case 'change_password':
    $index = 1;
    break;  
    case 'notifications':
    $index = 2;
    break;     
    case 'manage_account':
    $index = 3;
    break;        
    
}
?>
<div id="vue-profile-menu" class="d-block d-lg-none mb-2 mt-4">
   <component-profile-menu
     index_selected="<?php echo $index;?>"
     :data='<?php echo json_encode($menu)?>'
   >
   </component-profile-menu>
</div>