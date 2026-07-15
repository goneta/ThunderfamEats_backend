<?php
require 'intervention/vendor/autoload.php';
use Intervention\Image\ImageManager;

class CImageUploader{

    public static function saveBase64Image($data='',$image_type='',$folder='')
    {
        if($data=="dev"){
            return [
                'filename'=>'test.png',
                'path'=>'/upload'
            ];     
        }

        $data = base64_decode($data);

        $upload_uuid = CommonUtility::createUUID("{{media_files}}",'upload_uuid');   
        $filename = "$upload_uuid.$image_type";	
        $path = CommonUtility::uploadDestination($folder)."/".$filename;	

        $image_set_width = isset(Yii::app()->params['settings']['review_image_resize_width']) ? intval(Yii::app()->params['settings']['review_image_resize_width']) : 0;
		$image_set_width = $image_set_width<=0?500:$image_set_width;

        $image_driver = !empty(Yii::app()->params['settings']['image_driver'])?Yii::app()->params['settings']['image_driver']:Yii::app()->params->image['driver'];
        $manager = new ImageManager(array('driver' => $image_driver ));	
        $image = $manager->make($data);

        $image_width = $manager->make($data)->width();
        if($image_width>$image_set_width){
            $image->resize(null, $image_set_width, function ($constraint) {
                $constraint->aspectRatio();
            });
            $image->save($path);
        } else {
            $image->save($path,60);
        }		

        return [
            'filename'=>$filename,
			'path'=>$folder
        ];     
    }

}
// end class