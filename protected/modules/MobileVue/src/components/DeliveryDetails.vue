<template>
  <q-dialog v-model="show_modal" position="bottom">
    <q-card>
      <q-card-section>
        <q-btn-toggle
          v-model="DeliveryschedStore.transaction_type"
          toggle-color="secondary"
          no-caps
          unelevated
          class="rounded-group q-mb-sm"
          :options="DeliveryschedStore.transaction_list"
          @click="changeTransactionType"
        />

        <q-list>
          <q-item class="q-pa-none">
            <q-item-section class="col-1" avatar>
              <q-icon color="grey" name="las la-map-marker-alt" size="20px" />
            </q-item-section>
            <q-item-section>
              <q-item-label lines="1">
                <span class="text-weight-medium">
                  {{ PlaceStore.address }}
                </span>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn
                @click="ChangeAddress"
                flat
                color="secondary"
                :label="$t('Change')"
                no-caps
                dense
              />
            </q-item-section>
          </q-item>
          <q-item class="q-pa-none">
            <q-item-section class="col-1" avatar>
              <q-icon color="grey" name="eva-clock-outline" size="20px" />
            </q-item-section>
            <q-item-section>
              <q-item-label lines="1">
                <span
                  v-if="DeliveryschedStore.whento_deliver === 'schedule'"
                  class="text-weight-medium"
                >
                  {{ DeliveryschedStore.trans_data.delivery_date_pretty }}
                  {{ DeliveryschedStore.trans_data.delivery_time.pretty_time }}
                </span>
                <span v-else class="text-weight-medium">
                  <template
                    v-for="item_option in DeliveryschedStore.delivery_options"
                    :key="item_option"
                  >
                    <template
                      v-if="
                        DeliveryschedStore.whento_deliver == item_option.value
                      "
                    >
                      {{ item_option.label }}
                    </template>
                  </template>
                </span>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn
                @click="this.$emit('showSched')"
                flat
                color="secondary"
                :label="$t('Change')"
                no-caps
                dense
              />
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>
  </q-dialog>

  <ClientAddress
    ref="client_address"
    :redirect="this.$route.path"
    @after-setplaceid="afterSetplaceid"
    @fill-address="fillAddress"
  />
</template>

<script>
import { defineAsyncComponent } from "vue";
//import APIinterface from "src/api/APIinterface";
import { usePlaceStore } from "stores/PlaceStore";
import { useDeliveryschedStore } from "stores/DeliverySched";
import auth from "src/api/auth";
import APIinterface from "src/api/APIinterface";

export default {
  name: "DeliveryDetails",
  props: ["back_url"],
  components: {
    ClientAddress: defineAsyncComponent(() =>
      import("components/ClientAddress.vue")
    ),
  },
  data() {
    return {
      show_modal: false,
      loading: false,
    };
  },
  setup() {
    const PlaceStore = usePlaceStore();
    const DeliveryschedStore = useDeliveryschedStore();
    return { PlaceStore, DeliveryschedStore };
  },
  mounted() {
    this.PlaceStore.getPlace();
  },
  methods: {
    showModal(data) {
      this.show_modal = data;
    },
    TransactionInfo() {
      this.transactionStore.TransactionInfo();
    },
    showSched() {
      this.showModal(false);
      this.$refs.delivery_sched.showSched(true);
    },
    changeTransactionType() {
      this.DeliveryschedStore.new_transaction_type =
        this.DeliveryschedStore.transaction_type;
      console.log("d2");
      this.saveTransactionType();
    },
    ChangeAddress() {
      if (auth.authenticated()) {
        this.show_modal = false;
        this.$refs.client_address.showModal(true);
      } else {
        this.$router.push({ name: "map", query: { url: this.back_url } });
      }
    },
    afterSetplaceid() {
      this.PlaceStore.getPlace();
      this.$emit("afterSetplaceid");
    },
    saveTransactionType() {
      const cartUUID = APIinterface.getStorage("cart_uuid");
      if (APIinterface.empty(cartUUID)) {
        return false;
      }
      APIinterface.saveTransactionType({
        cart_uuid: cartUUID,
        transaction_type: this.DeliveryschedStore.transaction_type,
      })
        .then((data) => {
          console.log("fin");
          this.$emit("afterSavetranstype");
        })
        .catch((error) => {})
        .then((data) => {});
    },
  },
};
</script>
