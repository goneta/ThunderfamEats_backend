<html>
<head>
<title><?php echo t("Processing payment")?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
<link href="https://cdn.jsdelivr.net/npm/quasar@2.14.5/dist/quasar.prod.css" rel="stylesheet" type="text/css">
</head>

<body >

<div id="q-app">

<q-layout view="hHh lpR fFf">

    <q-page-container>
      <q-page class="flex flex-center">
      
        <div class="text-center">
           <h5 class="q-ma-none">We receive your payment!</h5>
           <q-space class="q-pa-sm"></q-space>
            <p>Thank you for your payment! Your transaction is currently pending. 
                <br/>
                We’ll notify you as soon as it is confirmed. Please check your email for updates, or feel free to contact us if you have any questions.
            </p>            
            <q-space class="q-pa-md"></q-space>
            <q-btn color="primary" size="17px" label="Back to home" no-caps unelevated href="<?php echo $redirect_url;?>" ></q-btn>
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
    },
    methods: {        
    },
});

app.use(Quasar)
app.mount('#q-app')
</script>
</body>
</html>