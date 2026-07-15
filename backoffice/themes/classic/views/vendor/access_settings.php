<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'frm-merchant',
	'enableAjaxValidation'=>false,
)); ?>

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

<div class="row">
<div class="col-md-6">


<h6 class="mb-0"><?php echo t("Access Settings")?></h6>
<p class="text-muted"><?php echo t("leave empty to allow access to all menu")?>.</p>

</div> <!--col-->

<div class="col-md-6">

<div class="d-flex flex-row justify-content-end">
  <div class="p-2">
  
  <a type="button" class="btn btn-black btn-circle checkbox_select_all" 
  href="javascript:;">
    <i class="zmdi zmdi-check"></i>
  </a>
  
  </div>
  <div class="p-2"><h5><?php echo t("Check All")?></h5></div>
</div> <!--flex-->

</div> <!--col-->
</div><!-- row-->


<table class="table table-striped sticky-header">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th><?php echo t("Menu")?></th>
            <th width="12%"><?php echo t("Create")?></th>
            <th width="12%"><?php echo t("Update")?></th>
            <th width="12%"><?php echo t("Delete")?></th>
            <th width="12%"><?php echo t("View")?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($menu as $key => $val):?>
        <?php $action_name = !empty($val['action_name'])?$val['action_name']:$val['label'];?>
        <tr>
            <td>            
            <div class="custom-control custom-checkbox">               
               <?php echo $form->checkBox($model,"role_access[$action_name]",array(
                    'class'=>"custom-control-input checkbox_child",     
                    'id'=>"role_access[$action_name]",
                    'checked'=>in_array($action_name,(array)$role_access)?true:false
               )); ?>
               <label class="custom-control-label" for="<?php echo "role_access[$action_name]";?>"></label>
            </div>
            </td>
            <td><?php echo t($val['label'])?></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <?php if(isset($val['items'])):?>
            <?php foreach ($val['items'] as $val_items):?>
                   <?php 
                       $items_action_name = !empty($val_items['action_name'])?$val_items['action_name']:$val_items['label'];
                       $role_create = !empty($val_items['role_create'])?$val_items['role_create']:'';
                       $role_update = !empty($val_items['role_update'])?$val_items['role_update']:'';
                       $role_delete = !empty($val_items['role_delete'])?$val_items['role_delete']:'';
                       $role_view = !empty($val_items['role_view'])?$val_items['role_view']:'';
                    ?>
                    <tr>
                        <td>                           
                        </td>
                        <td>
                            <div class="custom-control custom-checkbox">               
                            <?php echo $form->checkBox($model,"role_access[$items_action_name]",array(
                                    'class'=>"custom-control-input checkbox_child",     
                                    'id'=>"role_access[$items_action_name]",
                                    'checked'=>in_array($items_action_name,(array)$role_access)?true:false
                            )); ?>
                            <label class="custom-control-label" for="<?php echo "role_access[$items_action_name]";?>">
                               <?php echo t($val_items['label'])?>
                            </label>
                            </div>

                        <td>
                            <?php if(!empty($role_create)):?>                                                                
                                <div class="custom-control custom-checkbox">               
                                <?php echo $form->checkBox($model,"role_access[$role_create]",array(
                                        'class'=>"custom-control-input checkbox_child",     
                                        'id'=>"role_access[$role_create]",
                                        'checked'=>in_array($role_create,(array)$role_access)?true:false
                                )); ?>
                                <label class="custom-control-label" for="<?php echo "role_access[$role_create]";?>"></label>
                                </div>
                            <?php endif;?>
                        </td>
                        <td>
                             <?php if(!empty($role_update)):?>                                                                
                                <div class="custom-control custom-checkbox">               
                                <?php echo $form->checkBox($model,"role_access[$role_update]",array(
                                        'class'=>"custom-control-input checkbox_child",     
                                        'id'=>"role_access[$role_update]",
                                        'checked'=>in_array($role_update,(array)$role_access)?true:false
                                )); ?>
                                <label class="custom-control-label" for="<?php echo "role_access[$role_update]";?>"></label>
                                </div>
                            <?php endif;?>
                        </td>
                        <td>
                            <?php if(!empty($role_delete)):?>                                                                
                                <div class="custom-control custom-checkbox">               
                                <?php echo $form->checkBox($model,"role_access[$role_delete]",array(
                                        'class'=>"custom-control-input checkbox_child",     
                                        'id'=>"role_access[$role_delete]",
                                        'checked'=>in_array($role_delete,(array)$role_access)?true:false
                                )); ?>
                                <label class="custom-control-label" for="<?php echo "role_access[$role_delete]";?>"></label>
                                </div>
                            <?php endif;?>
                        </td>
                        <td>
                             <?php if(!empty($role_view)):?>                                                                
                                <div class="custom-control custom-checkbox">               
                                <?php echo $form->checkBox($model,"role_access[$role_view]",array(
                                        'class'=>"custom-control-input checkbox_child",     
                                        'id'=>"role_access[$role_view]",
                                        'checked'=>in_array($role_view,(array)$role_access)?true:false
                                )); ?>
                                <label class="custom-control-label" for="<?php echo "role_access[$role_view]";?>"></label>
                                </div>
                            <?php endif;?>
                        </td>
                    </tr>
                <?php endforeach;?>
        <?php endif;?>
        <?php endforeach;?>
    </tbody>
</table>

<div style="height:50px;"></div>
<div class="float-button" style="width:55%;">
    <?php echo CHtml::submitButton('submit',array(
        'class'=>"btn btn-green w-100",
        'value'=>t("Save"),            
    )); ?>
</div>

<?php $this->endWidget(); ?>
 