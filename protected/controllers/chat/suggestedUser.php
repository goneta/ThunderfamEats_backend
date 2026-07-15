<?php
class suggestedUser extends CAction
{
    public $_controller;
    public $_id;
    public $data;

    public function __construct($controller,$id)
    {              
       $this->_controller=$controller;
       $this->_id=$id;
    }

    public function run()
    {        
        try {

            $this->_controller->responseJson();
            
            $data = [];
            $this->data = $this->_controller->data;                        
            $search_type = isset($this->data['search_type'])?$this->data['search_type']:null;
            
            if($search_type!=null){
                $search = CommonUtility::arrayToQueryParameters($search_type);                
                $stmt = "SELECT * FROM {{view_user_union}}
                WHERE user_type IN ($search)
                ORDER BY RAND()
                LIMIT 0,50
                ";                
                if($result = CCacheData::queryAll($stmt)){
                    foreach ($result as $items) {
                        $photo = CMedia::getImage($items['avatar'],$items['path'],'@thumbnail',CommonUtility::getPlaceholderPhoto('customer'));                        
                        $data[] = [
                            'client_uuid'=>$items['uuid'],
                            'first_name'=>$items['first_name'],
                            'last_name'=>$items['last_name'],
                            'user_type'=>$items['user_type'],
                            'photo'=>$items['avatar'],
                            'photo_url'=>$photo,
                        ];
                    }
                    $this->_controller->code = 1;
                    $this->_controller->msg = "Ok";
                    $this->_controller->details = $data;
                } else $this->_controller->msg = t(HELPER_NO_RESULTS);
            } else $this->_controller->msg = t("Invalid search type");
		} catch (Exception $e) {			
            $this->_controller->msg = t($e->getMessage());	
		}					
        $this->_controller->responseJson();
    }
  
}
// end class