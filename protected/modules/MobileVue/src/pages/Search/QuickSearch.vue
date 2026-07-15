<template>
  <q-header>
    <q-toolbar class="bg-white text-dark">
      <q-btn
        @click="$router.back()"
        color="white"
        rounded
        unelevated
        text-color="dark"
        icon="arrow_back"
        dense
        no-caps
      />

      <q-input
        v-model="q"
        ref="items"
        :label="$t('Food, Restaurant, drinks etc')"
        dense
        outlined
        color="grey"
        bg-color="white"
        class="full-width input-borderless"
        :loading="awaitingSearch"
      >
        <template v-if="!awaitingSearch" v-slot:append>
          <q-icon
            v-if="isSearch"
            @click="clearField()"
            class="cursor-pointer"
            name="close"
          />
        </template>
      </q-input>
    </q-toolbar>
  </q-header>
  <!-- <q-space class="q-pa-xs bg-grey-2"></q-space> -->

  <div class="q-pa-sm q-pr-md q-pl-md bg-grey-2">
    <div class="row">
      <div class="col font12">
        <template v-if="tab === 'food'">
          <template v-if="hasItems">
            <b class="text-green-5">{{ totalItemFound }}</b>
            {{ $t("results for") }} "{{ q }}"
          </template>
        </template>
        <template v-else>
          <template v-if="hasData">
            <b class="text-green-5">{{ totalFound }}</b>
            {{ $t("results for") }} "{{ q }}"
          </template>
        </template>
      </div>
      <div class="col text-right"></div>
    </div>
  </div>

  <q-page>
    <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="dark"
      indicator-color="dark"
      align="justify"
      narrow-indicator
    >
      <q-tab name="food" label="Food" no-caps />
      <q-tab name="restaurant" label="Restaurants" no-caps />
    </q-tabs>
    <q-separator />

    <q-tab-panels
      v-model="tab"
      animated
      transition-next="fade"
      transition-prev="fade"
    >
      <q-tab-panel name="restaurant">
        <template v-if="awaitingSearch">
          <q-list>
            <q-item v-for="i in 7" :key="i">
              <q-item-section avatar>
                <q-skeleton type="QAvatar" />
              </q-item-section>
              <q-item-section>
                <q-skeleton type="text" style="width: 100px" />
                <q-skeleton type="text" />
              </q-item-section>
            </q-item>
          </q-list>
        </template>

        <template v-else>
          <q-list dense v-if="hasData">
            <template v-for="items in merchant_data" :key="items">
              <q-item
                clickable
                v-ripple
                :to="{ name: 'menu', params: { slug: items.restaurant_slug } }"
              >
                <q-item-section avatar>
                  <q-img
                    :src="items.url_logo"
                    fit="cover"
                    loading="lazy"
                    style="height: 50px; width: 50px"
                    spinner-color="amber"
                    spinner-size="sm"
                  />
                </q-item-section>
                <q-item-section class="font12">
                  <div class="font12 text-weight-medium q-mb-xs">
                    {{ items.restaurant_name }}
                  </div>
                  <p
                    class="font11 text-grey height-normal no-margin ellipsis-2-lines"
                  >
                    <template
                      v-for="cuisine_index in items.cuisine_group"
                      :key="cuisine_index"
                    >
                      <template v-if="cuisine[cuisine_index]"
                        >{{ cuisine[cuisine_index].name }},
                      </template>
                    </template>
                  </p>
                </q-item-section>
              </q-item>
              <q-separator spaced />
            </template>
          </q-list>

          <div v-else class="fit q-mt-xl text-center q-pa-md flex flex-center">
            <div v-if="isSearch">
              <q-img
                src="bankrupt.png"
                style="height: 80px; max-width: 80px"
                class="light-dimmed"
              />
              <p class="font11 text-grey">
                {{ $t("No results for") }} "{{ q }}"
              </p>
            </div>
          </div>
        </template>
      </q-tab-panel>
      <q-tab-panel name="food">
        <template v-if="awaitingSearch">
          <q-list>
            <q-item v-for="i in 7" :key="i">
              <q-item-section avatar>
                <q-skeleton type="QAvatar" />
              </q-item-section>
              <q-item-section>
                <q-skeleton type="text" style="width: 100px" />
                <q-skeleton type="text" />
              </q-item-section>
            </q-item>
          </q-list>
        </template>

        <template v-else>
          <q-list v-if="hasItems">
            <template v-for="items in food_list" :key="items">
              <q-item clickable v-ripple @click="onClickitem(items)">
                <q-item-section avatar>
                  <q-img
                    :src="items.url_image"
                    lazy
                    fit="cover"
                    style="height: 50px; width: 50px"
                    class="rounded-borders"
                    spinner-color="amber"
                    spinner-size="sm"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label
                    lines="1"
                    class="font12 text-weight-bold q-mb-sm"
                  >
                    {{ items.item_name }}
                    <p
                      v-if="merchant_list[items.merchant_id]"
                      class="font11 text-grey no-margin"
                    >
                      {{ merchant_list[items.merchant_id].restaurant_name }}
                    </p>
                    <p
                      v-if="items.price[0]"
                      class="font12 text-weight-medium no-margin"
                    >
                      <template v-if="items.price[0].discount <= 0">
                        <span class="q-mr-sm"
                          >{{ items.price[0].size_name }}
                          {{ items.price[0].pretty_price }}</span
                        >
                      </template>
                      <template v-else>
                        <span class="q-mr-sm"
                          >{{ items.price[0].size_name }}
                          <del>{{ items.price[0].pretty_price }}</del>
                          {{ items.price[0].pretty_price_after_discount }}</span
                        >
                      </template>
                    </p>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-list>

          <div v-else class="fit q-mt-xl text-center q-pa-md flex flex-center">
            <div v-if="isSearch">
              <q-img
                src="bankrupt.png"
                style="height: 80px; max-width: 80px"
                class="light-dimmed"
              />
              <p class="font11 text-grey">
                {{ $t("No results for") }} "{{ q }}"
              </p>
            </div>
          </div>
        </template>
      </q-tab-panel>
    </q-tab-panels>

    <ItemDetails
      ref="item_details"
      :money_config="money_config"
      @after-additems="afterAdditems"
    />
  </q-page>

  <q-footer
    v-if="CartStore.hasData"
    reveal
    class="bg-white q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
  >
    <q-btn
      to="/cart"
      :loading="CartStore.loading"
      unelevated
      color="primary"
      text-color="dark"
      no-caps
      class="radius8 full-width"
    >
      <div class="row full-width items-center">
        <div class="col text-left">
          <q-avatar
            square
            size="24px"
            color="amber-2"
            text-color="dark"
            class="text-weight-bold rounded-borders"
          >
            <template v-if="CartStore.data">
              {{ CartStore.data.items_count }}
            </template>
          </q-avatar>
        </div>
        <div class="col text-weight-600">{{ $t("View Order") }}</div>
        <div class="col text-right text-weight-bold">
          <template v-if="CartStore.data">
            {{ CartStore.data.data.subtotal.value }}
          </template>
        </div>
      </div>
    </q-btn>
  </q-footer>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "stores/CartStore";

