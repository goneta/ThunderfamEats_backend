<template>
  <template v-if="DataStore.banner_loading">
    <q-skeleton height="130px" />
  </template>
  <template v-else>
    <template v-if="hasData">
      <swiper :slidesPerView="1" :spaceBetween="0" :loop="true" class="q-mb-md">
        <swiper-slide
          v-for="(items, index) in DataStore.banner"
          :key="items.banner_id"
          :name="index"
          style="height: 130px"
        >
          <q-img
            :src="items.image"
            class="fit radius10 cursor-pointer"
            fit="cover"
            loading="lazy"
            spinner-color="primary"
            spinner-size="md"
            @click="showBanner(items)"
          />
        </swiper-slide>
      </swiper>
    </template>
  </template>

  <ItemDetails
    ref="item_details"
    :slug="restaurant_slug"
    :money_config="DataStore.money_config"
    :currency_code="DataStorePersisted.use_currency_code"
    @after-additems="afterAdditems"
  />
</template>

<script>
import { defineAsyncComponent } from "vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";
import { useCartStore } from "stores/CartStore";

export default {
  name: "HomeBanner",
  components: {
    Swiper,
    SwiperSlide,
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
  },
  data() {
    return {
      loading: false,
      slide: 1,
      data: [],
      test: [],
      restaurant_slug: "",
      payload: [
        "items",
        "subtotal",
        "distance_local",
        "items_count",
        "merchant_info",
        "check_opening",
        "transaction_info",
      ],
    };
  },
  setup() {
    const DataStore = useDataStore();
    const DataStorePersisted = useDataStorePersisted();
    const CartStore = useCartStore();
    return { DataStore, DataStorePersisted, CartStore };
  },
  mounted() {
    if (Object.keys(this.DataStore.banner).length <= 0) {
      this.DataStore.getBanner();
    }
  },
  computed: {
    hasData() {
      if (this.DataStore.banner.length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    afterAdditems() {
      console.log("afterAdditems");
      APIinterface.setStorage("merchant_slug", this.restaurant_slug);
      this.CartStore.getCart(true, this.payload);
    },
    showBanner(data) {
      switch (data.banner_type) {
        case "restaurant":
          let slug = !APIinterface.empty(
            this.DataStore.merchant_list[data.merchant_id]
          )
            ? this.DataStore.merchant_list[data.merchant_id].restaurant_slug
            : "";

          if (!APIinterface.empty(slug)) {
            this.$router.push({ name: "menu", params: { slug: slug } });
          }
          break;

        case "food":
          console.log(data.item_id);
          let items = !APIinterface.empty(
            this.DataStore.food_list[data.item_id]
          )
            ? this.DataStore.food_list[data.item_id]
            : "";

          if (Object.keys(items).length > 0) {
            this.restaurant_slug = items.restaurant_slug;
            const params = {
              cat_id: parseInt(items.cat_id),
              item_uuid: items.item_uuid,
            };
            this.$refs.item_details.showItem2(params, this.restaurant_slug);
          }
          break;

        case "restaurant_featured":
          let featured = data.featured;
          if (!APIinterface.empty(featured)) {
            this.$router.push({
              name: "feed",
              query: { query: "featured", featured_id: featured },
            });
          }
          break;
        case "cuisine":
          let cuisine_name = !APIinterface.empty(
            this.DataStore.cuisine_list[data.cuisine_id]
          )
            ? this.DataStore.cuisine_list[data.cuisine_id]
            : "";

          this.$router.push({
            name: "feed",
            query: {
              query: "all",
              cuisine_id: data.cuisine_id,
              cuisine_name: cuisine_name,
            },
          });
          break;
      }
    },
  },
};
</script>
