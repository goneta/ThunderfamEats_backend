<?php
class WidgetFacebookPixel extends CWidget 
{	
	public $data=array();	
	
	public function run() {		        
        $enabled_fb_pixel = isset($this->data['enabled_fb_pixel'])?$this->data['enabled_fb_pixel']:false;		
		$fb_pixel_id = isset($this->data['fb_pixel_id'])?$this->data['fb_pixel_id']:'';		                
        
        if($enabled_fb_pixel==1 && !empty($fb_pixel_id)){            
            $this->render('facebook-pixel',[
                'pixel_id'=>$fb_pixel_id,
            ]);
        }
	}
	
}
/*end class*/