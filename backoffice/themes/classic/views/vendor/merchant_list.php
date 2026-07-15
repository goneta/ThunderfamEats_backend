<?php $this->renderPartial("/tpl/search-form",array(
 'link'=>$link,
 'bulk_link'=>isset($bulk_link)?$bulk_link:''
))?>

<?php echo CHtml::beginForm('','post',array(
  'id'=>"frm_datatables",
  'class'=>"frm_datatables",
  'onsubmit'=>"return false;"
)); 
?> 

<div class="table-responsive-md">
<table class="ktables_list table_datatables">
<thead>
<tr>
<th width="10%"></th>
<th width="18%"><?php echo t("Name")?></th>
<th width="18%"><?php echo t("ID")?></th>
<th width="30%"><?php echo t("Address")?></th>
<th width="20%"><?php echo t("Charge Type")?></th>
<th width="15%"><?php echo t("Actions")?></th>
</tr>
</thead>

<tbody></tbody>

</table>
</div>

<?php echo CHtml::endForm(); ?>

<?php $this->renderPartial("/admin/modal_delete");?>
<?php $this->renderPartial("/vendor/clone_merchant_modal");?>