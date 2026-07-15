<?php
class Citems{

    public static function findItem($item_id=null)
    {
        $model = AR_item::model()->findByPk($item_id);
        if($model){
            return $model;
        }
        throw new Exception( t("Item not found") ); 
    }

    public static function SuggestedItemsValidate($merchant_id=0,$to_add=0,$max=5)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = "merchant_id = :merchant_id";
        $criteria->params = array(':merchant_id' => $merchant_id);
        $criteria->addNotInCondition("status",[
            'rejected'
        ]);        
        $count = AR_suggested_items::model()->count($criteria);
        $count2 = $count+$to_add;
        if($count2>$max){
            $remaining = $max-$count;
            if($remaining>0){
                throw new Exception(t("You alreay reach the maximum submitted items. you have {count} remaining items.",[
                    '{count}'=>$remaining
                ]));            
            } else throw new Exception(t("You alreay reach the maximum submitted items."));                        
        }
        return $count;
    }

    public static function findSuggestemItems($merchant_id=0,$item_id=0)
    {
        $model = AR_suggested_items::model()->find("merchant_id=:merchant_id AND item_id=:item_id AND status!=:status",[
            ':merchant_id'=>$merchant_id,
            ':item_id'=>$item_id,
            ':status'=>'rejected',
        ]);
        if($model){
            return true;
        }
        return false;
    }

    public static function bulkFeatureItemsApproved($items_ids=[])
    {
        if(!is_array($items_ids)){
            throw new Exception(t("Item id is not an array")); 
        }
        $stmt = "
        UPDATE {{suggested_items}}
        SET status='approved'
        WHERE item_id IN (".CommonUtility::arrayToQueryParameters($items_ids).");
        ";                
        Yii::app()->db->createCommand($stmt)->execute();

        $stmt = "
        UPDATE {{item}}
        SET is_featured=1
        WHERE item_id IN (".CommonUtility::arrayToQueryParameters($items_ids).");
        ";
        Yii::app()->db->createCommand($stmt)->execute();

        CommonUtility::pushJobs("SuggestedItemsBulk",[
            'status'=>'approved',
            'items_ids'=>$items_ids,
            'language'=>Yii::app()->language
        ]);

    }

    public static function bulkFeaturedItemsRejected($items_ids=[])
    {
        if(!is_array($items_ids)){
            throw new Exception(t("Item id is not an array")); 
        }
        $stmt = "
        UPDATE {{suggested_items}}
        SET status='rejected'
        WHERE item_id IN (".CommonUtility::arrayToQueryParameters($items_ids).");
        ";                
        Yii::app()->db->createCommand($stmt)->execute();

        $stmt = "
        UPDATE {{item}}
        SET is_featured=0,
        featured_priority=0
        WHERE item_id IN (".CommonUtility::arrayToQueryParameters($items_ids).");
        ";
        Yii::app()->db->createCommand($stmt)->execute();

        CommonUtility::pushJobs("SuggestedItemsBulk",[
            'status'=>'rejected',
            'items_ids'=>$items_ids,
            'language'=>Yii::app()->language
        ]);
    }

}
// end class