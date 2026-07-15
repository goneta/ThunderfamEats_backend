<?php
class WebsiteController extends CommonController
{
    public function beforeAction($action)
	{										
		InlineCSTools::registerStatusCSS();
		return true;
	}

    public function actionIndex()
    {
        $this->actionseosetup();
    }

    public function actionseosetup()
    {
        $this->pageTitle=t("SEO Setup");
		$action_name='seo_pages_list';
		$delete_link = Yii::app()->CreateUrl("website/pages_delete");
		
		ScriptUtility::registerScript(array(
		  "var action_name='$action_name';",
		  "var delete_link='$delete_link';",
		),'action_name');
									
		$this->render( 'seo_setup' ,array(
			'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/pages_create"),
            'settings'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/seo_settings"),
		));        
    }

    public function actionpage_update()
    {
        $this->actionpages_create(true);
    }

    public function actionpages_create($update=false)
    {
        $this->pageTitle = $update==false? t("Add seo page") : t("Update seo page");
		CommonUtility::setMenuActive('.sales_channel',".website_seosetup");

        if($update){
			$id = (integer) Yii::app()->input->get('id');	
			$model = AR_pages_seo::model()->findByPk( $id );	
					
			if(!$model){				
				$this->render("error");				
				Yii::app()->end();
			}	
			
		} else {			
			$model=new AR_pages_seo;							
		}
        
        if(isset($_POST['AR_pages_seo'])){

            if(DEMO_MODE){			
                $this->render('//tpl/error',array(  
                      'error'=>array(
                        'message'=>t("Modification not available in demo")
                      )
                    ));	
                return false;
            }
                
            $model->attributes=$_POST['AR_pages_seo'];
            $model->slug = CommonUtility::toSeoURL($model->meta_title);
            if($model->validate()){                                
                if($model->save()){
					if(!$update){
					   $this->redirect(array('website/seosetup'));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
            } else Yii::app()->user->setFlash('error',  CommonUtility::parseModelErrorToString($model->getErrors()) );
        }

        $data  = array();
        if($update){
			$translation = AttributesTools::pagesTranslation2($id);            
			$data['meta_title_translation'] = isset($translation['meta_title'])?$translation['meta_title']:'';
			$data['meta_description_translation'] = isset($translation['meta_description'])?$translation['meta_description']:'';
            $data['meta_keywords_translation'] = isset($translation['meta_keywords'])?$translation['meta_keywords']:'';            
		}

        $fields = [];
        $fields[]=array(
            'name'=>'meta_title_translation',
            'placeholder'=>"Enter [lang] meta title here"
        );
        $fields[]=array(
            'name'=>'meta_description_translation',
            'placeholder'=>"Enter [lang] meta description here"
        );
        $fields[]=array(
            'name'=>'meta_keywords_translation',
            'placeholder'=>"Enter [lang] meta keywords here"
        );

        $this->render("pages_create",[
            'model'=>$model,
            'links'=>array(
	            t("SEO Setup")=>array('website/seosetup'),        
                $this->pageTitle,
		    ),
            'fields'=>$fields,
            'language'=>AttributesTools::getLanguage(),
            'status_list'=>(array)AttributesTools::StatusManagement('post'),
            'data'=>$data,
        ]);
    }

    public function actionpages_delete()
    {

        if(DEMO_MODE){			
			$this->render('//tpl/error',array(  
				  'error'=>array(
					'message'=>t("Modification not available in demo")
				  )
				));	
			return false;
		}
				        
        $id = (integer) Yii::app()->input->get('id');			
		$model = AR_pages_seo::model()->findByPk( $id );
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array('website/seosetup'));			
		} else $this->render("error");
    }

    public function actionseo_settings()
    {
        $this->pageTitle = t("Settings");
		CommonUtility::setMenuActive('.sales_channel',".website_seosetup");

        $model = new AR_admin_meta;
		$save_post = [
			'seo_page_homepage','seo_page_search','seo_page_contactus','seo_page_cuisine','seo_page_menu',
            'seo_page_login','seo_page_signup','seo_page_manage_account','seo_page_change_password','seo_page_user_order',
            'seo_page_user_address','seo_page_user_saved_payments','seo_page_user_saved_store','seo_page_restaurant_signup',
            'seo_page_checkout','seo_page_guest_checkout','seo_page_table_booking','seo_page_manage_table_booking','seo_page_tracking_orderpage'
		];

        if(isset($_POST['AR_admin_meta'])){
			if(DEMO_MODE){			
			    $this->render('//tpl/error',array(  
			          'error'=>array(
			            'message'=>t("Modification not available in demo")
			          )
			        ));	
			    return false;
			}
					
			$post = $_POST['AR_admin_meta'];            
			
			foreach ($save_post as $items) {				                   
				 $model->saveMeta($items , isset($post['seo_page'][$items])?$post['seo_page'][$items]:'' );			 
			}            									
			
			Yii::app()->user->setFlash('success',CommonUtility::t(Helper_success));
			$this->refresh();			
		} else {
			$criteria = new CDbCriteria;
			$criteria->addInCondition('meta_name',(array) $save_post );            
			$find = AR_admin_meta::model()->findAll($criteria);		
			if($find){
				foreach ($find as $items) {										                    					
                    $model->seo_page[$items->meta_name] = $items->meta_value;
				}                
			}	
		}		
        
        $page_list = CommonUtility::getDataToDropDown("{{pages}}",'page_id','title',"Where owner='seo' AND status='publish'");

        $fields = [];
		$fields['seo_page']['seo_page_homepage'] = t("Homepage");
		$fields['seo_page']['seo_page_search'] = t("Search results");
		$fields['seo_page']['seo_page_contactus'] = t("Contact us");
        $fields['seo_page']['seo_page_cuisine'] = t("Cuisine");
        $fields['seo_page']['seo_page_menu'] = t("Menu");
        $fields['seo_page']['seo_page_login'] = t("Login");
        $fields['seo_page']['seo_page_signup'] = t("Signup page");
        $fields['seo_page']['seo_page_manage_account'] = t("Manage account");
        $fields['seo_page']['seo_page_change_password'] = t("Change password");
        $fields['seo_page']['seo_page_user_order'] = t("User Orders");
        $fields['seo_page']['seo_page_user_address'] = t("User address");
        $fields['seo_page']['seo_page_user_saved_payments'] = t("User saved payments");
        $fields['seo_page']['seo_page_user_saved_store'] = t("User saved store");
        $fields['seo_page']['seo_page_restaurant_signup'] = t("Restaurant signup");
        $fields['seo_page']['seo_page_checkout'] = t("Checkout");
        $fields['seo_page']['seo_page_guest_checkout'] = t("Guest checkout");
        $fields['seo_page']['seo_page_table_booking'] = t("Table booking");
        $fields['seo_page']['seo_page_manage_table_booking'] = t("Manage table booking");
        $fields['seo_page']['seo_page_tracking_orderpage'] = t("Tracking order page");

        $this->render( 'seo_settings' ,array(
            'model'=>$model,
			'links'=>array(
	            t("SEO Setup")=>array('website/seosetup'),        
                $this->pageTitle,
		    ),
            'page_list'=>$page_list,
            'fields'=>$fields,	
		));        
    }

    public function actionsitemap()
    {
        $this->pageTitle = t("XML Sitemap");
        $this->render("sitemap",[
            'sitemap_url'=>CommonUtility::getHomebaseUrl()."/sitemap.xml",
            'links'=>array(
	            t("Website")=>array('website/sitemap'),        
                $this->pageTitle,
		    ),
        ]);
    }

}
// end class