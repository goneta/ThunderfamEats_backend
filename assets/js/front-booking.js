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

const $timeout = 20000;

const getCustomerToken = function(){
  if (typeof identity_token === "undefined" || identity_token==null || identity_token=="" || identity_token=="null" || identity_token=="undefined" ) {
      return '';
  }
  return identity_token;      
};

/*
  COMPONENTS PHONE NUMBER
*/
const ComponentsPhoneNumber = {
	props: ['ajax_url','label','mobile_number','mobile_prefix'],	
	emits: ['update:mobile_number','update:mobile_prefix'],
	data(){
	   return {
		  data : [],		  
		  country_flag : ''          
	   };	
	},		
	mounted () {
  	  this.getLocationCountries();
    },    
    methods :{  
       getLocationCountries(){
        axios({
            method: 'POST',
            url: this.ajax_url+"/getLocationCountries" ,
            data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') ,
            timeout: $timeout,
          }).then( response => {	 
               if(response.data.code==1){		
                    this.data = response.data.details.data;
	 	    		this.country_flag = response.data.details.default_data.flag;	 	    		
	 	    		this.$emit('update:mobile_prefix', response.data.details.default_data.phonecode );	                   
               } else {						 	 				 	 	                   
                    this.data = [];
	 	    		this.country_flag='';
	 	    		this.mobile_prefix='';
               }
          }).catch(error => {	
             //
          }).then(data => {			     
              
          });			       	       	            
       },	   
       setValue(data){
       	  this.country_flag = data.flag;       	  
       	  this.$emit('update:mobile_prefix', data.phonecode );       	
       	  this.$refs.ref_mobile_number.focus();  
       },   
    },    	
	template: `				    
    <div class="inputs-with-dropdown d-flex align-items-center mb-3" >
	    <div class="dropdown">
		  <button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		    <img v-if="country_flag" :src="country_flag">
		  </button>
		  <div class="dropdown-menu" >		    
		    <a v-for="item in data" @click="setValue(item)"
		    href="javascript:;"  class="dropdown-item d-flex align-items-center">
		      <div class="mr-2">
		        <img :src="item.flag">
		      </div>
		      <div>{{item.country_name}}</div>
		    </a>		    
		  </div>
		</div> <!--dropdown-->
		
		<div class="mr-0 ml-1" v-if="mobile_prefix">+{{mobile_prefix}}</div>
		<input type="text"    ref="ref_mobile_number"
		:value="mobile_number" @input="$emit('update:mobile_number', $event.target.value)" >
		
	</div> <!--inputs-->
	`
};


/*
  COMPONENTS RECAPCHA  
*/
const componentsRecaptcha = {
	props: ['sitekey','size','theme','is_enabled'],
	data() {
        return {
            recaptcha: null,            
        }
    },
    mounted() {    	    	
    	if(this.is_enabled==1 || this.is_enabled=="true" || this.is_enabled==true){
    	   this.initCapcha();
    	}
    },
    methods: {
       initCapcha(){       	
       	   if (window.grecaptcha == null) {
       	   	  new Promise((resolve) => {
                window.recaptchaReady = function () {                    	
                    resolve();
                };

                const doc = window.document;
                const scriptId = "recaptcha-script";
                const scriptTag = doc.createElement("script");
                scriptTag.id = scriptId;
                scriptTag.setAttribute("src", "https://www.google.com/recaptcha/api.js?onload=recaptchaReady&render=explicit");
                doc.head.appendChild(scriptTag);
             }).then(() => {
                this.renderRecaptcha();
             });       	   	   
       	   } else {
       	   	   this.renderRecaptcha();
       	   }              	
       },    
       renderRecaptcha(){       	  
       	  this.recaptcha = grecaptcha.render( this.$refs.recaptcha_target , {
		      'sitekey' : this.sitekey,
		      'theme': this.theme,
		      'size': this.size,
              'tabindex': this.tabindex,
              'callback': (response) => this.$emit("verify", response),
              'expired-callback': () => this.$emit("expire"),
              'error-callback': () => this.$emit("fail")
		  });
       },
       reset() {
          grecaptcha.reset(this.recaptcha);
       }
    },
    template: `	
    <div class="mb-2 mt-2" ref="recaptcha_target"></div>
    `
};


