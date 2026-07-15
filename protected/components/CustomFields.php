<?php
class CustomFields extends CWidget 
{	
     public function init(){
        
     }

	 public function run(){
        $customFields = AttributesTools::getCustomFields();        
        $this->render("custom-fields",[
            'customFields'=>$customFields
        ]);
     }
}
/*end class*/