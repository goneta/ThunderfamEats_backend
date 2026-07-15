<template>
  <q-dialog v-model="show_modal" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("Address") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="show_modal = !true"
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
      <q-card-section style="max-height: 50vh" class="scroll">
        <q-list>
          <template v-for="items in ClientStore.data" :key="items">
            <q-item @click.stop="setPlaceID(items)" tag="label">
              <q-item-section avatar class="qlist-item-min2">
                <q-icon name="las la-map-marker" color="grey-5" />
              </q-item-section>
              <q-item-section>
                <q-item-label lines="2" v-if="items.address.address1">{{
                  items.address.address1
                }}</q-item-label>
                <q-item-label lines="2" v-else>{{
                  items.address.address2
                }}</q-item-label>
                <q-item-label lines="2" caption class="font11">{{
                  items.address.formatted_address
                }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-radio
                  v-model="place_id"
                  :val="items.place_id"
                  color="secondary"
                />
              </q-item-section>
            </q-item>
          </template>
        </q-list>
      </q-card-section>
      <q-btn
        class="row items-start full-width border-grey-top"
        unelevated
        no-caps
        text-color="primary"
        size="lg"
        :to="{ name: 'map', query: { url: this.redirect } }"
      >
        <q-icon name="o_add" color="primary" class="q-mr-md" />
        <div>{{ $t("Add a new address") }}</div>
      </q-btn>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { useClientStore } from "stores/ClientStore";
import auth from "src/api/auth";

export default {
  name: "ClientAddress",
  props: ["redirect"],
  data() {
    return {
      show_modal: false,
      loading: false,
      data: [],
      place_id: APIinterface.getStorage("place_id"),
    };
  },
  setup() {
    const ClientStore = useClientStore();
    return { ClientStore };
  },
  created() {
    //if (!this.ClientStore.hadData() && auth.authenticated()) {
    if (auth.authenticated()) {
      this.ClientStore.getAddress();
    }
  },
  methods: {
    showModal(data) {
      this.show_modal = data;
    },
    setPlaceID(data) {
      this.place_id = data.place_id;
      APIinterface.setStorage("place_data", data);
      APIinterface.setStorage("place_id", data.place_id);
      this.show_modal = false;
      this.$emit("afterSetplaceid");
    },
  },
};
</script>
