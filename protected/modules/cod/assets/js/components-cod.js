/*
COMPONENTS CASH 
*/
//cashComponents
const componentsCod = {
	props: ['title','label','payment_code','merchant_id'],
	data() {
     return {
        is_loading: false,           
        error : [],        
     }
    },     
	methods: {
		showPaymentForm(){
			this.error = [];	
			$('#cashForm').modal('show');
		},
		close(){
			$('#cashForm').modal('hide');
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
	},
	template: `		        
	 <div class="modal" id="cashForm" tabindex="-1" role="dialog" aria-labelledby="cashForm" aria-hidden="true">
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
