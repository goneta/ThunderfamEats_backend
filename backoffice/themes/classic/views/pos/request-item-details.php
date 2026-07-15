<q-dialog
v-model="modal"    
:maximized="this.$q.screen.lt.sm?true:false"
:position="this.$q.screen.lt.sm?'bottom':'standard'"
@before-show="beforeShow"
@show="OnShow"
persistent
>
<q-card style="min-height: calc(35vh);width: 350px; max-width: 80vw;">

<q-card-section class="row items-center q-pb-none bg-primary text-white">   
   <div class="text-weight-regular text-body1 line-normal ellipsis">
    <span>#Main-10</span>
   </div>
   <q-space ></q-space>
   <q-btn icon="close" color="white" flat round dense v-close-popup ></q-btn>
</q-card-section>

<q-list separator>
    <template v-for="items in 5">
    <q-item tag="label" v-ripple>
        <q-item-section avatar>
            <q-checkbox v-model="request" val="teal" color="primary" />
        </q-item-section>
        <q-item-section>
        <q-item-label>1 x Chopstick</q-item-label>
        </q-item-section>
    </q-item>
    </template>
</q-list>

<q-card-section>    
<q-card-actions>
    <q-btn outline 
    color="primary" 
    label="Completed" 
    class="fit radius6" 
    no-caps
    size="16px"
    >        
    </q-btn>
</q-card-actions>
</q-card-section>

</q-card>
</q-dialog>