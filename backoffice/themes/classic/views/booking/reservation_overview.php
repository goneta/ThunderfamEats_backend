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

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-9">
                <div class="d-flex align-items-center">
                <div class="pr-2">
                <img src="<?php echo $data['photo_url']?>" class="img-60 rounded-circle">
                </div>
                <div>
                    <h5><?php echo $data['full_name']?></h5>
                    <p><span class="pr-2"><b>Phone</b>: +<?php echo $data['contact_phone']?></span> <span><b>Email</b>: <?php echo $data['email_address']?></span> </p>
                </div>
                </div>
            </div>
            <div class="col-md-3 d-none d-lg-block text-right">
                <a href="<?php echo $edit_link?>"><i class="zmdi zmdi-edit"></i> <?php echo t("Edit Reservation")?></a>               
                <div class="p-1"></div>
                
                <div class="dropdown">
                    <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo $data['status_pretty'];?>
                    </button>  
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">		
                        <?php if(is_array($status_list) && count($status_list)>=1):?>                        
                            <?php foreach ($status_list as $key => $value):?>
                                <a class="dropdown-item <?php echo $data['status']==$key?"disabled":""?>" href="<?php echo Yii::app()->CreateUrl("/booking/update_status",[
                                   'id'=>$data['reservation_uuid'],
                                   'status'=>$key,
                                   'redirect'=>Yii::app()->createAbsoluteUrl("/booking/reservation_overview",['id'=>$data['reservation_uuid']])
                                ])?>"><?php echo $value;?></a>
                            <?php endforeach;?>
                        <?php endif;?>
                    </div>
                </div>
            </div>
        </div>
        <!-- row -->
        
        <div class="bg-light p-3 mb-3 mt-3 rounded">
            <div class="row">
                <div class="col-md-2 col-sm-6 col-xs-6 text-center">
                    <h5><?php echo isset($summary['total_upcoming'])?$summary['total_upcoming']:0?></h5>
                    <p class="p-0 m-0"><?php echo t("Upcoming")?></p>
                </div>            
                <div class="col-md-2 col-sm-6 col-xs-6 text-center">
                    <h5><?php echo isset($summary['total_reservation'])?$summary['total_reservation']:0?></h5>
                    <p class="p-0 m-0"><?php echo t("Total")?></p>
                </div>            
                <div class="col-md-2 col-sm-6 col-xs-6 text-center">
                    <h5><?php echo isset($summary['total_denied'])?$summary['total_denied']:0?></h5>
                    <p class="p-0 m-0"><?php echo t("Denied")?></p>
                </div>            
                <div class="col-md-2 col-sm-6 col-xs-6 text-center">
                    <h5><?php echo isset($summary['total_cancelled'])?$summary['total_cancelled']:0?></h5>
                    <p class="p-0 m-0"><?php echo t("Cancelled")?></p>
                </div>            
                <div class="col-md-2 col-sm-6 col-xs-6 text-center">
                    <h5><?php echo isset($summary['total_noshow'])?$summary['total_noshow']:0?></h5>
                    <p class="p-0 m-0"><?php echo t("No show")?></p>
                </div>            
                <div class="col-md-2 col-sm-6 col-xs-6 text-center">
                    <h5><?php echo isset($summary['total_waitlist'])?$summary['total_waitlist']:0?></h5>
                    <p class="p-0 m-0"><?php echo t("Wait List")?></p>
                </div>            
            </div>
        </div>

        <div class="row">
            <div class="col-md-9" style="border-right:1px solid rgba(0,0,0,.1);">
            
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#details" role="tab" aria-controls="home" aria-selected="true">
                        <?php echo t("Details")?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#reservation" role="tab" aria-controls="profile" aria-selected="false">
                        <?php echo t("Reservation")?>
                    </a>
                </li>        
                </ul>
                <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active pt-3 pl-2" id="details" role="tabpanel" aria-labelledby="home-tab">
                
                <div class="row"> 
                        <div class="col">                                                        
                            <h5><?php echo t("Reservation Details")?></h5>
                            <table class="table">
                                <tr><td>
                                    <span><?php echo t("Reservation ID")?>: <?php echo $data['reservation_id']?></span>
                                    <span class="pl-3"><b><?php echo $data['status_pretty']?></b></span>
                                </td></tr>
                                <tr><td><?php echo $data['guest_number']?></td></tr>
                                <tr><td><?php echo $data['reservation_date']?></td></tr>
                                <tr><td><?php echo $data['reservation_time']?></td></tr>           
                                <tr><td><?php echo t("Table #{table_id}, Room name : {room_name}",
                                [
                                    '{table_id}'=>isset($table_names[$data['table_id']])?$table_names[$data['table_id']]:$data['table_id'],
                                    '{room_name}'=>isset($room_names[$data['room_id']])?$room_names[$data['room_id']]:$data['room_id']
                                ]);
                                ?></td></tr>           
                                <tr><td><?php echo t("Date booked")?> <?php echo $data['date_created']?></td></tr>           
                            </table>
                        </div>
                        <div class="col">
                            <h5><?php echo t("Customer Details")?></h5>
                            <table class="table">
                                <tr><td><?php echo $data['full_name']?></td></tr>
                                <tr><td><?php echo $data['email_address']?></td></tr>
                                <tr><td>+<?php echo $data['contact_phone']?></td></tr>
                                <tr><td>
                                    <b><?php echo t("Special Request")?></b> <?php echo $data['special_request']?>
                                    <?php if(!empty($data['cancellation_reason'])):?>
                                        <p><?php echo t("CANCELLATION NOTES = {cancellation_reason}",['{cancellation_reason}'=>$data['cancellation_reason']])?></p>
                                    <?php endif?>
                                </td></tr>   
                                <tr><td></td></tr>
                            </table>
                        </div>
                    </div>
                    <!-- row -->

                </div>
                <!-- details -->
                <div class="tab-pane fade pt-3 pl-2" id="reservation" role="tabpanel" aria-labelledby="profile-tab">
                    <div  id="vue-tables">

                    <components-datatable
                    ref="datatable"
                    ajax_url="<?php echo Yii::app()->createUrl("/apibackend")?>" 
                    actions="reservationList"
                    :table_col='<?php echo json_encode($table_col)?>'
                    :columns='<?php echo json_encode($columns)?>'
                    :filter_id="<?php echo $data['client_id']?>"
                    :settings="{
                        filter_date_disabled : '<?php echo true;?>',   
                        filter : '<?php echo false;?>',   
                        ordering :'<?php echo true;?>',    
                        order_col :'<?php echo intval($order_col);?>',   
                        sortby :'<?php echo $sortby;?>',                                
                        placeholder : '<?php echo CJavaScript::quote(t("Start date -- End date"))?>',  
                        separator : '<?php echo CJavaScript::quote(t("to"))?>',
                        all_transaction : '<?php echo CJavaScript::quote(t("All transactions"))?>'
                    }"  
                    page_limit = "<?php echo Yii::app()->params->list_limit?>"  
                    >
                    </components-datatable>

                    </div>
                </div>
                <!-- reservation  -->
                        
                </div>
                <!-- tab content -->

            </div>
            <!-- col -->
            <div class="col-md-3">
                <h5 class="text-success"><?php echo t("Timeline")?></h5>
                <div v-cloak id="vue-booking">                    
                    <reservation-timeline
                    ajax_url="<?php echo Yii::app()->createUrl("/apibackend")?>" 
                    id="<?php echo $data['reservation_uuid']?>"
                    :label="{                        
                        no_data : '<?php echo CJavaScript::quote(t("No available data"))?>'
                    }"  
                    >
                    </reservation-timeline>                  
                </div>
            </div>
            <!-- col -->
        </div>
        <!-- row -->



    </div>
    <!-- card-body -->
</div>
<!-- card -->