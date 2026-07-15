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
        <q-toolbar-title class="text-weight-bold">
          <template v-if="DataStore.featured_data[this.featured_id]">
            {{ DataStore.featured_data[this.featured_id] }}
          </template>
          <template v-else-if="cuisine_name">
            {{ cuisine_name }}
          </template>
          <template v-else-if="page_title">
            {{ page_title }}
          </template>
        </q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page padding>
      <DIV class="q-pl-md q-pr-md">
        <div class="row items-center justify-between no-wrap">
          <q-btn
            :color="$q.dark.mode ? 'grey600' : 'mygrey'"
            :text-color="$q.dark.mode ? 'grey300' : 'dark'"
            icon-size="11px"
            unelevated
            no-caps
            size="md"
            class="radius10"
            @click="this.$refs.sort_list.show_modal = true"
          >
            <div class="row items-center no-wrap full-width">
              <div class="text-center q-pr-sm">
                <template v-if="DataStore.sort_list[sort_by]">
                  {{ DataStore.sort_list[sort_by] }}
                </template>
                <template v-else> {{ $t("Sort") }} </template>
              </div>
              <q-icon name="las la-angle-down" size="15px" />
            </div>
          </q-btn>

          <div>
            <q-btn
              :color="$q.dark.mode ? 'grey600' : 'mygrey'"
              :text-color="$q.dark.mode ? 'grey300' : 'dark'"
              unelevated
              no-caps
              round
              size="12px"
              class="q-mr-sm"
              @click="
                this.menu_type = this.menu_type == 'list' ? 'column' : 'list'
              "
            >
              <q-icon :name="switchIconList" />
            </q-btn>
            <q-btn
              :color="$q.dark.mode ? 'grey600' : 'mygrey'"
              :text-color="$q.dark.mode ? 'grey300' : 'dark'"
              unelevated
              no-caps
              round
              size="12px"
              @click="this.$refs.merchant_filter.filter = true"
            >
              <q-icon name="tune" />
            </q-btn>
          </div>
        </div>
        <!-- row -->

        <div v-if="loading && page <= 0" class="text-grey q-pt-sm q-pb-xs">
          <q-skeleton type="text" style="width: 100px" />
        </div>
        <div
          v-else
          class="text-grey q-pt-sm q-pb-xs"
          :class="{ 'min-height-inherit flex flex-center q-pa-none': !hasData }"
        >
          <template v-if="hasData">
            {{ total_found }} {{ $t("restaurant") }}
          </template>
          <template v-else>
            <NoResults @filter-again="filterAgain"></NoResults>
          </template>
        </div>
      </DIV>

      <q-infinite-scroll ref="nscroll" @load="getMerchantFeed" :offset="250">
        <q-list class="no-wrap">
          <template v-for="itemsdata in data" :key="itemsdata">
            <q-item
              v-for="items in itemsdata"
              :key="items.merchant_id"
              clickable
              v-ripple
              @click.stop="
                this.$router.push({
                  name: 'menu',
                  params: {
                    slug: items.restaurant_slug,
                  },
                })
              "
            >
              <!-- :to="{ name: 'menu', params: { slug: items.restaurant_slug } }" -->
              <MerchantListInline
                v-if="menu_type === 'list'"
                :items="items"
                :cuisine="cuisine"
                :reviews="reviews"
                :estimation="estimation"
                :services="services"
                :items_min_max="items_min_max"
                :promos="promos"
              />
              <MerchantListTpl
                v-else
                :items="items"
                :cuisine="cuisine"
                :reviews="reviews"
                :estimation="estimation"
                :services="services"
                :items_min_max="items_min_max"
                :promos="promos"
              />
            </q-item>
          </template>
        </q-list>

        <template v-slot:loading>
          <MerchantListSkeleton v-if="page <= 0" />
          <div v-else class="row justify-center absolute-bottom">
            <q-spinner-dots color="secondary" size="40px" />
          </div>
        </template>
      </q-infinite-scroll>

      <SortList ref="sort_list" @after-selectsort="afterSelectsort"></SortList>

      <MerchantFilter
        ref="merchant_filter"
        :cuisine_data="DataStore.cuisine"
        @apply-filter="applyFilter"
        @run-filter="runFilter"
      />
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { defineAsyncComponent, ref } from "vue";
import { useCartStore } from "stores/CartStore";
import { useDataStore } from "stores/DataStore";
import auth from "src/api/auth";

