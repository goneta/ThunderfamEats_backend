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

<h6 class="mb-4"><?php echo t("Import Merchant file")?></h6>

<div class="form-group">
<?php 
echo $form->fileField($model, 'csv',array(
    'class'=>"form-control-file"
));
echo $form->error($model, 'csv');
?>
</div>

<p><?php echo t("Download csv template") ?> <a href="https://bastisapp.com/csv/merchant.csv" target="_blank"><?php echo t("click here");?></a></p>


<?php echo CHtml::submitButton('submit',array(
'class'=>"btn btn-green btn-full mt-3",
'value'=>t("Save")
)); ?>


<div class="pt-4"></div>

<h6><?php echo t("CSV Parameters")?></h6>
<table class="table table-striped" style="width:60%;">
   <thead>
	<tr>
		<th><?php echo t("Field Name")?></th>
		<th><?php echo t("Notes")?></th>
	</tr>
   </thead>
	<tr>
		<td>restaurant_name</td>
		<td><?php echo t("Name of restaurant")?></td>
	</tr>
	<tr>
		<td>restaurant_slug</td>
		<td>			
			<?php echo t("Restaurant slug, this is the url of your merchant example https://yourserver/mcdonalds")?>
			<br/>
			<?php echo t("where mcdonalds is slug")?>
		</td>
	</tr>
	<tr>
		<td>contact_name</td>
		<td><?php echo t("Contact name")?></td>
	</tr>
	<tr>
		<td>contact_phone</td>
		<td><?php echo t("Contact number")?></td>
	</tr>
	<tr>
		<td>contact_email</td>
		<td><?php echo t("Contact email address")?></td>
	</tr>
	<tr>
		<td>address</td>
		<td><?php echo t("Restaurant address")?></td>
	</tr>
	<tr>
		<td>latitude</td>
		<td><?php echo t("Restaurant Coordinates latitude")?></td>
	</tr>
	<tr>
		<td>lontitude</td>
		<td><?php echo t("Restaurant Coordinates longitude")?></td>
	</tr>
	<tr>
		<td>list_cuisine_id</td>
		<td>
		<?php echo t("List of cuisine ID separated by dash")?> 
			<br/>
			<?php echo t("example 20-21-23")?>
		</td>
	</tr>
	<tr>
		<td>list_service_id</td>
		<td>
		<?php echo t("List of service separated by dash")?> 
			<br/>
			<?php echo t("example delivery-pickup-dinein")?>
		</td>
	</tr>
	<tr>
		<td>delivery_distance_covered</td>
		<td>
		   <?php echo t("Delivery distance covered")?>
			<br/>
			<?php echo t("example 100")?> 
		</td>
	</tr>
	<tr>
		<td>distance_unit</td>
		<td>
		<?php echo t("Distance unit expected value are mi or km")?>
			<br/> 			
			<?php echo t("example mi or km")?>
		</td>
	</tr>
	<tr>
		<td>merchant_type</td>
		<td>
		<?php echo t("Merchant type expected value are 1 or 2")?>
			<br/>
			<?php echo t("1 = Membership")?><br/>
			<?php echo t("2 = Commission")?>
		</td>
	</tr>
	<tr>
		<td>is_ready</td>
		<td>
		<?php echo t("Published value are 1 or 2")?>
			<br/>
			<?php echo t("1 = Unpublished")?><br/>
			<?php echo t("2 = Published")?>
		</td>
	</tr>
	<tr>
		<td>status</td>
		<td>
		<?php echo t("Status value are active,pending,draft")?>			
		</td>
	</tr>
</table>

</div> <!--body-->
</div> <!--card-->


<?php $this->endWidget(); ?>