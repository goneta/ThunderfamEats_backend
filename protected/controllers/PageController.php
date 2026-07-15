<?php
class PageController extends SiteCommon
{
	public function beforeAction($action)
	{				
		// CHECK MAINTENANCE MODE
		$this->maintenanceMode();
				
		return true;
	}
	
	public function actionindex()
	{
		$pathInfo = Yii::app()->request->getPathInfo();		
		$matches = explode('/', $pathInfo);
		if(is_array($matches) && count($matches)>=1){
			$slug_name = isset($matches[0])?$matches[0]:''; 						
			try {
			   $model = PPages::pageDetailsSlug($slug_name , Yii::app()->language );			   			   
			   CommonUtility::setSEO($model->title,$model->meta_title, $model->meta_description,$model->meta_keywords , $model->image);
			   $this->render('//store/page',array(
			    'model'=>$model,
				'responsive'=>AttributesTools::FrontCarouselResponsiveSettings('full'), 
			   ));
			   return ;
			} catch (Exception $e) {
			    //dump(t($e->getMessage()));
			}			
		} 		
		$this->render("//store/404-page");		
	}
			
}
/*end class*/