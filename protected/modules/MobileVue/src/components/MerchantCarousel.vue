<template>
  <div v-if="DataStore.car_loading[index]" class="row q-gutter-sm items-center">
    <div v-for="i in 3" :key="i" class="col">
      <q-skeleton height="90px" class="full-width" />
      <q-skeleton type="text" class="w-75" />
      <q-skeleton type="text" class="w-100" />
    </div>
  </div>
  <template v-else>
    <swiper
      :slidesPerView="2.3"
      :spaceBetween="10"
      :loop="true"
      @swiper="onSwiper"
    >
      <swiper-slide
        v-for="items in DataStore.car_data[index]"
        :key="items"
        class="row"
      >
        <div class="fit">
          <router-link
            :to="{ name: 'menu', params: { slug: items.restaurant_slug } }"
          >
            <div
              class="bg-grey-2 radius8 relative-position"
              style="height: 90px"
              :style="`background-image: url(${items.url_logo}) !important;background-size:cover!important;min-height:100%;`"
            >
              <div
                v-if="items.open_status == 0"
                class="absolute-top fit light-dimmed"
              ></div>
            </div>
          </router-link>

          <div class="text-left">
            <div class="ellipsis font13 text-weight-bold q-pt-xs">
              {{ items.restaurant_name }}
            </div>
            <div class="text-grey font12 line-1 ellipsis">
              <template
                v-for="cuisine_index in items.cuisine_group"
                :key="cuisine_index"
              >
                <template v-if="cuisine[cuisine_index]"
                  >{{ cuisine[cuisine_index].name }},
                </template>
              </template>
            </div>
            <q-chip
              size="sm"
              :text-color="$q.dark.mode ? 'grey300' : 'secondary'"
              icon="star"
              class="no-padding transparent"
            >
              <span
                v-if="DataStore.car_reviews[index][items.merchant_id]"
                class="text-weight-medium"
                :class="{
                  'text-white': $q.dark.mode,
                  'text-dark': !$q.dark.mode,
                }"
              >
                {{ DataStore.car_reviews[index][items.merchant_id].ratings }}
              </span>
              <span
                v-else
                class="text-weight-medium text-dark"
                :class="{
                  'text-white': $q.dark.mode,
                  'text-dark': !$q.dark.mode,
                }"
                >0</span
              >
            </q-chip>

            <q-chip
              size="sm"
              :text-color="$q.dark.mode ? 'grey300' : 'secondary'"
              icon="fiber_manual_record"
              class="no-padding transparent"
            >
              <span
                class="text-weight-medium"
                :class="{
                  'text-white': $q.dark.mode,
                  'text-dark': !$q.dark.mode,
                }"
              >
                <template
                  v-if="
                    DataStore.car_estimation[index][items.merchant_id] &&
                    DataStore.car_services[index][items.merchant_id]
                  "
                >
                  <template
                    v-for="(service_name, index_service) in DataStore
                      .car_services[index][items.merchant_id]"
                  >
                    <template v-if="index_service <= 0">
                      <template
                        v-if="
                          DataStore.car_estimation[index][items.merchant_id][
                            service_name
                          ][items.charge_type]
                        "
                      >
                        {{
                          DataStore.car_estimation[index][items.merchant_id][
                            service_name
                          ][items.charge_type].estimation
                        }}
                        {{ $t("min") }}
                      </template>
                    </template>
                  </template>
                </template>
              </span>
            </q-chip>
          </div>
        </div>
        <!-- fit -->
      </swiper-slide>
    </swiper>
  </template>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";

export default {
  props: ["list_type", "featured_id", "filters", "index"],
  name: "MerchantCarousel",
  components: {
    Swiper,
    SwiperSlide,
  },
  data() {
    return {
      loading: true,
      slide: 0,
      data: [],
      cuisine: [],
      reviews: [],
      estimation: [],
      services: [],
      items_min_max: [],
    };
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  mounted() {
    if (APIinterface.empty(this.DataStore.car_data[this.index])) {
      this.getData(this.index);
    } else {
      if (Object.keys(this.DataStore.car_data[this.index]).length <= 0) {
        this.getData(this.index);
      }
    }
  },
  watch: {
    // filters(newval, oldval) {
    //   console.log("carousel new filters");
    //   this.getData(this.index);
    // },
    filters: {
      handler(newval, oldval) {
        console.log("carousel new filters");
        this.getData(this.index);
      },
      deep: true,
    },
  },
  methods: {
    getData(index) {
      const params = {
        list_type: this.list_type,
        featured_id: this.featured_id,
        place_id: APIinterface.getStorage("place_id"),
        rows: 0,
        payload: [
          "cuisine",
          "reviews",
          "estimation",
          "services",
          "items_min_max",
        ],
        filters: this.filters,
      };
      this.DataStore.getCarouselData(params, index);
    },
    getFeaturedMerchant() {
      const $params = {
        list_type: this.list_type,
        featured_id: this.featured_id,
        place_id: APIinterface.getStorage("place_id"),
        rows: 0,
        payload: [
          "cuisine",
          "reviews",
          "estimation",
          "services",
          "items_min_max",
        ],
        filters: this.filters,
      };
      this.loading = true;
      APIinterface.getMerchantFeed($params)
        .then((data) => {
          this.data = data.details.data;
          this.cuisine = data.details.cuisine;
          this.reviews = data.details.reviews;
          this.estimation = data.details.estimation;
          this.services = data.details.services;
          this.items_min_max = data.details.items_min_max;
        })
        // eslint-disable-next-line
        .catch((error) => {
          this.data = [];
          this.cuisine = [];
          this.reviews = [];
          this.estimation = [];
          this.services = [];
          this.items_min_max = [];
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
