<nav class="navbar navbar-light justify-content-between">
<?php
$this->widget('zii.widgets.CBreadcrumbs', 
array(
'links'=>isset($links)?$links:array(),
'homeLink'=>false,
'separator'=>'<span class="separator">
<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
));
?>
</nav>

<div id="vue-tables" class="card">
<div class="card-body">

    <?php echo $print_content;?>

</div>
</div>

<?php $this->renderPartial("/admin/modal_delete");?>