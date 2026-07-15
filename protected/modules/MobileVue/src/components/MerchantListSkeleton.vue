<template>
  <q-list  class="qlist-no-padding">
      <q-item v-for="i in 5" :key="i"  clickable v-ripple>
        <q-item-section side>
            <q-skeleton style="height:80px; width:80px;" square />
        </q-item-section>
        <q-item-section top>
           <q-skeleton  type="text" style="width:40%" />
           <q-skeleton  type="text"  />
           <div class="row q-mt-sm q-col-gutter-sm">
             <div class="col-3"><q-skeleton  type="text" /></div>
             <div class="col-4"><q-skeleton  type="text" /></div>
           </div>
        </q-item-section>
      </q-item>
   </q-list>
</template>

<script>
export default {
  name: 'MerchantListSkeleton',
  setup () {
    return {}
  }
}
</script>
