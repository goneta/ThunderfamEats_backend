<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header
      reveal
      reveal-offset="10"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-black': !$q.dark.mode,
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
          $t("Orders")
        }}</q-toolbar-title>

        <q-btn
          v-if="is_search"
          flat
          round
          dense
          icon="close"
          @click="resetPage"
        />

        <q-btn
          v-else
          @click="modal_search = !modal_search"
          flat
          round
          dense
          icon="search"
        />
      </q-toolbar>
    </q-header>
    <q-page
      :class="{
        'bg-mydark': $q.dark.mode,
        'bg-grey-1': !$q.dark.mode,
      }"
    >
      <q-infinite-scroll ref="nscroll" @load="orderHistory" :offset="250">
        <div class="q-pl-md q-pr-md text-center q-pt-sm">
          <template v-if="is_search">
            <div v-if="hasData" class="text-h5 text-weight-bold">
              {{ $t("Search result") }}
            </div>
          </template>
          <template v-else>
            <swiper :slidesPerView="2.3" :spaceBetween="10" class="q-mb-md">
              <swiper-slide v-for="items in orderTabs" :key="items" class="row">
                <q-btn
                  unelevated
                  no-caps
                  class="radius8 fit"
                  size="md"
                  :label="items.label"
                  @click="order_tab = items.value"
                  :color="
                    order_tab == items.value
                      ? 'primary'
                      : $q.dark.mode
                      ? 'grey600'
                      : 'mygrey'
                  "
                  :text-color="
                    order_tab == items.value
                      ? 'white'
                      : $q.dark.mode
                      ? 'grey300'
                      : 'dark'
                  "
                >
                </q-btn>
              </swiper-slide>
            </swiper>
          </template>
        </div>

        <div
          v-if="!hasData && !loading"
          class="flex flex-center"
          style="min-height: 300px"
        >
          <div class="text-center full-width">
            <div class="text-h5 text-weight-bold">
              {{ NodataMessage }}
            </div>
            <p class="text-grey font12">{{ NodataMessage2 }}</p>
          </div>
        </div>

        <q-list>
          <template v-for="datas in data" :key="datas">
            <template v-for="order in datas" :key="order.order_id_raw">
              <q-item clickable @click="showQuickDetails(order)">
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
                        >{{ order.date_created }}</q-chip
                      >

                      <!-- <q-chip
                        v-if="services[order.service_code]"
                        color="primary"
                        text-color="white"
                        size="sm"
                        :style="`color:${
                          services[order.service_code].font_color_hex
                        } !important; background-color:${
                          services[order.service_code].background_color_hex
                        } !important; `"
                        >{{ services[order.service_code].service_name }}</q-chip
                      > -->
                      <q-chip
                        v-if="status_list[order.status]"
                        color="mygrey"
                        text-color="dark"
                        size="sm"
                        :style="`color:${
                          status_list[order.status].font_color_hex
                        } !important; background-color:${
                          status_list[order.status].background_color_hex
                        } !important; `"
                        >{{ status_list[order.status].status }}</q-chip
                      >
                    </div>

                    <div class="font13">
                      {{ $t("Order ID")
                      }}<span class="text-secondary q-ml-sm text-weight-bold"
                        >#{{ order.order_id_raw }}</span
                      >
                    </div>

                    <div class="row justify-between items-center">
                      <div class="font13" v-if="merchants[order.merchant_id]">
                        {{ merchants[order.merchant_id].restaurant_name }}
                      </div>
                      <div class="text-weight-bold font13">
                        {{ order.total }}
                      </div>
                    </div>
                    <div class="row justify-between items-center">
                      <div class="font13">{{ $t("Payment") }}</div>
                      <div
                        class="font12"
                        v-if="payment_list[order.payment_code]"
                      >
                        {{ payment_list[order.payment_code] }}
                      </div>
                    </div>

                    <div class="row items-center">
                      <q-chip
                        :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                        :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                        size="sm"
                        >{{ order.total_items }}</q-chip
                      >

                      <q-chip
                        v-if="services[order.service_code]"
                        :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                        :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                        size="sm"
                        >{{ services[order.service_code].service_name }}</q-chip
                      >

                      <q-chip
                        v-if="payment_status[order.payment_status]"
                        :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                        :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                        size="sm"
                        >{{
                          payment_status[order.payment_status].title
                        }}</q-chip
                      >
                    </div>
                  </q-card-section>
                </q-card>
              </q-item>
            </template>
          </template>
        </q-list>
        <template v-slot:loading>
          <q-inner-loading
            v-if="page <= 1"
            :showing="true"
            color="primary"
            size="md"
            label-class="dark"
            class="transparent"
          />
          <div v-else class="row justify-center absolute-bottom">
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
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          dense
          padding="3px"
        />
      </q-page-scroller>
    </q-page>
  </q-pull-to-refresh>

  <q-dialog
    v-model="modal_search"
    position="top"
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card
      class="no-shadow q-pt-md q-pb-sm"
      :class="{
        'text-dark': $q.dark.mode,
        'bg-white': !$q.dark.mode,
      }"
    >
      <q-card-section>
        <q-form @submit="onSubmit">
          <q-input
            v-model="q"
            :label="$t('Search order')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            borderless
            class="input-borderless cursor-pointer"
          >
            <template v-slot:append>
              <!-- <q-icon name="eva-search-outline" size="sm" /> -->
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

  <OrderPreview
    ref="OrderPreview"
    :data="order_items"
    :items_details="items"
  ></OrderPreview>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import { defineAsyncComponent } from "@vue/runtime-core";

export default {
  name: "OrderList",
  components: {
    Swiper,
    SwiperSlide,
    OrderPreview: defineAsyncComponent(() =>
      import("components/OrderPreview.vue")
    ),
  },
  data() {
    return {
      q: "",
      data: [],
      loading: false,
      load_more: false,
      page: 0,
      merchants: [],
      services: [],
      payment_list: [],
      payment_status: [],
      status_list: [],
      order_tab: "all",
      modal_search: false,
      is_search: false,
      is_refresh: undefined,
      order_items: [],
      items: {},
      orderTabs: [
        {
          label: this.$t("All"),
          value: "all",
        },
        {
          label: this.$t("Active"),
          value: "active",
        },
        {
          label: this.$t("Past Order"),
          value: "past_order",
        },
        {
          label: this.$t("Cancel Order"),
          value: "cancel_order",
        },
      ],
    };
  },
  watch: {
    order_tab(newval, oldval) {
      this.resetPage();
    },
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
    NodataMessage() {
      if (this.is_search) {
        return "No results found";
      } else {
        if (this.order_tab == "all") {
          return "You don't have any orders here!";
        } else {
          return "No results";
        }
      }
    },
    NodataMessage2() {
      if (this.is_search) {
        return "Sorry, we couldn't find any results";
      } else {
        if (this.order_tab == "all") {
          return "Let's change that";
        } else {
          return "no current order with this status";
        }
      }
    },
  },
  methods: {
    refresh(done) {
      this.resetPage();
      this.is_refresh = done;
    },
    orderHistory(index, done) {
      this.loading = true;
      this.page = index;
      //APIinterface.orderHistory(index, this.q)
      APIinterface.fetchDataByTokenPost(
        "orderHistory",
        "page=" + index + "&q=" + this.q + "&order_tab=" + this.order_tab
      )
        .then((data) => {
          if (data.details.show_next_page == false && index > 1) {
            this.$refs.nscroll.stop();
          } else {
            this.data.push(data.details.data.data);
            Object.entries(data.details.data.merchants).forEach(
              ([key, merchant]) => {
                this.merchants[key] = merchant;
              }
            );

            this.services = data.details.data.services;
            this.payment_list = data.details.payment_list;
            this.status_list = data.details.data.status;
            this.payment_status = data.details.payment_status;

            Object.entries(data.details.data.items2).forEach(
              ([item_id, food]) => {
                this.items[item_id] = food;
              }
            );
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
      console.log("onSubmit");
      this.modal_search = false;
      this.is_search = true;
      this.resetPagination();
    },
    resetPage() {
      this.q = "";
      this.resetPagination();
      this.is_search = false;
    },
    resetPagination() {
      this.page = 0;
      this.data = [];
      this.services = [];
      this.payment_list = [];
      this.status_list = [];
      this.$refs.nscroll.reset();
      this.$refs.nscroll.resume();
      this.$refs.nscroll.trigger();
    },
    showQuickDetails(data) {
      this.order_items = data;
      this.$refs.OrderPreview.dialog = !this.$refs.OrderPreview.dialog;
    },
  },
};
</script>

<style lang="scss">
.q-focus-helper {
  visibility: hidden;
}
</style>
