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

   <p><?php echo t("Drag list to sort")?></p>

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
    
    <?php if(is_array($category) && count($category)>=1):?>
        <?php foreach ($category as $key => $cat):?>        
        <?php 
        $category_group[] = $cat['category_uiid'];
        ?>
         <ul class="list-group list-group-flushx mb-3">
           <li class="list-group-item font-weight-bold"><?php echo $cat['category_name']?></li> 

           <?php if(is_array($cat['items']) && count($cat['items'])>=1):?>            
           <ul class="list-group" id="<?php echo $cat['category_uiid'];?>">
             <?php foreach ($cat['items'] as $item_id):?>        
             <li class="list-group-item " type="button">
                <?php                 
                $cat_id = $cat['cat_id'];                
                echo CHtml::hiddenField("id[$cat_id][]", $item_id);
                if(isset($items[$item_id])){
                    echo $items[$item_id]['item_name'];
                }
                ?>
             </li>            
             <?php endforeach?>
           </ul>           
           <?php endif;?>

         </ul>
        <?php endforeach?>
    <?php endif;?>

    
    <?php if(is_array($category) && count($category)>=1):?>
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