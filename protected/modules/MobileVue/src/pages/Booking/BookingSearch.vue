<template>
  <template v-if="!loading">
    <q-header
      reveal
      reveal-offset="50"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-black': !$q.dark.mode,
      }"
    >
      <q-toolbar>
        <q-toolbar-title class="text-weight-bold">{{
          $t("Bookings")
        }}</q-toolbar-title>
        <q-btn
          @click="$router.back()"
          flat
          round
          dense
          icon="close"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
      </q-toolbar>
    </q-header>
  </template>
  <q-page
    :class="{
      'bg-mydark': $q.dark.mode,
      'bg-grey-1': !$q.dark.mode,
      'flex flex-center': !hasData && !loading,
    }"
  >
    <div v-if="!hasData && !loading">
      <p class="text-grey q-ma-none">{{ $t("No data available") }}</p>
    </div>

    <q-list>
      <q-item
        v-for="items in getData"
        :key="items"
        clickable
        :to="{
          path: '/booking/track',
          query: {
            id: items.reservation_uuid,
          },
        }"
      >
        <q-card flat class="fit radius8">
          <q-card-section class="q-pl-md q-pr-md q-pt-sm q-pb-sm">
            <div class="row justify-between items-center">
              <q-chip
                icon="las la-calendar"
                :color="$q.dark.mode ? 'grey600' : 'white'"
                :text-color="$q.dark.mode ? 'grey300' : 'grey'"
                size="sm"
                :class="{
                  '': $q.dark.mode,
                  'q-pl-none': !$q.dark.mode,
                }"
                >{{ items.reservation_date_raw }}</q-chip
              >
              <q-chip
                size="sm"
                :style="{
                  'background-color': `${items.status_color.background}`,
                  color: `${items.status_color.color}`,
                }"
                >{{ items.status }}</q-chip
              >
            </div>
            <div class="font13">
              {{ $t("Booking ID")
              }}<span class="text-secondary q-ml-sm text-weight-bold"
                >#{{ items.reservation_id }}</span
              >
            </div>

            <div class="row justify-between items-center">
              <div
                v-if="merchant[items.merchant_id]"
                class="font13 text-weight-bold"
              >
                {{ merchant[items.merchant_id].restaurant_name }}
              </div>
              <div v-if="merchant[items.merchant_id]" class="font13">
                {{ merchant[items.merchant_id].merchant_address }}
              </div>
            </div>

            <div class="row justify-between items-center">
              <div class="font13">
                {{ $t("Guest") }} : {{ items.guest_number }}
              </div>
              <div v-if="table_list[items.table_id]" class="font12">
                {{ table_list[items.table_id] }}
              </div>
            </div>
          </q-card-section>
        </q-card>
      </q-item>
    </q-list>

    <q-inner-loading
      :showing="loading"
      color="primary"
      size="md"
      label-class="dark"
      class="transparent"
    />

    <q-page-scroller
      position="bottom-right"
      :scroll-offset="150"
      :offset="[18, 18]"
    >
      <q-btn
        fab
        icon="keyboard_arrow_up"
        color="mygrey"
        text-color="dark"
        dense
        padding="3px"
      />
    </q-page-scroller>
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "BookingSearch",
  data() {
    return {
      loading: false,
      data: [],
      q: "",
      merchant: [],
      table_list: [],
    };
  },
  created() {
    this.q = this.$route.query.q;
    this.Search();
  },
  computed: {
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
    getData() {
      return this.data;
    },
  },
  methods: {
    Search() {
      this.loading = true;
      APIinterface.fetchDataPostTable2("BookingSearch", "search=" + this.q)
        .then((data) => {
          if (data.code == 1) {
            this.data = data.details.data;
            this.merchant = data.details.merchant;
            this.table_list = data.details.table_list;
          }
        })
        .catch((error) => {
          //
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
