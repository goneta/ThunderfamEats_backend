<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-page
      padding
      class="q-pl-md q-pr-md"
      :class="{ 'flex flex-center': !hasResult }"
    >
      <!-- <q-scroll-observer @scroll="onScroll" /> -->
      <template v-if="hasResult">
        <q-page-sticky expand position="top" class="z-top" v-if="isSticky">
          <q-toolbar
            class="text-dark q-pl-md q-pr-md q-pt-sm q-pb-sm"
            :class="{
              'bg-mydark': $q.dark.mode,
              'bg-white': !$q.dark.mode,
            }"
          >
            <q-input
              v-model="q"
              :label="$t('Search food and restaurants')"
              @click="goSearch"
              readonly
              outlined
              lazy-rules
              :bg-color="$q.dark.mode ? 'grey600' : 'input'"
              :label-color="$q.dark.mode ? 'grey300' : 'grey'"
              borderless
              class="input-borderless fit"
              dense
            >
              <template v-slot:prepend>
                <q-icon name="eva-search-outline" size="sm" />
              </template>
              <template v-slot:append>
                <q-icon
                  name="tune"
                  class="cursor-pointer"
                  @click="this.$refs.merchant_filter.filter = true"
                  size="sm"
                />
              </template>
            </q-input>
          </q-toolbar>
        </q-page-sticky>

        <h5 class="text-weight-bold q-ma-none">
          {{ $t("Hello") }},
          <span v-if="userData" class="text-secondary">{{
            userData.first_name
          }}</span>
          <span v-else class="text-secondary">{{ $t("Guest") }}</span>
        </h5>
        <p class="text-weight-medium q-ma-none">
          {{ $t("Find your delicious dish") }}
        </p>

        <q-space class="q-pa-xs"></q-space>

        <q-input
          v-model="q"
          :label="$t('Search food and restaurants')"
          @click="goSearch"
          readonly
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
              name="tune"
              class="cursor-pointer"
              @click="this.$refs.merchant_filter.filter = true"
              size="sm"
            />
          </template>
        </q-input>

        <q-space class="q-pa-sm"></q-space>

        <HomeBanner ref="home_banner" />

        <div class="q-mt-md">
          <template v-if="DataStore.loading_cuisine">
            <div class="row justify-between">
              <q-skeleton type="text" style="width: 100px" />
              <q-skeleton type="text" style="width: 50px" />
            </div>
          </template>
          <div v-else class="row">
            <div class="col">
              <div class="text-h5 text-weight-medium">
                {{ $t("Explore Cuisine") }}
              </div>
            </div>
            <div class="col text-right">
              <q-btn
                flat
                :color="$q.dark.mode ? 'grey300' : 'secondary'"
                :label="$t('Show All')"
                no-caps
                class="text-weight-600"
                dense
                @click="
                  this.$refs.browse_cuisine.modal =
                    !this.$refs.browse_cuisine.modal
                "
              />
            </div>
          </div>
        </div>

        <CuisineCarousel
          ref="cuisine_carousel"
          :design="1"
          @after-getdata="afterGetdata"
          class="q-pt-sm q-pb-md"
        />

        <div class="q-mt-sm q-mb-xs">
          <template v-if="DataStore.car_loading[0]">
            <div class="row justify-between">
              <q-skeleton type="text" style="width: 100px" />
              <q-skeleton type="text" style="width: 50px" />
            </div>
          </template>
          <template v-else>
            <div v-if="DataStore.hasCarData(0)" class="row">
              <div class="col">
                <div class="text-h5 text-weight-medium">
                  {{ $t("Popular Near You") }}
                </div>
              </div>
              <div class="col text-right">
                <q-btn
                  :to="{
                    name: 'feed',
                    query: { query: 'featured', featured_id: topResto },
                  }"
                  flat
                  :color="$q.dark.mode ? 'grey300' : 'secondary'"
                  :label="$t('Show All')"
                  no-caps
                  class="text-weight-600"
                  dense
                />
              </div>
            </div>
          </template>
        </div>

        <MerchantCarousel
          ref="merchantRefCarousel"
          list_type="featured"
          :featured_id="topResto"
          :filters="DataStore.filters"
          :index="0"
        />

        <div class="q-mt-sm q-mb-xs">
          <template v-if="DataStore.car_loading[1]">
            <div class="row justify-between">
              <q-skeleton type="text" style="width: 100px" />
              <q-skeleton type="text" style="width: 50px" />
            </div>
          </template>
          <template v-else>
            <div v-if="DataStore.hasCarData(1)" class="row">
              <div class="col">
                <div class="text-h5 text-weight-medium">
                  {{ $t("Recommended") }}
                </div>
              </div>
              <div class="col text-right">
                <q-btn
                  :to="{
                    name: 'feed',
                    query: { query: 'featured', featured_id: 'recommended' },
                  }"
                  flat
                  :color="$q.dark.mode ? 'grey300' : 'secondary'"
                  :label="$t('Show All')"
                  no-caps
                  class="text-weight-600"
                  dense
                />
              </div>
            </div>
          </template>
        </div>

        <MerchantCarousel
          ref="merchantRefCarousel"
          list_type="featured"
          featured_id="recommended"
          :filters="DataStore.filters"
          :index="1"
        />

        <q-space class="q-pa-xs"></q-space>

        <q-tabs
          v-model="featuredTab"
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
        >
          <q-tab
            v-for="(tabname, tabkey) in DataStore.featured_data"
            :key="tabname"
            :name="tabkey"
            :label="tabname"
            content-class="text-weight-500 "
          />
        </q-tabs>

        <q-tab-panels
          v-model="featuredTab"
          animated
          :class="{
            'bg-mydark text-white': $q.dark.mode,
            'bg-white text-black': !$q.dark.mode,
          }"
        >
          <q-tab-panel
            v-for="(tabname, tabkey) in DataStore.featured_data"
            :key="tabname"
            :name="tabkey"
            :label="tabname"
            class="q-pa-sm"
          >
            <MerchantList
              ref="merchantList"
              list_type="featured"
              :featured_id="tabkey"
              :filters="DataStore.filters"
              @after-results="afterResults"
            />
            <div class="text-center q-pa-sm">
              <template v-if="DataStore.hasFeed">
                <q-btn
                  :color="$q.dark.mode ? 'grey300' : 'secondary'"
                  :to="{
                    path: 'feed',
                    query: {
                      query: 'featured',
                      featured_id: tabkey,
                    },
                  }"
                  unelevated
                  no-caps
                  flat
                  dense
                  :label="$t('Show All')"
                ></q-btn>
              </template>
              <template v-else>
                <div
                  class="font12 q-pa-md"
                  :class="{
                    'text-grey300': $q.dark.mode,
                    'text-dark': !$q.dark.mode,
                  }"
                >
                  {{ $t("No Results") }}
                </div>
              </template>
            </div>
          </q-tab-panel>
        </q-tab-panels>
      </template>
      <template v-else>
        <NoResults @filter-again="filterAgain"></NoResults>
      </template>

      <!-- :cuisine_data="cuisineList" -->
      <MerchantFilter
        ref="merchant_filter"
        :cuisine_data="DataStore.cuisine"
        @apply-filter="applyFilter"
        @run-filter="runFilter"
      />

      <BrowseCuisine ref="browse_cuisine"> </BrowseCuisine>

      <!-- <q-space class="q-pa-xs"></q-space> -->
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { defineAsyncComponent, ref, onMounted, watch } from "vue";
import APIinterface from "src/api/APIinterface";
import { useRouter } from "vue-router";
import auth from "src/api/auth";
//import { useTransactionStore } from "stores/Transaction";
import { useDataStore } from "stores/DataStore";
import { useDeliveryschedStore } from "stores/DeliverySched";
import { usePlaceStore } from "stores/PlaceStore";

