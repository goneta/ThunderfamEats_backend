/*
COMPONENTS STRIPE PLANS
*/
const componentsplansStripe = {	
	props: ['title','label','ajax_url','payment_code','payment_id','subscription_type'],
	data() {
      return {
         loading : false,      
      }
    },        
    methods: {    	
		subscribe(){ 
			this.$emit('showLoader'); 	
			let data = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content');			
			data="&payment_id="+payment_id;
    		axios({
				method: 'POST',
				url: this.ajax_url +"/createSubscriptions" ,
				data : data,				
			  }).then( response => {	 
				   if(response.data.code==1){			
					  window.location.href = response.data.details.checkout_url;
				   } else {										    
					  this.$emit('errorMessage',response.data.msg);
				   }			 			 	 
			  }).catch(error => {	
				 //
			  }).then(data => {			     
				 this.$emit('closeLoader'); 				
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
	template:`		 
	`
};