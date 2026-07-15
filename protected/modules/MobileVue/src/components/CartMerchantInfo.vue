<template>
  <template v-if="CartStore.cart_loading"> </template>
  <DIV v-else class="q-pl-md q-pr-md q-mb-sm">
    <template v-if="CartStore.items_count > 0">
      <q-btn
        no-caps
        unelevated
        flat
        class="q-pa-none"
        :to="{
          name: 'menu',
          params: { slug: CartStore.cart_merchant.slug },
        }"
      >
        <div class="font16 text-weight-bold line-normal">
          {{ CartStore.cart_merchant.restaurant_name }}
        </div>
        <q-icon name="las la-angle-right" color="grey" size="15px"></q-icon>
      </q-btn>
      <div class="row q-gutter-sm">
        <q-img
          :src="CartStore.cart_merchant.logo"
          lazy
          fit="cover"
          style="height: 70px; width: 70px"
          class="radius8"
          spinner-color="amber"
          spinner-size="sm"
        />

        <div
          class="col-8"
          v-if="
            CartStore.data_transaction[
              CartStore.transaction_info.transaction_type
            ]
          "
        >
          <div class="font12 text-weight-bold">
            {{
              CartStore.data_transaction[
                CartStore.transaction_info.transaction_type
              ].service_name
            }},
            <span class="text-capitalize">{{
              CartStore.transaction_info.whento_deliver
            }}</span>
          </div>

          <div
            v-if="CartStore.transaction_info.whento_deliver == 'schedule'"
            class="font10 text-weight-light text-weight-medium"
          >
            {{ transactionStore.transaction_data.delivery_datetime }}
          </div>

          <div class="font12 text-weight-light">
            {{
              CartStore.data_transaction[
                CartStore.transaction_info.transaction_type
              ].service_name
            }}
            {{ $t("in") }} {{ CartStore.transaction_info.estimation }}
            {{ $t("mins") }}
          </div>

          <!-- <div class="font12 text-weight-light ellipsis-2-lines">
            {{ transactionStore.transaction_data.formatted_address }}
          </div> -->

          <q-btn
            flat
            :color="$q.dark.mode ? 'secondary' : 'blue'"
            no-caps
            :label="$t('Change order settings')"
            dense
            size="sm"
            @click="this.$emit('onClickchange')"
            class="q-pt-none"
          />
        </div>
      </div>
      <!-- row -->
    </template>
  </DIV>
</template>

<script>
import { useCartStore } from "stores/CartStore";
import { useTransactionStore } from "stores/Transaction";

export default {
  name: "CartMerchantInfo",
  props: ["show_transinfo"],
  setup() {
    const CartStore = useCartStore();
    const transactionStore = useTransactionStore();
    return { CartStore, transactionStore };
  },
};
</script>
