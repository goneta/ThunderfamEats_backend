<?php
class CReviews
{
	public static function reviewsCount($merchant_id='')
	{		
		$criteria=new CDbCriteria();
			
		if($merchant_id>0){
			$criteria->condition = "merchant_id=:merchant_id AND status='publish' and parent_id=0";
			$criteria->params = array(
			  ':merchant_id'=>$merchant_id,		  
			);
		} else {
			$criteria->condition = "status='publish' and parent_id=0";			
		}
		
		$dependency = CCacheData::dependency();	
		$count = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->count($criteria); 
		return intval($count);		
	}
	
	public static function totalCountByRange($merchant_id=0, $start='', $end='')
	{
		$criteria=new CDbCriteria();
		
		if($merchant_id>0){
			$criteria->condition = "merchant_id=:merchant_id AND status=:status ";
			$criteria->params = array( 
			    ':merchant_id'=>intval($merchant_id),
			    ':status'=>'publish'
			 );		
		} else {
			$criteria->condition = "status='publish'";			
		}
		
		$criteria->addBetweenCondition("DATE_FORMAT(date_created,'%Y-%m-%d')", $start , $end );
				
		$dependency = CCacheData::dependency();	
		$count = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->count($criteria); 
		return intval($count);
	}
	
	public static function userAddedReview($merchant_id=0,$limit=3)
	{
		$data = array();
		$criteria=new CDbCriteria();
		$criteria->alias = "a";
		$criteria->select = "a.client_id, count(*) as total_review,
		b.first_name,b.last_name,b.date_created, b.avatar as logo, b.path
		";
		$criteria->join='LEFT JOIN {{client}} b on  a.client_id=b.client_id ';
		
		if($merchant_id>0){
			$criteria->condition = "a.merchant_id=:merchant_id and b.client_id IS NOT NULL";
			$criteria->params = array(':merchant_id'=>$merchant_id);
		} else {
			$criteria->condition = "b.client_id IS NOT NULL";			
		}
		
		$criteria->group="a.client_id";
		$criteria->order = "count(*) DESC";	
		$criteria->limit = intval($limit);		
				
		$dependency = CCacheData::dependency();			
		$model = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->findAll($criteria);    
		
		if($model){
			foreach ($model as $item) {				
				$data[]=array(
				  'client_id'=>$item->client_id,
				  'first_name'=>$item->first_name,
				  'last_name'=>$item->last_name,
				  'image_url'=>CMedia::getImage($item->logo,$item->path,'@thumbnail',CommonUtility::getPlaceholderPhoto('customer')),
				);
			}
			return $data;
		}
		return false;
	}
	
	public static function summaryCount($merchant_id=0,$grand_total=0)
	{
		$review_list = array(5,4,3,2,1); $data = array();
		foreach ($review_list as $count) {
			$criteria=new CDbCriteria();
			$criteria->select ="count(*) as total";
			
			if($merchant_id>0){
				$criteria->condition = "merchant_id=:merchant_id AND status=:status AND rating=:rating";
			    $criteria->params = array(
			      ':merchant_id'=>$merchant_id,
			      ':status'=>'publish',
			      'rating'=>$count
			    );			
			} else {
				$criteria->condition = "status=:status AND rating=:rating";
			    $criteria->params = array(			      
			      ':status'=>'publish',
			      'rating'=>$count
			    );			
			}
		    		    
			$dependency = CCacheData::dependency();				
			$model = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
		    $total = isset($model->total)?$model->total:0;
		    
		    if($total>0){
			    $percent = round(($total/$grand_total)*100);
			    $data[] = array(
			      'count'=>$count,
			      'review'=>$percent,
			      'in_percent'=>"$percent%"
			    );
		    } else {
		    	$data[] = array(
			      'count'=>$count,
			      'review'=>0,
			      'in_percent'=>"0%"
			    );
		    }
		}
		return $data;
	}
	
