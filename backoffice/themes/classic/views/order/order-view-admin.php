
<DIV id="vue-order-view" v-cloak class="position-relative fixed-height">

<div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
    <div>
      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
    </div>
</div>

<!--CONTENT SECTION-->

  <components-orderinfo
  ref="orderinfo"  
  :group_name="group_name"
  ajax_url="<?php echo $ajax_url?>"
  @after-update="afterUpdateStatus" 
  @show-menu="showMerchantMenu" 
  @refresh-order="refreshOrderInformation"
  @view-customer="viewCustomer"
  @to-print="toPrint"
  @delay-orderform="delayOrder"
  @rejection-orderform = "orderReject"
  @order-history = "orderHistory"
  @view-merchant-transaction = "viewMerchantTransaction"
  @show-assigndriver="showAssigndriver"
  @show-manageorder="showManageorder"
  @delete-orderconfirm="deleteOrderconfirm"
  @show-issuerefund="showIssuerefund"
  @after-fetchorder="afterFetchorder"
  
  :manual_status="manual_status"
  :modify_order="modify_order"  
  :filter_buttons="filter_buttons"  
  :enabled_delay_order="<?php echo false;?>"
  
  :refund_label="<?php 
  echo CommonUtility::safeJsonEncode([
    'title'=>t("Refund this item"),
    'content'=>t("This automatically remove this item from your active orders."),
    'go_back'=>t("Go back"),
    'complete'=>t("Confirm")
  ])
  ?>"
  :remove_item="<?php 
  echo CommonUtility::safeJsonEncode([
    'title'=>t("Remove this item"),
    'content'=>t("This will remove this item from your active orders."),
    'go_back'=>t("Go back"),
    'confirm'=>t("Confirm")
  ]);
  ?>"
  :out_stock_label="<?php echo CommonUtility::safeJsonEncode([
    'title'=>t("Item is Out of Stock")
  ])?>"
  
   :update_order_label="<?php echo CommonUtility::safeJsonEncode([
    'title'=>t("Order decrease"),
    'title_increase'=>t("Order Increase"),
    'content'=>t("By accepting this order, we will refund the amount of {{amount}} to customer."),
    'content_collect'=>t("Total amount for this order has increase, Send invoice to customer or less from your account with total amount of {{amount}}."),
    'cancel'=>t("Cancel"),
    'confirm'=>t("Confirm"),
    'send_invoice'=>t("Send invoice"),
    'less_acccount'=>t("Less on my account"),
    'close'=>t("Close"),
    'content_payment'=>t('This order has unpaid invoice, until its paid you cannot change the order status.')
   ])?>"    
  
  >
  </components-orderinfo>
  <!--CONTENT SECTION-->
  
<components-manage-order
ref="ref_manage_order"
:label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Manage order"),
  'status'=>t("Status"),
  'close'=>t("Close"),
  'confirm'=>t("Confirm"),
  'change_status'=>t("Change Order Status for Order #{order_id}"),  
])?>"
:order_uuid="order_uuid"
:status_list="<?php echo CommonUtility::safeJsonEncode($status_list)?>"
@after-changeorderstatus="afterChangeorderstatus"
></components-manage-order>

<components-issue-refund
ref="ref_issue_refund"
:label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Manage Refund"),
  'close'=>t("Close"),
  'confirm'=>t("Confirm"),
  'change_status'=>t("Change Order Status for Order #{order_id}"),  
])?>"
:order_uuid="order_uuid"
:order_information="order_information"
:refund_type_list="<?php echo CommonUtility::safeJsonEncode([
  'full_refund'=>t("Full Refund"),
  'partial_refund'=>t("Partial Refund"),
])?>"
:payment_list="<?php echo $payment_list?CommonUtility::safeJsonEncode($payment_list):null;?>"
@after-issuerefund="afterIssuerefund"
>
</components-issue-refund>
  
<components-delay-order
ref="delay"
@after-confirm="afterConfirmDelay"
@after-update="afterUpdateStatus" 
:order_uuid="order_uuid"
ajax_url="<?php echo $ajax_url?>"  
:label="<?php 
echo CommonUtility::safeJsonEncode([
  'title'=>t("Delay Order"),
  'sub1'=>t("How much additional time you need?"),
  'sub2'=>t("We'll notify the customer about the delay."),
  'confirm'=>t("Confirm")
])
?>"
>
</components-delay-order>
  

<components-rejection-forms
ref="rejection"
ajax_url="<?php echo $ajax_url;?>"  
@after-submit="afterRejectionFormsSubmit"
@after-update="afterUpdateStatus" 
:order_uuid="order_uuid"
 :label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Enter why you cannot make this order."),
  'reject_order'=>t("Reject order"),
  'reason'=>t("Reason")
 ])?>"
>
</components-rejection-forms>

