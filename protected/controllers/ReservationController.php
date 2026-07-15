<?php
class ReservationController extends SiteCommon
{
  
	public function beforeAction($action)
	{				
		if(isset($_GET)){			
			$_GET = Yii::app()->input->xssClean($_GET);			
		}

        // SEO 
		CSeo::setPage();
        CBooking::setIdentityToken();       
        
		return true;
	}

    public function actiondetails()
    {        
        $id = Yii::app()->input->get("id");        
        $this->render('booking_details',[
            'id'=>$id
        ]);
    }

    public function actioncancel()
    {
        $id = Yii::app()->input->get("id");        
        $this->render('booking_cancel',[
            'id'=>$id,
            'back_url'=>Yii::app()->createUrl("/reservation/details",['id'=>$id])
        ]);
    }

    public function actionupdate()
    {
        try {
            $id = Yii::app()->input->get("id");        
            $data = CBooking::getBookingDetails($id);
            $merchant_id = $data['merchant_id'];

            $merchant = CMerchants::get($merchant_id);
            $merchant_uuid = $merchant->merchant_uuid;
            
            $options = OptionsTools::find([
                'booking_enabled','booking_enabled_capcha',''
            ],$merchant_id);
            $booking_enabled = isset($options['booking_enabled'])?$options['booking_enabled']:false;
            $booking_enabled = $booking_enabled==1?true:false;
            $booking_enabled_capcha = isset($options['booking_enabled_capcha'])?$options['booking_enabled_capcha']:false;
            $booking_enabled_capcha = $booking_enabled_capcha==1?true:false;

            $options = OptionsTools::find(['captcha_site_key']);
            $captcha_site_key = isset($options['captcha_site_key'])?$options['captcha_site_key']:'';
            
            $this->render('update_booking',[
                'id'=>$id,
                'merchant_uuid'=>$merchant_uuid,
                'booking_enabled_capcha'=>$booking_enabled_capcha,
                'captcha_site_key'=>$captcha_site_key,
                'back_url'=>Yii::app()->createUrl("/reservation/details",['id'=>$id])
            ]);
        } catch (Exception $e) {
			$this->render("//store/404-page");		
		}
    }
	
} 
// end class