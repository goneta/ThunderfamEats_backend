<template>
  <q-header reveal reveal-offset="50" class="bg-transparent text-dark">
    <q-toolbar>
      <q-btn
        to="/home"
        flat
        round
        dense
        icon="las la-angle-left"
        class="q-mr-sm"
        :color="$q.dark.mode ? 'white' : 'dark'"
      />
      <q-toolbar-title
        class="text-weight-bold"
        :class="{
          'text-white': $q.dark.mode,
          'text-dark': !$q.dark.mode,
        }"
        >{{ $t("Search") }}</q-toolbar-title
      >
      <NotiButton></NotiButton>
    </q-toolbar>
  </q-header>
  <q-page>
    <div class="q-pl-md q-pr-md">
      <q-input
        v-model="q"
        :label="$t('Search food and restaurants')"
        outlined
        lazy-rules
        :bg-color="$q.dark.mode ? 'grey600' : 'input'"
        :label-color="$q.dark.mode ? 'grey300' : 'grey'"
        borderless
        class="input-borderless"
      >
        <template v-slot:prepend>
          <q-icon name="eva-search-outline" size="sm" />
        </template>
        <template v-slot:append>
          <q-icon
            v-if="hasFilter"
            class="cursor-pointer"
            name="close"
            color="grey"
            @click="this.q = ''"
          />
        </template>
      </q-input>
    </div>

    <q-space class="q-pa-xs"></q-space>

    <template v-if="hasResults && hasFilter && !awaitingSearch">
      <q-tabs
        v-model="tab"
        dense
        active-color="red"
        indicator-color="white"
        align="justify"
        narrow-indicator
        shrink
        switch-indicator="false"
        class="text-grey"
      >
        <q-tab name="all" no-caps class="no-wrap q-pa-none">
          <q-btn
            :label="$t('All')"
            unelevated
            no-caps
            :color="tab == 'all' ? 'primary' : 'mygrey'"
            :text-color="tab == 'all' ? 'white' : 'dark'"
            class="radius28"
          ></q-btn>
        </q-tab>
        <q-tab name="restaurant" no-caps class="q-pa-none">
          <q-btn
            :label="$t('Restaurants')"
            unelevated
            no-caps
            :color="tab == 'restaurant' ? 'primary' : 'mygrey'"
            :text-color="tab == 'restaurant' ? 'white' : 'dark'"
            class="radius28"
          ></q-btn>
        </q-tab>
        <q-tab name="food" no-caps class="q-pa-none">
          <q-btn
            :label="$t('Food')"
            unelevated
            no-caps
            :color="tab == 'food' ? 'primary' : 'mygrey'"
            :text-color="tab == 'food' ? 'white' : 'dark'"
            class="radius28"
          ></q-btn>
        </q-tab>
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
        <q-tab-panel name="all" class="q-pa-none">
          <q-list separator>
            <template v-for="items in all_data" :key="items">
              <q-item @click="onClickResult(items)" clickable>
                <template v-if="items.restaurant_name">
                  <SearchListMerchant
                    :items="items"
                    :cuisine="cuisine"
                  ></SearchListMerchant>
                </template>
                <template v-else>
                  <SearchListFood
                    :items="items"
                    :merchant_list="merchant_list"
                    :money_config="money_config"
                  ></SearchListFood>
                </template>
              </q-item>
            </template>
          </q-list>
        </q-tab-panel>
        <q-tab-panel name="restaurant" class="q-pa-none">
          <q-list separator>
            <template v-for="items in merchant_data" :key="items">
              <q-item @click="onClickResult(items)" clickable>
                <SearchListMerchant
                  :items="items"
                  :cuisine="cuisine"
                ></SearchListMerchant>
              </q-item>
            </template>
          </q-list>
        </q-tab-panel>
        <q-tab-panel name="food" class="q-pa-none">
          <q-list separator>
            <template v-for="items in food_list" :key="items">
              <q-item @click="onClickResult(items)" clickable>
                <SearchListFood
                  :items="items"
                  :merchant_list="merchant_list"
                  :money_config="money_config"
                ></SearchListFood>
              </q-item>
            </template>
          </q-list>
        </q-tab-panel>
      </q-tab-panels>
    </template>

    <template v-else-if="hasFilter && awaitingSearch">
      <q-list>
        <q-item v-for="i in 8" :key="i">
          <q-item-section avatar>
            <q-skeleton width="80px" height="80px" />
          </q-item-section>
          <q-item-section>
            <q-skeleton type="text" />
            <q-skeleton type="text" class="w-50" />
            <q-skeleton type="text" class="w-70" />
            <q-skeleton type="text" class="w-25" />
          </q-item-section>
        </q-item>
      </q-list>
    </template>

    <template v-else>
      <template v-if="hasFilter && !awaitingSearch">
        <div class="min-height-inherit flex flex-center">
          <div class="full-width text-center q-pb-xl">
            <div class="text-h5 text-weight-bold">
              {{ $t("No Restaurants found") }}.
            </div>
            <p class="text-grey font12">
              {{ $t("Sorry, we couldn't find any results") }}
            </p>
          </div>
        </div>
      </template>
      <template v-else>
        <div class="q-pl-md q-pr-md">
          <div class="row item-center justify-between">
            <div class="font13 text-weight-bold text-h5">
              {{ $t("Recently Search") }}
            </div>
            <div v-if="hasHistory">
              <q-btn
                @click="removeHistory"
                round
                color="grey"
                icon="las la-times"
                size="xs"
                flat
              />
            </div>
          </div>

          <q-btn-toggle
            v-model="search_history"
            toggle-color="secondary"
            :color="$q.dark.mode ? 'grey600' : 'mygrey'"
            :text-color="$q.dark.mode ? 'grey300' : 'dark'"
            no-caps
            no-wrap
            unelevated
            class="rounded-group2 text-weight-bold line-1"
            :options="data_recent"
            @click="this.q = this.search_history"
          />

          <div class="flex flex-center" style="min-height: 300px">
            <div class="full-width text-center">
              <q-img
                src="cuttery.png"
                fit="fill"
                spinner-color="primary"
                style="height: 150px; max-width: 130px"
              />
              <div class="text-h5 text-weight-bold">
                {{ $t("Search Restaurants") }}
              </div>
              <p class="text-grey font12">
                {{ $t("Search your favourite cuisine and restaurants") }}
              </p>
            </div>
          </div>
        </div>
      </template>
      <!-- end -->
    </template>

    <ItemDetails
      ref="refItem"
      :slug="slug"
      :money_config="money_config"
      @after-additems="afterAdditems"
      :currency_code="DataStorePersisted.getUseCurrency()"
    />
  </q-page>

  <q-footer
    v-if="CartStore.items_count > 0 && item_added == true"
    reveal
    class="q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
    :class="{
      'bg-primary': !CartStore.cart_reloading,
      'bg-grey-5': CartStore.cart_reloading,
    }"
  >
    <q-btn
      to="/checkout"
      :loading="CartStore.cart_loading"
      :disable="!CartStore.canProceed"
      unelevated
      text-color="white"
      no-caps
      class="radius10 fit"
      :color="{
        primary: !CartStore.cart_reloading,
        'grey-5': CartStore.cart_reloading,
      }"
    >
      <div class="row items-center justify-between fit">
        <div class="text-weight-bold">{{ $t("Checkout") }}</div>
        <div class="text-weight-bold">
          {{ CartStore.cart_subtotal.value }}
        </div>
      </div>
    </q-btn>
  </q-footer>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "src/stores/CartStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";