export default {
  name: "FeedPage",
  components: {
    MerchantListInline: defineAsyncComponent(() =>
      import("components/MerchantListInline.vue")
    ),
    MerchantListTpl: defineAsyncComponent(() =>
      import("components/MerchantListTpl.vue")
    ),
    MerchantListSkeleton: defineAsyncComponent(() =>
      import("components/MerchantListSkeleton.vue")
    ),
    SortList: defineAsyncComponent(() => import("components/SortList.vue")),
    MerchantFilter: defineAsyncComponent(() =>
      import("components/MerchantFilter.vue")
    ),
    NoResults: defineAsyncComponent(() => import("components/NoResults.vue")),
  },
  data() {
    return {
      loading: true,
      list_type: "",
      featured_id: "",
      data: [],
      cuisine: [],
      reviews: [],
      estimation: [],
      services: [],
      items_min_max: [],
      title: "",
      page: 0,
      sort_by: "",
      total_found: 0,
      filters: [],
      transactionType: "",
      menu_type: "column",
      cuisine_id: "",
      cuisine_name: "",
      page_title: "",
      promos: [],
      payload: [
        "cuisine",
        "reviews",
        "estimation",
        "services",
        "items_min_max",
        "offers",
        "promo",
      ],
    };
  },
  setup() {
    const CartStore = useCartStore();
    const DataStore = useDataStore();
    return { CartStore, DataStore };
  },
  watch: {
    menu_type(newval, oldval) {
      console.log(newval);
      APIinterface.setStorage("listType", newval);
    },
  },
  created() {
    this.transactionType = APIinterface.getStorage("transaction_type");
    this.list_type = this.$route.query.query;
    this.featured_id = this.$route.query.featured_id;
    this.cuisine_id = this.$route.query.cuisine_id;
    this.cuisine_name = this.$route.query.cuisine_name;
    this.page_title = this.$route.query.page_title;

    if (this.cuisine_id > 0) {
      this.filters = {
        cuisine: [this.cuisine_id],
      };
    }

    let listType = APIinterface.getStorage("listType");
    if (!APIinterface.empty(listType)) {
      this.menu_type = listType;
    }

    if (Object.keys(this.DataStore.featured_data).length <= 0) {
      this.DataStore.getFeaturedList();
    }

    if (!this.DataStore.hasDataCuisine()) {
      this.DataStore.CuisineList();
    }
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
    switchIconList() {
      if (this.menu_type == "list") {
        return "grid_view";
      } else {
        return "reorder";
      }
    },
  },
  methods: {
    refresh(done) {
      done();
      this.resetPagination();
    },
    getMerchantFeed(index, done) {
      const $params = {
        list_type: this.list_type,
        featured_id: this.featured_id,
        place_id: APIinterface.getStorage("place_id"),
        payload: this.payload,
        page: index,
        sort_by: this.sort_by,
        filters: this.filters,
      };
      if (this.list_type === "featured") {
        $params.featured_id = this.featured_id;
      }
      this.loading = true;

      if (auth.authenticated()) {
        APIinterface.fetchDataByToken("getMerchantFeed2", $params)
          .then((data) => {
            this.page = index;
            if (data.code == 1) {
              this.data.push(data.details.data);
              this.cuisine = data.details.cuisine;
              this.reviews = data.details.reviews;
              this.estimation = data.details.estimation;
              this.services = data.details.services;
              this.items_min_max = data.details.items_min_max;
              this.total_found = data.details.total_found;
              this.promos = data.details.promos;
            } else {
              this.total_found = data.details.total_found;
              this.$refs.nscroll.stop();
            }
          })
          .catch((error) => {
            if (this.$refs.nscroll) {
              this.$refs.nscroll.stop();
            }
            this.total_found = 0;
          })
          .then((data) => {
            this.loading = false;
            done();
          });
      } else {
        APIinterface.getMerchantFeed($params)
          .then((data) => {
            this.page = index;
            if (data.code == 1) {
              this.data.push(data.details.data);
              this.cuisine = data.details.cuisine;
              this.reviews = data.details.reviews;
              this.estimation = data.details.estimation;
              this.services = data.details.services;
              this.items_min_max = data.details.items_min_max;
              this.total_found = data.details.total_found;
              this.promos = data.details.promos;
            } else {
              this.total_found = data.details.total_found;
              this.$refs.nscroll.stop();
            }
          })
          .catch((error) => {
            this.$refs.nscroll.stop();
            this.total_found = 0;
          })
          .then((data) => {
            this.loading = false;
            done();
          });
      }
    },
    afterSelectsort(data) {
      console.log("afterSelectsort=>" + data);
      this.sort_by = data;
      this.resetPagination();
      this.page = 0;
    },
    applyFilter(data) {
      this.filters = data;
      this.filters.transaction_type = this.transactionType;
      this.resetPagination();
      this.page = 0;
    },
    resetPagination() {
      this.page = 0;
      this.data = [];
      this.$refs.nscroll.reset();
      this.$refs.nscroll.resume();
      this.$refs.nscroll.trigger();
    },
    filterAgain() {
      console.log("filterAgain");
      this.$refs.merchant_filter.filter = true;
    },
  },
};
</script>
