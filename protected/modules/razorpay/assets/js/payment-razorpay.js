const app = Vue.createApp({
    data() {
        return {
            loading :true
        }
    },
    mounted() {        
        this.init();
    },
    methods: {
        init(){                     
            try {
                
                this.loading = true;
                const options = {
				    "key": key_id, 
				    "amount": parseFloat(amount) ,
				    "currency": currency_code ,
				    "name": restaurant_name ,
				    "description":  payment_description,
				    "order_id": order_id,
				    "customer_id" : customer_id,                    
                    //"callback_url" : callback_url,
				    "handler":  response => {
                        console.log("response",response);                        
                        this.verifyPayment(response);
				    },			    
				    "theme": {
				        "color": "#3399cc"
				    },
				      "modal": {
				        "ondismiss": data => {	
				            console.log("close payment");
                            window.location.href = redirect_url;
				        }
				    }
				};   
                console.log("options",options);

                var rzr_handle = new Razorpay(options);
				rzr_handle.on('payment.failed',  response => {                    
                    this.notify("red",response?.error?.description || "Error has occured" ,'error');
                });				
                this.loading = false;							
				rzr_handle.open();				

            } catch(err) {
                console.log("err",err);
            }
        },
        verifyPayment(value){
            console.log("verifyPayment",value);  
            const $q = Quasar.Dialog;
            const dialog = $q.create({
                message: payment_processing,
                progress: true, 
                persistent: true, 
                ok: false,
                html:true
            })          
            const final_url = (!return_url || return_url === 'null') ? '' : return_url    
            const params = new URLSearchParams({
                razorpay_payment_id : value.razorpay_payment_id,
                razorpay_order_id : value.razorpay_order_id,
                razorpay_signature : value.razorpay_signature,
                order_uuid : order_uuid,
                cart_uuid : cart_uuid,                
                request_from : request_from,
                return_url : final_url,
            }).toString();            
    		axios({
				method: 'POST',				
                url: ajax_url +"/"+verifyMethod ,
				data : params,				
			  }).then( response => {	 
                   console.log("response",response);
				   if(response.data.code==1){								  
                      window.location.href = response.data.details.redirect_url;
				   } else {							                       
                       this.notify("red",response.data.msg || "Error has occured" ,'error','bottom','2000');  
                       setTimeout(() => {            			    					  
                         window.location.href = response.data.details.redirect_url;
                       }, 1000);               
				   }			 			 	 
			  }).catch(error => {	
				 //
			  }).then(data => {			
                 setTimeout(() => {
                    dialog.hide();
                 }, 1000);                                      
			  });			
        },
        notify (color, message, icon, position , timeout, iconColor , actions) {		
            if ( typeof position !== "undefined" && position !== null) {			
            } else {
                position = "bottom";
            }		
            if ( typeof timeout !== "undefined" && timeout !== null) {			
            } else {
                timeout = 3000;
            }			
            if ( typeof iconColor !== "undefined" && iconColor !== null) {			
            } else {
                iconColor = color;
            }			
            if ( typeof actions !== "undefined" && actions !== null) {			
            } else {
                actions = [];
            }			
            const $q = Quasar.Notify;			
            $q.create({
            message,
            color,
            icon,
            iconColor:iconColor,
            classes: "primevue_toats",
            position: position,
            html: true,
            timeout: timeout,
            multiLine: false,
            actions: actions			
            })
        },
        //
    },
});

app.use(Quasar)
app.mount('#q-app')