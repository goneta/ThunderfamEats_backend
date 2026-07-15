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

<?php
$category_group = [];
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'form',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('enctype' => 'multipart/form-data'),				
	)
);
?>

<div class="card">
  <div class="card-body" id="sort-menu-items">

    <p><?php echo t("Drag image to sort")?></p>
   
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

    <?php if(is_array($data) && count($data)>=1):?>
        <?php foreach ($data as $key => $cat):?>        
            <?php $category_group[] = $cat['subcat_id'];?>
            <ul class="list-group list-group-flushx mb-3">
                <li class="list-group-item font-weight-bold"><?php echo $cat['subcategory_name']?></li> 
                <?php if(is_array($cat['addoncategory']) && count($cat['addoncategory'])>=1):?>
                    <ul class="list-group" id="<?php echo $cat['subcat_id'];?>">
                      <?php foreach ($cat['addoncategory'] as $item_id):?>  
                         <li class="list-group-item " type="button">
                            <?php
                            $cat_id = $cat['subcat_id'];                
                            echo CHtml::hiddenField("id[$cat_id][]", $item_id);                            
                            echo  isset($addonitems[$item_id])? $addonitems[$item_id]['name']:t("unknown");
                            ?>
                         </li>
                      <?php endforeach;?>
                    </ul>
                <?php endif;?>
            </ul>
        <?php endforeach;?>
    <?php endif;?>

    <div class="p-3"></div>
    
    <?php if(is_array($data) && count($data)>=1):?>
    <?php echo CHtml::submitButton('submit',array(
    'class'=>"btn btn-green btn-full mt-3",
    'value'=>t("Save")
    )); ?>
    <?php endif;?>
    
      </div> 
    </div>   
    
    <?php 
ScriptUtility::registerScript(array(    
    "var category_group='".CJavaScript::quote(json_encode($category_group))."';"
  ),'sort_item');		
?>

    <?php $this->endWidget(); ?>