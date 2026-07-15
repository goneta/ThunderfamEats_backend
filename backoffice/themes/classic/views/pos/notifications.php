<!-- NOTIFICATIONS HERE -->
<q-btn round icon="las la-bell" size="12px" unelevated color="light" text-color="grey-7">
<template v-if="count>0">
   <q-badge floating color="red" rounded :label="count" ></q-badge>
</template>
<q-menu class="q-pt-mdx q-pb-md overflow-hidden-x" transition-show="slide-up" >

<!-- <pre>{{getData}}</pre> -->

   <template v-if="hasData">      
   <q-list separator style="min-width: 380px">                                
        <q-item>
           <q-item-section class="text-weight-bold">              
              <?php echo t("Notification")?>
           </q-item-section>
           <q-item-section side>
            <q-btn 
            label="<?php echo t("Clear all")?>" 
            dense 
            flat 
            no-caps 
            color="blue"
            @click="clearNotifications"
            >
            </q-btn>
           </q-item-section>
        </q-item>
        <template v-for="items in getData">
        <q-item clickablex class="q-pt-md q-pb-md">
            <q-item-section>
            <q-item-label>
               {{items.message}}
            </q-item-label>   
            <q-item-label caption>
               {{items.date}}
            </q-item-label>      
            </q-item-section>                       
        </q-item>                   
        </template>             
    </q-list>

   </template>
   <template v-else>
       <div v-if="!loading" style="min-width: 380px" class="flex flex-center card-small">
            <div class="text-center text-body2">
                <div class="text-weight-regular"><?php echo t("No notifications yet")?></div>
                <div class="text-caption text-grey"><?php echo t("When you get notifications, they'll show up here")?></div>
            </div>
        </div>
   </template>    
   
</q-menu>
</q-btn>