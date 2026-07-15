<?php
class AR_item_bulk extends CActiveRecord
{	

	public $csv;
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
		return '{{item}}';
	}
	
	public function primaryKey()
	{
	    return 'item_id';	 
	}
		
	public function attributeLabels()
	{
		return array(
		    'item_id'=>t("Item ID"),		    
		    'item_name'=>t("Item name"),	
		);
	}
	
	public function rules()
	{
		return array(

		  array('csv,item_description','safe'),

		  array('csv',
				'file', 'types' => 'xlsx',
				'maxSize'=>5242880,
				'allowEmpty' => false,
				'wrongType'=>t('Only xlsx allowed.'),
				'tooLarge'=>t('File too large! 5MB is the limit'),
				'on'=>'bulk_import'
		   ),		    
		   
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
	}

	protected function afterDelete()
	{
		parent::afterDelete();		
	}
		
}
/*end class*/