/*
  COMPONENTS RESERVATION
*/
const ComponentsReservation = {
    props: ['label','ajax_url','api_url','merchant_uuid','booking_enabled_capcha','captcha_site_key','reservation_uuid'],
    components : {
        'component-phone' : ComponentsPhoneNumber,
        'vue-recaptcha' : componentsRecaptcha,  
    },
    data() {
        return {
            steps : 1,
            guest_list :[],
            guest  : 1,
            reservation_date : '',
            reservation_time : '',
            reservation_time_pretty : '',
            time_slot : [],
            date_list : [],            
            all_time_slot : [],
            tc : '',
            first_name : '',
            last_name : '',
            email_address : '',            
            special_request :'',
            mobile_prefix : '',
            mobile_number :'',
            loading : false,
            loading_time_slot : false,
            submit_loading : false,
            next_step_loading : false,
            reservation_info : [],
            success_data : [],
            recaptcha_response : '',
            not_available_time : [],
            track_reservation_link : '',
            data_booking : [],
            details_link : '',
            allowed_choose_table : false,
            room_list : [],
            room_uuid : '',
            table_uuid : '',            
            user_data : ''
        }
    },
    created() {
        this.getBookingAttributes();        
    },
    computed: {
        hasTimeSlot(){			
            if (Object.keys(this.all_time_slot).length > 0) {
              return true;
            } 
            return false;
        },
        bookingValid(){
            let $pass = true;
            if(this.guest<=0){
                $pass = false;
            }
            if(empty(this.reservation_date)){
                $pass = false;
            }
            if(empty(this.reservation_time)){
                $pass = false;
            }
            return $pass;
        },
        reservationValid(){
            let $pass = true;            
            if(empty(this.first_name)){
                $pass = false;
            }
            if(empty(this.last_name)){
                $pass = false;
            }
            if(!this.validEmail(this.email_address)){
                $pass = false;
            }
            if(empty(this.mobile_number)){
                $pass = false;
            }
            return $pass;
        },        
        isEdit(){
          if(!empty(this.reservation_uuid)){
            return true;
          }
          return false;
        },
    },    
    methods: {            
        validEmail(email){
            var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },
        getBookingAttributes(){
            this.loading = true;
            let reservation_uuid = !empty(this.reservation_uuid)?this.reservation_uuid:'';
            axios({
                method: 'POST',
                url: this.ajax_url+"/Getbookingattributes" ,
                data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&merchant_uuid="+ this.merchant_uuid +"&id="+ reservation_uuid ,
                timeout: $timeout,
              }).then( response => {	 
                   if(response.data.code==1){			
                       this.guest_list  = response.data.details.guest_list;
                       this.date_list = response.data.details.date_list;
                       this.time_slot = response.data.details.time_slot;
                       this.all_time_slot = response.data.details.all_time_slot;
                       this.reservation_date = response.data.details.default_date;
                       this.tc = response.data.details.tc;
                       this.allowed_choose_table = response.data.details.allowed_choose_table;
                       this.room_list = response.data.details.room_list;

                       this.not_available_time = response.data.details.not_available_time;
                       this.guest = response.data.details.default_guest;
                       this.data_booking = response.data.details.data_booking;
                       this.details_link = response.data.details.details_link;

                       if(!empty(this.reservation_uuid)){
                          this.guest = response.data.details.data_booking.guest_number_raw;
                          this.reservation_time = response.data.details.data_booking.reservation_time_raw;                          
                          this.first_name = response.data.details.data_booking.first_name;
                          this.last_name = response.data.details.data_booking.last_name;
                          this.email_address = response.data.details.data_booking.email_address;
                          this.special_request = response.data.details.data_booking.special_request;
                          
                          this.mobile_prefix = response.data.details.data_booking.phone_prefix;
                          this.mobile_number = response.data.details.data_booking.contact_phone_without_prefix;

                          // this.room_uuid = response.data.details.data_booking.room_uuid;
                          // this.table_uuid = response.data.details.data_booking.table_uuid;
                       }                       
                   } else {						 	 				 	 	
                       this.guest_list = [];                       
                       this.date_list = [];
                       this.time_slot = [];
                       this.all_time_slot = [];
                       this.reservation_date = '';
                       this.tc = '';
                       this.not_available_time = [];
                       this.data_booking = [];
                       this.allowed_choose_table = [];
                       this.room_list = [];
                       this.room_uuid  = '';
                       this.table_uuid  = '';
                   }
              }).catch(error => {	
                 //
              }).then(data => {			     
                this.loading = false;
              });			
        },      
        getTimeslot(){          
            this.loading_time_slot = true;
            this.reservation_time = '';  
            let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&merchant_uuid="+ this.merchant_uuid ;
            $params+="&reservation_date="+ this.reservation_date;
            $params+="&guest="+ this.guest;
            $params+="&id="+ this.reservation_uuid;
            axios({
                method: 'POST',
                url: this.ajax_url+"/Gettimeslot" ,
                data : $params,
                timeout: $timeout,
              }).then( response => {	 
                   if(response.data.code==1){			
                       this.time_slot = response.data.details.time_slot;
                       this.all_time_slot = response.data.details.all_time_slot;
                       //this.guest_list  = response.data.details.guest_list;
                       this.not_available_time = response.data.details.not_available_time;
                   } else {						 	 				 	 	
                       this.time_slot = [];
                       this.all_time_slot = [];
                       //this.guest_list = [];
                       this.not_available_time = [];
                   }
              }).catch(error => {	
                 //
              }).then(data => {			     
                this.loading_time_slot = false;
              });			
        },
        nextStep(){
            this.next_step_loading = true;         
            let reservation_uuid = !empty(this.reservation_uuid)?this.reservation_uuid:'';
            let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&merchant_uuid="+ this.merchant_uuid ;
            $params+="&reservation_date="+ this.reservation_date;
            $params+="&reservation_time="+ this.reservation_time;
            $params+="&guest="+ this.guest;
            $params+="&id="+ reservation_uuid; 

            axios({
                method: 'POST',
                url: this.ajax_url+"/SetBooking" ,
                data : $params,
                timeout: $timeout,
                headers : {
                  Authorization : `token ${getCustomerToken()}`
                }
              }).then( response => {	 
                   if(response.data.code==1){		
                       this.steps = 2;	
                       this.reservation_info = response.data.details;
                       this.table_list = response.data.details.table_list;                       
                       if(!empty(this.reservation_uuid)){
                           this.room_uuid = response.data.details.room_uuid;
                           this.table_uuid = response.data.details.table_uuid;
                       } else {
                           this.room_uuid = '';
                           this.table_uuid = '';
                       }                      

                       this.user_data = response.data.details.user_data;
                       if (Object.keys(this.user_data).length > 0) {
                          this.first_name = this.user_data.first_name;
                          this.last_name = this.user_data.last_name;
                          this.email_address = this.user_data.email_address;
                          this.mobile_prefix = this.user_data.phone_prefix;
                          this.mobile_number = this.user_data.contact_number_without_prefix;
                       }

                   } else {						 	 				 	 	
                       this.reservation_info = [];
                       this.table_list  = [];
                       ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                       });
                   }
              }).catch(error => {	
                 //
              }).then(data => {			     
                this.next_step_loading = false;
              });			
        },
        submit(){                    
            this.submit_loading = true;    
            let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&merchant_uuid="+ this.merchant_uuid ;
            $params+="&reservation_date="+ this.reservation_date;
            $params+="&reservation_time="+ this.reservation_time;
            $params+="&guest="+ this.guest;
            $params+="&first_name="+ this.first_name;
            $params+="&last_name="+ this.last_name;
            $params+="&email_address="+ this.email_address;
            $params+="&mobile_prefix="+ this.mobile_prefix;
            $params+="&mobile_number="+ this.mobile_number;
            $params+="&room_uuid="+ this.room_uuid;
            $params+="&table_uuid="+ this.table_uuid;
            $params+="&special_request="+ this.special_request;
            $params+="&recaptcha_response="+ this.recaptcha_response;                        
            $params+="&id="+ this.reservation_uuid;
            axios({
                method: 'POST',
                url: this.ajax_url+"/ReserveTable" ,
                data : $params,
                timeout: $timeout,
                headers : {
                  Authorization : `token ${getCustomerToken()}`
                }
              }).then( response => {	 
                   if(response.data.code==1){			
                       this.steps = 3;
                       this.success_data = response.data.details;    
                       this.track_reservation_link = response.data.details.track_reservation_link;                   
                       setTimeout(() => {	
                        const el = document.getElementById('reservation_ty');                        
                        el.scrollIntoView({behavior: "smooth"});                        
                       }, 500); 

                        this.room_uuid = '';
                        this.table_uuid = '';
                   } else {						 	 				 	 	
                       this.success_data = [];
                       this.track_reservation_link = '';
                       ElementPlus.ElNotification({			
                          title: "",			
                          message: response.data.msg,
                          position: 'bottom-right',
                          type: 'warning',
                      });
                   }
              }).catch(error => {	
                 //
              }).then(data => {			     
                this.submit_loading = false;
              });			
        },
        resetReservation(){
          this.steps  = 1;
          this.getBookingAttributes();
          this.guest = 1;
          this.reservation_date = '';
          this.reservation_time = '';          
        },
        recaptchaVerified(response) {
              this.recaptcha_response = response;
        },
        recaptchaExpired() {
            if ( this.booking_enabled_capcha){			
                this.$refs.vueRecaptcha.reset();
            }
        },
        recaptchaFailed() {
              
        },     
        isNotavailable(bookingTime){   
          if (Object.keys(this.not_available_time).length > 0) {             
            if(this.not_available_time.includes(bookingTime)===true){
              return true;
            }
          }
          return false;
        },
        trackReservation(){           
           window.location.href = this.track_reservation_link;
        },
        clearTableList(){
           this.table_uuid = '';
        }
    },    
    template:`   
    
    <template v-if="steps==2">
                        
        <div class="mt-2 mb-2" v-loading="submit_loading">

         <div class="d-flex align-items-center mb-2">                        
           <b><i class="zmdi zmdi-arrow-left mr-2"></i></b>
           <a @click="this.steps=1" class="mr-2"><b>{{label.back}}</b></a>
         </div>        

          <h5>{{label.reservation_details}}</h5>          
          <p class="m-0">{{reservation_info.full_time}}</p>
          <p class="m-0">{{reservation_info.guest}}</p>

          <h5 class="mt-3">{{label.personal_details}}</h5>

        <div class="row mb-3">
            <div class="col">
               <p class="m-0 p-0 ">{{label.first_name}}</p>
               <el-input v-model="first_name"  size="large" />
            </div>
            <div class="col">
               <p class="m-0 p-0">{{label.last_name}}</p>
               <el-input v-model="last_name"  size="large" />
            </div>
          </div>
          <!-- row -->

          <div class="row mb-3">
            <div class="col">
              <p class="m-0 p-0">{{label.email_address}}</p>
              <el-input v-model="email_address"  size="large" />
            </div>
          </div>
          <!-- row -->

          <component-phone	    
           :ajax_url="ajax_url"
            v-model:mobile_number="mobile_number"
            v-model:mobile_prefix="mobile_prefix"
          >
	      </component-phone>   
                  
        <template v-if="allowed_choose_table">
        <div class="row mb-3">
          <div class="col"> 
            <p class="m-0 p-0">{{label.room_name}}</p>   
            <el-select v-model="room_uuid"  @change="clearTableList"  class="m-2" placeholder="Select" size="large">
              <el-option
              v-for="item in this.room_list"
              :key="item.value"
              :label="item.label"
              :value="item.value"
              />
             </el-select>
          </div>

          <div class="col">                          
             <p class="m-0 p-0">{{label.table_name}}</p>                
             <el-select v-model="table_uuid"  class="m-2" placeholder="Select" size="large">
             <el-option
             v-for="item in this.table_list[room_uuid]"
             :key="item.value"
             :label="item.label"
             :value="item.value"
             />
             </el-select>
          </div>

        </div>
        </template>
        <!-- row -->
          
          <div class="row mb-3">
            <div class="col ">
                <p class="m-0 p-0">{{label.special_request}}</p>                
                <el-input
                v-model="special_request"
                :rows="3"
                type="textarea"                
                >
                </el-input>
            </div>
          </div>
          <!-- row -->

          <p v-html="label.agree"></p>

          <vue-recaptcha  
          :sitekey="captcha_site_key"
          size="normal" 
          theme="light"
          :tabindex="0"
          :is_enabled="booking_enabled_capcha"
          @verify="recaptchaVerified"
          @expire="recaptchaExpired"
          @fail="recaptchaFailed"
          ref="vueRecaptcha">
          </vue-recaptcha>		

        </div>
        <!-- mt -->               

        <div class="mt-3 mb-1">                
          <el-button @click="submit"  size="large" type="success" style="width:100%;" 
          :disabled="!reservationValid" 
          :loading="submit_loading"
          >{{label.reserve}}</el-button>
       </div>

    </template>

    <template v-else-if="steps==3">
            
      <div id="reservation_ty" class="card border p-3">
        <div class="text-center">    
          <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"> <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/> <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
          </svg>    
                    
          <template v-if="isEdit">
             <h4>{{label.reservation_updated}}</h4>
             <p>{{label.reservation_succesful_notes}}</p>          
          </template>
          <template>
            <h4>{{label.reservation_succesful}}</h4>
            <p>{{label.reservation_succesful_notes}}</p>
          </template>

          <h6>{{success_data.full_time}}</h6>
          <p>{{success_data.guest}}</p>
          <p>{{label.reservation_id}}# <span class="text-success">{{success_data.reservation_id}}</span></p>

          <el-button          
          v-if="!isEdit"
          type="success"        
          @click="resetReservation()"  
          >
          {{label.reserved_table_again}}
          </el-button>
          
          <el-button                              
          @click="trackReservation"
          >
          {{label.track_your_reservation}}
          </el-button>          
          
        </div>        
      </div>
  
    </template>   
    <template v-else>
                    

    <div v-if="isEdit" class="d-flex align-items-center mb-2">                        
     <b><i class="zmdi zmdi-arrow-left mr-2"></i></b>
      <a :href="details_link"  class="mr-2"><b>{{label.back}}</b></a>
    </div>        


    <div class="row">
      <div class="col">        
       <p class="m-0 p-0 ml-2">{{label.guest}}</p>
       <el-select v-model="guest" @change="getTimeslot" class="m-2" placeholder="Select" size="large">
        <el-option
        v-for="item in this.guest_list"
        :key="item.value"
        :label="item.label"
        :value="item.value"
        />
       </el-select>
      </div>
      
      <div class="col ">         
         <p class="m-0 p-0 ml-2">{{label.date}}</p>
         <el-select v-model="reservation_date" @change="getTimeslot" class="m-2" placeholder="Select" size="large">
            <el-option
            v-for="item in this.date_list"
            :key="item.value"
            :label="item.label"
            :value="item.value"
            />
        </el-select>
      </div>

      <div class="col ">
        <p class="m-0 p-0 ml-2 mb-2">{{label.time}}</p>
         <el-input v-model="reservation_time"  size="large" disabled />
      </div>
    </div>
                    
    <div class="mt-2 mb-2" v-loading="loading_time_slot">       
       <template v-if="hasTimeSlot">
       <el-radio-group v-model="reservation_time" size="large">
            <template v-for="items in time_slot">                  
               <template v-for="(item,index) in items">
                 <el-radio-button :label="index" :disabled="isNotavailable(index)" >{{item}}</el-radio-button>            
              </template>
            </template>
        </el-radio-group>
       </template>
       <template v-else>        
        <el-alert 
        v-if="!loading"
        :title="label.no_results"
         type="error"
         :closable="false"
        >
        </el-alert>
       </template>    
    </div>
    
    <template v-if="this.tc">
    <div class="mt-2 mb-3">
        <h6>{{label.terms}}</h6>
        <div v-html="this.tc"></div>
    </div>
    </template>            
    
    <div class="mt-3 mb-1">
    <el-button @click="nextStep" size="large" type="success" style="width:100%;" 
    :disabled="!bookingValid"
    :loading="next_step_loading"
    >
      {{label.continue}}
    </el-button>
    </div>
    
    </template>
    `
};

