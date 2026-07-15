xx<html>
<head>
<title><?php echo $page_title ?? '' ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
<link href="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet" type="text/css">
</head>
<body>

<div id="q-app" >

<q-layout view="hHh lpR fFf">

    <q-page-container>
      <q-page >
        <q-header class="bg-white">
          <q-toolbar>
            <q-btn                
                flat
                round
                dense
                icon="arrow_back"
                color="dark"
                href="<?php echo $redirect_url;?>"
            >            
            </q-btn>
            <q-toolbar-title class="text-subtitle2 text-weight-bold text-dark">
              <?php echo $page_title ?? '' ?>
            </q-toolbar-title>
            </q-toolbar>
        </q-header>

      
        <div class="absolute-center full-width q-pa-md" >

          <div  class="text-center">
            <template v-if="loading">
              <q-circular-progress
                indeterminate
                size="md"
                :thickness="0.22"
                rounded
                color="orange-1"
                track-color="orange"
              />
            </template>                                  
            <div ref="paypal_button"></div>                             
          </div>

        </div>

      </q-page>
    </q-page-container>

</q-layout>

</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js"></script>

<script>
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
                            reference_id : order_uuid,
                            transaction_type : transaction_type,
                            cart_uuid : cart_uuid,
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
                        if (transaction_type == "purchase_food") {
                            const params = {
                                transaction_id: transaction.id,
                                order_id: orderData.id,
                                order_uuid: order_uuid,
                                cart_uuid: cart_uuid,
                                request_from : request_from,
                                return_url : return_url
                            };                             
                            this.verifyPayment(params);              
                        } else if (transaction_type == "wallet_loading") {
                        }
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
          url: ajax_url +"/verifyPayment" ,
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
</script>
</body>
</html>