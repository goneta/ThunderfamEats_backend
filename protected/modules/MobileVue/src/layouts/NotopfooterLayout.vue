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
  setup() {
    const deliveryschedStore = useDeliveryschedStore();
    return { deliveryschedStore };
  },
  created() {
    // console.log(this.$route.fullPath);
    // this.$route.fullPath != "/checkout"

    let $continue = true;
    if (
      this.$route.fullPath == "/cart" ||
      this.$route.fullPath == "/checkout"
    ) {
      $continue = false;
    }

    if (!this.deliveryschedStore.hadTransactionList() && $continue) {
      this.deliveryschedStore.getDeliverySched(
        APIinterface.getStorage("cart_uuid"),
        0
      );
    }
  },
});
</script>
