<?php
class AR_client_meta extends CActiveRecord
{	

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
		return '{{client_meta}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'client_id'=>t("Client ID"),		    
		);
	}
	
	public function rules()
	{
		return array(

		  array('client_id', 
		  'required','message'=> t( Helper_field_required ) ,'on'=>'registration_phone' ),
		  
		  array('meta1,meta2,meta3,meta4,date_created,ip_address','safe')
		  		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){
			$this->date_created = CommonUtility::dateNow();	
			$this->ip_address = CommonUtility::userIp();	
			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
	}

	public static function saveMeta($client_id=0, $meta1='',$meta2='')
	{		
		$model = AR_client_meta::model()->find("client_id=:client_id AND meta1=:meta1",[
			':client_id'=>intval($client_id),
			':meta1'=>$meta1
		]);
		if(!$model){
			$model = new AR_client_meta;
		}

		$model->client_id = intval($client_id);
		$model->meta1 = $meta1;
		$model->meta2 = $meta2;		
				
		if(!$model->save()){
			throw new Exception( CommonUtility::parseModelErrorToString( $model->getErrors() ) );						
		}		

		/*ADD CACHE REFERENCE*/
		CCacheData::add();			
		return true;
	}	

	public static function getMeta($meta_name=array())
     {
     	  $data = array();
     	  $criteria=new CDbCriteria();
     	  $criteria->addInCondition('meta1', (array) $meta_name );
     	  
     	  $dependency = CCacheData::dependency();
     	  $model = AR_client_meta::model()->cache( Yii::app()->params->cache , $dependency , Yii::app()->params->cache_count )->findAll($criteria);  
     	  if($model){
     	  	  foreach ($model as $item) {
     	  	  	 $data[$item->meta1] = $item->meta2;
     	  	  }
     	  }
     	  return $data;
     }

	 public static function getMeta2($meta_name=array(),$client_id=0)
     {
     	  $data = array();
     	  $criteria=new CDbCriteria();
		  $criteria->condition = "client_id=:client_id";
		  $criteria->params = [
			':client_id'=>intval($client_id)
		  ];
     	  $criteria->addInCondition('meta1', (array) $meta_name );
     	  
     	  $dependency = CCacheData::dependency();		  
     	  $model = AR_client_meta::model()->cache( Yii::app()->params->cache , $dependency , Yii::app()->params->cache_count )->findAll($criteria);  
     	  if($model){
     	  	  foreach ($model as $item) {
     	  	  	 $data[$item->meta1] = $item->meta2;
     	  	  }
     	  }
     	  return $data;
     }
		
}
/*end class*/