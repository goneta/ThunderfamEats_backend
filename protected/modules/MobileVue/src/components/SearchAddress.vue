<template>
  <q-select
    v-model="address"
    ref="select_address"
    use-input
    fill-input
    input-debounce="0"
    :options="options"
    @filter="filterFn"
    @update:model-value="selectAddress"
    :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
    :color="$q.dark.mode ? 'grey300' : 'dark'"
    borderless
    :placeholder="placeholder"
    behavior="menu"
    hide-dropdown-icon
  >
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar top style="min-width: auto" class="q-pr-xs">
          <q-icon name="las la-map-marker" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ scope.opt.description }}</q-item-label>
          <q-item-label caption>{{ scope.opt.addressLine2 }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>

    <template v-slot:prepend>
      <q-icon name="las la-arrow-left" color="grey" class="q-pl-sm" />
    </template>
  </q-select>
  <div class="flex flex-center q-pa-md text-center" style="min-height: 300px">
    <p class="text-grey">
      {{ $t("Enter your address to explore restaurant around you") }}
    </p>
  </div>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "SearchAddress",
  props: ["placeholder"],
  data() {
    return {
      address: "",
      address_data: [],
      options: [],
      data: [],
      loading: false,
    };
  },
  methods: {
    Focus() {
      this.$refs.select_address.focus();
    },
    filterFn(val, update, abort) {
      if (val.length < 2) {
        abort();
        return;
      }

      APIinterface.getlocationAutocomplete(val)
        .then((data) => {
          update(() => {
            this.options = data.details.data;
          });
        })
        .catch((error) => {
          console.debug(error);
        })
        .then((data) => {});
    },
    selectAddress(val) {
      this.address_data = val;
      this.address = val.description;
      APIinterface.getLocationDetails(val.id)
        .then((data) => {
          const results = data.details.data;
          if (!APIinterface.empty(results.latitude)) {
            this.$emit("afterSelectaddress", results);
          }
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {});
    },
  },
};
</script>
