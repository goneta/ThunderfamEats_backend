<?php $this->renderPartial("/tpl/search-form",array(
 'link'=>isset($link)?$link:''
))?>


<?php echo CHtml::beginForm('','post',array(
  'id'=>"frm_datatables",
  'class'=>"frm_datatables",
  'onsubmit'=>"return false;"
)); 
?> 

<?php if(is_array($default_country) && count($default_country)>=1):?>  
  <div class="mb-4">  
  <h6>Default Country</h6>
  <table class="ktables_list">
    <tr>
      <td width="10%"><?php echo $default_country['country_id']?></td>
      <td width="30%"><?php echo $default_country['country_name']?></td>
      <td width="15%">
      <div class="btn-group btn-group-actions" role="group">
        <a href="<?php echo Yii::app()->createUrl("/location/state_list",['country_id'=>$default_country['country_id']])?>" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Define Locations">
          <i class="zmdi zmdi-collection-item"></i>
        </a>
        <a href="<?php echo Yii::app()->createUrl("/location/country_update",['id'=>$default_country['country_id']])?>" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">
          <i class="zmdi zmdi-border-color"></i>
        </a>
        <a href="javascript:;" data-id="<?php echo $default_country['country_id'];?>" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">
          <i class="zmdi zmdi-delete"></i>
        </a>
      </div>
      </td>
    </tr>
  </table>
  </div>  
<?php endif;?>

<div class="table-responsive-md">
<table class="ktables_list table_datatables">
<thead>
<tr>
<th width="10%"></th>
<th width="30%"><?php echo t("Name")?></th>
<th width="15%"><?php echo t("Actions")?></th>
</tr>
</thead>
<tbody></tbody>
</table>
</div>

<?php echo CHtml::endForm(); ?>

<?php $this->renderPartial("/admin/modal_delete");?>