	public static function reviews($merchant_id='',$page=0, $page_limit=10)
	{
		$stmt="
		SELECT a.review,a.rating,
		concat(b.first_name,' ',b.last_name) as fullname,
		b.avatar, b.path,
		a.date_created,a.as_anonymous,
		(
		 select group_concat(meta_name,';',meta_value)
		 from {{review_meta}}
		 where review_id = a.id
		) as meta,
		
		(
		 select group_concat(upload_uuid,';',filename,';',path)
		 from {{media_files}}
		 where upload_uuid IN (
		      select meta_value from {{review_meta}}
		      where review_id = a.id
		   )
		) as media
			
		FROM {{review}} a
		LEFT JOIN {{client}} b
		ON
		a.client_id = b.client_id
		
		WHERE a.merchant_id=".q($merchant_id)."
		AND a.status ='publish'
		AND parent_id = 0
		ORDER BY a.id DESC
		LIMIT $page,$page_limit
		";
		
		//dump($stmt);die();
		if(Yii::app()->params->db_cache_enabled){
		  $dependency = new CDbCacheDependency("SELECT count(*),MAX(date_modified) FROM {{review}} WHERE merchant_id=".q($merchant_id)."  ");
		  $res = Yii::app()->db->cache(Yii::app()->params->cache, $dependency)->createCommand($stmt)->queryAll();		  
		} else $res = Yii::app()->db->createCommand($stmt)->queryAll();	
		
		if($res){
			$data = array();
			foreach ($res as $val) {
				$meta = !empty($val['meta'])?explode(",",$val['meta']):'';
				$media = !empty($val['media'])?explode(",",$val['media']):'';
				
				$meta_data = array(); $media_data=array();
				
				if(is_array($media) && count($media)>=1){
					foreach ($media as $media_val) {
						$_media = explode(";",$media_val);
						$media_data[$_media['0']] = array(
						  'filename'=>$_media[1],
						  'path'=>$_media[2],
						);
					}
				}
							
				if(is_array($meta) && count($meta)>=1){
					foreach ($meta as $meta_value) {
						$_meta = explode(";",$meta_value);						
						if($_meta[0]=="upload_images"){							 
							 if(isset( $media_data[$_meta[1]] )){									 								    
							    $meta_data[$_meta[0]][] = CMedia::getImage(
							      $media_data[$_meta[1]]['filename'],
							      $media_data[$_meta[1]]['path']
							    );
							 }
						} else {
							//$meta_data[$_meta[0]][] = $_meta[1];
							if(isset($meta_data[$_meta[0]])){
								$meta_data[$_meta[0]][] = isset($_meta[1])?$_meta[1]:'';
							}							
						}
					}
				}
				
				$data[]=array(
				  'review'=>Yii::app()->input->xssClean($val['review']),
				  'rating'=>intval($val['rating']),
				  'fullname'=>Yii::app()->input->xssClean($val['fullname']),
				  'hidden_fullname'=>CommonUtility::mask($val['fullname']),				  				  				  
				  'url_image'=>CMedia::getImage($val['avatar'],$val['path'],Yii::app()->params->size_image,
				   CommonUtility::getPlaceholderPhoto('customer')),
				  'as_anonymous'=>intval($val['as_anonymous']),
				  'meta'=>$meta_data,
				  'date_created'=>Date_Formatter::dateTime($val['date_created'])
				);
			}			
			return $data;
		}
		throw new Exception( 'no results' );
	}
	
	public static function getUuid($uiid='')
	{
		$media = AR_media::model()->findAll(
			    'upload_uuid=:upload_uuid',
			    array(':upload_uuid'=>$uiid)
			);
		if($media){
			$data = array();
			foreach ($media as $val) {
				$data[]=array(
				  'url_image'=>CMedia::getImage($val->filename,$val->path),			   
			      'id'=>$val->filename,	
			      'upload_uuid'=>$val->upload_uuid
				);
			}
			return $data;
		}
		return false;
	}	
	
	public static function insertMeta( $review_id='', $meta_name='',$meta_array = array())
	{
		if(is_array($meta_array) && count($meta_array)>=1){
			$data = array();
			foreach ($meta_array as $value) {
				$data[] = array(
				  'review_id'=>intval($review_id),
				  'meta_name'=>trim($meta_name),
				  'meta_value'=>$value,
				  'date_created'=>CommonUtility::dateNow()
				);
			}
			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand('{{review_meta}}',$data);
		    $command->execute();
		}
	}
	
	public static function insertMetaImages( $review_id='', $meta_name='',$meta_array = array())
	{
		if(is_array($meta_array) && count($meta_array)>=1){
			$data = array();			
			foreach ($meta_array as $value) {
				$data[] = array(
				  'review_id'=>intval($review_id),
				  'meta_name'=>trim($meta_name),
				  'meta_value'=>$value['id'],
				  //'meta_value'=>intval($review_id),
				  'date_created'=>CommonUtility::dateNow()
				);
			}
			$builder=Yii::app()->db->schema->commandBuilder;
		    $command=$builder->createMultipleInsertCommand('{{review_meta}}',$data);
		    $command->execute();
		}
	}
		

