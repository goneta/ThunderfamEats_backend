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
          $t("Favourites")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page class="q-pl-md q-pr-md">
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
      >
        <q-tab
          name="restaurant"
          :label="$t('Restaurants')"
          no-caps
          content-class="text-weight-500 "
        />
        <q-tab
          name="food"
          :label="$t('Items')"
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
        <q-tab-panel name="restaurant" class="q-pl-none q-pr-none">
          <FavouriteRestuarant ref="fav_resto" :is_done="is_done" />
        </q-tab-panel>
        <q-tab-panel name="food" class="q-pl-none q-pr-none">
          <FavouriteItems ref="fav_items" :is_done="is_done" />
        </q-tab-panel>
      </q-tab-panels>
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { defineAsyncComponent } from "vue";

export default {
  name: "FavouritesPage",
  data() {
    return {
      tab: "restaurant",
      is_done: undefined,
    };
  },
  components: {
    FavouriteRestuarant: defineAsyncComponent(() =>
      import("components/FavouriteRestuarant.vue")
    ),
    FavouriteItems: defineAsyncComponent(() =>
      import("components/FavouriteItems.vue")
    ),
  },
  methods: {
    refresh(done) {
      this.is_done = done;
    },
  },
};
</script>
