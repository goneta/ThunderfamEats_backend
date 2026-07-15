<?php
class AdminMenu
{	 	 	 
	 public static $items = array();
	 
	 public static function buildMenu($parent_id=0,$with_visible=false,$role_id=0,$menu_type='admin',$merchant_id=0)
	 {	 	 
	 	  $criteria=new CDbCriteria();
	 	  $sub_query = ''; $sub_sub_query = '';
	 	  if($role_id>0){
			  if($merchant_id>0){
				$sub_sub_query = "
					and action_name IN (
						select meta_value from {{merchant_meta}}
						where merchant_id=".q($merchant_id)."
						and meta_name='menu_access'
					)
			    ";
			  }
			  
	 	  	  $sub_query="
	 	  	  AND a.action_name IN (
		 	 	  select action_name from {{role_access}}
		 	 	  where role_id=".q($role_id)."				  
				  $sub_sub_query
		 	   )
	 	  	  ";
	 	  } else if ( $merchant_id>0){
			 $sub_query="
				AND a.action_name IN (
				select meta_value from {{merchant_meta}}
				where merchant_id=".q($merchant_id)."
				and meta_name='menu_access'
			 )
			 ";
		  }
	 	  
	 	  $criteria->alias="a";
	 	  $criteria->condition="a.menu_type=:menu_type AND a.status=:status AND a.parent_id=:parent_id $sub_query";
	 	  $criteria->params = array(
	 	    ':menu_type'=>$menu_type,
	 	    ':status'=>1,
	 	    ':parent_id'=>intval($parent_id),
	 	  );
	 	  
	 	  if($with_visible){
	 	  	 $criteria->addInCondition('a.visible', array(0,1) );
	 	  } else $criteria->addInCondition('a.visible', array(1) );
	 	  
	 	  $criteria->order="sequence ASC";
	 	  	 	  		  
	 	  $dependency = CCacheData::dependency();	 	  
	 	  if($model = AR_menu::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria)){
	 	  	 foreach ($model as $items) {
	 	  	     $class_li = str_replace(".","_",$items->action_name);		 	  	     
	 	  	     if($items->parent_id>0){
	 	 			self::$items[ $items->parent_id ]['items'][] = array(
	 	 			  'label'=>t($items->menu_name),
	 	 		      'url'=>!empty($items->link)?array($items->link):'javascript:;',
					  'url_absolute'=>!empty($items->link)? Yii::app()->createAbsoluteUrl($items->link) :'javascript:;',
	 	 		      'action_name'=>$items->action_name,
	 	 		      'itemOptions'=>array('class'=>"position-relative ".$class_li,'ref'=>$class_li),
					  'role_create'=>isset($items->role_create)?$items->role_create:false,
					  'role_update'=>isset($items->role_update)?$items->role_update:false,
					  'role_delete'=>isset($items->role_delete)?$items->role_delete:false,
					  'role_view'=>isset($items->role_view)?$items->role_view:false,
	 	 			);
	 	 		} else self::$items[$items->menu_id] = array(
	 	 		   'label'=>t($items->menu_name),
	 	 		   'url'=>!empty($items->link)?array($items->link):'javascript:;',

				   'url_absolute'=>!empty($items->link)? Yii::app()->createAbsoluteUrl($items->link) :'javascript:;',
	 	 		   'action_name'=>$items->action_name,
	 	 		   'itemOptions'=>array('class'=>$class_li),
				   'role_create'=>isset($items->role_create)?$items->role_create:false,
				   'role_update'=>isset($items->role_update)?$items->role_update:false,
				   'role_delete'=>isset($items->role_delete)?$items->role_delete:false,
				   'role_view'=>isset($items->role_view)?$items->role_view:false,
	 	 		);	 	 	
	 	 			 	  	
	 	 		$sub=self::buildMenu($items->menu_id,$with_visible,$role_id,$menu_type,$merchant_id);     
	 	  	 }
	 	  }	 	  
	 }
	 
}
/*end class*/