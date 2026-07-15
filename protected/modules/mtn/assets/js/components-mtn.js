/*
COMPONENTS CASH 
*/
//cashComponents
const componentsMtn = {
	props: ['title','label','payment_code','merchant_id'],
	data() {
     return {
        is_loading: false,           
        error : [],        
		loading : false,
		mobile_number : '',
		payment_url : null
     }
    },     
	computed: {
		hasPhone(){
			return this.mobile_number?false:true;
		},
	},
	methods: {
		showPaymentForm(){
			this.error = [];	
			$('#mtnForm').modal('show');
		},
		close(){
			$('#mtnForm').modal('hide');
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
			this.payment_url = data.force_payment_data.payment_url;
			$('#mtnForm1').modal('show');		
		},
		Dopayment(data){						
			window.location.href = this.ajax_url + "/processpayment?data="+ data;
		},			
		payNow(){
			this.loading = true;			
			window.location.href = this.payment_url + "&phone="+this.mobile_number;
		},
		close2(){
			$('#mtnForm1').modal('hide');
			this.$emit('afterCancelPayment');	 	
		},	
	},
	template: `		        
	<div class="modal" id="mtnForm1" tabindex="-1" role="dialog" aria-labelledby="mtnForm1" aria-hidden="true">
	   <div class="modal-dialog" role="document"> 
	       <div class="modal-content">
		   <div class="modal-body">		   
		   
		      <a href="javascript:;" @click="close2" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        			  
	          <h4 class="m-0 mb-3 mt-3">{{label.enter_phone}}</h4>  
			  
			  <div class="form-group">				
				<input v-model="mobile_number" 
				type="email" 
				class="form-control" 
				id="mobile_number" 
				aria-describedby="mobile_number" 
				:placeholder="label.mobile_number"				
				>		
			  </div>

			  <div class="modal-footer justify-content-start">	        
		       <button class="btn btn-green w-100" @click="payNow" :class="{ loading: loading }"  :disabled="hasPhone"  >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		      </div> 

		   </div>   
		   </div>   
	   </div>   
	</div>

	 <div class="modal" id="mtnForm" tabindex="-1" role="dialog" aria-labelledby="mtnForm" aria-hidden="true">
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
		       <button class="btn btn-green w-100" @click="submitForms" :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->           
	`
};