const ComponentsBookingDetails = {
  props : ['id','ajax_url'],
  data() {
    return {
      steps : 1,
      loading : false,
      data : [],
      merchant : [],
      cancel_link : '',
      update_link : '',
      cancel_reservation_stats : [],
      pending_reservation_stats : [],
      confirm_reservation_stats : [],
      completed_reservation_stats : []
    }
  },
  created() {
    this.getBookingDetails();
  },
  computed: {
    hasData(){			
      if (Object.keys(this.data).length > 0) {
        return true;
      } 
      return false;
    },
    hasMerchant(){			
      if (Object.keys(this.merchant).length > 0) {
        return true;
      } 
      return false;
    },
    CanCancelReservation(){       
       if(this.cancel_reservation_stats.includes(this.data.status)===true){               
          return false;
       }
       return true
    }
  },
  methods: {   
    getBookingDetails(){
      this.loading = true;            
      let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&id="+ this.id ;            
      axios({
          method: 'POST',
          url: this.ajax_url+"/BookingDetails" ,
          data : $params,
          timeout: $timeout,
          headers : {
            Authorization : `token ${getCustomerToken()}`
          }
        }).then( response => {	 
              if(response.data.code==1){		
                  this.data = response.data.details.data;
                  this.merchant = response.data.details.merchant;
                  this.cancel_link = response.data.details.cancel_link;
                  this.update_link = response.data.details.update_link;

                  this.cancel_reservation_stats = response.data.details.cancel_reservation_stats;
                  this.pending_reservation_stats = response.data.details.pending_reservation_stats;
                  this.confirm_reservation_stats = response.data.details.confirm_reservation_stats;
                  this.completed_reservation_stats = response.data.details.completed_reservation_stats;

                  this.setSteps();

              } else {						 	 				 	 	
                this.data = [];
                this.merchant = [];
                this.cancel_link = '';
                this.update_link = '';

                this.cancel_reservation_stats = [];
                this.pending_reservation_stats = [];
                this.confirm_reservation_stats = [];
                this.completed_reservation_stats = [];
              }
        }).catch(error => {	
            //
        }).then(data => {			     
          this.loading = false;
        });			
    },
    setSteps(){
      dump('setSteps');      
      if(this.confirm_reservation_stats.includes(this.data.status)===true){
         this.steps = 2;
      } else if ( this.completed_reservation_stats.includes(this.data.status)===true ){
         this.steps = 3;
      } else if ( this.cancel_reservation_stats.includes(this.data.status)===true ){
         this.steps = 4;
      } else {
         this.steps = 1;
      }
    },
    toCancelPage(){
      window.location.href = this.cancel_link;
    },
    toUpdatePage(){
      window.location.href = this.update_link;
    }
  },
  template: '#xtemplate_booking_details',
};

