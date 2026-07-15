<template>
  <q-dialog
    v-model="show_modal"
    persistent
    @before-show="cancelOrderStatus"
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card style="width: 500px; max-width: 80vw">
      <q-card-section class="row items-center q-pb-none q-pa-none">
        <q-space />
        <q-btn icon="eva-close-outline" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section>
        <h5
          class="text-weight-bold no-margin"
          v-if="cancel_response.refund_status === 'full_refund'"
        >
          {{ $t("Are you sure?") }}
        </h5>
        <h5 class="text-weight-bold no-margin" v-else>
          {{ $t("How would you like to proceed?") }}
        </h5>

        <div class="q-mt-md">
          <p class="font12 text-weight-light">{{ cancel_msg }}</p>
        </div>

        <div class="q-mt-md q-mb-sm" v-if="cancel_response.refund_msg">
          <p class="font12 text-weight-light">
            {{ cancel_response.refund_msg }}
          </p>
        </div>
      </q-card-section>

      <q-card-actions>
        <q-btn
          unelevated
          color="secondary"
          text-color="white"
          no-caps
          class="full-width"
          :disable="!cancel_status"
          :loading="loading"
          @click="applyCancelOrder"
          size="md"
        >
          {{ $t("Cancel order") }}
        </q-btn>

        <q-space class="q-pa-sm"></q-space>

        <q-btn
          unelevated
          color="mygrey"
          text-color="dark"
          no-caps
          class="full-width"
          v-close-popup
          size="md"
        >
          {{ $t("Don't Cancel") }}
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "CancelOrder",
  data() {
    return {
      loading: false,
      show_modal: false,
      order_uuid: "",
      cancel_status: false,
      cancel_msg: "",
      cancel_response: [],
    };
  },
  methods: {
    showModal(orderUuid) {
      this.order_uuid = orderUuid;
      this.show_modal = true;
    },
    cancelOrderStatus() {
      this.loading = true;
      APIinterface.cancelOrderStatus(this.order_uuid)
        .then((data) => {
          this.cancel_status = data.details.cancel_status;
          this.cancel_msg = data.details.cancel_msg;
          this.cancel_response = data.details;
        })
        .catch((error) => {
          APIinterface.notify("grey-8", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    applyCancelOrder() {
      this.loading = true;
      APIinterface.applyCancelOrder(this.order_uuid)
        .then((data) => {
          this.show_modal = false;
          APIinterface.notify("light-green", data.msg, "check_circle", this.$q);
          this.$emit("afterCancelorder");
        })
        .catch((error) => {
          APIinterface.notify("grey-8", error, "warning", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
