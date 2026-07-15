<?php
class WidgetFooterMenu extends CWidget 
{	
	public function run() {		

		$cacheKey = 'yii1cache_footer_menu';
        $data = Yii::app()->cache->get($cacheKey);
		if ($data === false) {
			MMenu::buildMenu(0,false,PPages::menuType());
			$data = MMenu::$items;
			Yii::app()->cache->set($cacheKey, $data, CACHE_LONG_DURATION);
		}		
		$this->render('footer-menu',array(
		  'menu'=>$data
		));
	}
	
}
/*end class*/