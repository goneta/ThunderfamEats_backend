/*
COMPONENTS STRIPE PLANS
*/
const componentsplansRazorpay = {	
	props: ['title','label','ajax_url','payment_id','key_id','subscription_type'],
	data() {
      return {
         is_loading: false,         
         error : [],
         stripe : undefined,
         elements: undefined,
         cardElement : undefined,
         cardholder_name : '',
         button_enabled : false,
         steps : 1,
         customer_id : '',
         client_secret : '',
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
					  this.CheckoutSubscriptions(response.data.details);
				   } else {										    
					  this.$emit('errorMessage',response.data.msg);
				   }			 			 	 
			  }).catch(error => {	
				 //
			  }).then(data => {			     
				this.$emit('closeLoader'); 
			  });			
		},    
		rewewsubscriptions(){
			this.$emit('showLoader'); 						
    		axios({
				method: 'GET',
				url: this.ajax_url +"/rewewsubscriptions",						
				params: {
					payment_id: payment_id					
				}
			  }).then( response => {	 
				   if(response.data.code==1){
					   let data = response.data.details;
					   let options = {
							"key": this.key_id,
							"subscription_id": data.subscription_id,
							"name": data.name,
							"description": data.description,
							"image": data.image,			
							"subscription_card_change": 1,
							"callback_url": data.callback_url,
							"theme": {
								"color": "#F37254"
							}
						};			
						var rzp1 = new Razorpay(options);
						rzp1.open();

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
		},
		CheckoutSubscriptions(data){			
			let options = {
				"key": this.key_id,
				"subscription_id": data.subscription_id,
				"name": data.name,
				"description": data.description,
				"image": data.image,			
				"callback_url": data.callback_url,
				"theme": {
					"color": "#F37254"
				}
			};			
			var rzp1 = new Razorpay(options);
			rzp1.open();
		},
		//		
    },
	template: `	
	 <div>	 	 
	 </div>
	`
};