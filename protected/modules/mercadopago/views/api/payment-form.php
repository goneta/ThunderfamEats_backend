<html>
<head>
<title><?php echo t("Processing payment")?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
<link href="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css" rel="stylesheet" type="text/css">
<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet" type="text/css">
</head>

<body >

<div id="q-app">

<q-layout view="hHh lpR fFf">

    <q-page-container>
      <q-page class="flex flex-center">
        <q-header class="bg-white">
          <q-toolbar>
            <q-btn                
                flat
                round
                dense
                icon="arrow_back"
                color="dark"
                href="<?php echo $redirect_url;?>"
            />            
            </q-toolbar>
        </q-header>

      
        <div class="text-center">

            <template v-if="loading">
                <q-inner-loading
                :showing="true"
                label="<?php echo t("Please wait...")?>"
                label-class="text-primary"
                color="primary"
                >
                </q-inner-loading>
            </template>
            <template v-else>             
                <div>
                <h4 class="q-ma-none"><?php echo t("Amount to pay {amount_to_pay}",['{amount_to_pay}'=>$amount_to_pay])?></h4>
                </div>
            </template>
            
            <div id="wallet_container"></div>

        </div>

      </q-page>
    </q-page-container>

</q-layout>

</div>

<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.prod.js"></script>
<script src="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.umd.prod.js"></script>
<script src="https://sdk.mercadopago.com/js/v2"></script>

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
            const mp = new MercadoPago('<?php echo $public_key?>', {
                locale: 'en'
            });

            mp.bricks().create("wallet", "wallet_container", {
            initialization: {
                preferenceId: "<?php echo $payment_reference_id;?>",
            },
            callbacks: {
                onReady: () => {
                    this.loading = false;
                },
                onSubmit: () => {},
                onError: (error) => console.error(error),
            },
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