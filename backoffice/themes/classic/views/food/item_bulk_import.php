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
$form = $this->beginWidget(
	'CActiveForm',
	array(
		'id' => 'upload-form',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('enctype' => 'multipart/form-data'),
	)
);
?>

<div class="card">
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

<h6 class="mb-4"><?php echo t("Import File")?></h6>

<div class="form-group">
<?php 
echo $form->fileField($model, 'csv',array(
    'class'=>"form-control-file"
));
echo $form->error($model, 'csv');
?>
</div>

<p><?php echo t("Download sample template") ?> <a href="https://bastisapp.com/csv/food-items.xlsx" target="_blank"><?php echo t("click here");?></a></p>

<h5 class="mb-1"><?php echo t("Notice")?>:</h5>
<ul>
	<li><?php echo t("Each item ID must be unique. If an item ID already exists, it will not be saved")?>.</li>
	<li><?php echo t("Item IDs should start with {last_id}",['{last_id}'=> '<b>'.$last_id.'</b>' ])?>.</li>
	<li><?php echo t("Category IDs and Size IDs must also be unique")?>.</li>
	<li><?php echo t("Category ID should start with {cat_id}, and size id with {size_id}",[
		'{cat_id}'=>'<b>'.$cat_id.'</b>',
		'{size_id}'=>'<b>'.$size_id.'</b>',
	])?></li>
</ul>

<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>


<div class="pt-4"></div>


<h6><?php echo t("Excel File Parameters")?></h6>

<p><?php echo t("Notice: If your food item has multiple sizes, you need to add it as two rows with the same item ID.");?></p>
<table class="table table-striped" style="width:60%;">
   <thead>
	<tr>
		<th><?php echo t("Field Name")?></th>
		<th><?php echo t("Notes")?></th>
	</tr>
   </thead>
	<tr>
		<td><?php echo t("Item ID")?></td>
		<td><?php echo t("The id of food item")?></td>
	</tr>
	<tr>
		<td><?php echo t("Item Name")?></td>
		<td>			
			<?php echo t("The food item name")?>	
		</td>
	</tr>
	<tr>
		<td><?php echo t("Short Description")?></td>
		<td><?php echo t("Item short description")?></td>
	</tr>
	<tr>
		<td><?php echo t("Long Description")?></td>
		<td><?php echo t("Item long description")?></td>
	</tr>
	<tr>
		<td><?php echo t("Size ID")?></td>
		<td><?php echo t("Size id")?></td>
	</tr>
	<tr>
		<td><?php echo t("Size Name")?></td>
		<td><?php echo t("Size name")?></td>
	</tr>
	<tr>
		<td><?php echo t("Price")?></td>
		<td><?php echo t("Item price")?></td>
	</tr>
	<tr>
		<td><?php echo t("Cost Price")?></td>
		<td><?php echo t("Item cost price")?></td>
	</tr>
	<tr>
		<td><?php echo t("Discount Type")?></td>
		<td>
		<?php echo t("Discount type possible value fixed or percentage")?> 			
		</td>
	</tr>
	<tr>
		<td><?php echo t("Discount")?></td>
		<td>
		<?php echo t("Discount value must be numeric example 1.00")?> 			
		</td>
	</tr>
	<tr>
		<td><?php echo t("Discount Start")?></td>
		<td>
		   <?php echo t("Discount start date must be in correct format YYYY-mm-dd")?>
			<br/>
			<?php echo t("example 2024-06-21")?> 
		</td>
	</tr>
	<tr>
		<td><?php echo t("Discount End")?></td>
		<td>
		<?php echo t("Discount end date must be in correct format YYYY-mm-dd")?>
			<br/> 			
			<?php echo t("example 2024-06-21")?>
		</td>
	</tr>
	<tr>
		<td><?php echo t("Category ID")?></td>
		<td>
		<?php echo t("Category ID")?>			
		</td>
	</tr>
	<tr>
		<td><?php echo t("Category Name")?></td>
		<td>
		<?php echo t("Category name")?>			
		</td>
	</tr>
	<tr>
		<td><?php echo t("Featured Image")?></td>
		<td>
		<?php echo t("Image filename, supported type png,jpg and webp")?>
		<br/><br/>
		<?php 
		echo t("Upload the images in your server in the folder /upload/1 where in 1 = is the merchant id");
		?>
		</td>
	</tr>
	<tr>
		<td><?php echo t("Featured")?></td>
		<td>
		<?php echo t("If food is featured must separated by comma, possible values are new,trending,best_seller,recommended")?>
		<br/><br/>
		<?php echo t("Example new,trending")?>
		</td>
	</tr>
	<tr>
		<td><?php echo t("Status")?></td>
		<td>
		<?php echo t("Food item status possible value are draft,pending and publish")?>			
		</td>
	</tr>
</table>

</div> <!--body-->
</div> <!--card-->


<?php $this->endWidget(); ?>