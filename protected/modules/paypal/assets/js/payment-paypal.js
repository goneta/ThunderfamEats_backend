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

                let paypalHandle = null;
                this.loading = true;

                paypalHandle = paypal.Buttons({
                    createOrder: (data, actions) => {
                    return actions.order.create({
                        purchase_units: [{
                            invoice_id : order_uuid,
                            custom_id :  transaction_type + '|' + cart_uuid,                            
                            amount: {
                               value: amount,
                            },
                        }],
                    });
                    },
                    onCancel: (data) => {
                         //
                    },
                    onError: (error) => {                                        
                        this.notify("red",error ,'error');
                    },
                    onApprove: (data, actions) => {
                    return actions.order.capture().then((orderData) => {
                        const transaction = orderData.purchase_units[0].payments.captures[0];
                        console.log("transaction_type", transaction_type);                        
                        const final_url = (!return_url || return_url === 'null') ? '' : return_url    
                        const params = {
                            transaction_id: transaction.id,
                            order_id: orderData.id,
                            order_uuid: order_uuid,
                            cart_uuid: cart_uuid,
                            request_from : request_from,
                            return_url : final_url
                        };                                              
                        this.verifyPayment(params);                                      
                    });
                    },
               });

               this.loading = false;
               paypalHandle.render(this.$refs.paypal_button);

            } catch(error) {                
                this.notify("red",error ,'error');
            }
        },        
        verifyPayment(value){
          const $q = Quasar.Dialog;
          const dialog = $q.create({
              message: payment_processing,
              progress: true, 
              persistent: true, 
              ok: false,
              html:true
          })          

          const params = new URLSearchParams(value).toString();
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
               this.notify("red",error ,'error');        
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