<?php
class BackCustomFields extends CWidget 
{	
     public $data;
     public function init(){
        
     }

	 public function run(){
        $customFields = AttributesTools::getCustomFields();        
        $this->render("back-custom-fields",[
            'customFields'=>$customFields,
            'data'=>$this->data
        ]);
     }
}
/*end class*/