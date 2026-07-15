<?php
Yii::import('zii.widgets.CMenu', true);

class WidgetCustomerMerchant extends CMenu
{		 
	 public function init()
	 {		 		 	  
	 	  $id = trim(Yii::app()->input->get('id'));			  
	 	  	 	  
	 	  $menu = array();
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-account-circle"></i>'.t("Basic Details"),
	 	    'url'=>array("/customer/view",'id'=>$id)
	 	  );	 	  	 	 	 	
	 	  
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-pin"></i>'.t("Address"),
	 	    'url'=>array("/customer/addresses",'id'=>$id),
	 	    'itemOptions'=>array(
	 	      'class'=>"customer-address"
	 	    )
	 	  );	 	  	 	 	 	
	 	  
	 	  $menu[]=array(
	 	    'label'=>'<i class="zmdi zmdi-shopping-basket"></i>'.t("Order history"),
	 	    'url'=>array("/customer/order_history",'id'=>$id),
	 	    'itemOptions'=>array(
	 	      'class'=>"customer-order-history"
	 	    )
	 	  );	 	  	 	 	 		 		 	  
	 	  
	 	  $this->items = $menu;	 	  
	 	  	 	  
	 	  $this->encodeLabel = false;
	 	  $this->activeCssClass = "active";
	 	  $this->activateParents = true;
	 	  $this->htmlOptions = array(
	 	    'class'=>'customer-menu'
	 	  ); 
	 	  $this->submenuHtmlOptions = array(
	 	    'class'=>'customer-sub-menu'
	 	  ); 
	 	  
	 	  parent::init();
	 }
	 
}
/*end class*/