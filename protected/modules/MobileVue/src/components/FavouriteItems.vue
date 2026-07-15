<template>
  <div v-if="loading">
    <div class="fit q-pa-xl">
      <q-inner-loading
        :showing="true"
        color="primary"
        size="md"
        label-class="dark"
      />
    </div>
  </div>

  <template v-else-if="!loading && !hasData">
    <div class="flex flex-center q-pt-xl q-pb-xl">
      <q-img
        src="cuttery.png"
        fit="fill"
        spinner-color="primary"
        style="height: 160px; max-width: 150px"
      />
      <div class="text-h5 text-weight-bold line-normal">
        {{ $t("You don't have any save items here!") }}
      </div>
      <p class="text-grey font12">{{ $t("Let's change that!") }}</p>
    </div>
  </template>

  <div v-else class="row items-center">
    <div v-for="items in data" :key="items.item_id" class="col-6">
      <div class="border-grey q-ma-xs radius8">
        <template v-if="data_items[items.item_id].price[0]">
          <div class="relative-position" style="height: 100px">
            <q-img
              :src="data_items[items.item_id].url_image"
              lazy
              fit="contain"
              placeholder-src="placeholder.png"
              :class="{ 'light-dimmed': items.save_item === false }"
              class="fit"
            />
          </div>
          <div class="q-pa-sm">
            <div
              class="font13 text-weight-medium no-margin line-normal ellipsis"
            >
              {{ data_items[items.item_id].item_name }}
            </div>

            <div class="row items-center justify-between">
              <div
                class="font12 text-weight-medium"
                :class="{
                  'text-grey300': $q.dark.mode,
                  'text-grey-7': !$q.dark.mode,
                }"
              >
                <template v-if="data_items[items.item_id].price[0]">
                  <template
                    v-if="data_items[items.item_id].price[0].discount <= 0"
                  >
                    {{ data_items[items.item_id].price[0].size_name }}
                    {{ data_items[items.item_id].price[0].pretty_price }}
                  </template>
                  <template v-else>
                    {{ data_items[items.item_id].price[0].size_name }}
                    <del>{{
                      data_items[items.item_id].price[0].pretty_price
                    }}</del>
                    {{
                      data_items[items.item_id].price[0]
                        .pretty_price_after_discount
                    }}
                  </template>
                </template>
              </div>
              <FavsItem
                ref="favs"
                :data="items"
                :layout="3"
                :item_token="data_items[items.item_id].item_uuid"
                :cat_id="items.cat_id"
                :active="items.save_item"
                @after-savefav="afterSavefav"
              />
            </div>

            <q-btn
              :label="$t('Add to cart')"
              no-caps
              dense
              class="fit q-mt-sm"
              unelevated
              :color="items.save_item ? 'primary' : 'mygrey'"
              :text-color="items.save_item ? 'white' : 'grey'"
            ></q-btn>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";

export default {
  name: "FavouriteItems",
  props: ["is_done"],
  components: {
    FavsItem: defineAsyncComponent(() => import("components/FavsItem.vue")),
  },
  data() {
    return {
      data: [],
      data_items: [],
      loading: true,
    };
  },
  mounted() {
    this.getSaveItems();
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
  },
  watch: {
    is_done(newval, oldval) {
      this.getSaveItems();
    },
  },
  methods: {
    getSaveItems() {
      if (APIinterface.empty(this.is_done)) {
        this.loading = true;
      }
      APIinterface.getSaveItems()
        .then((data) => {
          this.data = data.details.data;
          this.data_items = data.details.items;
        })
        .catch((error) => {
          this.data = [];
          this.data_items = [];
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(this.is_done)) {
            this.is_done();
          }
        });
    },
    afterSavefav(data, found) {
      data.save_item = found;
    },
  },
};
</script>