export default {
  name: "QuickSearch",
  components: {
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
  },
  data() {
    return {
      q: "",
      tab: "food",
      awaitingSearch: false,
      merchant_data: [],
      cuisine: [],
      food_list: [],
      merchant_list: [],
      slug: "",
      money_config: [],
    };
  },
  setup() {
    const CartStore = useCartStore();
    return { CartStore };
  },
  mounted() {
    this.initSearch();
    this.CartStore.getCartCount();
  },
  watch: {
    q(newdata, oldata) {
      if (!this.awaitingSearch) {
        if (
          typeof this.q === "undefined" ||
          this.q === null ||
          this.q === "" ||
          this.q === "null" ||
          this.q === "undefined"
        ) {
          this.data = [];
          return false;
        }
        setTimeout(() => {
          this.saveHistory();
          APIinterface.Search(this.q, APIinterface.getStorage("place_id"))
            .then((data) => {
              console.debug(data);
              this.merchant_data = data.details.merchant_data;
              this.cuisine = data.details.cuisine;
              this.food_list = data.details.food_list;
              this.merchant_list = data.details.merchant_list;
              this.money_config = data.details.money_config;
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
  computed: {
    totalFound() {
      return Object.keys(this.merchant_data).length;
    },
    totalItemFound() {
      return Object.keys(this.food_list).length;
    },
    hasData() {
      if (this.merchant_data.length > 0) {
        return true;
      }
      return false;
    },
    hasItems() {
      if (Object.keys(this.food_list).length > 0) {
        return true;
      }
      return false;
    },
    isSearch() {
      if (!APIinterface.empty(this.q)) {
        return true;
      }
      return false;
    },
  },
  methods: {
    Focus() {
      this.$refs.items.focus();
    },
    clearField() {
      this.q = "";
      this.Focus();
    },
    initSearch() {
      this.q = this.$route.query.q;
      this.Search();
    },
    Search() {
      this.awaitingSearch = true;
      APIinterface.Search(this.q, APIinterface.getStorage("place_id"))
        .then((data) => {
          this.merchant_data = data.details.merchant_data;
          this.cuisine = data.details.cuisine;
          this.food_list = data.details.food_list;
          this.merchant_list = data.details.merchant_list;
          this.money_config = data.details.money_config;
        })
        // eslint-disable-next-line
        .catch((error) => {
          this.merchant_data = [];
          this.cuisine = this.food_list = [];
          this.merchant_list = [];
        })
        .then((data) => {
          this.awaitingSearch = false;
        });
    },
    onClickitem(data) {
      const $params = {
        cat_id: data.cat_id,
        item_uuid: data.item_uuid,
      };
      if (!APIinterface.empty(this.merchant_list[data.merchant_id])) {
        this.slug = this.merchant_list[data.merchant_id].restaurant_slug;
      }
      this.$refs.item_details.showItem2($params, data.slug);
    },
    afterAdditems() {
      console.debug("afterAdditems");
      this.CartStore.getCartCount();
    },
    saveHistory() {
      const history = APIinterface.getStorage("search_history");
      let historyJson = [];
      let historyCount = 0;
      if (!APIinterface.empty(history)) {
        historyJson = JSON.parse(history);
        historyCount = historyJson.length;
      }
      console.debug(historyCount);
      if (historyCount > 4) {
        historyJson.splice(0, 1);
      }
      historyJson.push(this.q);
      APIinterface.setStorage("search_history", JSON.stringify(historyJson));
    },
  },
};
</script>
