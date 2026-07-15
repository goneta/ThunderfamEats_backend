<?php
class ThemeController extends CommonController
{
		
	public function beforeAction($action)
	{					
		return true;
	}
	
	public function actionchanger()
	{
		$this->pageTitle=t("Themes");		
		
		//$theme_image_preview = CMedia::themeAbsoluteUrl()."/assets/images/screenshot.png";		
		/*'theme_image_preview'=>$theme_image_preview,
		'theme_name'=>t("{{theme_name}} Theme", array('{{theme_name}}'=>Yii::app()->theme->name) ),*/
		
		$themes = array(); $error = '';
				
		try {
		   $themes = TThemeManager::getSiteTheme( CMedia::homeDir()."/themes" , CMedia::homeUrl()."/themes"  );
		} catch (Exception $e) {
		    $error = t($e->getMessage());
		}			
		$this->render("theme-selection",array(		  
		  'themes'=>$themes,
		  'error'=>$error
		));
	}	
	
	public function actionsettings()
	{
		$this->pageTitle=t("Theme settings");
		CommonUtility::setMenuActive('.sales_channel',".theme_changer");
		$this->render("theme-settings",array(
		   'links'=>array(
				'links'=>array(
				    t("Themes")=>array('theme/changer'),        
				    t("Setting"),
				),
				'homeLink'=>false,
				'separator'=>'<span class="separator">
				<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
		));		
	}
	
	public function actionmenu()
	{
		$this->pageTitle=t("Theme menu");
		CommonUtility::setMenuActive('.sales_channel',".theme_changer");
		$this->render("theme-menu",array(
		   'ajax_url'=>Yii::app()->createUrl("/api"),		   
		   'links'=>array(
				'links'=>array(
				    t("Themes")=>array('theme/changer'),        
				    t("Settings")=>array('theme/settings'),        
				    t("Menu"),
				),
				'homeLink'=>false,
				'separator'=>'<span class="separator">
				<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>'
			)
		));		
	}

	public function actionlayout()
	{
		$this->pageTitle=t("Theme menu");
		CommonUtility::setMenuActive('.sales_channel',".theme_changer");

		$model=new AR_option;
		$model->scenario=Yii::app()->controller->action->id;		

		$options = array('enabled_home_steps','enabled_home_promotional','enabled_signup_section','enabled_mobileapp_section','enabled_social_links');
		
		if(isset($_POST['AR_option'])){
						
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}

			$model->attributes=$_POST['AR_option'];
			if($model->validate()){												
				if(OptionsTools::save($options, $model)){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_settings_saved));
					$this->refresh();
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			}
		}
		
		if($data = OptionsTools::find($options)){
			foreach ($data as $name=>$val) {
				$model[$name]=$val;
			}		
		}

		$this->render("front-layout",array(
		   'ajax_url'=>Yii::app()->createUrl("/api"),		   
		   'model'=>$model,
		   'links'=>array(
				'links'=>array(
				    t("Themes")=>array('theme/changer'),        
				    t("Settings")=>array('theme/settings'),        
				    t("Layout"),
				),
				'homeLink'=>false,
				'separator'=>'<span class="separator">
				<i class="zmdi zmdi-chevron-right"></i><i class="zmdi zmdi-chevron-right"></i></span>',
			)
		));		
	}
		
}
/*end class*/