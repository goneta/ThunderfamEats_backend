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
        <q-toolbar-title class="text-weight-bold"></q-toolbar-title>
      </q-toolbar>
    </q-header>
    <q-page
      class="q-pl-md q-pr-md"
      :class="{
        'flex flex-center': !DataStore.hasPage && !DataStore.loading_page,
      }"
    >
      <template v-if="!DataStore.loading_page">
        <div class="text-h5 text-weight-bold">
          {{ DataStore.page_data.title }}
        </div>
        <div v-html="DataStore.page_data.long_content"></div>
      </template>
      <template v-if="!DataStore.hasPage && !DataStore.loading_page">
        <div class="text-center full-width">
          <div class="text-h5 text-weight-bold">{{ $t("No results") }}</div>
          <p class="text-grey font12">
            {{ $t("This page is not available please come back later.") }}
          </p>
        </div>
      </template>
      <q-inner-loading
        :showing="DataStore.loading_page"
        color="primary"
        size="md"
      />
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { useDataStore } from "stores/DataStore";
export default {
  name: "PageRender",
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  created() {
    this.DataStore.loading_page = true;
    this.DataStore.getPage(null, this.$route.params.page_id);
  },
  methods: {
    refresh(done) {
      this.DataStore.getPage(done, this.$route.params.page_id);
    },
  },
};
</script>
