<template>
  <MerchantListSkeleton v-if="DataStore.list_loading" />

  <q-list v-else class="qlist-no-padding">
    <q-item
      v-for="items in DataStore.list_data"
      :key="items.merchant_id"
      clickable
      v-ripple
      :to="{ name: 'menu', params: { slug: items.restaurant_slug } }"
    >
      <MerchantListTpl
        :items="items"
        :cuisine="DataStore.list_cuisine"
        :reviews="DataStore.list_reviews"
        :estimation="DataStore.list_estimation"
        :services="DataStore.list_services"
        :promos="DataStore.promos"
      />
    </q-item>
  </q-list>

  <q-dialog v-model="alert">
    <q-card>
      <q-card-section class="text-center">
        <q-img
          src="bankrupt.png"
          style="height: 80px; max-width: 80px"
          class="q-mb-sm light-dimmed"
        />
        <p class="text-grey">
          {{
            $t(
              "We're sorry. We were not able to find a match with your filters."
            )
          }}
        </p>
      </q-card-section>

      <q-card-actions align="right">
        <q-btn unelevated label="OK" color="primary" v-close-popup />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { defineAsyncComponent } from "vue";
import { useDataStore } from "stores/DataStore";

export default {
  name: "MerchantList",
  props: ["list_type", "filters", "featured_id"],
  components: {
    MerchantListTpl: defineAsyncComponent(() =>
      import("components/MerchantListTpl.vue")
    ),
    MerchantListSkeleton: defineAsyncComponent(() =>
      import("components/MerchantListSkeleton.vue")
    ),
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  data() {
    return {
      data: [],
      cuisine: [],
      reviews: [],
      estimation: [],
      services: [],
      alert: false,
      loading: false,
      promos: [],
    };
  },
  watch: {
    // filters(newval, oldval) {
    //   console.log("merchant list new filters");
    //   this.getData();
    // },
    filters: {
      handler(newval, oldval) {
        console.log("merchant list new filters");
        this.getData();
      },
      deep: true,
    },
  },
  mounted() {
    if (Object.keys(this.DataStore.list_data).length <= 0) {
      this.DataStore.list_featured_id = this.featured_id;
      this.getData();
    } else {
      if (this.featured_id === this.DataStore.list_featured_id) {
      } else {
        this.DataStore.list_featured_id = this.featured_id;
        this.getData();
      }
    }
  },
  methods: {
    getData() {
      const $params = {
        featured_id: this.featured_id,
        list_type: this.list_type,
        place_id: APIinterface.getStorage("place_id"),
        payload: ["cuisine", "reviews", "estimation", "services", "promo"],
        filters: this.filters,
      };
      this.DataStore.getMerchantFeed($params);
    },
    // getMerchantFeed() {
    //   console.debug("getMerchantFeed");
    //   const $params = {
    //     featured_id: this.featured_id,
    //     list_type: this.list_type,
    //     place_id: APIinterface.getStorage("place_id"),
    //     payload: ["cuisine", "reviews", "estimation", "services", "promo"],
    //     filters: this.filters,
    //   };
    //   this.loading = true;
    //   APIinterface.getMerchantFeed($params)
    //     .then((data) => {
    //       this.data = data.details.data;
    //       this.cuisine = data.details.cuisine;
    //       this.reviews = data.details.reviews;
    //       this.estimation = data.details.estimation;
    //       this.services = data.details.services;
    //       this.promos = data.details.promos;
    //       this.$emit("afterResults", true);
    //     })
    //     // eslint-disable-next-line
    //     .catch((error) => {
    //       //this.alert = true;
    //       this.$emit("afterResults", false);
    //     })
    //     .then((data) => {
    //       this.loading = false;
    //     });
    // },
  },
};
</script>