export default {
  name: "HomePage",
  components: {
    HomeBanner: defineAsyncComponent(() => import("components/HomeBanner.vue")),
    CuisineCarousel: defineAsyncComponent(() =>
      import("components/CuisineCarousel.vue")
    ),
    BrowseCuisine: defineAsyncComponent(() =>
      import("components/BrowseCuisine.vue")
    ),
    MerchantCarousel: defineAsyncComponent(() =>
      import("components/MerchantCarousel.vue")
    ),
    MerchantList: defineAsyncComponent(() =>
      import("components/MerchantList.vue")
    ),
    MerchantFilter: defineAsyncComponent(() =>
      import("components/MerchantFilter.vue")
    ),
    NoResults: defineAsyncComponent(() => import("components/NoResults.vue")),
  },
  setup() {
    const transactionType = APIinterface.getStorage("transaction_type");
    const DataStore = useDataStore();
    const filters = ref({
      transaction_type: transactionType,
    });
    const topResto = ref("popular");
    const featuredTab = ref("all");
    const featured = ref([]);
    const q = ref("");
    const slide = ref(1);
    const cuisineList = ref([]);
    const $router = useRouter();
    const hasResult = ref(true);
    const merchant_filter = ref(undefined);

    const merchantList = ref(null);
    const merchantRefCarousel = ref(null);
    const merchantRefCarousel2 = ref(null);
    const userData = ref([]);
    const isSticky = ref(false);

    const DeliveryschedStore = useDeliveryschedStore();

    watch(
      () => DeliveryschedStore.new_transaction_type,
      (newValue, oldValue) => {
        console.log("DeliveryschedStore=>" + newValue);
        DeliveryschedStore.filters.transaction_type = newValue;
        applyFilter(DeliveryschedStore.filters);
      }
    );

    watch(
      () => DeliveryschedStore.main_layout_header,
      (newValue, oldValue) => {
        isSticky.value = !newValue;
      }
    );

    const PlaceStore = usePlaceStore();
    watch(
      () => PlaceStore.data,
      (newValue, oldValue) => {
        DataStore.filters.place_id = newValue.place_id;
        applyFilter(DataStore.filters);
      }
      //{ deep: true }
    );

    watch(
      () => DataStore.list_loading_handle,
      (newValue, oldValue) => {
        if (featuredTab.value == "all") {
          if (Object.keys(DataStore.list_data).length > 0) {
            hasResult.value = true;
          } else {
            hasResult.value = false;
          }
        }
      }
    );

    onMounted(() => {
      userData.value = auth.getUser();

      if (Object.keys(DataStore.filters).length <= 0) {
        applyFilter({
          transaction_type: transactionType,
        });
      }

      if (Object.keys(DataStore.featured_data).length <= 0) {
        DataStore.getFeaturedList();
      }
    });

    const afterGetdata = (data) => {
      cuisineList.value = data;
    };

    const applyFilter = (data) => {
      hasResult.value = true;
      DataStore.filters = data;
    };

    const goSearch = () => {
      $router.push("/search");
    };

    const runFilter = () => {
      console.debug("runFilter");
    };

    const getFeaturedList = () => {
      APIinterface.getFeaturedList()
        .then((data) => {
          featured.value = data.details.data;
        })
        // eslint-disable-next-line
        .catch((error) => {})
        .then((data) => {});
    };

    const afterResults = (data) => {
      console.log("afterResults =>" + data);
      hasResult.value = data;
    };

    const filterAgain = () => {
      console.log("filterAgain");
      merchant_filter.value.filter = true;
    };

    const refresh = (done) => {
      DataStore.getBanner();
      DataStore.CuisineList();
      DataStore.getFeaturedList();

      applyFilter({
        transaction_type: transactionType,
      });

      setTimeout(() => {
        done();
      }, 1000);
    };

    return {
      filters,
      topResto,
      q,
      slide,
      cuisineList,
      afterGetdata,
      applyFilter,
      goSearch,
      runFilter,
      merchantList,
      merchantRefCarousel,
      merchantRefCarousel2,
      featuredTab,
      featured,
      afterResults,
      hasResult,
      DataStore,
      userData,
      filterAgain,
      merchant_filter,
      refresh,
      isSticky,
      PlaceStore,
    };
  },
};
</script>
