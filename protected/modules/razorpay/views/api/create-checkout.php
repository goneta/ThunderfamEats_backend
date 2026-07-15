<html>
<head>
<title><?php echo $this->pageTitle?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
</head>
<body>

<div id="q-app" >

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
            >            
            </q-btn>
            </q-toolbar>
        </q-header>

      
        <div class="text-center">

          <template v-if="loading">
             <q-circular-progress
              indeterminate
              size="lg"
              :thickness="0.22"
              rounded
              color="orange-1"
              track-color="orange"
            />
          </template>                        

        </div>

      </q-page>
    </q-page-container>

</q-layout>

</div>
</body>
</html>