<html>
<head>
<title><?php echo t("Processing payment")?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
<link href="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css" rel="stylesheet" type="text/css">
</head>

<body>

<div id="q-app">    
    <div class="flex flex-center fit">
        <div class="text-center">
           <div><h5><?php echo t("Waiting for payment")?>...</h5></div>    
           <div class="text-body1 text-grey"><?php echo t("Please don't close this window")?></div> 
           <div class="q-mt-sm">
              <q-spinner-oval color="amber" size="lg" ></q-spinner-oval>
           </div>
        </div>
    </div>
</div>
<!-- q-app -->

<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
const app = Vue.createApp({
    data() {
        return {
            loading : false,
        }
    },
    mounted() {         
        this.validatePayment();
    },
    methods: {
        validatePayment(){
            let params = "transaction_uuid="+ transaction_uuid;
            params+="&order_uuid="+order_uuid;
            params+="&cart_uuid="+cart_uuid;
            params+="&is_app="+is_app;
            axios.get(mtn_api+"/getPaymentstatus?" + params ).then(response => {                                
                if(response.data.code==1){
                    location.href = response.data.details.redirect;                                                                    
                } else if (response.data.code==3) {                                        
                    setTimeout(() => {              
                        this.validatePayment();
                    }, 10000); 
                } else {
                    location.href = response.data.details.redirect; 
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {						
			});  
        },
        //
    },
});

app.use(Quasar)
app.mount('#q-app')
</script>
</body>
</html>