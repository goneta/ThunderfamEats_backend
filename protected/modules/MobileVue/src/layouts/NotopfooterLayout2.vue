<template>
  <q-layout view="lHh Lpr lFf">
    <q-page-container>
      <router-view />
    </q-page-container>

    <PushDialog ref="push_dialog" />
  </q-layout>
</template>

<script>
import { defineComponent, defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useDeliveryschedStore } from "stores/DeliverySched";

export default defineComponent({
  name: "NotopfooterLayout",
  components: {
    PushDialog: defineAsyncComponent(() => import("components/PushDialog.vue")),
  },
  // setup() {
  //   const deliveryschedStore = useDeliveryschedStore();
  //   return { deliveryschedStore };
  // },
  // mounted() {
  //   if (!this.deliveryschedStore.hadTransactionList()) {
  //     this.deliveryschedStore.getDeliverySched(
  //       APIinterface.getStorage("cart_uuid")
  //     );
  //   }
  // },
});
</script>
<style scoped>
.q-page-container {
  padding-top: 0 !important;
}
</style>
