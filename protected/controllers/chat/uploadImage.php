<?php
require 'intervention/vendor/autoload.php';
use Intervention\Image\ImageManager;

class uploadImage extends CAction
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
                        
            $this->data = $_POST;                        
            $conversation_id = isset($this->data['conversation_id'])?$this->data['conversation_id']:'';
            $allowed_extension = explode(",",Helper_imageType);
		    $maxsize = (integer)Helper_maxSize;            

            if (!empty($_FILES)) {                
                
                $title = $_FILES['file']['name'];   
                $size = (integer)$_FILES['file']['size'];   
                $filetype = $_FILES['file']['type'];   								
                
                if(isset($_FILES['file']['name'])){
                $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                } else $extension = strtolower(substr($title,-3,3));
                
                if(!in_array($extension,$allowed_extension)){			
                    $this->msg = t("Invalid file extension");
                    $this->_controller->responseJson();
                }
                if($size>$maxsize){
                    $this->msg = t("Invalid file size");
                    $this->_controller->responseJson();
                }

                $upload_path = "upload/chat";
                $tempFile = $_FILES['file']['tmp_name'];   								
                $upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');
                $filename = $upload_uuid.".$extension";						
                $path = CommonUtility::uploadDestination($upload_path)."/".$filename;

                $image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];			                
			    $manager = new ImageManager(array('driver' => $image_driver ));	
                $image = $manager->make($tempFile);
                $image->save($path,60);

                $this->_controller->code = 1;
                $this->_controller->msg = "Ok";          
                $this->_controller->details = [
                    'file_url'=>CMedia::getImage($filename,$upload_path),
                    'file_type'=>$filetype
                ];   

            } else$this->_controller->msg = t("Invalid file");                    
            
		} catch (Exception $e) {			
            $this->_controller->msg = t($e->getMessage());	
		}					
        $this->_controller->responseJson();
    }
  
}
// end class