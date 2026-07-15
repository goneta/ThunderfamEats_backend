
const componentsmanageStripe = {
	props: ['label','ajax_url','api_url','publish_key','payment_code','return_url'],
	data() {
      return {
         is_loading: false,         
         plan_loading: false,    
         payment_loading : false,
         card_loading : false,
         error : '',
         data : [],
         invoice : [],
         stripe : undefined,     
         package_uuid : '',
         plan_details : [],
         subscribe_link : '',
         client_secret : '',   
         card_data : [],
         card_default : '',     
         invoice_data : [],
         invoice_loading : false,
      }
    },     
    mounted() {    	    	
    	this.getPlan();    	
    	this.getCards();
    },
    updated() {    	
    	
    },
    computed: {
		hasData(){						
			if(empty(this.error)){
		       return true;
		    } 
		    return false;
		},		
		hasCard(){						
			if(!empty(this.data.cards)){				
				if(!empty(this.data.cards.last4)){
					return true;
				}
			}
			return false;
		},
		hasCards(){						
			if(this.card_data.length>0){
				return true;
			}
			return false;
		},
    },
    methods: {  
    	getPlan(){
  	 	   this.is_loading = true;
  	 	   axios({
			   method: 'POST',
			   url: this.ajax_url+"/getMerchantPlan" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){						 	 	
			 	 	this.data = response.data.details;	
			 	 	this.package_uuid = response.data.details.package_uuid;
			 	 	this.subscribe_link = response.data.details.subscribe_link;
			 	 } else {						 	 				 	 	
			 	 	this.data = [];			
			 	 	this.error = response.data.msg;				 	 	
			 	 	this.subscribe_link = response.data.details.subscribe_link;
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.is_loading = false;
			 });						 			 
	  	},	  
	  	showPlan(){	  		
	  		this.$emit('showPlan');	  		
	  	},
	  	goBack(){
	  		this.close();
	  		this.$emit('showPlan');
	  	},
	  	cancelSubscription(){
	  		
	  		bootbox.confirm({ 
			    size: "small",
			    title : "" ,
			    message: '<h5>'+this.label.confirm_cancel+'</h5>' + '<p>'+ this.label.confirm_cancel_sub +'</p>',
			    centerVertical: true,
			    animate: false,
			    buttons: {
			    	cancel: {
			    	   label: this.label.cancel,
			    	   className: 'btn btn-black small pl-4 pr-4'
			    	},
			    	confirm: {
			            label: this.label.confirm,
			            className: 'btn btn-green small pl-4 pr-4'
			        },
			    },
			    callback: result => {				    	
			    	if(result){
			    		this.confirmCancelSubscription();
			    	}
			    }
			});				
	  		
	  	},
	  	confirmCancelSubscription(){	  	
	  				  
	  		 this.$emit('showLoading');	 
  	 	     axios({
			   method: 'POST',
			   url: this.ajax_url+"/cancelSubscription" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&package_uuid=" + this.package_uuid ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){						 	 	
			 	 	this.$emit('notify',response.data.msg);
			 	 	this.$emit('afterCancelplan');
			 	 	this.getPlan();
			 	 } else {						 	 				 	 	
			 	 	this.$emit('notify',response.data.msg,'error');
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoading');
			 });			
	  		
	  	},
	  	changePlan(package_uuid){	  	  		
	  		this.show();
	  		this.package_uuid = package_uuid;
	  		this.getPlanDetails();
	  	},
	  	show(){    	  	 	  	
  	  	 $( this.$refs.modal_plan_confirm ).modal('show');
  	    },
  	    close(){  	  	 
  	  	  $( this.$refs.modal_plan_confirm ).modal('hide');
  	    },
  	    getPlanDetails(){
  	       this.plan_loading = true;	  	    	
  	 	   axios({
			   method: 'POST',
			   url: this.api_url+"/getPlanDetails" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&package_uuid=" + this.package_uuid ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){						 	 	
			 	 	this.plan_details = response.data.details;
			 	 } else {						 	 				 	 	
			 	 	this.plan_details = [];			 	 				 	 
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			    this.plan_loading = false;
			 });			
			 
  	    },
  	    confirmPlan(){
  	       this.plan_loading = true;	  	    	
  	 	   axios({
			   method: 'POST',
			   url: this.ajax_url+"/changePlan" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&package_uuid=" + this.package_uuid ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){		
			 	 	this.close();			 	 		 
			 	 	this.$emit('notify',response.data.msg);
			 	 	this.$emit('afterChangeplan');
			 	 	this.getPlan();
			 	 	this.$emit('refreshDatatables');
			 	 } else {						 	 				 	 	
			 	 	this.$emit('notify',response.data.msg,'error');
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			    this.plan_loading = false;
			 });			
  	    },  	   
  	    createIntent(){
  	    	
  	        this.$emit('showLoading');	   
  	 	    axios({
			   method: 'POST',
			   url: this.ajax_url+"/createIntent" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&package_uuid=" + this.package_uuid + "&payment_code=" + this.payment_code ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){
			 	 	this.client_secret = response.data.details.client_secret;
			 	 	this.initStripe();
			 	 } else {			
			 	 	this.client_secret = ''; 	 
			 	 	this.$emit('notify',response.data.msg,'error');	 	 				 	 				 	 	
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			    this.$emit('closeLoading');	
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
			$( this.$refs.modal_card ).modal('show');		
			this.payment_loading = true;
			
			this.stripe = Stripe( this.publish_key );
			const options = {
			  clientSecret: this.client_secret,
			};
			this.elements = this.stripe.elements(options);			
			this.cardElement = this.elements.create('payment');
			setTimeout(() => {	
			   this.cardElement.mount( this.$refs.card_element );			   
			}, 10); 
			
			setTimeout(() => {	
			   this.payment_loading = false;
			}, 1000); 
		},
		submitForms(){
			
			this.payment_loading =  true;
		    var elements = this.elements;
			
			this.stripe.confirmSetup({
			  elements,
			  confirmParams: {			    
			    return_url: this.return_url+ "?package_uuid=" + this.package_uuid + "&payment_code=" + this.payment_code ,
			  },			 
			}).then( (result) => {
				 dump("ERROR");
				 dump(result);
				 if(result.error){
			        this.payment_loading =  false;			        
			        this.$emit('notify', result.error.message ,'error');	
				 }
			});
			
		},
		getCards(){	
			this.card_loading =  true;		    
			axios({
			   method: 'POST',
			   url: this.ajax_url+"/getCards" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){	
			 	 	this.card_data = response.data.details.cards;
			 	 	this.card_default = response.data.details.default;
			 	 } else {						 	 	
			 	 	this.card_data = [];
			 	 	this.card_default  = '';
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			    this.card_loading =  false;
			 });		
		},
		confirmDeleteCard(data){
			
			bootbox.confirm({ 
			    size: "small",
			    title : "" ,
			    message: '<h5>Delete payment method</h5>' + '<p>This will permanently delete your payment method.</p>',
			    centerVertical: true,
			    animate: false,
			    buttons: {
			    	cancel: {
			    	   label: this.label.cancel,
			    	   className: 'btn btn-black small pl-4 pr-4'
			    	},
			    	confirm: {
			            label: this.label.confirm,
			            className: 'btn btn-danger small pl-4 pr-4'
			        },
			    },
			    callback: result => {				    	
			    	if(result){
			    		this.deleteCard(data.payment_method);
			    	}
			    }
			});							
		},
		deleteCard(payment_method){
			this.$emit('showLoading');	 
			axios({
			   method: 'POST',
			   url: this.ajax_url+"/deleteCard" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&payment_method=" + payment_method ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){	
			 	 	 this.refreshComponents();
			 	 } else {						 	 	
			 	 	 this.$emit('notify',response.data.msg,'error');
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoading');
			 });		
		},
		setCardDefault(data){
			this.$emit('showLoading');	 
			axios({
			   method: 'POST',
			   url: this.ajax_url+"/setCardDefault" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&payment_method=" + data.payment_method ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){	
			 	 	 this.refreshComponents();
			 	 } else {						 	 	
			 	 	this.$emit('notify',response.data.msg,'error');
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoading');
			 });		
		},
		invoiceDetails(invoice_number){
			this.$emit('showLoading');	 
			axios({
			   method: 'POST',
			   url: this.ajax_url+"/invoiceDetails" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&invoice_number=" + invoice_number ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){					 	 	
			 	 	this.invoice_data = response.data.details;
			 	 	$( this.$refs.modal_invoice ).modal('show');		 	 	 
			 	 } else {						 	 	
			 	 	this.$emit('notify',response.data.msg,'error');
			 	 	this.invoice_data = [];
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoading');
			 });		
		},
		payInvoice(){			
			$( this.$refs.modal_invoice ).modal('hide');
			this.$emit('showLoading');	 
			axios({
			   method: 'POST',
			   url: this.ajax_url+"/payInvoice" ,
			   data : 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&invoice_id=" + this.invoice_data.id ,
			   timeout: $timeout_cmp ,
			 }).then( response => {	 
			 	 if(response.data.code==1){					 	 	
			 	 	this.refreshComponents();
			 	 } else {						 	 	
			 	 	this.$emit('notify',response.data.msg,'error');			 	 	
			 	 }
			 }).catch(error => {	
			    //
			 }).then(data => {			     
			     this.$emit('closeLoading');
			 });		
			 
		},
		refreshComponents(){
			this.getPlan();
			this.getCards();
			this.$emit('refreshDatatables');
		},
    },
    template: `	
    <div class="card m-auto">
    	    
	<div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	</div>
      	 
	 <div v-if="hasData" class="card-body">
	   	 
	  <h5 class="mb-4">{{label.manage_plan}}</h5>
	  	  
	  
	  <div class="d-flex justify-content-between">
	   <div class="flex-col">{{label.current_plan}}</div>
	   <div class="flex-col">{{data.plan_title}}</div>
	  </div>
	  
	  <div class="d-flex justify-content-between">
	   <div class="flex-col">{{label.credit_card}}</div>
	   <div v-if="hasCard" class="flex-col">{{data.cards.brand}}<span class="mr-1 ml-1">&middot;&middot;&middot;&middot;&middot;</span>{{data.cards.last4}}</div>
	   <div v-else>
	     <p class="font11 text-muted">No default payment</p>
	   </div>	   
	  </div>
	  
	  <div class="mt-3">
	   <p>
	   <a class="link btn p-0" @click="showPlan" :class="{disabled:!hasCard}">
	   <h5 class="m-0 d-inline position-relative pr-4">
	    <span class="chevron">{{label.change_plan}}</span>
	   </h5>
	   </a>
	   </p>
	   
	   <p>
	   <a class="link" @click="cancelSubscription">
	   <h5 class="m-0 d-inline position-relative pr-4">
	    <span class="chevron">{{label.cancel_subscriptions}}</span>
	   </h5>
	   </a>
	   </p>
	   
	  </div>
	  <!--mt-3-->
	 
	 </div> <!-- card body -->
	 
	 <!--- SUBSCRIBE TO PLAN -->
	 <div v-else class="card-body">

	   <h5 class="mb-4">{{label.manage_plan}}</h5> 
	   
	   <div class="mt-3">
		   <p v-if="subscribe_link">
		   <a class="link" :href="subscribe_link">
		   <h5 class="m-0 d-inline position-relative pr-4">
		    <span class="chevron">{{label.subscribe}}</span>
		   </h5>
		   </a>
		   </p>
	   </div>
	 
	 </div> <!-- card body -->	 
	</div> <!--card -->
		
 
   <!-- MANAGE CARD -->
   <div class="card mt-3">
   
    <div v-if="card_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	</div>
   
   <div class="card-body">

      <div class="d-flex justify-content-between mb-4">
       <div><h5 class="mb-0">Manage card</h5>  </div>
       <div><a class="link m-0" @click="createIntent">Add card</a></div>
      </div>
                
     <!-- cards -->
     <div v-for="cards in card_data" class="d-flex justify-content-between align-items-center mb-2">
       <div class="flexcol">
          <h6 class="text-secondary m-0">{{cards.brand}} &middot;&middot;&middot;&middot; {{cards.last4}}</h6>
		  <p class="text-secondary m-0">{{cards.expiry}}</p>
		  <div v-if="card_default==cards.payment_method" class="badge badge-light">Default</div>
       </div> 
       <div class="flexcol">
         
        <div class="dropdown">
		  <button class="btn btn-sm btn-link" type="button" id="card_dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		    <h5 class="m-0 text-secondary"><i class="zmdi zmdi-more"></i></h5>
		  </button>
		  <div class="dropdown-menu" aria-labelledby="card_dropdown">
		    <a class="dropdown-item" :class="{disabled : card_default==cards.payment_method}" @click="setCardDefault(cards)" >Make default</a>
		    <a class="dropdown-item" :class="{disabled : card_default==cards.payment_method}" @click="confirmDeleteCard(cards)" >Delete</a>		    
		  </div>
		</div>
       
       </div>
     </div>
     
     <p v-if="!hasCards" class="text-muted text-center">No cards added yet</p>
     <!-- cards -->
		      
   
   </div> <!-- card body -->	 
   </div> <!--card -->
   <!-- END MANAGE CARD -->
	
   
	<div ref="modal_plan_confirm" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
    
     <div v-if="plan_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	   </div>
    
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Confirm your new plan</h5>      
      </div>
      
      <div class="modal-body ">
             
      
        <h5>CHANGING TO</h5>
        
        <div class="d-flex w-50 justify-content-between">
         <div>{{plan_details.title}}</div>
         <div>
         
         <template v-if="plan_details.promo_price_raw>0" >
           {{plan_details.promo_price}}
         </template>
         <template v-else >
           {{plan_details.price}}
         </template>
         /{{plan_details.package_period}}
         </div>
        </div>
       
      </div>  <!-- modal body -->  
	
       <div class="modal-footer">        
      
        <button type="button" class="btn btn-black" @click="goBack">
          <span class="pl-3 pr-3">Go back</span>
        </button>
	               
        <button type="button" @click="confirmPlan" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"                  
         >
          <span>Confirm</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
     </div> <!--content-->
    </div> <!--dialog-->
    </div> <!--modal-->     	 

    
    <div ref="modal_card" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
    
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Add Card</h5>      
        </div>
      
       <div class="modal-body ">  
              
         <div class="mb-4" ref="card_element" id="card-element"></div>   
          
       </div>
       <!-- modal-body -->
       
        <div class="modal-footer">        
      
       <button type="button" class="btn btn-black" data-dismiss="modal" >
          <span class="pl-3 pr-3">Cancel</span>
        </button>
	               
        <button type="button" @click="submitForms" class="btn btn-green pl-4 pr-4" :class="{ loading: payment_loading }"                  
         >
          <span>Submit</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
    
    </div> <!--content-->
    </div> <!--dialog-->
    </div> <!--modal-->     	

    
    <div ref="modal_invoice" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">   
    
      <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Invoice</h5>      
      </div>
      
      <div class="modal-body "> 
            
      <h5>Summary</h5>
      <div class="row">
        <div class="col">
        
          <div class="row no-gutters">
           <div class="col font12">Billed to</div>
           <div class="col font12">{{invoice_data.customer_email}}</div>
          </div>    
          
          <div class="row no-gutters">
           <div class="col font12">Name</div>
           <div class="col font12">{{invoice_data.customer_name}}</div>
          </div>  
          
          <div class="row no-gutters">
           <div class="col font12">Address</div>
           <div class="col">
             <span v-if="invoice_data.customer_address" class="font12">{{invoice_data.customer_address}}</span>
             <span v-else class="text-muted font12">No address</span>
           </div>
          </div>  
          
           <div class="row no-gutters">
           <div class="col font12">Phone number</div>
           <div class="col">
             <span v-if="invoice_data.customer_address" class="font12">{{invoice_data.customer_phone}}</span>
             <span v-else class="text-muted font12">No phone number</span>           
           </div>
          </div> 
          
          <div class="row no-gutters">
           <div class="col font12">Currency</div>
           <div class="col font12">{{invoice_data.currency}}</div>
          </div>  
        
        </div> <!-- col -->
        <div class="col">
        
          <div class="row no-gutters">
           <div class="col font12">Invoice number</div>
           <div class="col font12">{{invoice_data.number}}</div>
          </div>   
          
        
        </div> <!-- col -->
      </div> <!-- row -->
            
      
      <table class="table table-borderedx mt-3">
       <thead>
        <tr>
         <th colspan="2">DESCRIPTION</th>
         <th width="15%" class="text-right">AMOUNT</th>
         <th width="5%"></th>
        </tr>
       </thead>
       <tbody>
       
        <tr v-for="item in invoice_data.items">
         <td colspan="2" width="70%">
           <p class="m-0">{{item.description}}</p>
           <p class="m-0">{{item.period_start}} - {{item.period_end}}</p>
         </td>
         <td class="text-right" >{{item.amount}}</td>
         <td></td>
        </tr>
        
        <tr>
         <td width="50%"></td>
         <td class="text-right"><b>Subtotal</b></td>
         <td class="text-right">{{invoice_data.subtotal0}}</td>        
         <td></td> 
        </tr>
        
        <tr>
         <td></td>
         <td class="text-right"><b>Total</b></td>
         <td class="text-right">{{invoice_data.total0}}</td>       
         <td></td>  
        </tr>
        
        <tr v-if="invoice_data.amount_paid>0">
         <td></td>
         <td class="text-right text-muted">Amount paid</td>
         <td class="text-right">-{{invoice_data.amount_paid0}}</td>         
         <td></td>
        </tr>
        
        <tr>
         <td></td>
         <td class="text-right"><b>Amount due</b></td>
         <td class="text-right">{{invoice_data.amount_due0}}</td>         
         <td></td>
        </tr>
        
        
       </tbody>
      </table>
      
      </div>
       <!-- modal-body -->
       
      
      <div class="modal-footer border-white">       
      
       <button type="button" class="btn btn-black" data-dismiss="modal" >
          <span class="pl-3 pr-3">Close</span>
        </button>
        
        <button v-if="invoice_data.status=='open'" type="button" @click="payInvoice" class="btn btn-green pl-4 pr-4" :class="{ loading: invoice_loading }"                  
         >
          <span>Pay invoice</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
        
      </div>
    
    </div> <!--content-->
    </div> <!--dialog-->
    </div> <!--modal-->     	
    `
};