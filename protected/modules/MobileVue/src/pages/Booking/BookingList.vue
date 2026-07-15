<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header
      reveal
      reveal-offset="50"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-white text-dark': !$q.dark.mode,
      }"
    >
      <q-toolbar>
        <q-btn
          @click="$router.back()"
          flat
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
        <q-toolbar-title class="text-weight-bold">{{
          $t("Bookings")
        }}</q-toolbar-title>

        <q-btn
          flat
          round
          dense
          icon="search"
          color="grey"
          @click="dialog = !dialog"
        />
      </q-toolbar>
    </q-header>
    <q-page class="q-pl-md q-pr-md">
      <!-- POINTS BALANCE -->
      <div class="q-pa-md bg-grey-1 q-mb-sm radius8">
        <div class="row items-center q-gutter-md">
          <div>
            <q-skeleton type="QAvatar" v-if="BookingStore.summary_loading" />
            <q-icon
              v-else
              color="grey-4"
              name="table_restaurant"
              style="font-size: 60px"
            />
          </div>
          <div class="col">
            <template v-if="BookingStore.summary_loading">
              <q-skeleton type="text" class="text-subtitle1" />
              <q-skeleton type="text" width="50%" class="text-subtitle1" />
            </template>
            <template v-else>
              <h4 class="no-margin">
                <template v-if="BookingStore.getSummary.total_reservation">
                  {{ BookingStore.getSummary.total_reservation }}
                </template>
                <template v-else>0</template>
              </h4>
              <p class="font12 no-margin">{{ $t("Total Bookings") }}</p>
            </template>
          </div>
        </div>
      </div>
      <!-- POINTS BALANCE -->

      <div class="border-dark-grey radius28">
        <q-tabs
          v-model="tabs"
          class="text-grey bigtabs"
          active-color="primary"
          indicator-color="primary"
          align="justify"
          narrow-indicator
          no-caps
        >
          <template
            v-for="(items, index) in DataStore.getBookingStatusList"
            :key="items"
          >
            <q-tab :name="index" :label="items" />
          </template>
        </q-tabs>
      </div>

      <q-space class="q-pa-sm"></q-space>

      <template v-if="!hasData && !loading">
        <div class="flex flex-center" style="min-height: 300px">
          <p class="text-grey">{{ $t("No data available") }}</p>
        </div>
      </template>

      <q-infinite-scroll ref="nscroll" @load="BookingList" :offset="250">
        <q-list>
          <template v-for="datas in data" :key="datas">
            <template v-for="items in datas" :key="items.reservation_id">
              <q-item
                clickable
                :to="{
                  path: '/booking/track',
                  query: {
                    id: items.reservation_uuid,
                  },
                }"
              >
                <q-item-section avatar v-if="merchant[items.merchant_id]">
                  <q-avatar square class="rounded-borders">
                    <q-img
                      :src="merchant[items.merchant_id].url_logo"
                      spinner-size="xs"
                      spinner-color="primary"
                      style="width: 80px; height: 80px"
                      fit="cover"
                    ></q-img>
                  </q-avatar>
                </q-item-section>
                <q-item-section>
                  <q-item-label v-if="merchant[items.merchant_id]">
                    {{ merchant[items.merchant_id].restaurant_name }}
                  </q-item-label>
                  <q-item-label caption>{{ items.booking_id }}</q-item-label>
                  <q-item-label caption class="font11">
                    {{ items.reservation_date_raw }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge
                    rounded
                    :label="items.status"
                    :style="{
                      'background-color': `${items.status_color.background}`,
                      color: `${items.status_color.color}`,
                    }"
                  />
                </q-item-section>
              </q-item>
              <q-separator spaced inset />
            </template>
          </template>
        </q-list>

        <template v-slot:loading>
          <template v-if="page <= 1">
            <div class="q-pa-xl">
              <q-inner-loading
                :showing="true"
                color="primary"
                size="md"
                label-class="dark"
                class="transparent"
              />
            </div>
          </template>
          <div v-else-if="page > 2" class="row justify-center absolute-bottom">
            <q-spinner-dots color="secondary" size="40px" />
          </div>
        </template>
      </q-infinite-scroll>

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
  </q-pull-to-refresh>

  <q-dialog
    v-model="dialog"
    position="top"
    full-width
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card>
      <q-card-section>
        <q-form @submit="onSubmit">
          <q-input
            v-model="q"
            :label="$t('Search Booking')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            borderless
            class="input-borderless cursor-pointer"
          >
            <template v-slot:append>
              <q-btn
                @click="onSubmit"
                round
                color="primary"
                flat
                icon="eva-search-outline"
              />
            </template>
          </q-input>
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import "swiper/css";
import { useDataStore } from "stores/DataStore";
import { useBookingStore } from "stores/BookingStore";

export default {
  name: "BookingList",
  setup() {
    const DataStore = useDataStore();
    const BookingStore = useBookingStore();
    return { DataStore, BookingStore };
  },
  data() {
    return {
      dialog: false,
      loading: false,
      status: [],
      tabs: "all",
      data: [],
      merchant: [],
      table_list: [],
      page: 0,
      is_refresh: undefined,
      q: "",
    };
  },
  created() {
    this.BookingStore.getBookingSummary();
  },
  watch: {
    tabs(newval, oldval) {
      console.log(newval);
      this.resetPage();
    },
  },
  computed: {
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    refresh(done) {
      this.resetPage();
      this.is_refresh = done;
    },
    resetPage() {
      this.resetPagination();
    },
    resetPagination() {
      this.page = 0;
      this.data = [];
      this.merchant = [];
      this.table_list = [];
      this.$refs.nscroll.reset();
      this.$refs.nscroll.resume();
      this.$refs.nscroll.trigger();
    },
    BookingList(index, done) {
      this.loading = true;
      this.page = index;
      APIinterface.fetchDataPostTable2(
        "BookingList",
        "page=" + index + "&status=" + this.tabs
      )
        .then((data) => {
          if (data.code == 1) {
            this.data.push(data.details.data);
            this.merchant = data.details.merchant;
            this.table_list = data.details.table_list;
          } else {
            this.$refs.nscroll.stop();
          }
        })
        .catch((error) => {
          if (this.$refs.nscroll) {
            this.$refs.nscroll.stop();
          }
        })
        .then((data) => {
          this.loading = false;
          done();
          if (!APIinterface.empty(this.is_refresh)) {
            this.is_refresh();
          }
        });
    },
    onSubmit() {
      //this.$router.push("/booking/search");
      this.$router.push({ path: "/booking/search", query: { q: this.q } });
    },
  },
};
</script>
