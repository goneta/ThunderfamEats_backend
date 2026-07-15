<?php
class CMigrationTools
{

    public static function TableToMigrate()
    {
        return [
            'mt_merchant','mt_opening_hours','mt_client','mt_size','mt_size_translation','mt_ingredients','mt_ingredients_translation','mt_cooking_ref',
            'mt_cooking_ref_translation','mt_category','mt_category_translation','mt_subcategory',
            'mt_subcategory_translation','mt_subcategory_item','mt_subcategory_item_relationships',
            'mt_subcategory_item_translation','mt_item','mt_item_translation','mt_item_relationship_category',
            'mt_item_relationship_size','mt_item_relationship_subcategory_item','mt_item_relationship_subcategory',
            'mt_item_relationship_size','mt_item_meta'
        ];
    }

    public static function DataType(){      
        $data_type = [
            'merchant'=>t("Merchant"),            
            'customer'=>t("Customer"),
            'food_size'=>t("Food Size"),            
            'food_ingredients'=>t("Food Ingredients"),            
            'cooking_ref'=>t("Cooking Reference"),            
            'category'=>t("Category"),            
            'subcategory'=>t("Subcategory"),            
            'subcategory_item'=>t("Subcategory Item"),            
            'item'=>t("Food Item"),
            'item_addon'=>t("Food Item addon"),
            'item_attributes'=>t("Food Attributes"),
        ];
        return $data_type;
    }

    public static function OpeningDays()
    {
        return [
            1=>"monday",
            2=>"tuesday",
            3=>"wednesday",
            4=>"thursday",
            5=>"friday",
            6=>"saturday",
            7=>"sunday",            
        ];
    }

    public static function getMerchantServices($service=0)
    {
        switch ($service) {
            case '1':        
                return ['delivery','pickup'];
                break;
            case '2':                
                return ['delivery'];
                break;                    
            case '3':                
                return ['pickup'];
                break;                    
            case '4':        
                return ['delivery','pickup','dinein'];        
                break;                                    
            case '5':                
                return ['delivery','dinein'];        
                break;                                                
            case '6':                
                return ['pickup','dinein'];        
                break;                                                            
            case '7':                
                return ['dinein'];    
                break;                                                                            
        }
    }

    public static function RemoveOwnTableFromList($data=array())
    {
        $result = [];
        if(is_array($data) && count($data)>=1){
            foreach ($data as $item) {
                $prefix = substr($item,0,3);
                if($prefix!=DB_PREFIX){
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    public static function CheckTableExist($data=array())
    {        
        if(is_array($data) && count($data)>=1){
            foreach ($data as $table_name) {
                if(!Yii::app()->db->schema->getTable($table_name)){
                    throw new Exception( t("The table named {table_name} doesn't exist. Please make sure the table exists before continuing.",[
                        '{table_name}'=>$table_name
                    ]) );
                }
            }
        }
    }

}
// end class