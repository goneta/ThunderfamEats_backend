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
          $t("Points")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>
    <q-page class="q-pl-md q-pr-md">
      <!-- POINTS BALANCE -->
      <div class="q-pa-md bg-grey-1 q-mb-sm radius8">
        <div class="row items-center q-gutter-md">
          <div>
            <q-skeleton type="QAvatar" v-if="loading_balance" />
            <q-icon
              v-else
              color="grey-4"
              name="card_giftcard"
              style="font-size: 60px"
            />
          </div>
          <div class="col">
            <template v-if="loading_balance">
              <q-skeleton type="text" />
              <q-skeleton type="text" />
            </template>
            <template v-else>
              <h4 class="no-margin">{{ balance }}</h4>
              <p class="font12 no-margin">{{ $t("Available Points") }}</p>
            </template>
          </div>
        </div>
      </div>
      <!-- POINTS BALANCE -->

      <q-tabs
        v-model="tab"
        dense
        active-color="primary"
        indicator-color="primary"
        align="left"
        narrow-indicator
        no-caps
        :class="{
          'text-grey300': $q.dark.mode,
          'text-dark': !$q.dark.mode,
        }"
        :breakpoint="0"
        @update:model-value="tabChange"
      >
        <q-tab
          name="transaction"
          :label="$t('Transactions')"
          no-caps
          content-class="text-weight-500 "
        />
        <q-tab
          name="points_merchant"
          :label="$t('Points by merchant')"
          no-caps
          content-class="text-weight-500 "
        />
      </q-tabs>

      <q-tab-panels
        v-model="tab"
        animated
        transition-next="fade"
        transition-prev="fade"
        :class="{
          'bg-mydark ': $q.dark.mode,
          'bg-white ': !$q.dark.mode,
        }"
      >
        <q-tab-panel
          name="transaction"
          class="q-pl-none q-pr-none"
          style="min-height: 300px"
        >
          <q-infinite-scroll
            ref="nscroll"
            @load="getPointsTransaction"
            :offset="250"
          >
            <template v-slot:default>
              <div
                v-if="!hasData && !loading"
                class="flex flex-center"
                style="min-height: 300px"
              >
                <p class="text-grey">{{ $t("No data available") }}</p>
              </div>
              <q-list>
                <template v-for="datas in data" :key="datas">
                  <template
                    v-for="items in datas"
                    :key="items.transaction_date"
                  >
                    <q-item>
                      <q-item-section>
                        <q-item-label>{{
                          items.transaction_description
                        }}</q-item-label>
                        <q-item-label caption>{{
                          items.transaction_date
                        }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <div
                          class="text-bold"
                          :class="{
                            'text-green': items.transaction_type == 'credit',
                            'text-red': items.transaction_type == 'debit',
                          }"
                        >
                          {{ items.transaction_amount }}
                        </div>
                      </q-item-section>
                    </q-item>
                    <q-separator spaced inset />
                  </template>
                </template>
              </q-list>
            </template>

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
              <div
                v-else-if="page > 2"
                class="row justify-center absolute-bottom"
              >
                <q-spinner-dots color="secondary" size="40px" />
              </div>
            </template>
          </q-infinite-scroll>
        </q-tab-panel>

        <!-- POINTS MERCHANT -->
        <q-tab-panel
          name="points_merchant"
          class="q-pl-none q-pr-none"
          style="min-height: 300px"
        >
          <q-infinite-scroll
            ref="merchantScroll"
            @load="getPointsTransactionMerchant"
            :offset="250"
          >
            <template v-slot:default>
              <div
                v-if="!hasDataMerchant && !loading_merchant"
                class="flex flex-center"
                style="min-height: 300px"
              >
                <p class="text-grey">{{ $t("No data available") }}</p>
              </div>
              <q-list>
                <template v-for="datas in data_merchant" :key="datas">
                  <template v-for="items in datas" :key="items.total_earning">
                    <q-item>
                      <q-item-section>
                        <q-item-label>{{ items.restaurant_name }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <div class="text-bold text-green">
                          {{ items.total_earning }}
                        </div>
                      </q-item-section>
                    </q-item>
                    <q-separator spaced inset />
                  </template>
                </template>
              </q-list>
            </template>

            <template v-slot:loading>
              <template v-if="page_merchant <= 1">
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
              <div
                v-else-if="page_merchant > 2"
                class="row justify-center absolute-bottom"
              >
                <q-spinner-dots color="secondary" size="40px" />
              </div>
            </template>
          </q-infinite-scroll>
        </q-tab-panel>
      </q-tab-panels>
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "PointsPage",
  data() {
    return {
      tab: "transaction",
      loading: false,
      loading_balance: true,
      data: [],
      page: 0,
      is_refresh: undefined,
      merchant_refresh: undefined,
      balance: 0,
      loading_merchant: false,
      data_merchant: [],
      page_merchant: 0,
    };
  },
  created() {
    this.getAvailablePoints();
  },
  computed: {
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
    hasDataMerchant() {
      if (Object.keys(this.data_merchant).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    tabChange(data) {
      if (data == "transaction") {
        this.data = [];
      } else {
        this.data_merchant = [];
      }
    },
    getAvailablePoints() {
      this.loading_balance = true;
      APIinterface.fetchDataByTokenPost("getAvailablePoints")
        .then((data) => {
          if (data.code == 1) {
            this.balance = data.details.total;
          } else {
            this.balance = 0;
          }
        })
        .catch((error) => {})
        .then((data) => {
          this.loading_balance = false;
        });
    },
    getPointsTransaction(index, done) {
      this.loading = true;
      this.page = index;

      APIinterface.fetchDataByTokenPost("getPointsTransaction", "page=" + index)
        .then((data) => {
          console.log(data);
          if (data.code == 1) {
            this.data.push(data.details.data);
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
    refresh(done) {
      if (this.tab == "transaction") {
        this.resetPage();
        this.is_refresh = done;
        this.getAvailablePoints();
      } else {
        this.resetPagination2();
        this.merchant_refresh = done;
      }
    },
    resetPage() {
      this.resetPagination();
    },
    resetPagination() {
      this.page = 0;
      this.data = [];
      this.$refs.nscroll.reset();
      this.$refs.nscroll.resume();
      this.$refs.nscroll.trigger();
    },
    resetPagination2() {
      this.page_merchant = 0;
      this.data_merchant = [];
      this.$refs.merchantScroll.reset();
      this.$refs.merchantScroll.resume();
      this.$refs.merchantScroll.trigger();
    },
    getPointsTransactionMerchant(index, done) {
      this.loading_merchant = true;
      this.page_merchant = index;

      APIinterface.fetchDataByTokenPost(
        "getPointsTransactionMerchant",
        "page=" + index
      )
        .then((data) => {
          console.log(data);
          if (data.code == 1) {
            this.data_merchant.push(data.details.data);
          } else {
            this.$refs.merchantScroll.stop();
          }
        })
        .catch((error) => {
          if (this.$refs.merchantScroll) {
            this.$refs.merchantScroll.stop();
          }
        })
        .then((data) => {
          this.loading_merchant = false;
          done();
          if (!APIinterface.empty(this.merchant_refresh)) {
            this.merchant_refresh();
          }
        });
    },
  },
};
</script>
