/*
COMPONENTS PAYPAL PLANS
*/
const componentsplansPaypal = {	
	props: ['title','label','ajax_url','payment_id','client_id','subscription_type'],
	data() {
      return {
         loading : false,       
         error : [],         
         modal : false,         
      }
    },             
    methods: {    			    		
		subscribe(){       
            this.modal = true;     		            
            
			let data = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content');			
			data="&payment_id="+payment_id;
    		axios({
				method: 'POST',
				url: this.ajax_url +"/createSubscriptions" ,
				data : data,				
			  }).then( response => {	 
				   if(response.data.code==1){					   
                       let plan_id = response.data.details.price_id;
                       let callback_url = response.data.details.callback_url;

                       document.getElementById('paypal-button-container').innerHTML = '';

                       paypal.Buttons({
                        createSubscription: function(data, actions) {                
                          return actions.subscription.create({                
                           'plan_id': plan_id
                           });                
                         },                
                         onApprove: function(data, actions) {                                
                             window.location.href = callback_url+"&subscription_id="+ data.subscriptionID;
                         }            
                       }).render('#paypal-button-container');

				   } else {										    
					  this.$emit('errorMessage',response.data.msg);
				   }			 			 	 
			  }).catch(error => {	
				 //
			  }).then(data => {			     
				
			  });			
        },
        updatesubscriptions(){
            console.log("updatesubscriptions");
            this.$emit('showLoader'); 			
            let data = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content');			
            data="&payment_id="+payment_id;
              axios({
              method: 'POST',
              url: this.ajax_url +"/updatesubscriptions" ,
              data : data,				
              }).then( response => {	 				
                if(response.data.code==1){		
                            let next_actions = response.data.details.next_actions;			   
                            if(next_actions=="subscribe"){
                                this.subscribe();		  				
                              } else {
                                  window.location.href = response.data.details.redirect_url;
                              }					  
                } else {										    
                  this.$emit('errorMessage',response.data.msg);
                }			 			 	 
              }).catch(error => {	
              //
              }).then(data => {			     
              this.$emit('closeLoader'); 
              });			
        }
        //
    },
	template: `	
     <el-dialog
        v-model="modal"
        title="Paypal"        
        align-center
    >
      <div v-loading="loading">
          <div id="paypal-button-container"></div>
      </div>      
    </el-dialog>	
	`
};