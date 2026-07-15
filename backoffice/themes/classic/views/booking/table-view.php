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



<div class="card" id="vue-printhis">
 <div class="card-header" style="background-color:#fff;">
     <div class="row align-items-center">
        <div class="col">
            <h5 class="card-title p-0 m-0">
                <?php echo t("Dining Tables")?>
            </h5>
        </div>
        <div class="col text-right">
           <button type="button" class="btn btn-primary"  @click="printDiv" :disabled="is_printing">              
             <i class="zmdi zmdi-print" style="font-size: 16px;"></i>
             <span class="pl-2"><?php echo t("Print")?></span>
           </button>
        </div>
     </div>
  </div>
  <div class="card-body text-center printhis" id="print-content">
    
    <div class="p-4" id="url-top"></div>

    <h2 class="font-weight-bold">
        <?php echo t("SCAN THIS CODE")?><br/> 
        <?php echo t("QR TO SEE")?><br/>
        <?php echo t("THE MENU")?><br/>
    </h2>
    <h5><?php echo t("More comfortable, faster and environment friendly.")?></h5>
    <img src="<?php echo isset($qrcode)?$qrcode:'';?>" style="width:17rem;" />
    <h5 class="font-weight-bold">
        <?php                 
        if (is_numeric($model['table_name'])) {
            echo t("Table #{room_name}-{table_name}",[
                '{room_name}'=>$model['room_name'],
                '{table_name}'=>$model['table_name']
            ]);
        } else {            
            echo $model['room_name']."-".$model['table_name'];
        }
        ?>
    </h5>

    <div class="p-4"></div>

  
</div> <!--body-->
</div> <!--card-->
