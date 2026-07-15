/*
COMPONENTS PAYPAL 
*/
//
let paypal_handle;
const componentsPaypal = {
	props: ['title','label','payment_code','merchant_id','client_id','amount','currency_code','ajax_url','cart_uuid','on_error'],
	data() {
     return {
        is_loading: false,           
        error : [],        
        order_uuid : '',
        success : '',        
        enabled_force : false,
        force_currency : '',
        force_amount : 0,
		jwt_data :[]
     }
    },     
    mounted() {    	    	
    	//this.initPaypal();    
    },
    updated() {    	
    	
    },
	methods: {
		showPaymentForm(){
			this.error = [];								
			$('#PaypalForm').modal('show');			
		},
		close(){
			$('#PaypalForm').modal('hide');
		},	
		closeRender(){			
			paypal_handle.close();
			$('#PaypalRender').modal('hide');			
			this.$emit('afterCancelPayment');
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
		PaymentRender( data ){					
			this.order_uuid = data.order_uuid;
			this.error = [];			

            if(data.force_payment_data){                
                if(data.force_payment_data.enabled_force){   
                    this.enabled_force = true;                 
                    this.force_currency = data.force_payment_data.use_currency_code;
                    this.force_amount = data.force_payment_data.total_exchange;
                }
            }
						
			$('#PaypalRender').modal('show');				
			this.close();
			this.initPaypal(); 			
		},
		initPaypal(){
			if (window.paypal == null) {

                let currencyCode = this.currency_code;
                if(this.enabled_force){
                    currencyCode = this.force_currency;
                }                                

	    		new Promise((resolve) => {	    	
	                const doc = window.document;
	                const scriptId = "paypal-script";
	                const scriptTag = doc.createElement("script");
	                scriptTag.id = scriptId;	                
	                scriptTag.setAttribute("src", "https://www.paypal.com/sdk/js?client-id="+this.client_id + "&currency=" + currencyCode + "&disable-funding=credit,card" );
	                doc.head.appendChild(scriptTag);
	                
	                scriptTag.onload = () => {
				       resolve(); 
				    };					                
	             }).then(() => {
	                this.renderPaypal();
	             });       	   	   
    		} else {
    			this.renderPaypal();
    		}    	
		},		
		renderPaypal(){		  					 
           let Amount = this.amount;
           if(this.enabled_force){
               Amount = this.force_amount;
           }            		   
		   paypal_handle = paypal.Buttons({		     
		     createOrder: (data,actions) => { 
		       return actions.order.create({
		        purchase_units: [{
		          amount: {
		            value: parseFloat(Amount)
		          }
		        }]
		      });
		    },		    
		    onCancel: data => {		    	
		    	//
		    },
		    onError: err => {		    	
		    	this.error[0] = this.on_error.error;
		    },
		    onApprove: (data,actions) => {
		       return actions.order.capture().then( orderData => { 
		    	    var transaction = orderData.purchase_units[0].payments.captures[0];		    	    
		    	    this.CompletePaymentRequest(transaction.status,transaction.id,orderData.id);
		       });
		    },
		  });		  
		  paypal_handle.render('#paypal-button-container');		  
		},			
		CompletePaymentRequest(status,transaction_id,order_id){			
			var $ajaxurl = this.ajax_url;						        
            var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
			   'transaction_id' : transaction_id,
			   'order_id' : order_id,
			   'order_uuid' : this.order_uuid,
			   'cart_uuid' : this.cart_uuid
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
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    });		    

	 	    ajax_request_cmp[timenow].done( data => {	 	    		 	    
	 	    	if ( data.code==1){	
	 	    		this.success = data.msg;
	 	    		this.error = [];			 	    		
	 	    		this.close();
	 	    		window.location.href = data.details.redirect;	    	 	    		
	 	    	} else {	
	 	    		this.error = data.msg; 	    		
	 	    	}	 	    	 	    
            });			
            
            ajax_request_cmp[timenow].always( data => {	 	    	 	    	    
            	this.is_loading = false;
            });		  	   		  			
		},
		// NEW CODE FOR PAYMENT
		Dopayment(data,datas){          
		   this.jwt_data = data;				   		   
		   this.force_amount = datas.amount;		   
		   this.force_currency = datas.currency_code;
		   $('#PaypalRender').modal('show');	
		   this.PaypalInit(); 		
        },
		PaypalInit(){
			if (window.paypal == null) {
                let currencyCode = this.currency_code;
	    		new Promise((resolve) => {	    	
	                const doc = window.document;
	                const scriptId = "paypal-script";
	                const scriptTag = doc.createElement("script");
	                scriptTag.id = scriptId;	                
	                scriptTag.setAttribute("src", "https://www.paypal.com/sdk/js?client-id="+this.client_id + "&currency=" + currencyCode + "&disable-funding=credit,card" );
	                doc.head.appendChild(scriptTag);
	                
	                scriptTag.onload = () => {
				       resolve(); 
				    };					                
	             }).then(() => {
	                this.renderPayment();
	             });       	   	   
    		} else {
    			this.renderPayment();
    		}    	
		},		
		renderPayment(){									
			this.$emit('closeTopup');
			let Amount = this.force_amount;

			paypal_handle = paypal.Buttons({		     
				createOrder: (data,actions) => { 
				  return actions.order.create({
				   purchase_units: [{
					 amount: {
					   value: parseFloat(Amount)
					 }
				   }]
				 });
			   },		    
			   onCancel: data => {		    	
				   //
			   },
			   onError: err => {		    	
				   this.error[0] = this.on_error.error;
			   },
			   onApprove: (data,actions) => {
				  return actions.order.capture().then( orderData => { 
					   var transaction = orderData.purchase_units[0].payments.captures[0];		    	    
					   this.processPayment(transaction.status,transaction.id,orderData.id);
				  });
			   },
			 });		  
			 paypal_handle.render('#paypal-button-container');		  
		},
		processPayment(status,transaction_id,order_id){			
			$('#PaypalRender').modal('hide');
			this.$emit('showLoader');  		
            var $params = {
                'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),      
                'data': this.jwt_data ,				
				'transaction_id': transaction_id,
				'order_id': order_id,
            };					
			axios({
			   method: 'PUT',
			   url: this.ajax_url + "/processpayment" ,
			   data: $params ,
			   timeout: $timeout_cmp,
			 }).then( response => {	 
			 	 if(response.data.code==1){		
					paypal_handle.close();
                    this.$emit('afterSuccessfulpayment' ,response.data.details);	 	    			 	    		 
			 	 } else {				 
					paypal_handle.close();	 	
			 	    this.$emit('afterCancelPayment' ,response.data.msg);	 	    			 	    		
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoader'); 
			 });				       
		}
	},
	template: `		        
	 <div class="modal" id="PaypalForm" tabindex="-1" role="dialog" aria-labelledby="PaypalForm" aria-hidden="true">
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
			 </DIV>  
			
	          <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			  </div>    
	         
	       </div> <!--modal body-->	  
	       
	       <div class="modal-footer justify-content-start">	        
		       <button class="btn btn-green w-100" @click="submitForms" :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->      

	 <div class="modal" id="PaypalRender" tabindex="-1" role="dialog" aria-labelledby="PaypalRender" aria-hidden="true"
	 data-backdrop="static" data-keyboard="false" 
	 >
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">
	       <div class="modal-body">
	       
	         <a href="javascript:;" @click="closeRender" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{label.payment_title}}</h4>  	
	         
	         <p>{{label.payment_notes}}</p>	    						
	         
			 <DIV v-if="is_loading">
			  <div class="loading mt-5">      
			    <div class="m-auto circle-loader" data-loader="circle-side"></div>
			  </div>
			 </DIV>  
			
	          <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			  </div>    
			  
			  <div  v-cloak v-if="success" class="alert alert-success" role="alert">
			    <p class="m-0">{{success}}</p>	    
			   </div>
	         
	       </div> <!--modal body-->	  
	       
	       <div v-if="!is_loading" class="modal-footer justify-content-start">	        
		       <div ref="paypal_button" id="paypal-button-container" style="width:100%;" ></div>
		   </div> <!--footer-->
	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->   
	`
};
