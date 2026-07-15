<template>
  <template v-if="PaymentStore.loading">
    <div class="text-center">
      <q-spinner color="primary" size="sm" />
    </div>
  </template>
  <template v-else>
    <q-list>
      <template v-for="items in PaymentStore.data[merchant_id]" :key="items">
        <q-item tag="label">
          <q-item-section v-if="items.logo_type == 'image'" avatar>
            <q-img
              :src="items.logo_image"
              fit="contain"
              style="height: 30px; width: 40px"
            />
          </q-item-section>
          <q-item-section v-else avatar>
            <q-icon color="secondary" name="las la-credit-card" />
          </q-item-section>
          <q-item-section>
            <q-item-label lines="2">
              {{ items.payment_name }}
              <template v-if="items.card_fee_percent && items.card_fee_fixed">
                ({{ items.card_fee_percent }}% + {{ items.card_fee_fixed }})
              </template>
              <template v-else-if="items.card_fee_percent">
                ({{ items.card_fee_percent }}% )
              </template>
              <template v-else-if="items.card_fee_fixed">
                ({{ items.card_fee_fixed }})
              </template>
            </q-item-label>
            <q-item-label lines="2" caption class="font11">
              {{ items.attr2 }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-radio
              v-model="payment_uuid"
              :val="items.payment_uuid"
              color="secondary"
              @click="setPayment(items)"
            />
          </q-item-section>
        </q-item>
      </template>
    </q-list>

    <template v-if="!PaymentStore.data[merchant_id] && !PaymentStore.loading">
      <div class="q-pl-md q-pr-md font12 text-dark">
        {{ $t("You don't have payment saved yet") }}.
      </div>
    </template>
  </template>
</template>

<script>
import { usePaymentStore } from "stores/PaymentStore";
import APIinterface from "src/api/APIinterface";

export default {
  name: "PaymentListSaved",
  props: ["merchant_id"],
  data() {
    return {
      payment_uuid: "",
      loading: false,
      data: [],
      credentials: [],
      inner_loading: false,
    };
  },
  setup(props, { emit }) {
    const PaymentStore = usePaymentStore();
    return { PaymentStore };
  },
  created() {
    this.PaymentStore.SavedPaymentList(this.merchant_id);
  },
  watch: {
    payment_uuid(newval, oldval) {
      this.$emit("setPaymentuuid", newval);
    },
    PaymentStore: {
      immediate: true,
      deep: true,
      handler(newValue, oldValue) {
        if (
          !APIinterface.empty(this.PaymentStore.payment_uuid[this.merchant_id])
        ) {
          this.payment_uuid = this.PaymentStore.payment_uuid[this.merchant_id];
        }
      },
    },
  },
  methods: {
    setPayment(data) {
      this.$emit("setPayment", data);
    },
  },
};
</script>
