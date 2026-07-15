<template>
  <template v-if="loading">
    <div class="flex flex-center q-pa-md">
      <q-circular-progress indeterminate rounded size="sm" color="primary" />
    </div>
  </template>
  <template v-else>
    <div
      class="q-pa-md"
      style="border-top: 1px solid #b1b1b1"
      v-if="booking_enabled && transaction_type == 'dinein'"
    >
      <div class="font13 text-weight-bold q-mb-sm">
        {{ $t("Choose Table") }} <span class="text-red">*</span>
      </div>

      <div class="q-gutter-y-xs">
        <div>{{ $t("Guest") }}</div>
        <q-btn-group unelevated class="radius8 border-grey" spread>
          <q-btn
            @click="guest_number > 1 ? guest_number-- : 1"
            :color="$q.dark.mode ? 'grey600' : 'mygrey'"
            :text-color="$q.dark.mode ? 'grey300' : 'grey'"
            icon="o_remove"
            size="md"
            dense
            class="q-pa-sm"
          />
          <q-btn
            dense
            :label="guest_number"
            class="no-pointer-events text-weight-medium"
          />
          <q-btn
            @click="guest_number++"
            :color="$q.dark.mode ? 'grey600' : 'mygrey'"
            :text-color="$q.dark.mode ? 'grey300' : 'grey'"
            icon="o_add"
            size="md"
            dense
            class="q-pa-sm"
          />
        </q-btn-group>

        <q-item-section>
          <q-select
            v-model="room_uuid"
            :options="room_list"
            @update:model-value="table_uuid = ''"
            :label="$t('Room name')"
            transition-show="scale"
            transition-hide="scale"
            emit-value
            map-options
          />
        </q-item-section>

        <q-select
          v-model="table_uuid"
          :options="this.table_list[room_uuid]"
          :label="$t('Table name')"
          transition-show="scale"
          transition-hide="scale"
          emit-value
          map-options
        />
      </div>
    </div>
  </template>
</template>

<script>
import { useMenuStore } from "stores/MenuStore";
import APIinterface from "src/api/APIinterface";

export default {
  name: "CheckoutBooking",
  props: ["transaction_type"],
  data() {
    return {
      guest_number: 1,
      room_uuid: "",
      table_uuid: "",
      booking_enabled: false,
      room_list: [],
      table_list: [],
      loading: false,
    };
  },
  setup() {
    const MenuStore = useMenuStore();
    return {
      MenuStore,
    };
  },
  created() {
    this.getTableAttributes();
  },
  methods: {
    getTableAttributes() {
      this.loading = true;
      APIinterface.fetchDataPost(
        "getTableAttributes",
        "merchant_uuid=" + this.MenuStore.merchant_uuid
      )
        .then((data) => {
          this.booking_enabled = data.details.booking_enabled;
          this.room_list = data.details.room_list;
          this.table_list = data.details.table_list;
        })
        .catch((error) => {})
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
