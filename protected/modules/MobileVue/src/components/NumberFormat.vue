<template>
  <!-- <pre>{{ money_config }}</pre> -->
  <!-- <pre>{{ DataStore.money_config }}</pre>  -->
  <span>{{ total }}</span>
</template>

<script>
import { NumberFormat } from "@coders-tm/vue-number-format";
import { useDataStore } from "stores/DataStore";

export default {
  name: "NumberFormat",
  props: ["amount", "money_config"],
  data() {
    return {
      total: 0,
      number: undefined,
    };
  },
  created() {
    if (
      typeof this.money_config === "undefined" ||
      this.money_config === null ||
      this.money_config === "" ||
      this.money_config === "null" ||
      this.money_config === "undefined"
    ) {
      this.number = new NumberFormat(this.DataStore.money_config);
    } else {
      this.number = new NumberFormat(this.money_config);
    }

    this.total = this.number.format(this.amount);
  },
  watch: {
    money_config(newval, oldval) {
      //console.log("d2xxx");
    },
    amount(newval, oldval) {
      this.total = this.number.format(newval);
    },
  },
  setup() {
    const DataStore = useDataStore();
    return {
      DataStore,
    };
  },
};
</script>