export default {
  name: "QuickSearchResultsPage",
  data() {
    return {
      tab: "all",
      q: "",
      merchant_data: [],
      cuisine: [],
      food_list: [],
      merchant_list: [],
      money_config: [],
      loading: false,
      awaitingSearch: false,
      data_recent: [],
      search_history: [],
      all_data: [],
      slug: "",
      item_added: false,
    };
  },
  setup() {
    const CartStore = useCartStore();
    const DataStorePersisted = useDataStorePersisted();
    return { CartStore, DataStorePersisted };
  },
  components: {
    SearchListMerchant: defineAsyncComponent(() =>
      import("components/SearchListMerchant.vue")
    ),
    SearchListFood: defineAsyncComponent(() =>
      import("components/SearchListFood.vue")
    ),
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
  },
  created() {
    this.getSearchHistory();
  },
  computed: {
    hasHistory() {
      if (Object.keys(this.data_recent).length > 0) {
        return true;
      }
      return false;
    },
    hasFilter() {
      if (!APIinterface.empty(this.q)) {
        return true;
      }
      return false;
    },
    hasResults() {
      if (Object.keys(this.all_data).length > 0) {
        return true;
      }
      return false;
    },
  },
  watch: {
    // search_history(newval, oldval) {
    //   this.q = newval;
    // },
    q(newdata, oldata) {
      if (!this.awaitingSearch) {
        if (
          typeof this.q === "undefined" ||
          this.q === null ||
          this.q === "" ||
          this.q === "null" ||
          this.q === "undefined"
        ) {
          return false;
        }
        setTimeout(() => {
          this.saveHistory();
          this.tab = "all";
          APIinterface.Search(
            this.q,
            APIinterface.getStorage("place_id"),
            this.DataStorePersisted.use_currency_code
          )
            .then((data) => {
              console.debug(data);
              this.merchant_data = data.details.merchant_data;
              this.cuisine = data.details.cuisine;
              this.food_list = data.details.food_list;
              this.merchant_list = data.details.merchant_list;
              //this.money_config = data.details.money_config;
              this.all_data = this.merchant_data.concat(this.food_list);
            })
            // eslint-disable-next-line
            .catch((error) => {
              this.merchant_data = [];
              this.cuisine = [];
              this.food_list = [];
              this.merchant_list = [];
            })
            .then((data) => {
              this.awaitingSearch = false;
            });
        }, 1000); // 1 sec delay
      }
      this.awaitingSearch = true;
    },
  },
  methods: {
    clearField() {
      this.q = "";
      this.Focus();
    },
    getSearchHistory() {
      const history = APIinterface.getStorage("search_history");
      if (!APIinterface.empty(history)) {
        let dataRecent = JSON.parse(history);
        if (Object.keys(dataRecent).length > 0) {
          this.data_recent = [];
          Object.entries(dataRecent).forEach(([key, items]) => {
            this.data_recent.push({
              label: items,
              value: items,
            });
          });
        }
      }
    },
    saveHistory() {
      console.log("saveHistory");
      const history = APIinterface.getStorage("search_history");
      let historyJson = [];
      let historyCount = 0;
      if (!APIinterface.empty(history)) {
        historyJson = JSON.parse(history);
        historyCount = historyJson.length;
        console.log(historyJson);
        if (historyCount > 4) {
          historyJson.splice(0, 1);
        }

        if (!historyJson.includes(this.q)) {
          historyJson.push(this.q);
          APIinterface.setStorage(
            "search_history",
            JSON.stringify(historyJson)
          );
        }
        this.getSearchHistory();
      } else {
        historyJson.push(this.q);
        APIinterface.setStorage("search_history", JSON.stringify(historyJson));
      }
    },
    removeHistory() {
      this.data_recent = [];
      APIinterface.setStorage(
        "search_history",
        JSON.stringify(this.data_recent)
      );
    },
    onClickResult(data) {
      if (data.restaurant_name) {
        this.$router.push({
          name: "menu",
          params: { slug: data.restaurant_slug },
        });
      } else {
        this.item_added = false;
        this.slug = data.slug;
        const $params = {
          cat_id: data.cat_id,
          item_uuid: data.item_uuid,
        };
        this.money_config = data.money_config;
        this.$refs.refItem.showItem2($params, this.slug);
      }
    },
    afterAdditems() {
      console.log("afterAdditems");
      this.item_added = true;
      this.CartStore.getCart(false, this.CartStore.cart_payload);
    },
  },
};
</script>
