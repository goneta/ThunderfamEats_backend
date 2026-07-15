<?php
class AR_printer_meta extends CActiveRecord
{	

    public $file;
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
		return '{{printer_meta}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'printer_id	'=>t("printer_id"),		    
		);
	}
	
	public function rules()
	{
		return array(
		  array('printer_id,meta_name', 
		  'required','message'=> t( Helper_field_required ) ),
		  		  
		  array('meta_value1,meta_value2', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		  
		  
		  array('meta_value1,meta_value2','safe'),		  
		  
		);
	}

    protected function beforeSave()
	{
		if(parent::beforeSave()){			
			return true;
		} else return true;
	}
	
	protected function afterSave()
	{
		parent::afterSave();
        CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
        CCacheData::add();
	}

	public static function saveMeta($printer_id=0,$meta_name='', $meta_value1='', $meta_value2='',$scenario='')
	{		
		$model=AR_printer_meta::model()->find("printer_id=:printer_id AND meta_name=:meta_name",array(
		  ':printer_id'=>intval($printer_id),
		  ':meta_name'=>$meta_name
		));
		if($model){			
			$model->scenario = $scenario;
			$model->meta_value1=$meta_value1;
			if(!empty($meta_value2)){
				$model->meta_value2=$meta_value2;
			}			
			$model->save();
		} else {			
			$model = new AR_printer_meta;
			$model->scenario = $scenario;
			$model->printer_id = intval($printer_id);
			$model->meta_name = $meta_name;
			$model->meta_value1 = $meta_value1;
			if(!empty($meta_value2)){
				$model->meta_value2=$meta_value2;
			}
			$model->save();
		}
		
		/*ADD CACHE REFERENCE*/
		CCacheData::add();
		
		return true;
	}

	public static function getValue($printer_id=0,$meta_name='')
    {
     	$model  = AR_printer_meta::model()->find("printer_id=:printer_id AND meta_name=:meta_name",array(
		':printer_id'=>intval($printer_id),
		 ':meta_name'=>trim($meta_name)
		));			
		if($model){
			return array(
			  'meta_value1'=>$model->meta_value1,
			  'meta_value2'=>$model->meta_value2,
			);
		}
		return false;
     }
	
	 public static function getMeta($printer_id=0, $meta_name=array())
     {
     	  $data = array();
     	  $criteria=new CDbCriteria();
     	  $criteria->condition="printer_id=:printer_id";
     	  $criteria->params = array(':printer_id'=>intval($printer_id));
     	  $criteria->addInCondition('meta_name', (array) $meta_name );     	  
     	  
     	  $dependency = CCacheData::dependency();
     	  $model = AR_printer_meta::model()->cache( Yii::app()->params->cache , $dependency  )->findAll($criteria);  
     	  if($model){
     	  	  foreach ($model as $item) {     	  	  	 
     	  	  	 $data[$item->meta_name] = array(     	  	  	   
     	  	  	   'meta_value1'=>$item->meta_value1,
     	  	  	   'meta_value2'=>$item->meta_value2,			       
     	  	  	 );
     	  	  }
     	  }
     	  return $data;
     }
		
}
/*end class*/
