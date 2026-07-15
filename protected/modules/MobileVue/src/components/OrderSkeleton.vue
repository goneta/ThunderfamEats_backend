<template>
  <!-- <div class="text-center margin-auto" style="width:25%;">
    <h4 class="no-margin text-weight-bold q-pb-sm"><q-skeleton type="rect" /></h4>
    <p><q-skeleton type="text" class="w-50 margin-auto" /></p>
  </div>

  <q-skeleton type="QInput"  height="35px" class="rounded q-mb-sm" /> -->

  <q-list>
    <q-item v-for="i in 5" :key="i">
      <q-item-section side>
        <q-skeleton
          square
          style="height: 50px; width: 50px"
          class="rounded-borders"
        />
      </q-item-section>
      <q-item-section top>
        <q-skeleton type="text" style="width: 100px" />
        <q-skeleton type="text" style="width: 120px" />
        <q-skeleton type="text" style="width: 120px" />
      </q-item-section>
      <q-item-section side top>
        <q-skeleton type="text" style="width: 50px" />
        <q-skeleton type="text" style="width: 50px" />
      </q-item-section>
    </q-item>
  </q-list>
</template>

<script>
export default {
  name: "OrderSkeleton",
};
</script>
