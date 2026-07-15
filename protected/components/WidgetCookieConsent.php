<?php
class WidgetCookieConsent extends CWidget 
{	    
	public function run() {					
                
        $cookie_consent_enabled = isset(Yii::app()->params['settings']['cookie_consent_enabled'])?Yii::app()->params['settings']['cookie_consent_enabled']:false;
		    $cookie_consent_enabled = $cookie_consent_enabled==1?true:false;
        if(!$cookie_consent_enabled){
            return ;
        }
        
        $cookieConsent = isset($_COOKIE['cookieConsent'])?$_COOKIE['cookieConsent']:false;
        $cookieConsentPrefs = isset($_COOKIE['cookieConsentPrefs'])?$_COOKIE['cookieConsentPrefs']:'';
        if(!empty($cookieConsentPrefs)){
          $cookieConsentPrefs = explode(",",$cookieConsentPrefs);
          if(is_array($cookieConsentPrefs) && count($cookieConsentPrefs)>=1){
            $cookieConsent = true;
          }
        }        
        if($cookieConsent){
            return ;
        }
                
        $options = ['cookie_show_preferences','cookie_theme_mode','cookie_theme_primary_color',
            'cookie_theme_dark_color','cookie_theme_light_color',
            'cookie_position','cookie_title','cookie_link_label','cookie_link_accept_button','cookie_link_reject_button',
            'cookie_message','cookie_expiration'
        ];
        $data = OptionsTools::find($options);            
        $cookie_title = isset($data['cookie_title'])?$data['cookie_title']:'';    
        $accept_button = isset($data['cookie_link_accept_button'])?$data['cookie_link_accept_button']:'';
        $reject_button = isset($data['cookie_link_reject_button'])?$data['cookie_link_reject_button']:'';
        $cookie_message = isset($data['cookie_message'])?$data['cookie_message']:'';
        $cookie_link_label = isset($data['cookie_link_label'])?$data['cookie_link_label']:'';
        $cookie_expiration = isset($data['cookie_expiration'])?intval($data['cookie_expiration']):365;          

        $cookie_theme_mode = isset($data['cookie_theme_mode'])?$data['cookie_theme_mode']:'light';    
        $cookie_theme_primary_color = isset($data['cookie_theme_primary_color'])?$data['cookie_theme_primary_color']:'#409eff';    
        $cookie_theme_dark_color = isset($data['cookie_theme_dark_color'])?$data['cookie_theme_dark_color']:'#121212';    
        $cookie_theme_light_color = isset($data['cookie_theme_light_color'])?$data['cookie_theme_light_color']:'#fff';    

        $cookie_position = isset($data['cookie_position'])?$data['cookie_position']:'right';    
        $cookie_show_preferences = isset($data['cookie_show_preferences'])?$data['cookie_show_preferences']:false;            
        $cookie_show_preferences = $cookie_show_preferences==1?true:false;        
        
        try {
          $preferences_data = AR_admin_meta::getTranslationdata('cookie_preferences',Yii::app()->language);
        } catch (Exception $e) {
          $preferences_data = [];
        }
        
        $css_themes='';
        if($cookie_theme_mode=="dark"){         
           $bg_color = $cookie_theme_dark_color;
           $font_color = '#fff';
        } else {
           $bg_color = $cookie_theme_light_color;
           $font_color = '#000';
        }

        $css_themes='
          #vue-cookie-consent .el-notification{
              background-color:'.$bg_color.';
              color:'.$font_color.';
          }
          #vue-cookie-consent .el-notification .el-content-message,
          #vue-cookie-consent .el-notification .el-notification__title,
          #vue-cookie-consent  div.font13,
          #vue-cookie-consent .el-checkbox__label
          {
            color:'.$font_color.';
          }
        ';

        Yii::app()->clientScript->registerCss('consentcss', '
          #vue-cookie-consent .el-dialog{            
            position:fixed;
            bottom:1px;
            right:20px;
          }            
          #vue-cookie-consent .el-dialog__header{
            padding-bottom:0;
          }
          .cookie-consent .cookie-content{            
            text-align:left;
            line-height: 20px;
          }          
          .cookie-consent .cookie-content a{
            color:#3ecf8e !important;
          }
          '.$css_themes.'
        ');
        
        try {
            $page = PPages::pageDetailsByID($cookie_link_label,Yii::app()->language);            
            $page_link  = Yii::app()->createUrl($page->page_slug);
            $privacy_policy_link = "<a href=\"$page_link\" target=\"_blank\">".$page->title."</a>";
            $cookie_message = t($cookie_message,[
                '{{privacy_policy_link}}'=>$privacy_policy_link
            ]);
        } catch (Exception $e) {
        }        

		$this->render('cookie-consent',[
            'cookie_title'=>$cookie_title,
            'accept_button'=>$accept_button,            
            'reject_button'=>$reject_button,
            'cookie_message'=>$cookie_message,
            'preferences_data'=>$preferences_data,
            'cookie_expiration'=>$cookie_expiration>0?$cookie_expiration:365,
            'cookie_theme_mode'=>$cookie_theme_mode,
            'cookie_theme_primary_color'=>$cookie_theme_primary_color,
            'cookie_theme_dark_color'=>$cookie_theme_dark_color,
            'cookie_theme_light_color'=>$cookie_theme_light_color,
            'cookie_position'=>$cookie_position,
            'cookie_show_preferences'=>$cookie_show_preferences
        ]);
	}
	
}
/*end class*/