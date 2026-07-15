<template>
  <q-page padding class="q-pl-md q-pr-md">
    <q-input
      v-model="q"
      label="Search food and restaurants"
      outlined
      lazy-rules
      bg-color="input"
      label-color="grey"
      borderless
      readonly
      class="input-borderless"
    >
      <template v-slot:prepend>
        <q-icon name="eva-search-outline" size="sm" />
      </template>
    </q-input>

    <q-space class="q-pa-xs"></q-space>

    <div class="font13 text-weight-bold text-h5">
      {{ $t("Recently Search") }}
    </div>

    <q-btn-toggle
      v-model="recently_search"
      toggle-color="secondary"
      color="mygrey"
      text-color="dark"
      no-caps
      no-wrap
      unelevated
      class="rounded-group2 text-weight-bold line-1"
      :options="[
        { label: 'One', value: 'one' },
        { label: 'Two', value: 'two' },
        { label: 'Three', value: 'three' },
      ]"
    />

    <div class="font13 text-weight-bold text-h5">
      {{ $t("Popular Cuisines") }}
    </div>

    <q-btn-toggle
      v-model="cuisine"
      toggle-color="secondary"
      color="mygrey"
      text-color="dark"
      no-caps
      no-wrap
      unelevated
      class="rounded-group2 text-weight-bold line-1"
      :options="[
        { label: 'One', value: 'one' },
        { label: 'Two', value: 'two' },
        { label: 'Three', value: 'three' },
      ]"
    />

    <div class="font13 text-weight-bold text-h5">
      {{ $t("Popular Items Near You") }}
    </div>
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { defineAsyncComponent } from "vue";

export default {
  name: "BrowsePage",
  components: {
    // MerchantCarousel: defineAsyncComponent(() =>
    //   import("components/MerchantCarousel.vue")
    // ),
    // CuisineCarousel: defineAsyncComponent(() =>
    //   import("components/CuisineCarousel.vue")
    // ),
  },
  mounted() {
    //this.getFeaturedList();
  },
  data() {
    return {
      featured: [],
      recently_search: "",
      cuisine: "",
      // featured: {
      //   new: 'New Restaurant',
      //   popular: 'Popular',
      //   best_seller: 'Best Seller',
      //   recommended: 'Recommended'
      // }
    };
  },
  methods: {
    getFeaturedList() {
      APIinterface.getFeaturedList()
        .then((data) => {
          this.featured = data.details.data;
        })
        // eslint-disable-next-line
        .catch((error) => {})
        .then((data) => {});
    },
  },
};
</script>