const ComponentsBookingCancel = {
  props : ['id','ajax_url','label'],
  data() {
    return {
      reason : '',
      loading : true,
      data : [],
      submit : false,      
    }
  },
  created() {
    this.getCancelreason()
  },
  computed: {
    hasData(){			
      if(!empty(this.reason)){
          return true;        
      }
      return false;
    },
  },
  methods: {
    getCancelreason(){
      this.loading = true;            
      let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&id="+ this.id ;            
      axios({
          method: 'POST',
          url: this.ajax_url+"/getCancelreason" ,
          data : $params,
          timeout: $timeout,
        }).then( response => {	 
              if(response.data.code==1){		    
                this.data = response.data.details.data;            
              } else {						 	 				 	 	                
                this.data = [];
              }
        }).catch(error => {	
            //
        }).then(data => {			     
          this.loading = false;
        });			
    },
    ConfirmcancelReservation(){      
      ElementPlus.ElMessageBox.confirm(
        this.label.confirm,
        this.label.cancel_reservation,
        {
          confirmButtonText: this.label.yes,
          cancelButtonText: this.label.cancel,
          type: 'warning',
        }
        )
        .then(() => {
           dump('here')     
           this.CancelReservation();
        })
        .catch(() => {
          //
        });

    },
    CancelReservation(){
      this.submit = true;            
      let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&id="+ this.id ;            
      $params+="&reason="+ this.reason;
      axios({
          method: 'POST',
          url: this.ajax_url+"/CancelReservation" ,
          data : $params,          
          timeout: $timeout,
          headers : {
            Authorization : `token ${getCustomerToken()}`
          },
        }).then( response => {	 
              if(response.data.code==1){		                     
                ElementPlus.ElNotification({			
                  title: "",			
                  message: response.data.msg,
                  position: 'bottom-right',
                  type: 'success',
                });                               
                setTimeout(() => {	
                   window.location.href = response.data.details.redirect_url;       
                }, 500);          
              } else {						
                this.submit = false; 	 				 	 	                
                ElementPlus.ElNotification({			
                  title: "",			
                  message: response.data.msg,
                  position: 'bottom-right',
                  type: 'warning',
                });
              }
        }).catch(error => {	
            //
        }).then(data => {			     
          
        });			
    },
  },
  template: '#xtemplate_booking_cancel',
};

