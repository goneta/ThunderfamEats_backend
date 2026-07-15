const headerAuthorization = {
	headers: {
		'Authorization': `token ${token}`,					
	}
};

const app_plans = Vue.createApp({
	data() {
		return {
			loading : false,
			data :null,		
			history_data : null,
			loading_history : false,
			loading_cancel : false,
			payment_code :null,
			subscriber_id : null,
			subscription_type : null,
			features_list : null,
			subscription_features : null
		}
	},
	created() {
		this.getActiveSubscriptions();
	},
	mounted() {		
		this.getSubscriptionHistory();
		if ((typeof subscriber_id !== "undefined") && ( subscriber_id !== null)) {
			this.subscriber_id = subscriber_id;
	    }	   				
	},
	computed: {
		hasData(){
			return this.data?true:false;
		},
		getData(){
			return this.data?this.data:null;
		},
		hasHistory(){
			return this.history_data?true:false;
		}
	},
	methods: {		
		getActiveSubscriptions(){		
			this.loading = true;	
			axios({
				method: 'GET',
				url: plan_api+"/getActivePlan?language="+language ,					
					headers: {
					Authorization: `token ${token}`,
				},
				}).then( result => {	 					
					if(result.data.code==1){ 	
						this.data = result.data.details.data;
						this.features_list = result.data.details.features_list;
						this.subscription_features = result.data.details.subscription_features;
					} else {
						this.data = null;
					}
				}).catch(error => {
					this.data = null;	
				//
				}).then(data => {			     
					this.loading = false;				
			  });          
		},
		getSubscriptionHistory(){
			this.loading_history = true;	
			axios({
				method: 'GET',
				url: plan_api+"/getSubscriptionHistory?language="+language ,					
					headers: {
					Authorization: `token ${token}`,
				},
				}).then( result => {	 					
					if(result.data.code==1){ 	
						this.history_data = result.data.details.data;
					}                    					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.loading_history = false;				
			  });          
		},
		statusColor(data){
			let $colors = '';
			switch (data) {
				case 'active':		
				    $colors = 'success';			
					break;					
				case "expired":		
				    $colors = 'danger';			
					break;					
				case "cancelled":						
				case "payment failed":			
				    $colors = 'danger';			
					break;				
				case "pending":	
				    $colors = 'info';			
				    break;				
			}
			return $colors;
		},
		confirmCancelSubscriptions(payment_code)
		{
			this.payment_code = payment_code;
			ElementPlus.ElMessageBox.confirm(
				'By cancelling, you will lose access to all benefits associated with your current plan. Any remaining item and order limits will be reset.',
				'Are you sure you want to cancel your subscription?',
				{
				  confirmButtonText: 'Confirm',
				  cancelButtonText: 'Cancel',
				  type: 'warning',
				}
			  )
				.then(() => {
				   this.cancelSubscriptions();
				})
				.catch(() => {

				});
		},
		cancelSubscriptions(){
			this.loading_cancel = true;	
			axios({
				method: 'GET',
				url: front_api+ "/" + this. payment_code + "/apiv2/cancelSubscriptions?language="+language + "&subscription_id=" + this.data.subscription_id ,					
					headers: {
					Authorization: `token ${token}`,
				},
				}).then( result => {	 					
					if(result.data.code==1){ 	
						this.SetCancelSubscriptions();
					} else {						
						ElementPlus.ElNotification({
							title: 'Something went wrong',
							message: result.data.msg,
							type: 'warning',
							position: 'bottom-right',
						});
					}               					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.loading_cancel = false;				
			  });          			
		},
		SetCancelSubscriptions(){
			this.loading = true;	
			axios({
				method: 'POST',
				url: plan_api+"/SetCancelSubscriptions?language="+language ,	
					headers: {
					Authorization: `token ${token}`,
				},
				data : "&subscription_id=" + this.data.subscription_id ,
				}).then( result => {	 					
					if(result.data.code==1){ 	
						this.data = result.data.details.data;
						this.getActiveSubscriptions();
						this.getSubscriptionHistory();
					} else {
						ElementPlus.ElNotification({
							title: 'Something went wrong',
							message: result.data.msg,
							type: 'warning',
							position: 'bottom-right',
						});
					}            					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.loading = false;				
			  });          
		},
		Subscribe(){
			this.subscription_type = 'subscribe';
			this.$refs.ref_plans.modal = true;		
		},
		UpdateSubscriptions(){
			this.subscription_type = 'updatesubscriptions';
			this.$refs.ref_plans.modal = true;		
		},
		RenewSubscriptions(){
			this.subscription_type = 'rewewsubscriptions';
			this.$refs.ref_plans.modal = true;		
		},
		createPaymentplan(package_id){
			console.log("subscriber_id",this.subscriber_id);
			console.log("subscription_type",this.subscription_type);			
			console.log("package_id",package_id);		
			
			this.loading = true;	

			let data = 'YII_CSRF_TOKEN=' + $('meta[name=YII_CSRF_TOKEN]').attr('content') + "&subscriber_id="+ this.subscriber_id + "&package_id="+ package_id;
			data+="&subscription_type="+ this.subscription_type;
			data+="&success_url="+ success_url;
			data+="&failed_url="+ failed_url;

			axios({
				method: 'POST',
				url: plan_api+"/createPaymentPlan?language="+language ,	
					headers: {
					Authorization: `token ${token}`,
				},
				data : data,
				}).then( result => {	 					
					if(result.data.code==1){ 	
						window.location.href = payment_url + "?payment_id="+ result.data.details.payment_id;
					} else {
						ElementPlus.ElNotification({
							title: 'Something went wrong',
							message: result.data.msg,
							type: 'warning',
							position: 'bottom-right',
						});
					}            					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.loading = false;				
			  });          

		}
		//
	},
});

