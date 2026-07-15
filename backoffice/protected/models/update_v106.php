<?php
$table = new TableDataStatus();

ob_start();

if(Yii::app()->db->schema->getTable("{{printer}}")){
    $data[] = $table->add_Column("{{printer}}",array(
        'service_id'=>"VARCHAR(100) NOT NULL DEFAULT '' AFTER `printer_model`",          
    ));
    $data[] = $table->add_Column("{{printer}}",array(
        'characteristics'=>"VARCHAR(100) NOT NULL DEFAULT '' AFTER `printer_model`",          
    ));
}

$parent_id = AttributesTools::getMenuParentID("admin",'Merchant');
$child_id = AttributesTools::getMenuID("admin",'auto login');
if($parent_id && !$child_id){        
    $model = new AR_menu();
    $model->menu_type="admin";
    $model->menu_name="auto login";
    $model->parent_id=intval($parent_id);
    $model->link="vendor/autologin";
    $model->action_name="vendor.autologin";
    $model->sequence=2;
    $model->save();
}

ob_end_clean();