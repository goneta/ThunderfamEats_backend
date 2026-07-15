<?php
class CSeo
{

    public static function setPage()
    {
        $meta_name = '';
        $settings = Yii::app()->params['settings'];
        $controller_name = Yii::app()->controller->id;
        $controller_action = Yii::app()->controller->action->id;

        //dump("$controller_name=>$controller_action");

        switch ($controller_action) {
            case 'index':                
                $meta_name = $controller_name=='orders' ?  'seo_page_tracking_orderpage' :  'seo_page_homepage';
                break;
            case "restaurants":            
                $meta_name = 'seo_page_search';
                break;
            case "cuisine":            
                $meta_name = 'seo_page_cuisine';
                break;
            case "contactus":            
                $meta_name = 'seo_page_contactus';
                break;
            case "login":            
                $meta_name = 'seo_page_login';
                break;
            case "signup":            
                $meta_name = $controller_name=='merchant' ?  'seo_page_restaurant_signup' :  'seo_page_signup';
                break;
            case "guest":            
                $meta_name = 'seo_page_guest_checkout';
                break;
            case "menu":            
                $meta_name = 'seo_page_menu';
                break;  
            case "checkout":          
                $meta_name = 'seo_page_checkout';
                break;  
            case "profile":          
            case "manage_account":
                $meta_name = 'seo_page_manage_account';
                break;     
            case "change_password":             
                $meta_name = 'seo_page_change_password';
                break;     
            case "orders":             
                $meta_name = 'seo_page_user_order';
                break;                     
            case "addresses":             
                $meta_name = 'seo_page_user_address';
                break;                                     
            case "payments":             
                $meta_name = 'seo_page_user_saved_payments';
                break;                                     
            case "favourites":             
                $meta_name = 'seo_page_user_saved_store';
                break;                                     
            case "details":             
                $meta_name = 'seo_page_table_booking';
                break;                                     
            case "update":
                $meta_name = 'seo_page_table_booking';
                break;                                     
        }        
        //dump($meta_name);
        if($meta = AR_admin_meta::getValue($meta_name)){            
            //dump($meta);
            $page_id = isset($meta['meta_value'])? intval($meta['meta_value']) :'';
            if($page_id>0){
                try {
                    $model = PPages::pageDetailsSlug($page_id , Yii::app()->language , "a.page_id" );                    
                    $seo_data = isset(Yii::app()->params['seo_data'])?Yii::app()->params['seo_data']:false;
                    if(is_array($seo_data) && count($seo_data)>=1){
                        $seo_data = CommonUtility::toLanguageParameters($seo_data);                        
                        $model->meta_title = t($model->meta_title,$seo_data);
                        $model->meta_description = t($model->meta_description,$seo_data);
                    }                    
                    CommonUtility::setSEO($model->meta_title,$model->meta_title, $model->meta_description,$model->meta_keywords , $model->image);
                } catch (Exception $e) {
                    //
                }
            }            
        } else {             
            $website_title = isset($settings['website_title'])?$settings['website_title']:Yii::app()->name;                        
            $new_title = "$website_title - ".ucfirst($controller_action);
            CommonUtility::setSEO($new_title,$new_title);
        }
    }

}
// end class