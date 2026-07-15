<?php
class MarketingController extends CommonController
{
		
	public function beforeAction($action)
	{				
		InlineCSTools::registerStatusCSS();
		InlineCSTools::registerOrder_StatusCSS();
			
		return true;
	}
    
	public function actionbanner_list()
	{
				
		$this->pageTitle=t("Banner");		
				
		$table_col = array(		  
		  'banner_id'=>array(
		    'label'=>t("ID"),
		    'width'=>'15%'
		  ),		  
		  'photo'=>array(
		    'label'=>t("Banner"),
		    'width'=>'15%'
		  ),
		  'status'=>array(
		    'label'=>t("Status"),
		    'width'=>'20%'
		  ),
		  'title'=>array(
		    'label'=>t("Title"),
		    'width'=>'20%'
		  ),		  
		  'banner_type'=>array(
		    'label'=>t("Type"),
		    'width'=>'20%'
		  ),		  
		  'banner_uuid'=>array(
		    'label'=>t("Actions"),
		    'width'=>'20%'
		  ),
		);
		$columns = array(
			array('data'=>'banner_id', 'visible'=>false),
			array('data'=>'photo'),
			array('data'=>'status'),
			array('data'=>'title'),			
			array('data'=>'banner_type'),			
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">
				  <a class="ref_edit normal btn btn-light tool_tips"><i class="zmdi zmdi-border-color"></i></a>
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//marketing/banner_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/banner_create"),
		  'sort_link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/banner_sort")
		));
	}

	public function actionbanner_create($update=false)
	{
		$this->pageTitle=t("Create Banner");
		CommonUtility::setMenuActive('.marketing','.marketing_banner_list');
		$upload_path = CMedia::adminFolder();
		$selected_item = array();
		
		$id = Yii::app()->input->get('id');			
		$model = new AR_banner;
		if($update){
			$model = AR_banner::model()->find("banner_uuid=:banner_uuid",array(
			 ':banner_uuid'=>$id
			));
			if(!$model){
				$this->render('//tpl/error',array(
				 'error'=>array(
				   'message'=>t(Helper_not_found)
				 )
				));
				return ;
			}

			if($model->banner_type=="food"){
				$selected_item = CommonUtility::getDataToDropDown("{{item}}",'item_id','item_name',
			    "WHERE item_id=".q($model->meta_value2)."");			
			} else if ( $model->banner_type=='custom_link'){
				$model->custom_link = $model->meta_value3;
			} else {
				$selected_item = CommonUtility::getDataToDropDown("{{merchant}}",'merchant_id','restaurant_name',
			    "WHERE merchant_id=".q($model->meta_value1)."");			
			}			
		}

        $model->scenario = 'admin_banner';
        
		if(isset($_POST['AR_banner'])){

			$model->attributes=$_POST['AR_banner'];						
						
			switch ($model->banner_type) {
				case 'food':
					$model->scenario ="food_banner";					
					$items = AR_item::model()->find("item_id=:item_id",[
						':item_id'=>$model->meta_value2
					]);
					if($items){						
						$merchant = CMerchants::get($items->merchant_id);						
						$model->meta_slug = $merchant->restaurant_slug."/menu/items/".$items->slug;						
					}					
					break;
				case 'restaurant_featured':
					$model->scenario ="admin_featured";					
					$model->meta_slug = $model->meta_value3;	
					break;
				case 'cuisine':
					$model->scenario ="admin_cuisine";										
					$model_cuisine = AR_cuisine::model()->find("cuisine_id=:cuisine_id",[
						':cuisine_id'=>$model->meta_value4
					]);
					if($model_cuisine){						
						$model->meta_slug = $model_cuisine->slug;
					}					
					break;
				case 'custom_link':
					$model->scenario ="admin_custom_link";					
					$model->meta_value3 = $model->custom_link;	
					break;

				default:
				    $model->scenario ="admin_banner";					
					$merchant = CMerchants::get($model->meta_value1);					
					$model->meta_slug = $merchant->restaurant_slug;					
					break;
			}

			$model->owner="admin";			

			if(isset($_POST['photo'])){
				if(!empty($_POST['photo'])){
					$model->photo = $_POST['photo'];
					$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
				} else $model->photo = '';
			} else $model->photo = '';
									
			if($model->validate()){										
				if($model->save()){
					if(!$update){
					   $this->redirect(array(Yii::app()->controller->id."/banner_list"));		
					} else {
						Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
						$this->refresh();
					}
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}

		$settings = OptionsTools::find(['home_search_mode','default_distance_unit']);
		$home_search_mode = isset($settings['home_search_mode'])?$settings['home_search_mode']:'address';
		$home_search_mode = !empty($home_search_mode)?$home_search_mode:'address';		
		$default_distance_unit = isset($settings['default_distance_unit'])?$settings['default_distance_unit']:'mi';
		$default_country = null;
		if($home_search_mode=="location"){
			$default_country = Clocations::getDefaultCountryDetails();		
		}		

		$location_data = [];
		if($home_search_mode=="location"){	
			$model->country_id = $model->country_id>0?$model->country_id:(isset($default_country['country_id'])?$default_country['country_id']:'');
			$location_data['country'] =[
				'value'=>$model->country_id,
				'label'=>AttributesTools::findCountry($model->country_id)
			];
			if($model->state_id>0){
				$location_data['state'] =[
					'value'=>$model->state_id,
					'label'=>AttributesTools::findState($model->state_id)
				];
			}
			if($model->city_id>0){
				$location_data['city'] =[
					'value'=>$model->city_id,
					'label'=>AttributesTools::findCity($model->city_id)
				];
			}
			if($model->area_id>0){
				$location_data['area'] =[
					'value'=>$model->area_id,
					'label'=>AttributesTools::findArea($model->area_id)
				];
			}						
		}
						
		$this->render('//marketing/banner_create',array(		  
		  'model'=>$model,
		  'status'=>(array)AttributesTools::StatusManagement('post'),
		  'banner_type'=>AttributesTools::BannerType2(),
		  'restaurant_featured'=>AttributesTools::MerchantFeatured(),
		  'cuisine_list'=>AttributesTools::ListSelectCuisine(),
		  'upload_path'=>$upload_path,
		  'items'=>$selected_item,
		  'links'=>array(
			    t("Banner")=>array('marketing/banner_list'),        
			    $this->pageTitle,
		  ),
		  'home_search_mode'=>$home_search_mode,
		  'default_country'=>$default_country,
		  'location_data'=>$location_data,
		  'units'=> AttributesTools::unit()
		));
	}
	
	public function actionbanner_update()
	{
		$this->actionbanner_create(true);
	}	    

    public function actionbanner_delete()
	{
		$id = Yii::app()->input->get('id');			
				
		$model = AR_banner::model()->find('owner=:owner AND banner_uuid=:banner_uuid', 
		array(':owner'=>'admin', ':banner_uuid'=>$id ));
		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/banner_list'));			
		} else $this->render("error");
	}

	public function actionbanner_sort()
	{
		$this->pageTitle=t("Banner Sort");
		CommonUtility::setMenuActive('.marketing',".marketing_banner_list");

		$data = [];
		$model = new AR_banner_sort();

		if(isset($_POST['id'])){
			$data = $_POST['id'];
			if(is_array($data) && count($data)>=1){				
				foreach ($data as $index=> $banner_id) {					
					$model = AR_banner_sort::model()->find("banner_id=:banner_id",[						
						':banner_id'=>intval($banner_id)
					]);
					if($model){
						$model->sequence = $index;
						if(!$model->save()){							
						}
					}
				}
				CCacheData::add();
				Yii::app()->user->setFlash('success',CommonUtility::t(Helper_update));
				$this->refresh();
			}						
		}
		
		try {
			$data_item = CMerchants::getBanner(0,'admin');
			foreach ($data_item as $items) {
				$data[] = [
					'id'=>$items['banner_id'],
					'name'=>$items['title'],
					'url_image'=>$items['image'],
					'url_icon'=>$items['image'],
				];
			}
		} catch (Exception $e) {			
		}		
				
		$this->render('//tpl/sort',[
			'data'=>$data,
			'model'=>$model,
			'links'=>array(
	            t("All Banner")=>array(Yii::app()->controller->id.'/banner_list'),        
                $this->pageTitle,
		    ),	    	
		]);
	}

	public function actionnotification()
	{
				
		$this->pageTitle=t("Push notification");		
				
		$table_col = array(		  
		  'push_uuid'=>array(
		    'label'=>t("ID"),
		    'width'=>'15%'
		  ),		  
		  'title'=>array(
		    'label'=>t("Title"),
		    'width'=>'15%'
		  ),
		  'body'=>array(
		    'label'=>t("Body"),
		    'width'=>'15%'
		  ),
		  'image'=>array(
		    'label'=>t("Image"),
		    'width'=>'15%'
		  ),		  
		  'channel_device_id'=>array(
		    'label'=>t("Channel/Device"),
		    'width'=>'15%'
		  ),		  
		  'status'=>array(
		    'label'=>t("Status"),
		    'width'=>'15%'
		  ),		  
		  'date_created'=>array(
		    'label'=>t("Actions"),
		    'width'=>'10%'
		  ),
		);
		$columns = array(
			array('data'=>'push_uuid', 'visible'=>false),
			array('data'=>'title'),
			array('data'=>'body'),
			array('data'=>'image'),			
			array('data'=>'channel_device_id'),			
			array('data'=>'status'),			
			array('data'=>null,'orderable'=>false,
			   'defaultContent'=>'
			   <div class="btn-group btn-group-actions" role="group">				
				  <a class="ref_delete normal btn btn-light tool_tips"><i class="zmdi zmdi-delete"></i></a>
			   </div>
			   '
			),	  
		);		
		
		$this->render('//marketing/push_list',array(
		  'table_col'=>$table_col,
		  'columns'=>$columns,
		  'order_col'=>1,
          'sortby'=>'desc',		  
          'ajax_url'=>Yii::app()->createUrl("/api"),
		  'link'=>Yii::app()->CreateUrl(Yii::app()->controller->id."/push_new")
		));
	}

	public function actionpush_new()
	{
		$this->pageTitle=t("Create new push notifications");
		CommonUtility::setMenuActive('.marketing','.marketing_notification');
		$upload_path = CMedia::adminFolder();
		$selected_item = array();
				
		$upload_path = CMedia::adminFolder();
		$model = new AR_push;
		
		if(isset($_POST['AR_push'])){
			$model->attributes=$_POST['AR_push'];	
			$model->provider = "firebase";
			//$model->platform = "android";
			$model->push_type = "broadcast";

			if(isset($_POST['image'])){
				if(!empty($_POST['image'])){
					$model->image = $_POST['image'];
					$model->path = isset($_POST['path'])?$_POST['path']:$upload_path;
				} else $model->image = '';
			} else $model->image = '';

			if($model->validate()){										
				if($model->save()){
					Yii::app()->user->setFlash('success',CommonUtility::t(Helper_created));
					$this->refresh();					
				} else Yii::app()->user->setFlash('error',t(Helper_failed_update));
			} else Yii::app()->user->setFlash('error', CommonUtility::parseModelErrorToString( $model->getErrors(),"<br/>" ));
		}
		
		$this->render('//marketing/push_new',array(		  
		  'model'=>$model,
		  'channel'=>AttributesTools::Channel(),
		  'platform_list'=>AttributesTools::PlatformList(),
		  'upload_path'=>$upload_path,
		  'links'=>array(
			    t("Push notification")=>array('marketing/notification'),        
			    $this->pageTitle,
			)
		));
	}

	public function actionnotification_delete()
	{
		$id = Yii::app()->input->get('id');			
						
		$model = AR_push::model()->find('push_uuid=:push_uuid',[
			':push_uuid'=>$id
		]);
		
		if($model){
			$model->delete(); 
			Yii::app()->user->setFlash('success', t("Succesful") );					
			$this->redirect(array(Yii::app()->controller->id.'/notification'));			
		} else $this->render("//tpl/error");
	}

	public function actionfeatured_items()
	{
		$this->pageTitle=t("Featured Items");

		$filter_by = AttributesTools::filterByList();

		ScriptUtility::registerScript(array(			
			"var filter_by='".CJavaScript::quote(json_encode($filter_by))."';",			
		),'filter_by');		

		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/featured_items.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);

		$this->render('featured_items');
	}

	public function actionsuggested_items()
	{
		$this->pageTitle=t("Suggested Items");

		$filter_by = AttributesTools::filterByList();

		ScriptUtility::registerScript(array(			
			"var filter_by='".CJavaScript::quote(json_encode($filter_by))."';",			
		),'filter_by');		

		Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl."/assets/js/featured_items_suggest.js?time=".time(),CClientScript::POS_END,[
			'type'=>"module",
			'defer'=>'defer'
		]);

		$this->render('featured_items_suggest');
	}
		
}
// end class