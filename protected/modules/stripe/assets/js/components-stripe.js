/*
COMPONENTS CASH 
*/
//cashComponents
const componentsStripe = {
	props: ['title','label','payment_code','merchant_id','merchant_type','publish_key','amount',
	'currency_code','ajax_url','cart_uuid','on_error','cardholder_name','prefix','reference'],
	data() {
     return {
        is_loading: false,      
        is_ready : false,     
        error : [],        
        order_uuid : '',
        success : '',
        client_secret : '',        
        stripe : undefined,
        cardElement : undefined,
        customer_id : '',
        button_enabled : true,
     }
    },     
    mounted() {    	    	
    	
    },
    updated() {    	
    	
    },
	methods: {
		showPaymentForm(){
			this.error = [];								
			$( this.$refs.stripe_modal ).modal('show');		
			this.createCustomer();
		},
		close(){
			dump("close");
			this.cardElement.unmount();
			$( this.$refs.stripe_modal ).modal('hide');
			this.is_ready = false;
		},			
		createCustomer(){
			var $ajaxurl = this.ajax_url;
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),				   
			   'merchant_id': this.merchant_id,
			   'payment_code': this.payment_code,
			   'merchant_type' : this.merchant_type,
			   'reference' : this.reference,
			};
			var timenow = 1;
		    $params = JSON.stringify($params);
		    		    
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl+"/CreateCustomer" + this.prefix,
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 	
	 	    		this.is_loading = true;	 
	 	    		this.error = [];	    	
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    });		    
		    
	 	    ajax_request_cmp[timenow].done( data => {	 	    		 	    
	 	    	if ( data.code==1){	
	 	    		this.is_ready = true;
	 	    		this.error = [];	 
	 	    		this.client_secret = data.details.client_secret;	 	
	 	    		this.customer_id = data.details.customer_id;    		
	 	    		this.initStripe();
	 	    	} else {	
	 	    		this.error = data.msg; 	   
	 	    		this.button_enabled = false; 		
	 	    	}	 	    	 	    
            });			
            
            ajax_request_cmp[timenow].always( data => {	 	    	 	    	    
            	this.is_loading = false;
            });		  	 	
			
		},
	    initStripe(){
			if (window.Stripe == null) {
	    		new Promise((resolve) => {	    	
	                const doc = window.document;
	                const scriptId = "stripe-script";
	                const scriptTag = doc.createElement("script");
	                scriptTag.id = scriptId;	                
	                scriptTag.setAttribute("src", "https://js.stripe.com/v3/" );
	                doc.head.appendChild(scriptTag);
	                
	                scriptTag.onload = () => {
	                   dump("added stripe");
				       resolve(); 
				    };					                
	             }).then(() => {
	                this.renderCard();
	             });       	   	   
    		} else {
    			this.renderCard();
    		}    	
		},		
		renderCard(){
			dump("renderCard");
			var style = {
			    base: {
			      color: "#32325d",
			      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
			      fontSmoothing: "antialiased",
			      fontSize: "16px",
			      "::placeholder": {
			        color: "#aab7c4"
			      }
			    },
			    invalid: {
			      color: "#fa755a",
			      iconColor: "#fa755a"
			    }
			};
						
			this.stripe = Stripe( this.publish_key );
			var elements = this.stripe.elements();
            this.cardElement = elements.create('card' ,  { 
				style: style ,
				hidePostalCode: true,
			});            		            
            setTimeout(() => {	
              this.cardElement.mount( this.$refs.card_element );			                        
            }, 500); 
		},
		submitForms(){
			
			 this.is_loading = true; 
			 this.error = [];	 
			
			 this.stripe.confirmCardSetup(
			    this.client_secret,
			    {
			      payment_method: {
			        card: this.cardElement,
			        billing_details: {
			          name: this.cardholder_name,
			        },
			      },
			    }
			  ).then( result => {
			  	console.debug(result);			  	
			    if (result.error) {
			        this.is_loading = false;
			        this.error[0] = result.error.message;
			        
			        dump(result.error.code);
			        if(result.error.code=="setup_intent_unexpected_state"){			        	
			        	this.createIntent();
			        }
			        
			    } else {			    	
			        this.savePayment( result.setupIntent.payment_method )
			    }
			 });
			
		},
		savePayment( payment_method_id){
			
			var $ajaxurl = this.ajax_url;
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),				   
			   'payment_method_id' : payment_method_id,
			   'merchant_id': this.merchant_id,
			   'payment_code': this.payment_code,
			   'merchant_type' : this.merchant_type,
			   'reference' : this.reference,
			};
			var timenow = 1;
		    $params = JSON.stringify($params);
		    		    
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl+"/savePayment" + this.prefix,	 	    	
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 	
	 	    		this.is_loading = true;	 
	 	    		this.error = [];	    	
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    });		    
	 	    
	 	    ajax_request_cmp[timenow].done( data => {	 	    		 	    
	 	    	if ( data.code==1){	
	 	    		this.error = [];	 	
	 	    		this.close();	    
	 	    		this.$emit('setPaymentlist'); 	    		
	 	    	} else {	
	 	    		this.error = data.msg; 	    		
	 	    	}	 	    	 	    
            });			
            
            ajax_request_cmp[timenow].always( data => {	 	    	 	    	    
            	this.is_loading = false;
            });		  	 	
	 	    
		},
		createIntent() {
			
			var $ajaxurl = this.ajax_url;
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),				   
			   'customer_id' : this.customer_id,
			   'merchant_id': this.merchant_id,
			   'payment_code': this.payment_code,
			   'merchant_type' : this.merchant_type,
			};
			var timenow = 2;
		    $params = JSON.stringify($params);
		    		    
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl+"/createIntent",
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 		 	    		
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    });		    
			
	 	    ajax_request_cmp[timenow].done( data => {	 	    		 	    
	 	    	if ( data.code==1){	
	 	    		this.client_secret = data.details.client_secret;
	 	    	}	    	 	    
            });			
	 	    
		},
		
		PaymentRender(data){			
			
			var $ajaxurl = this.ajax_url;
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),	
			   'cart_uuid' : this.cart_uuid,
			   'order_uuid' : data.order_uuid,
			   'payment_uuid' : data.payment_uuid,
			   'merchant_id': this.merchant_id,
			   'payment_code': this.payment_code,
			   'merchant_type' : this.merchant_type,
			};
			var timenow = 2;
		    $params = JSON.stringify($params);
		    		    
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl+"/PaymentIntent",
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 		
	 	    		this.$emit('showLoader');  	    		
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    });		    
			
	 	    ajax_request_cmp[timenow].done( data => {	 	    		 	    
	 	    	if ( data.code==1){	
	 	    		window.location.href = data.details.redirect;
	 	    	} else {
	 	    		this.$emit('afterCancelPayment');	 	    		
	 	    		this.$emit('alert', data.msg );	 	    		
	 	    	}
            });	
			
            ajax_request_cmp[timenow].always( data => {	 	    	 	    	    
            	this.$emit('closeLoader'); 
            });		  	 	
	 	    
		},		
		DoPaymentRender(data,actions){		
			this.$emit('showLoader');  	
			var $params = data;
			$params.YII_CSRF_TOKEN = $('meta[name=YII_CSRF_TOKEN]').attr('content');
			axios({
			   method: 'PUT',
			   url: this.ajax_url + "/"  + actions ,
			   data: $params ,
			   timeout: $timeout_cmp,
			 }).then( response => {	 
			 	 if(response.data.code==1){		
			 	     window.location.href = response.data.details.redirect;
			 	 } else {				 	 	
			 	    this.$emit('afterCancelPayment');	 	    		
	 	    		this.$emit('alert', response.data.msg );		 	    		
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoader'); 
			 });				 
		},	
        Dopayment(data){            
            this.$emit('showLoader');  		
            var $params = {
                'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),      
                'data': data         
            };		
			axios({
			   method: 'PUT',
			   url: this.ajax_url + "/processpayment" ,
			   data: $params ,
			   timeout: $timeout_cmp,
			 }).then( response => {	 
			 	 if(response.data.code==1){		
                    this.$emit('afterSuccessfulpayment' ,response.data.details);	 	    			 	    		 
			 	 } else {				 	 	
			 	    this.$emit('afterCancelPayment' ,response.data.msg);	 	    			 	    		
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoader'); 
			 });				       
        },
	},
	template: `		        
	 <div class="modal" ref="stripe_modal" tabindex="-1" role="dialog" aria-labelledby="StripeForm" aria-hidden="true"
	 data-backdrop="static" data-keyboard="false" 
	 >
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">
	     <form class="forms mt-2 mb-2" @submit.prevent="submitForms" >
	       <div class="modal-body">
	       
	         <a href="javascript:;" @click="close" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{title}}</h4>  	
	         
	         <p>{{label.notes}}</p>	 	  
	         	         
	         <DIV  v-if="is_ready" class="position-relative"> 
	         
	         <DIV v-if="is_loading">
			  <div class="loading mt-5">      
			    <div class="m-auto circle-loader" data-loader="circle-side"></div>
			  </div>
			 </DIV>  
	         
	         <div class="form-label-group">    
              <input v-model="cardholder_name"  class="form-control form-control-text" placeholder=""
               id="cardholder_name" type="text"  >   
              <label for="cardholder_name" class="required">{{label.cardholder_name}}</label> 
             </div>        
	         	                    
             <div class="mb-4" ref="card_element" id="card-element"></div>
             
             <p>{{label.agreement}}</p>
             
             </DIV> <!-- is_ready -->
	        
			
	          <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			  </div>    
	         
	       </div> <!--modal body-->	  
	       
	       <div class="modal-footer justify-content-start">	        
		       <button class="btn btn-green w-100" :disabled="!button_enabled" :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	     </form>               
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->      	
	`
};
