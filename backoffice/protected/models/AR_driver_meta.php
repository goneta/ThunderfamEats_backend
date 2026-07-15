<?php
class AR_driver_meta extends CActiveRecord
{	

	public $file_name,$file_size,$file_type;
	public $merchant_tax;
	public $account_name,$account_number_iban,$swift_code,$bank_name,$bank_branch;
	/**
	 * Returns the static model of the specified AR class.
	 * @return static the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{driver_meta}}';
	}
	
	public function primaryKey()
	{
	    return 'meta_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'account_name'=>t("Bank account holders name"),
			'account_number_iban'=>t("Bank account number/IBAN"),
			'swift_code'=>t("Swift Code"),
			'bank_name'=>t("Bank name in full"),
			'bank_branch'=>t("Bank branch city"),
		);
	}
	
	public function rules()
	{
		return array(
		  
		  array('reference_id,meta_name,meta_value1', 
		  'required','message'=> t( Helper_field_required ) ),

          array('meta_value2,meta_value3','safe'),
		  
		  array('meta_name,meta_value1,meta_value2,meta_value3,account_name,account_number_iban,swift_code,bank_name,bank_branch', 
		  'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),	
		  
		  array('account_name,account_number_iban,swift_code,bank_name,bank_branch', 
		  'required','message'=> t( Helper_field_required ), 'on'=>'bank_information' ),
		  
		);
	}

    protected function beforeSave()
	{
		if(!parent::beforeSave()){
			return false;
		} 
		
		return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
		
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
	public static function getMeta($merchant_id=0, $item_id=0, $meta_name=array())
	{
		  $data = array();
     	  $criteria=new CDbCriteria();
     	  $criteria->condition="merchant_id=:merchant_id AND reference_id=:reference_id";
     	  $criteria->params = array(':merchant_id'=>intval($merchant_id),'reference_id'=>intval($item_id));
     	  $criteria->addInCondition('meta_name', (array) $meta_name );     	
     	  
     	  $dependency = new CDbCacheDependency('SELECT MAX(date_modified) FROM {{cache}}');
     	  //$model = AR_item_meta::model()->cache( Yii::app()->params->cache , $dependency )->findAll($criteria);    
		   $model = AR_driver_meta::model()->cache( Yii::app()->params->cache , $dependency )->findAll($criteria);    
     	  
     	  if($model){
     	  	  foreach ($model as $item) {     	  	  	 
     	  	  	 $data[] = array(
     	  	  	   'reference_id'=>$item->reference_id,
     	  	  	   'meta_value1'=>$item->meta_value1,
                   'meta_value2'=>$item->meta_value2,
                   'meta_value3'=>$item->meta_value3,
     	  	  	 );
     	  	  }
     	  }
     	  return $data;
	}

	public static function getMeta2($merchant_id=0, $item_id=0, $meta_name=array())
	{
		  $data = array();
     	  $criteria=new CDbCriteria();
     	  $criteria->condition="merchant_id=:merchant_id AND reference_id=:reference_id";
     	  $criteria->params = array(':merchant_id'=>intval($merchant_id),'reference_id'=>intval($item_id));
     	  $criteria->addInCondition('meta_name', (array) $meta_name );     	
     	  
     	  $dependency = new CDbCacheDependency('SELECT MAX(date_modified) FROM {{cache}}');     	  
		   $model = AR_driver_meta::model()->cache( Yii::app()->params->cache , $dependency )->findAll($criteria);    
     	  
     	  if($model){
     	  	  foreach ($model as $item) {     	  	  	 
     	  	  	 $data[] = array(
     	  	  	   'reference_id'=>$item->reference_id,
     	  	  	   'meta_value1'=>$item->meta_value1,
                   'meta_value2'=>$item->meta_value2,
                   'meta_value3'=>$item->meta_value3,
				   'photo'=>CMedia::getImage($item->meta_value1,$item->meta_value2)
     	  	  	 );
     	  	  }
     	  }
     	  return $data;
	}
	
}
/*end class*/
