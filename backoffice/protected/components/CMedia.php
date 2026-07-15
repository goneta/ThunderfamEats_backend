<?php
class CMedia
{
	public static function homeDir()
	{
		return Yii::getPathOfAlias('home_dir');	
	}
	
	public static function homeUrl()
	{
		if(IS_FRONTEND){
			return Yii::app()->createAbsoluteUrl("/");
		} else return websiteDomain().Yii::app()->request->baseUrl."/..";		
	}

	public static function merchantFolder()
	{
		return "upload/".Yii::app()->merchant->merchant_id;
	}
	
	public static function adminFolder()
	{
		return "upload/all";
	}
	
	public static function avatarFolder()
	{
		return "upload/avatar";
	}

	public static function qrcodeFolder()
	{
		return "upload/qrcode";
	}
	
	public static function themeAbsoluteUrl()
	{
		return Yii::app()->getBaseUrl(true)."/themes/".Yii::app()->theme->name;
	}
	
	public static function getImage($filename='',$path='',$size='' , $fallback_image='')
	{
		$image_url='';
		$image_path_size='';
		$image_path  = self::homeDir()."/$path/$filename";
		
		if(!empty($filename)){
		   $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		   $filename_without_extension = str_replace(".$extension","",$filename);		
		   $filename_of_size = "$filename_without_extension$size.$extension";		   
		   $image_path_size  = self::homeDir()."/$path/$filename_of_size";		   
		}		
						
		if( !empty($filename) &&  file_exists($image_path)){			
			if(!empty($image_path_size) && file_exists($image_path_size) ){
				$image_url = self::homeUrl()."/$path/$filename_of_size";
			} else $image_url = self::homeUrl()."/$path/$filename";
		} else {			
			$avatar_path =  self::homeDir()."/".CMedia::adminFolder()."/$fallback_image";			
			if(file_exists($avatar_path) && !empty($fallback_image)){
				$image_url = self::homeUrl()."/".CMedia::adminFolder()."/$fallback_image";
			} else {
				if (php_sapi_name() !== 'cli') {
					$theme_url = websiteDomain()."/".Yii::app()->theme->baseUrl;
				} else {								
					$theme_url = websiteUrl()."/themes/".Yii::app()->params['theme'];
				}				
				$image_url = $theme_url."/assets/images/$fallback_image";			
			}
		}
		return $image_url;
	}
	
	public static function getFilenameSize($file=array(),$sizes=array())
	{
		$data = array();
		if(is_array($sizes) && count($sizes)>=1){
	    	foreach ($sizes as $size=>$item_size) {
	    		foreach ($file as $filename) {
	    			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
	    			$filename_without_extension = str_replace(".$extension","",$filename);		
		            $filename_of_size = "$filename_without_extension$size.$extension";
		            $data[] = $filename_of_size;           
	    		}
	    	}
	    	return $data;
	    }
	    return false;
	}
	
	public static function deleteFilesInArray($files=array(),$path='')
	{		
		if(is_array($files) && count($files)>=1){
		   foreach ($files as $items) {		   	 
		   	   $file_path = self::homeDir()."$path/$items";		   	   
		   	   if(file_exists($file_path)){		   	   	  
		   	   	  @unlink($file_path);
		   	   }
		   }		   
		}
	}
}
/*end class*/