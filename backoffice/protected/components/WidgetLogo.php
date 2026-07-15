<?php
class WidgetLogo extends CWidget 
{
	public $class_name, $link;
	
	public function run() {		
		$option = OptionsTools::find(['website_logo','mobilelogo']);
		$website_logo = isset($option['website_logo'])?$option['website_logo']:'';
		$image_url = CMedia::getImage($website_logo, CMedia::adminFolder());		
		$this->render('backend-logo',[
			'class_name'=>$this->class_name,
			'website_logo'=>$website_logo,
			'image_url'=>$image_url,
            'link'=>$this->link
		]);
	}
	
}
/*end class*/