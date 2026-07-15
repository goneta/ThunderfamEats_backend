<template>
  <q-dialog v-model="dialog" position="bottom">
    <q-card class="rounded-borders-top">
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title class="text-dark text-weight-bold">
          {{ $t("Order") }}
          <span class="text-primary">#{{ data.order_id_raw }}</span>
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="dialog = !true"
          color="white"
          square
          unelevated
          text-color="grey"
          icon="las la-times"
          dense
          no-caps
          size="sm"
          class="border-grey radius8"
        />
      </q-toolbar>

      <div class="q-pl-md q-pr-md q-mb-sm">
        <div class="text-right">
          <q-btn
            flat
            :color="$q.dark.mode ? 'secondary' : 'blue'"
            no-caps
            :label="$t('View full order details')"
            dense
            size="sm"
            :to="{
              path: '/account/order-details',
              query: { order_uuid: data.order_uuid },
            }"
          />
        </div>

        <q-list
          class="radius8"
          :class="{
            'bg-grey600 text-grey300': $q.dark.mode,
            'bg-lightprimary text-black': !$q.dark.mode,
          }"
        >
          <q-item v-for="items in data.items" :key="items">
            <q-item-section avatar>
              <q-img
                :src="items_details[items.item_id].photo"
                lazy
                fit="cover"
                style="height: 70px; width: 70px"
                class="radius8"
                spinner-color="secondary"
                spinner-size="sm"
                placeholder-src="placeholder.png"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label>
                <div class="font13 text-weight-bold no-margin line-normal">
                  {{ items_details[items.item_id].item_name }}
                </div>
                <div class="text-grey font11 line-normal">
                  {{ items.qty }} x

                  <template v-if="items.discount > 0">
                    <span class="text-strike">{{ items.price }}</span>
                    <NumberFormat
                      :amount="items.price - items.discount"
                      :money_config="data.price_format"
                    ></NumberFormat>
                  </template>
                  <template v-else>
                    <NumberFormat
                      :amount="items.price"
                      :money_config="data.price_format"
                    ></NumberFormat>
                  </template>
                </div>
                <div class="font14 text-weight-bold">
                  <NumberFormat
                    :amount="items.qty * items.price"
                    :money_config="data.price_format"
                  ></NumberFormat>
                </div>
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>

        <q-space class="q-pa-sm"></q-space>
        <div
          class="row items-center justify-between q-pl-xl q-pt-sm border-grey-top text-weight-bold"
        >
          <div>{{ $t("Total") }}</div>
          <div>{{ data.total }}</div>
        </div>
      </div>
    </q-card>
  </q-dialog>
</template>

<script>
import { useDataStore } from "src/stores/DataStore";
import { defineAsyncComponent } from "@vue/runtime-core";

export default {
  name: "OrderPreview",
  props: ["data", "items_details"],
  components: {
    NumberFormat: defineAsyncComponent(() =>
      import("components/NumberFormat.vue")
    ),
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  data() {
    return {
      dialog: false,
    };
  },
};
</script>
