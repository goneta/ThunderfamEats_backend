<template>
  <q-dialog
    v-model="show_modal"
    position="bottom"
    @before-show="beforeShow"
    @before-hide="beforeHide"
  >
    <q-card>
      <q-card-section>
        <q-btn-toggle
          v-model="schedStore.transaction_type"
          toggle-color="secondary"
          no-caps
          unelevated
          class="rounded-group q-mb-sm"
          :options="schedStore.transaction_list"
        />

        <q-option-group
          v-model="schedStore.whento_deliver"
          :options="schedStore.delivery_options"
          color="secondary"
          @update:model-value="chooseWhen"
        />

        <div class="q-mt-md" v-if="schedStore.whento_deliver === 'schedule'">
          <q-select
            outlined
            dense
            v-model="schedStore.delivery_date"
            color="secondary"
            class="q-mb-md"
            transition-show="scale"
            transition-hide="scale"
            :options="schedStore.delivery_date_list"
            emit-value
            map-options
            @update:model-value="chooseDate"
          />
          <q-select
            outlined
            dense
            v-model="schedStore.delivery_time"
            color="secondary"
            class="q-mb-md"
            transition-show="scale"
            transition-hide="scale"
            :options="schedStore.delivery_time_list"
          />
        </div>
      </q-card-section>

      <q-card-actions class="">
        <q-btn
          unelevated
          color="primary"
          text-color="white"
          no-caps
          class="full-width"
          :label="$t('Confirm')"
          size="lg"
          @click="saveTransactionInfo"
          :loading="schedStore.loading"
        >
        </q-btn>
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { useDeliveryschedStore } from "stores/DeliverySched";
//import { useTransactionStore } from "stores/Transaction";

const $openingHours = [];
const $openingDates = [];

export default {
  name: "DeliverySched",
  props: ["slug"],
  setup() {
    const schedStore = useDeliveryschedStore();
    //const transactionStore = useTransactionStore();
    return { schedStore };
  },
  data() {
    return {
      show_modal: false,
      loading: false,
      transaction_type: "",
      transaction_list: [],
      delivery_date: "",
      delivery_time: "",
      delivery_options: [],
      delivery_date_list: [],
      delivery_time_list: [],
      opening_hours: [],
      trans_data: [],
      is_saved: false,
    };
  },
  methods: {
    showSched(data) {
      this.show_modal = data;
    },
    beforeShow() {
      this.is_saved = false;
      if (this.schedStore.whento_deliver == "schedule") {
        this.schedStore.getDeliveryTimes(this.slug);
      }
    },
    beforeHide() {
      if (!this.is_saved) {
        this.schedStore.getDeliverySched(
          APIinterface.getStorage("cart_uuid"),
          this.slug
        );
      }
    },
    chooseWhen(data) {
      if (data === "schedule") {
        this.schedStore.getDeliveryTimes(this.slug);
      }
    },
    chooseDate(data) {
      this.schedStore.delivery_time_list = [];
      this.schedStore.delivery_time = "";
      this.schedStore.getTimeList(data);
    },
    saveTransactionInfo() {
      this.schedStore.loading = true;
      const cartUUID = APIinterface.getStorage("cart_uuid");
      const $params = {
        cart_uuid: cartUUID,
        delivery_date: this.schedStore.delivery_date,
        whento_deliver: this.schedStore.whento_deliver,
        delivery_time: this.schedStore.delivery_time,
        transaction_type: this.schedStore.transaction_type,
      };
      APIinterface.saveTransactionInfo($params)
        .then((data) => {
          if (APIinterface.empty(cartUUID)) {
            APIinterface.setStorage("cart_uuid", data.details.cart_uuid);
          }

          const $data = {
            whento_deliver: this.schedStore.whento_deliver,
            delivery_date: this.schedStore.delivery_date,
            delivery_time: this.schedStore.delivery_time,
          };

          APIinterface.setStorage("delivery_sched", 1);
          APIinterface.setStorage(
            "transaction_type",
            this.schedStore.transaction_type
          );
          APIinterface.setStorage("choosen_delivery", $data);

          this.schedStore.new_transaction_type =
            this.schedStore.transaction_type;

          this.schedStore.new_whento_deliver = this.schedStore.whento_deliver;

          this.is_saved = true;
          this.show_modal = false;

          this.$emit("afterSavetrans", $data);
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "warning", this.$q);
        })
        .then((data) => {
          this.schedStore.loading = false;
        });
    },
  },
};
</script>
