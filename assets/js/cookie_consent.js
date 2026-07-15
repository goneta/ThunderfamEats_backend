(function($) {
    "use strict";
    
    jQuery(document).ready(function() {
    
    var dump = function(data)
    {
        console.debug(data);
    }
    
    var empty = function(data){	
        if (typeof data === "undefined" || data==null || data=="" || data=="null" || data=="undefined" ) {	
            return true;
        }
        return false;
    };

    var setCookie = function(cname, cvalue, exdays){
        const d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        let expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }
      
    var getCookie = function(cname, cvalue, exdays){
        let name = cname + "=";
        let ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
          let c = ca[i];
          while (c.charAt(0) == ' ') {
            c = c.substring(1);
          }
          if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
          }
        }
        return "";
    }    

    const $timeout = 20000;

    const ComponentsCookieConsent = {
        props : ['label','preferences_data','cookie_expiration','themes','show_preferences'],
        data() {
            return {
                consent_visible : false,
                cookie_preferences : [],
                is_customize : false
            }
        },
        created() {       
            this.consent_visible = true;                        
        },        
        computed: {
            classObject(){
                let $class_position='left';
                if(this.themes.cookie_position=="top_right"){
                    $class_position="right";
                } else if( this.themes.cookie_position=="bottom_right"){
                    $class_position="right";
                } else if( this.themes.cookie_position=="bottom_left"){
                    $class_position="left";
                } else if( this.themes.cookie_position=="top_left"){
                    $class_position="left";
                }                
                return $class_position;
            },
            styleObject(){                             
                if(this.themes.cookie_position=="top_right" || this.themes.cookie_position=="top_left" ){
                    return {
                        top : '16px',
                        'z-index' : '2003'
                    };
                } else {
                    return {
                        bottom : '16px',
                        'z-index' : '2003'
                    };
                }                
            },
            isOkToAccept(){
                if(this.is_customize){
                    if (Object.keys(this.cookie_preferences).length > 0) {
                        //
                    } else {
                        return true;
                    }
                }
                return false;
            }
        },
        methods: {
            hasData(){
                if (Object.keys(this.cookie_preferences).length > 0) {
                    return true;
                }
                return false;
            },
            showConsent(){
                let $html='';
                $html+="<div class=\"cookie-content mb-2\">"
                $html+=this.label.cookie_message;
                $html+="</div>"
                $html+='<div class="d-flex align-items-center">';
                $html+='<button @click="test" type="button" class="btn btn-primary">'+this.label.accept_button+'</button>';
                $html+='<div>';
                ElementPlus.ElNotification({
                    title: this.label.cookie_title,
                    dangerouslyUseHTMLString: true,
                    message: $html,
                    position: 'bottom-left',
                    duration : 4500*1000,
                    customClass: 'cookie-consent'
                });
            },          
            close(){
                this.consent_visible = false;
            },
            accept(){                
                if(this.hasData()){                    
                    if (Object.keys(this.cookie_preferences).length > 0) {
                        let $data = [];
                        Object.entries(this.cookie_preferences).forEach(([key, items]) => {                            
                            $data.push(items);
                        });                        
                    }
                    setCookie('cookieConsentPrefs',this.cookie_preferences,this.cookie_expiration);
                } else {                    
                    if (Object.keys(this.preferences_data).length > 0) {
                        let $data = [];
                        Object.entries(this.preferences_data).forEach(([key, items]) => {                            
                            $data.push(items.preferences);
                        });                        
                        setCookie('cookieConsentPrefs',$data,this.cookie_expiration);
                    }
                }
                setCookie('cookieConsent',true, this.cookie_expiration);
                this.consent_visible = false;
            },
            decline(){
                dump("decline");
                setCookie('cookieConsent',false, this.cookie_expiration);
                this.consent_visible = false;
            },                    
        },
        template:`
        <div v-if="consent_visible" id="notification_1" class="el-notification cookie-consent" :class="classObject" :style="styleObject" role="alert">          
          <div class="el-notification__group">
             <h2 class="el-notification__title">{{label.cookie_title}}</h2>
             <div class="el-notification__content cookie-content" >            
                 <span class="el-content-message" v-html="label.cookie_message"></span>
                 
                 <template v-if="show_preferences">
                 <div class="mt-2 d-flex align-items-center">
                    <i class="zmdi zmdi-settings mr-2"></i> <el-button type="text" size="small" link @click="is_customize=!is_customize"  >{{label.customize}}</el-button>                    
                 </div>                           
                 <div v-if="is_customize" class="mt-2 mb-2">
                   <div class="font13">{{label.select_cookies}}</div>                   
                   <div class="d-flexx">
                        <el-checkbox-group v-model="cookie_preferences">
                          <template v-for="pref in preferences_data">
                          <el-checkbox :label="pref.preferences">{{pref.title}}</el-checkbox>
                          </template>
                        </el-checkbox-group>                    
                   </div>
                 </div>
                 </template>

                 <div class="mt-3 mb-2 row">                               
                   <div class="col"><el-button type="primary" class="w-100" round @click="accept" :color="themes.cookie_theme_primary_color"                   
                   :disabled="isOkToAccept"
                    >
                     {{label.accept_button}}</el-button>
                   </div>
                   <div class="col">
                     <el-button round class="w-100" @click="decline" :type="themes.cookie_theme_mode=='dark'?'info':''" >
                      {{label.reject_button}}
                      </el-button>
                   </div>
                 </div>                                 
             </div>
             <i class="el-icon el-notification__closeBtn" @click="close" >
              <svg class="icon" width="200" height="200" viewBox="0 0 1024 1024" xmlns="http://www.w3.org/2000/svg">
                <path fill="currentColor" d="M764.288 214.592L512 466.88 259.712 214.592a31.936 31.936 0 00-45.12 45.12L466.752 512 214.528 764.224a31.936 31.936 0 1045.12 45.184L512 557.184l252.288 252.288a31.936 31.936 0 0045.12-45.12L557.12 512.064l252.288-252.352a31.936 31.936 0 10-45.12-45.184z"></path>
              </svg>
             </i>
          </div>
        </div>
        `
    };
        
    const app_consent = Vue.createApp({
        components: {
            'component-cookie-consent' : ComponentsCookieConsent,            
        },   
        created() {
            //
        },
    });
    app_consent.use(ElementPlus);
    const vm_consent = app_consent.mount('#vue-cookie-consent');

}); 
// end ready

})(jQuery); 
/*end strict*/