	public static function reviewsCountDriver($driver_id='')
	{		
		$criteria=new CDbCriteria();
			
		$criteria->condition = "driver_id=:driver_id AND status='publish' and parent_id=0";
		$criteria->params = array(
			':driver_id'=>intval($driver_id),		  
		);

		$dependency = CCacheData::dependency();	
		$count = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->count($criteria); 
		return intval($count);		
	}

	public static function summaryDriver($driver_id=0,$grand_total=0)
	{
		$review_list = array(5,4,3,2,1); $data = array();
		foreach ($review_list as $count) {
			$criteria=new CDbCriteria();
			$criteria->select ="count(*) as total";
			
			$criteria->condition = "driver_id=:driver_id AND status=:status AND rating=:rating";
			$criteria->params = array(
				':driver_id'=>$driver_id,
				':status'=>'publish',
				'rating'=>$count
			);			

			$dependency = CCacheData::dependency();				
			$model = AR_review::model()->cache(Yii::app()->params->cache, $dependency)->find($criteria);
		    $total = isset($model->total)?$model->total:0;
		    
		    if($total>0){
			    $percent = round(($total/$grand_total)*100);
			    $data[] = array(
			      'count'=>$count,
			      'review'=>$percent,
			      'in_percent'=>"$percent%"
			    );
		    } else {
		    	$data[] = array(
			      'count'=>$count,
			      'review'=>0,
			      'in_percent'=>"0%"
			    );
		    }
		}
		return $data;
	}
	
	public static function getUserReviewsByOrder($client_id=0,$merchant_id=0,$order_ids=[])
	{
		$criteria=new CDbCriteria();
		$criteria->select ="order_id";
		$criteria->condition = "client_id=:client_id AND merchant_id=:merchant_id";
		$criteria->params = array(
			':client_id'=>intval($client_id),
			':merchant_id'=>intval($merchant_id)
		);		
		$criteria->addInCondition("order_id",(array)$order_ids);
		$model = AR_review::model()->findAll($criteria);
		if($model){			
			$data = [];
			foreach ($model as $items) {				
				$data[]=$items->order_id;
			}
			return $data;
		}
		return false;
	}

	public static function reviewDetails($user_id='', $order_id='')
	{
		$model = AR_review::model()->find("client_id=:client_id AND order_id=:order_id",[
			':client_id'=>$user_id,
			':order_id'=> $order_id
		]);
		if($model){
			return $model;
		}
		return false;
	}

	public static function driverReviewDetails($user_id='', $order_id='')
	{
		$model = AR_driver_reviews::model()->find("customer_id=:customer_id AND order_id=:order_id",[
			':customer_id'=>$user_id,
			':order_id'=> $order_id
		]);
		if($model){
			return $model;
		}
		return false;
	}

	public static function getMeaning($rating=0,$restaurant_name='')
	{
		$data = [];
		switch ($rating) {
			case 1:		
				$data = [
					'title'=>t("We're Sorry About Your Experience"),
					'subtitle'=>t("We're sorry to hear that your experience wasn't great. We appreciate your feedback and will work on improving!")
				];	
				break;
			case 2:	
				$data = [
					'title'=>t("We Appreciate Your Feedback!"),
					'subtitle'=>t("We understand this wasn't the best experience. Thanks for sharing your thoughts—we’ll strive to do better!")
				];				
				break;
			case 3:			
				$data = [
					'title'=>t("Thanks for Your Honest Review!"),
					'subtitle'=>t("Thanks for your feedback! We hope to make your next visit even better!")
				];					
				break;		
			case 4:		
				$data = [
					'title'=>t("Happy to Hear You Enjoyed It!"),
					'subtitle'=>t("Glad you enjoyed it! We're always looking to improve hope to see you again soon!")
				];						
				break;				
			case 5:		
				$data = [
					'title'=>t("Awesome! We're So Glad You Loved It!"),
					'subtitle'=>t("We're thrilled you loved it! Add {restaurant_name} to your Favorites so next time you're craving it, finding it will be a breeze!",[
						'{restaurant_name}'=>$restaurant_name
					])
				];						
				break;
			default:
				$data = [
					'title'=>t("Awesome! We're So Glad You Loved It!"),
					'subtitle'=>t("We're thrilled you loved it! Add {restaurant_name} to your Favorites so next time you're craving it, finding it will be a breeze!",[
						'{restaurant_name}'=>$restaurant_name
					])
				];			
			   break;
		}
		return $data;
	}

}
/*end class*/