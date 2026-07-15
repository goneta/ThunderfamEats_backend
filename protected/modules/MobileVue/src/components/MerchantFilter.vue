<template>
  <q-dialog v-model="filter" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("Filter Your Search") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="filter = !true"
          square
          unelevated
          :color="$q.dark.mode ? 'grey600' : 'white'"
          :text-color="$q.dark.mode ? 'grey300' : 'grey'"
          icon="las la-times"
          dense
          no-caps
          size="sm"
          class="border-grey radius8"
        />
      </q-toolbar>
      <q-card-section class="q-pt-none q-pl-md">
        <div class="row justify-between">
          <div class="font13 text-weight-bold text-h5">{{ $t("Filter") }}</div>
          <q-btn
            v-if="hasFilter"
            @click="resetFilter"
            flat
            dense
            color="primary"
            label="Reset"
            no-caps
          />
        </div>

        <q-btn-toggle
          v-if="hasSortData"
          v-model="sortby"
          toggle-color="secondary"
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          no-caps
          no-wrap
          unelevated
          :options="DataStore.sort_by"
          class="rounded-group2 text-weight-bold line-1"
        />
        <div class="font13 text-weight-bold text-h5">
          {{ $t("Price Range") }}
        </div>
        <q-btn-toggle
          v-if="hasPriceData"
          v-model="price_range"
          toggle-color="secondary"
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          no-caps
          no-wrap
          unelevated
          :options="DataStore.price_range_data"
          class="rounded-group2 text-weight-bold line-1"
        />

        <div class="font13 text-weight-bold text-h5">
          {{ $t("Max Delivery Fee") }}
        </div>
        <q-slider v-model="max_delivery_fee" :min="1" :max="20" />

        <div class="font13 text-weight-bold text-h5">{{ $t("Ratings") }}</div>
        <q-btn-toggle
          v-model="rating"
          toggle-color="secondary"
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          no-caps
          no-wrap
          unelevated
          class="rounded-group2 text-weight-bold line-1"
          :options="[
            { label: '1', value: 1, 'icon-right': 'star' },
            { label: '2', value: 2, 'icon-right': 'star' },
            { label: '3', value: 3, 'icon-right': 'star' },
            { label: '4', value: 4, 'icon-right': 'star' },
            { label: '5', value: 5, 'icon-right': 'star' },
          ]"
        />

        <div class="font13 text-weight-bold text-h5">{{ $t("Cuisine") }}</div>
        <q-btn-group
          v-if="hasCuisineData"
          no-caps
          no-wrap
          unelevated
          class="rounded-group2 text-weight-bold line-1"
        >
          <q-btn
            no-caps
            v-for="(button, index) in cuisine"
            :key="index"
            :color="button.color"
            :text-color="button.text_color"
            :label="button.label"
            @click="setActive(button, index)"
          ></q-btn>
        </q-btn-group>

        <q-space class="q-pa-xl"></q-space>
      </q-card-section>

      <q-footer bordered class="bg-white q-pa-sm no-border">
        <q-toolbar>
          <q-btn
            @click="applyFilter"
            color="primary"
            unelevated
            text-color="white"
            :label="$t('Apply')"
            no-caps
            class="full-width"
            size="lg"
          />
        </q-toolbar>
      </q-footer>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
//import { useTransactionStore } from "stores/Transaction";
import { useDeliveryschedStore } from "stores/DeliverySched";
import { useDataStore } from "stores/DataStore";

export default {
  name: "MerchantFilter",
  props: ["cuisine_data"],
  data() {
    return {
      filter: false,
      filter_maximize: true,
      price_range_data: [],
      loading: false,
      max_delivery_data: [],
      sortby: "",
      sort_by: [],
      price_range: "",
      max_delivery_fee: "",
      rating: "",
      cuisine: [],
      cuisine_selected: [],
    };
  },
  setup() {
    //const transactionStore = useTransactionStore();
    const DeliveryschedStore = useDeliveryschedStore();
    const DataStore = useDataStore();
    return { DeliveryschedStore, DataStore };
  },
  mounted() {
    if (Object.keys(this.DataStore.price_range_data).length <= 0) {
      this.DataStore.searchAttributes();
    }
    if (Object.keys(this.cuisine_data).length >= 0) {
      this.setCuisine();
    }
  },
  watch: {
    cuisine: {
      handler(newValue, oldValue) {
        this.cuisine_selected = [];
        if (Object.keys(this.cuisine).length > 0) {
          Object.entries(this.cuisine).forEach(([key, items]) => {
            if (items.onOff) {
              this.cuisine_selected.push(items.value);
            }
          });
        }
      },
      deep: true,
    },
    cuisine_data(newval, oldval) {
      this.setCuisine();
    },
  },
  computed: {
    hasFilter() {
      if (Object.keys(this.cuisine_selected).length > 0) {
        return true;
      }
      if (!APIinterface.empty(this.sortby)) {
        return true;
      }
      if (!APIinterface.empty(this.price_range)) {
        return true;
      }
      if (!APIinterface.empty(this.max_delivery_fee)) {
        return true;
      }
      if (!APIinterface.empty(this.rating)) {
        return true;
      }
      return false;
    },
    hasSortData() {
      if (Object.keys(this.DataStore.sort_by).length > 0) {
        return true;
      }
      return false;
    },
    hasPriceData() {
      if (Object.keys(this.DataStore.price_range_data).length > 0) {
        return true;
      }
      return false;
    },
    hasCuisineData() {
      if (Object.keys(this.cuisine).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    resetFilter() {
      this.sortby = "";
      this.price_range = "";
      this.max_delivery_fee = "";
      this.cuisine_selected = [];
      this.rating = "";
      this.cuisine = [];
      this.setCuisine();
    },
    setCuisine() {
      if (Object.keys(this.cuisine_data).length > 0) {
        Object.entries(this.cuisine_data).forEach(([key, items]) => {
          this.cuisine.push({
            label: items.cuisine_name,
            value: items.cuisine_id,
            color: this.$q.dark.mode ? "grey600" : "mygrey",
            text_color: this.$q.dark.mode ? "grey300" : "dark",
            onOff: false,
          });
        });
      }
    },
    applyFilter() {
      const $filter = {
        sortby: this.sortby,
        price_range: this.price_range,
        max_delivery_fee: this.max_delivery_fee,
        cuisine: this.cuisine_selected,
        //transaction_type: this.transactionStore.transaction_type,
        transaction_type: this.DeliveryschedStore.transaction_type,
        rating: this.rating,
      };
      //this.transactionStore.filters = $filter;
      this.DeliveryschedStore.filters = $filter;
      this.$emit("applyFilter", $filter);
      this.filter = false;
    },
    setActive(button, index) {
      if (button.onOff) {
        this.cuisine[index].color = "mygrey";
        this.cuisine[index].text_color = "dark";
        this.cuisine[index].onOff = false;
      } else {
        this.cuisine[index].color = "primary";
        this.cuisine[index].text_color = "white";
        this.cuisine[index].onOff = true;
      }
    },
  },
};
</script>
