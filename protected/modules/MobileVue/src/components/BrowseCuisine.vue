<template>
  <q-dialog v-model="modal" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("All Cuisine") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="modal = !true"
          square
          unelevated
          :color="$q.dark.mode ? 'grey600' : 'white'"
          :text-color="$q.dark.mode ? 'grey300' : 'grey'"
          icon="las la-times"
          dense
          no-caps
          size="sm"
          class="border-grey radius8"
        />
      </q-toolbar>
      <q-card-section class="q-pt-none q-pl-md">
        <div class="row q-gutter-md justify-center q-pa-none">
          <div
            v-for="(items, key) in DataStore.cuisine"
            :key="items"
            class="col-5"
          >
            <q-btn
              :color="
                key <= 0 ? 'primary' : $q.dark.mode ? 'grey600' : 'mygrey'
              "
              unelevated
              no-caps
              :text-color="
                key <= 0 ? 'white' : $q.dark.mode ? 'grey300' : 'dark'
              "
              class="radius8 full-width row items-center"
              size="lg"
              :to="{
                name: 'feed',
                query: {
                  query: 'all',
                  cuisine_id: items.cuisine_id,
                  cuisine_name: items.cuisine_name,
                },
              }"
            >
              <div class="row items-center no-wrap full-width">
                <div class="col-12">
                  <q-avatar size="30px">
                    <img :src="items.url_icon" />
                  </q-avatar>
                  <div class="ellipsis text-center font11 q-pl-sm line-normal">
                    {{ items.cuisine_name }}
                  </div>
                </div>
              </div>
            </q-btn>
          </div>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import { useDataStore } from "stores/DataStore";
export default {
  name: "BrowseCuisine",
  data() {
    return {
      modal: false,
    };
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  mounted() {
    if (!this.DataStore.hasDataCuisine()) {
      //this.DataStore.CuisineList();
    }
  },
};
</script>
