<template>
  <q-dialog v-model="show_modal" position="bottom">
    <q-card>
      <q-card-section>
        <q-list>
          <q-item-label header class="q-pa-none font11 q-mb-sm">{{
            $t("SORT")
          }}</q-item-label>

          <q-item
            class="q-pa-none"
            v-for="(items, index) in DataStore.sort_list"
            :key="items"
            tag="label"
            v-ripple
          >
            <q-item-section>
              <q-item-label>{{ items }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-radio v-model="sort_list_by" :val="index" color="secondary" />
            </q-item-section>
          </q-item>
        </q-list>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import { useDataStore } from "stores/DataStore";

export default {
  name: "SortList",
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  data() {
    return {
      show_modal: false,
      sort_list_by: "recommended",
    };
  },
  created() {
    if (Object.keys(this.DataStore.sort_list).length <= 0) {
      this.DataStore.searchAttributes();
    }
  },
  watch: {
    sort_list_by(newval, oldval) {
      this.applySort(newval);
    },
  },
  methods: {
    applySort(data) {
      this.show_modal = false;
      this.sort_list_by = data;
      this.$emit("afterSelectsort", data);
    },
  },
};
</script>
