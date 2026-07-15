/*
COMPONENTS CASH 
*/
//cashComponents
const componentsRazorpay = {
	props: ['title','label','payment_code','merchant_id','merchant_type','key_id','amount','currency_code','ajax_url','cart_uuid','on_error'],
	data() {
     return {
        is_loading: false,           
        error : [],        
        order_uuid : '',
        success : '',
        customer_id : '',
        doing_something : '',
        rzr_order_id : '',
        orders : [],
        button_enabled : true,
        jwt_data : [],
        force_amount : 0,
        force_currency : ''
     }
    },     
    mounted() {    	    	
    	
    },
    updated() {    	
    	
    },
	methods: {
		showPaymentForm(){
			this.error = [];								
			$('#razorpayForm').modal('show');	
			if(empty(this.key_id)){
				this.button_enabled = false;	
			} else this.CreateCustomer();					
		},
		close(){
			$('#razorpayForm').modal('hide');
		},	
		closePayment(){			
			this.$emit('afterCancelPayment');
		},			
		CreateCustomer(){
			
			var $ajaxurl = this.ajax_url;						        
            var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
			   'cart_uuid' : this.cart_uuid,
			   'payment_code' : this.payment_code,
			   'merchant_id' : this.merchant_id,
			   'merchant_type' : this.merchant_type,
			};
			
			var timenow = 1;
		    $params = JSON.stringify($params);
		            		            	
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl +"/CreateCustomer",
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
	 	    }).done( data => {
	 	    	if ( data.code==1){
	 	    		this.customer_id = data.details.customer_id;	 	 	    		
	 	    		this.button_enabled = true;
	 	    	} else {
	 	    		this.customer_id = '';
	 	    		this.error = data.msg;
	 	    		this.button_enabled = false;
	 	    	}
	 	    }).always( data => {	 	 	    	
	 	    	this.is_loading = false;
            });		
		},		
		initRazorPay(){
			if (window.paypal == null) {
	    		new Promise((resolve) => {	    	
	                const doc = window.document;
	                const scriptId = "razorpay-script";
	                const scriptTag = doc.createElement("script");
	                scriptTag.id = scriptId;	                
	                scriptTag.setAttribute("src", "https://checkout.razorpay.com/v1/checkout.js" );
	                doc.head.appendChild(scriptTag);
	                
	                scriptTag.onload = () => {
				       resolve(); 
				    };					                
	             }).then(() => {
	                this.initPayment();
	             });       	   	   
    		} else {
    			this.initPayment();
    		}    	
		},				
	    submitForms(){			
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
			   'payment_code' : this.payment_code,
			   'merchant_id' : this.merchant_id
			};
			var timenow = 1;
		    $params = JSON.stringify($params);
		    		    
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: ajaxurl+"/SavedPaymentProvider",
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 	
	 	    		this.is_loading = true;	 
	 	    		this.error = [];	  
	 	    		this.doing_something = '';
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    }).done( data => {	 	    		 	    
	 	    	if ( data.code==1){	
	 	    		this.error = [];			 	    		
	 	    		this.close();	    
	 	    		this.$emit('setPaymentlist');	 	    		
	 	    	} else {	
	 	    		this.error = data.msg; 	    		
	 	    	}	 	    	 	    
            }).always( data => {	 	    	 	    	    
            	this.is_loading = false;
            });		  	 				
		},			
		PaymentRender( data ){			
			this.order_uuid = data.order_uuid;			
			
			var $ajaxurl = this.ajax_url;						        
            var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
			   'cart_uuid' : this.cart_uuid,
			   'order_uuid' : this.order_uuid,
			   'payment_code' : this.payment_code,
			   'merchant_id' : this.merchant_id,
			   'merchant_type' : this.merchant_type,
			};
			
			var timenow = 1;
		    $params = JSON.stringify($params);
		            		            	
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl +"/CreateOrder",
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,			 	    	
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 		 	    		
	 	    		this.error = [];	 	    	
	 	    		this.$emit('showLoader', this.label.getting_payment ); 	
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    }).done( data => {
	 	    	 if (data.code==1){
	 	    	 	this.orders = data.details;
	 	    	 	this.initRazorPay();
	 	    	 } else {
	 	    	 	this.$emit('afterCancelPayment');	 	    		
	 	    		this.$emit('alert', data.msg );	
	 	    	 }
	 	    }).always( data => {	 	    	 	    	                	
            	this.$emit('closeLoader'); 	
            });	
		},
		initPayment(){
			try {
				var options = {
				    "key": this.key_id, 
				    "amount": this.orders.amount ,
				    "currency": this.orders.currency ,
				    "name": this.orders.name ,
				    "description":  this.orders.description ,			    
				    "order_id": this.orders.order_id ,
				    "customer_id" : this.orders.customer_id ,
				    "handler":  response => {
				        this.verifyPayment(response);
				    },			    
				    "theme": {
				        "color": "#3399cc"
				    },
				      "modal": {
				        "ondismiss": data => {	
				            this.closePayment();
				        }
				    }
				};
				var rzr_handle = new Razorpay(options);
				rzr_handle.on('payment.failed',  response => {	 					        
				     //this.$emit('alert', response.error.description );	
				});				
				this.$emit('closeLoader'); 				
				rzr_handle.open();				
			} catch(err) {				
				this.$emit('afterCancelPayment');	
				this.$emit('alert', err.message );	
			}
		},
		verifyPayment(data){
			
			var $ajaxurl = this.ajax_url;						        
            var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
			   'cart_uuid' : this.cart_uuid,
			   'order_uuid' : this.order_uuid,
			   'payment_code' : this.payment_code,
			   'merchant_id' : this.merchant_id,
			   'merchant_type' : this.merchant_type,
			   'razorpay_payment_id': data.razorpay_payment_id,
			   'razorpay_order_id': data.razorpay_order_id,
			   'razorpay_signature': data.razorpay_signature,
			};
			
			var timenow = 1;
		    $params = JSON.stringify($params);
		            		            	
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: $ajaxurl +"/verifyPayment",
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,			 	    	
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 	
	 	    		this.is_loading = true;	 
	 	    		this.error = [];	 	    	
	 	    		this.$emit('showLoader', this.label.processing_payment ); 	
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    }).done( data => {
	 	    	 if (data.code==1){
	 	    	 	window.location.href = data.details.redirect;
	 	    	 } else {
	 	    	 	this.$emit('afterCancelPayment');	 	    		
	 	    		this.$emit('alert', data.msg );	
	 	    	 }
	 	    }).always( data => {	 	    	 	    	    
            	this.$emit('closeLoader'); 	
            });	
		},
		// NEW PAYMENT HERE
        Dopayment(data,datas){                     
		   this.jwt_data = data;				   		   
		   this.force_amount = datas.amount;		   
		   this.force_currency = datas.currency_code;		

           this.$emit('showLoader');  		
           var $params = {
               'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),      
               'data': data         
           };		
           axios({
              method: 'PUT',
              url: this.ajax_url + "/createneworder" ,
              data: $params ,
              timeout: $timeout_cmp,
            }).then( response => {	 
                 if(response.data.code==1){		
                    this.orders = response.data.details;
                    this.RazorPayinit();
                 } else {				 	 	
                    this.$emit('afterCancelPayment');	 	    		
                    this.$emit('alert', data.msg );	
                 }
            }).catch(error => {	
               //
            }).then(data => {			     
                this.$emit('closeLoader'); 
            });			
        },
        RazorPayinit(){
			if (window.paypal == null) {
	    		new Promise((resolve) => {	    	
	                const doc = window.document;
	                const scriptId = "razorpay-script";
	                const scriptTag = doc.createElement("script");
	                scriptTag.id = scriptId;	                
	                scriptTag.setAttribute("src", "https://checkout.razorpay.com/v1/checkout.js" );
	                doc.head.appendChild(scriptTag);
	                
	                scriptTag.onload = () => {
				       resolve(); 
				    };					                
	             }).then(() => {
	                this.Paymentinit();
	             });       	   	   
    		} else {
    			this.Paymentinit();
    		}    	
		},			
        Paymentinit(){
			try {
				var options = {
				    "key": this.key_id, 
				    "amount": this.orders.amount ,
				    "currency": this.orders.currency ,
				    "name": this.orders.name ,
				    "description":  this.orders.description ,			    
				    "order_id": this.orders.order_id ,
				    "customer_id" : this.orders.customer_id ,
				    "handler":  response => {
				        this.processPayment(response);
				    },			    
				    "theme": {
				        "color": "#3399cc"
				    },
				      "modal": {
				        "ondismiss": data => {	
				            this.closePayment();
				        }
				    }
				};
				var rzr_handle = new Razorpay(options);
				rzr_handle.on('payment.failed',  response => {	 					        				     
				});				
				this.$emit('closeLoader'); 				
				rzr_handle.open();				
			} catch(err) {				
				this.$emit('afterCancelPayment');	
				this.$emit('alert', err.message );	
			}
		},	
        processPayment(data){            
			this.$emit('showLoader');  		
            var $params = {
                'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),      
                'data': this.jwt_data ,				
				'razorpay_payment_id': data.razorpay_payment_id,
			    'razorpay_order_id': data.razorpay_order_id,
			    'razorpay_signature': data.razorpay_signature,
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
	 <div class="modal" id="razorpayForm" tabindex="-1" role="dialog" aria-labelledby="razorpayForm" aria-hidden="true">
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">
	       <div class="modal-body">
	       
	         <a href="javascript:;" @click="close" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{title}}</h4>  	
	         	         
	         <p>{{label.notes}}</p>	 	       	         
	         	         
	         
			 <DIV v-if="is_loading">
			  <div class="loading mt-5">      
			    <div class="m-auto circle-loader" data-loader="circle-side"></div>
			  </div>
			  <p class="text-center mt-1">{{doing_something}}</p>
			 </DIV>  
			
	          <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			  </div>    
	         
	       </div> <!--modal body-->	  
	       
	       <div class="modal-footer justify-content-start">	        
		       <button class="btn btn-green w-100" @click="submitForms" :disabled="!button_enabled" 
		       :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->      	 		
	`
};
