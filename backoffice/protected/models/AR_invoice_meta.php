<?php
class AR_invoice_meta extends CActiveRecord
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
		return '{{invoice_meta}}';
	}
	
	public function primaryKey()
	{
	    return 'id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'id	'=>t("ID"),
		);
	}
	
	public function rules()
	{
		return array(
		  array('invoice_number,meta_name', 
		  'required','message'=> t( Helper_field_required )),
		  		  		  
		  array('meta_value1,meta_value2', 'filter','filter'=>array($obj=new CHtmlPurifier(),'purify')), 		            
		  
		  array('meta_value1,meta_value2','safe')
		  
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

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}

	protected function afterDelete()
	{
		parent::afterDelete();		

        /*ADD CACHE REFERENCE*/
		CCacheData::add();
	}
		
}
/*end class*/
