/*
COMPONENTS 
*/
const componentsVivawallet = {
	props: ['title','label','payment_code','merchant_id','ajaxurl','redirect','cart_uuid'],
	data() {
     return {
        is_loading: false,           
        error : [],        		
     }
    },     
	methods: {
		showPaymentForm(){
			this.error = [];	
			$(this.$refs.forms).modal('show');
		},
		close(){
			$(this.$refs.forms).modal('hide');
		},	
		submitForms(){			
			var $params = {
				'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
				'payment_code' : this.payment_code,
				'merchant_id' : this.merchant_id,
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
		PaymentRender(data){			
			this.$emit('showLoader');  	
			this.payment_uuid = data.payment_uuid;
	    	this.order_uuid = data.order_uuid;
			axios({
				method: 'PUT',
				url: this.ajaxurl +"/checkout" ,
				data : {
					 'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),
					 'payment_code' : this.payment_code,
			         'merchant_id' : this.merchant_id,
			         'merchant_type' : this.merchant_type,		   
					 'order_uuid':this.order_uuid,
			         'cart_uuid' : this.cart_uuid,
			         'payment_uuid' : this.payment_uuid,
				   },
				timeout: $timeout_cmp,
			  }).then( response => {	                  				
				  if(response.data.code==1){		
					window.location.href = response.data.details.redirect;
				  } else {				 	 	
					this.$emit('afterCancelPayment');	 	    		
					this.$emit('alert', response.data.msg );		 	    		
				  }
			  }).catch(error => {	
                this.error = error;
			  }).then(data => {			     
				this.$emit('closeLoader'); 
			  });			
		},
		Dopayment(data){						
			window.location.href = this.ajax_url + "/processpayment?data="+ data;
		},	
	},
	template: `		        
	 <div class="modal" ref="forms" tabindex="-1" role="dialog" aria-labelledby="forms" aria-hidden="true">
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">
		 

	       <div class="modal-body">
		   
	       
	         <a href="javascript:;" @click="close" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{title}}</h4>  	
	         
	         <p>{{label.notes}}</p>	 
	       
	          <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			  </div>    
			  			  
	         			
	       </div> <!--modal body-->	  
	       
	       <div class="modal-footer justify-content-start">			   
		       <button @click="submitForms" class="btn btn-green w-100" :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->


	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->           
	`
};