<components-order-history
ref="history"
ajax_url="<?php echo $ajax_url?>"
:order_uuid="order_uuid"
:label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Timeline"),
  'close'=>t("Close")
])?>"  
>
</components-order-history>

<components-order-print
  ref="print"      
  :order_uuid="order_uuid"
  mode="popup"
  :line="75"
  ajax_url="<?php echo $ajax_url?>"  
  >
</components-order-print>


<components-menu
  ref="menu"    
  ajax_url="<?php echo $ajax_url?>"
  @show-item="showItemDetails"
  
  image_placeholder="<?php echo websiteDomain().Yii::app()->theme->baseUrl."/assets/images/placeholder.png"?>"
  merchant_id="<?php echo $merchant_id?>"  
  :label="<?php echo CommonUtility::safeJsonEncode([
    'previous'=>t("Previous"),
    'next'=>t("next")
  ])?>"    
  >
</components-menu>

  <components-item-details
  ref="item"    
  ajax_url="<?php echo $ajax_url?>"
  @go-back="showMerchantMenu"
  @close-menu="hideMerchantMenu"
  @refresh-order="refreshOrderInformation"  
  
  image_placeholder="<?php echo websiteDomain().Yii::app()->theme->baseUrl."/assets/images/placeholder.png"?>"
  merchant_id="<?php echo $merchant_id?>"  
  :order_type="order_type"
  :order_uuid="order_uuid"
  >
  </components-item-details>
      
  <components-customer-details
  ref="customer"    
  :client_id="client_id"
  ajax_url="<?php echo $ajax_url?>"  
  merchant_id="<?php echo $merchant_id?>"  
  image_placeholder="<?php echo websiteDomain().Yii::app()->theme->baseUrl."/assets/images/placeholder.png"?>"
  page_limit = "<?php echo Yii::app()->params->list_limit?>"  
  :label="<?php echo CommonUtility::safeJsonEncode([
    'block_customer'=>t("Block Customer"),
    'block_content'=>t("You are about to block this customer from ordering to your restaurant, click confirm to continue?"),
    'cancel'=>t("Cancel"),
    'confirm'=>t("Confirm")
  ])?>"    
  >
  </components-customer-details>

   
<components-merchant-transaction
ref="merchant_transaction"
ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
image_placeholder="<?php echo websiteDomain().Yii::app()->theme->baseUrl."/assets/images/placeholder.png"?>"
:label="<?php echo CommonUtility::safeJsonEncode([
  'block'=>t("Deactivate Merchant"),
  'block_content'=>t("You are about to deactivate this merchant, click confirm to continue?"),
  'cancel'=>t("Cancel"),
  'confirm'=>t("Confirm")
])?>"  
>
</components-merchant-transaction>

<components-assign-driver
ref="assign_driver"
order_uuid="<?php echo $order_uuid?>"
ajax_url="<?php echo Yii::app()->createUrl("/api")?>" 
@refresh-order="refreshOrderInformation"
:map_center='<?php echo json_encode([
    'lat'=>isset($maps_config['default_lat'])?$maps_config['default_lat']:'',
    'lng'=>isset($maps_config['default_lng'])?$maps_config['default_lng']:'',
])?>'
zoom="<?php echo isset($maps_config['zoom'])?$maps_config['zoom']:'';?>"
>
</components-assign-driver>
  
 </DIV>
 <!--vue-order-view-->
 
<?php $this->renderPartial("/orders/order-details", array(
    'ajax_url'=>$ajax_url,
    'view_admin'=>$view_admin,
    'printer_list'=>isset($printer_list)?$printer_list:''
));?>
<?php $this->renderPartial("/orders/template_print");?>
<?php $this->renderPartial("/orders/template_menu");?>
<?php $this->renderPartial("/orders/template_item");?>
<?php $this->renderPartial("/orders/template_customer_all");?>
<?php $this->renderPartial("/orders/template_assigned_driver",[
  'maps_config'=>$maps_config
]);
$this->renderPartial("/orders/preparation_time");
$this->renderPartial("/orders/manage_order");
$this->renderPartial("/order/issue_refund");
?>
 
<DIV id="vue-bootbox">
<component-bootbox
ref="bootbox"
@callback="Callback"
size='small'
:label="<?php echo CommonUtility::safeJsonEncode([
  'confirm'=>t("Delete Confirmation"),
  'are_you_sure'=>t("Are you sure you want to continue?"),
  'yes'=>t("Yes"),
  'cancel'=>t("Cancel"),
  'ok'=>t("Okay")
])?>"
>
</component-bootbox>
</DIV>


<?php $this->renderPartial("//finance/template_merchant_transaction",array(
  'table_col_trans'=>$table_col_trans,
  'columns_trans'=>$columns_trans,
  'transaction_type'=>$transaction_type,
  'sortby'=>$sortby,
))?>