const componentsPlanSelections = {
	template: '#xtemplate_planselection',
	props : ['subscription_type','package_id'],
	data() {
		return {
			modal : false,
			loading :false,
			data : null,
			plan_details : []
		}
	},
	methods: {
		getSubscriptionList(){
			//if(!this.data){
				this.loading = true;
				axios({
					method: 'GET',
					url: plan_api+"/getSubscriptionList?language="+language + "&subscription_type="+this.subscription_type+ "&package_id="+this.package_id ,					
						headers: {
						Authorization: `token ${token}`,
					},
					}).then( result => {	 					
						if(result.data.code==1){ 	
							this.data = result.data.details.data;
							this.plan_details =  result.data.details.plan_details;
						} else {						
							ElementPlus.ElNotification({
								title: 'Something went wrong',
								message: result.data.msg,
								type: 'warning',
								position: 'bottom-right',
							});
						}               					  
					}).catch(error => {	
					//
					}).then(data => {			     
						this.loading = false;				
				});          			
			//}			
		},
		setChoosePlan(package_id){			
			this.$emit('createPaymentplan',package_id);
		},
	},
};


app_plans.component('components-plan-selection',componentsPlanSelections);
app_plans.use(ElementPlus);
const vm_plans = app_plans.mount('#vue-subscriptions');


// const paymentPlan = Vue.createApp({
// 	data() {
// 		return {
// 			loading : false,
// 			data : [],
// 			payment_code :''
// 		}
// 	},
// 	mounted() {
// 		this.getPaymentList();
// 	},
// 	computed: {
// 		hasPaymentSelected(){
// 			return empty(this.payment_code)? false : true;
// 		},
// 	},
// 	methods: {
// 		isActive(data){
// 			return this.payment_code==data?true:false;
// 		},
// 		getPaymentList(){
// 			this.loading = true;
// 			axios({
// 				method: 'GET',
// 				url: plan_api+"/getPaymentList?language="+language ,					
// 					headers: {
// 					Authorization: `token ${token}`,
// 				},
// 				}).then( result => {	 					
// 					if(result.data.code==1){ 			
// 						this.data = result.data.details.data;				
// 					} else {						
// 						ElementPlus.ElNotification({
// 							title: 'Something went wrong',
// 							message: result.data.msg,
// 							type: 'warning',
// 							position: 'bottom-right',
// 						});
// 					}               					  
// 				}).catch(error => {	
// 				//
// 				}).then(data => {			     
// 					this.loading = false;	
// 			});          			
// 		},
// 		//
// 	},
// });

// if ((typeof  components_bundle !== "undefined") && ( components_bundle !== null)) {	
// 	$.each(components_bundle,function(components_name, components_value) {				
// 		paymentPlan.component(components_name, components_value );		
// 	});
// }

// paymentPlan.use(ElementPlus);
// const vm_payment = paymentPlan.mount('#vue-payment-plan');