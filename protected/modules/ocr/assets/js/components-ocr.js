/*
COMPONENTS CREDIT CARD
*/
//creditCardComponents
const componentsOcr = {
	props: ['title','label','payment_code','merchant_id'],
	data() {
     return {
     	card_uuid : '',
        is_loading: false,   
        complete_fill : true,         
        error : [],
        card_name : '',
        credit_card_number : '',
        expiry_date : '',
        cvv : '',
        billing_address : '',        
     }
   },     
   mounted () {
  	 
   }, 
   methods: {
		showPaymentForm(){				
		    $( this.$refs.ocr_card ).modal('show');
		    this.clearForm(); 
		},
		close(){
			$( this.$refs.ocr_card  ).modal('hide');
		},	
		clearForm(){
			this.card_name = '';
			this.credit_card_number = '';
			this.expiry_date = '';
			this.cvv = '';
			this.billing_address = '';
			this.card_uuid = '';
		},
		empty(data){
			if (typeof data === "undefined" || data==null || data=="" || data=="null" || data=="undefined" ) {	
				return true;
			}
			return false;
		},
		submitForms(){			
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),
			   'card_name':this.card_name,
			   'credit_card_number':this.credit_card_number,
			   'expiry_date':this.expiry_date,
			   'cvv':this.cvv,
			   'billing_address':this.billing_address,
			   'payment_code' : this.payment_code,
			   'card_uuid' : this.card_uuid,
			   'merchant_id' : this.merchant_id
			};
			var timenow = 1;
		    $params = JSON.stringify($params);
		    
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: ajaxurl+"/savedCards",
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
	 	    		this.clearForm(); 
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
		Get(cc_id){
			this.showPaymentForm();
			
			var $params = {
			   'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			   
			   'cc_id' : cc_id
			};
			
			var timenow = 1;
		    $params = JSON.stringify($params);
			
		    ajax_request_cmp[timenow] = $.ajax({
	 	    	url: ajaxurl+"/getCards",
	 	    	method: "PUT",
	 	    	dataType: "json",
	 	    	data: $params ,
	 	    	contentType : $content_type.json ,
	 	    	timeout: $timeout_cmp,
	 	    	crossDomain: true,
	 	    	beforeSend: data => {	 	
	 	    		this.is_loading = true;	 
	 	    		this.error = [];	    
	 	    		this.clearForm(); 	
	 	    	 	if(ajax_request_cmp[timenow] != null) {	
	 	    	 		ajax_request_cmp[timenow].abort();
	 	    	 	}
	 	    	}
	 	    });		  
	 	    
	 	    ajax_request_cmp[timenow].done( data => {	 	    		 	    
	 	    	if ( data.code==1){		
	 	    		this.card_name = data.details.data.card_name;
	 	    		this.credit_card_number = data.details.data.credit_card_number;
	 	    		this.expiry_date = data.details.data.expiry_date;
	 	    		this.cvv = data.details.data.cvv;
	 	    		this.billing_address = data.details.data.billing_address;
	 	    		this.card_uuid = data.details.data.card_uuid;
	 	    	} else {	
	 	    		this.error = data.msg; 	    	
	 	    		this.card_name = '';
	 	    		this.credit_card_number = '';
	 	    		this.expiry_date = '';
	 	    		this.cvv = '';
	 	    		this.billing_address = '';
	 	    	}	 	    	 	    
            });			
            
            ajax_request_cmp[timenow].always( data => {	 	    	 	    	    
            	this.is_loading = false;
            });		  	 	  
		    
		}
	},
	computed: {
		hasFillForm(){		
			if ( !this.empty(this.card_name) && !this.empty(this.credit_card_number) && !this.empty(this.expiry_date) && !this.empty(this.cvv) ){
				this.complete_fill = false;
			} else this.complete_fill = true;
					
			return this.complete_fill;
		},
    },
	template: `	
	<div class="modal" ref="ocr_card" tabindex="-1" role="dialog" aria-labelledby="cardForm" aria-hidden="true">
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">  <!--opened-->    
	       <div class="modal-body">
	       
	         <a href="javascript:;" @click="close" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{title}}</h4>  		
	         
	         
	         <form class="forms mt-2 mb-2" @submit.prevent="submitForms" >
	         
	         		    
             <div class="form-label-group">    
              <input v-model="credit_card_number" v-maska="'#### #### #### ####'"  class="mask_card form-control form-control-text" placeholder=""
               id="credit_card_number" type="text"  >   
              <label for="credit_card_number" class="required">{{label.credit_card_number}}</label> 
             </div>        
             
             <div class="row">
              <div class="col">
              
              <div class="form-label-group"> 
              <input v-model="expiry_date" v-maska="'##/##'" class="form-control form-control-text" placeholder=""
               id="expiry_date" type="text" >   
              <label for="expiry_date" class="required">{{label.expiry_date}}</label>   
              </div>
              
              </div>
              <div class="col">
              
              <div class="form-label-group"> 
              <input v-model="cvv" v-maska="'###'" class="form-control form-control-text" placeholder=""
               id="cvv" type="text" maxlength="3" >   
              <label for="cvv" class="required">{{label.cvv}}</label>   
              </div>
              
              </div>
             </div> <!--row-->
             
             <div class="row">
              <div class="col">
              
              <div class="form-label-group"> 
              <input v-model="card_name" class="form-control form-control-text" placeholder=""
               id="card_name" type="text" maxlength="255" >   
              <label for="card_name" class="required">{{label.card_name}}</label>   
              </div>
              
              </div>
              <div class="col">
              
              <div class="form-label-group"> 
              <input v-model="billing_address" class="form-control form-control-text" placeholder=""
               id="billing_address" type="text" maxlength="255" >   
              <label for="billing_address" class="required">{{label.billing_address}}</label>   
              </div>
              
              </div>
             </div> <!--row-->
             
             
	         </form> <!--forms-->
	         	         
	         <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			 </div>    
	         
	       </div> <!--modal body-->
	       	       
	        <div class="modal-footer justify-content-start">	        
		       <button class="btn btn-green w-100" @click="submitForms" :class="{ loading: is_loading }" :disabled="hasFillForm"  >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	       
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->    
	`
};