const app_booking = Vue.createApp({
    components: {
        'component-reservation' : ComponentsReservation,
        'booking-details' : ComponentsBookingDetails,
        'booking-cancel' : ComponentsBookingCancel
    },   
    created() {
        //
    },
});
app_booking.use(ElementPlus);
const vm_booking = app_booking.mount('#vue-booking-reservation');

// MANAGE BOOKING
const ComponentsBookingList = {
  template: '#xtemplate_booking_list',
  props :['api_url','status_list','q'],
  data() {
    return {
      tab : 'all',
      page : 1,
      data : [],
      code : 0,
      loading : false,
      load_more : false,
      merchant : [],
      table_list : [],
      show_next : false,
      awaitingSearch : false,	
    }
  },
  created() {
    this.BookingList();    
  },
  computed: {
    hasData(){			
      if (Object.keys(this.data).length > 0) {
        return true;
      } 
      return false;
    },
    statusColor(){
      //:style="{background:order_status.background_color_hex,color:order_status.font_color_hex}"      
      return "{background:'#f44336'}";
    },
  },
  watch: {
    q(newsearch,oldsearch){
      if (!this.awaitingSearch) {
        if(empty(newsearch)){
           return false;
        }

        setTimeout(() => {	          
          this.resetData();
          this.awaitingSearch = false;
        }, 1000); // 1 sec delay
        this.awaitingSearch = true;

      }
    },
    awaitingSearch(newval,oldval){      
      this.$emit("setSearch",newval);
    },
  },
  methods: {
    resetData(){            
      this.page = 1;
      this.tab = 'all';
      this.data = [];
      this.merchant = [];
      this.table_list = [];          
      this.BookingList(false);
    },
    loadMore(data){
      this.page = data;
      this.BookingList(true);
    },
    tabChange(data){      
      this.page = 1;
      this.data = [];
      this.merchant = [];
      this.BookingList(false);
    },
    BookingList(data){
      if(data){
        this.load_more = true;
      } else {
        this.loading = true;
      }    
      let $params = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') +"&status="+ this.tab + "&page="+this.page + "&q="+ this.q;
      axios({
        method: 'POST',
        url: this.api_url+"/BookingList" ,
        data : $params,          
        timeout: $timeout,
        headers : {
          Authorization : `token ${getCustomerToken()}`
        },
      }).then( response => {	 
          this.code = response.data.code;                         
          if(response.data.code==1){             
             this.data.push(response.data.details.data);             
             this.merchant = response.data.details.merchant;
             this.table_list = response.data.details.table_list;
             this.show_next = response.data.details.show_next;
             this.page = response.data.details.page_raw;
          }
      }).catch(error => {	
          //
      }).then(data => {			     
        this.load_more = false;
        this.loading = false;        
      });			      
    },
    //
  },
};

const manage_booking  = Vue.createApp({
	components: {
    'component-booking-list' : ComponentsBookingList,   
   },      
   data() {
    return {
      q : '',
      awaitingSearch : false,	
    }
   },
   methods: {
    setSearch(data){      
      this.awaitingSearch = data;
    },
    resetData(){            
      this.q = '';
      setTimeout(() => {	
        this.$refs.booking_list.resetData();
       }, 500);     
    },    
   },
});
manage_booking.use(ElementPlus);
const vm_manage_booking = manage_booking.mount('#vue-my-bookings');


}); 
// end ready


})(jQuery); 
/*end strict*/