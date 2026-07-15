<template>
  <q-card flat>
      <q-list>
        <q-item class="q-pb-none" style="min-height:auto;">
          <q-item-section class="text-weight-bold">
            <q-skeleton type="text" style="width:100px" />
          </q-item-section>
        </q-item>
         <q-item v-for="i in 3" :key="i" style="min-height:auto;" >
          <q-item-section >
            <q-item-label>
              <q-skeleton type="text"  />
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-skeleton type="QBadge" />
          </q-item-section>
       </q-item>
      </q-list>
  </q-card>
  <q-space class="q-pa-xs"></q-space>
   <q-card flat>
     <q-list>
       <q-item>
         <q-item-section class="text-weight-bold">
           <q-skeleton type="text" style="width:120px" />
         </q-item-section>
         <q-item-section side>
           <q-skeleton type="QBadge" />
         </q-item-section>
       </q-item>
     </q-list>
   </q-card>
   <q-space class="q-pa-xs"></q-space>
</template>

<script>
export default {
  name: 'TransactionStoreSkeleton'
}
</script>
