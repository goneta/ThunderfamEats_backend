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
        {{ $t("You don't have any save stores here!") }}
      </div>
      <p class="text-grey font12">{{ $t("Let's change that!") }}</p>
    </div>
  </template>

  <div v-else class="row items-center q-col-gutter-sm q-mb-sm">
    <div
      class="col-6 cursor-pointer"
      v-for="items in data"
      :key="items.merchant_id"
      @click.stop="
        this.$router.push({
          name: 'menu',
          params: {
            slug: items.restaurant_slug,
          },
        })
      "
    >
      <q-card flat class="radius8 q-pa-sm rounded-borders border-greyx">
        <q-img
          :src="items.url_logo"
          style="height: 100px"
          lazy
          fit="cover"
          class="radius8 q-mb-sm"
          :class="{ 'light-dimmed': items.saved_store === false }"
        />
        <q-list dense class="qlist-small">
          <q-item>
            <q-item-section>
              <q-item-label
                lines="1"
                :class="{ 'text-grey': items.saved_store === false }"
              >
                {{ items.restaurant_name }}
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <FavsResto
                ref="favs"
                :data="items"
                :active="items.saved_store"
                :merchant_id="items.merchant_id"
                :layout="2"
                size="7px"
                @after-savefav="afterSavefav"
              />
            </q-item-section>
          </q-item>
          <q-item>
            <q-item-section v-if="items.cuisine_name">
              <q-item-label
                caption
                lines="1"
                class="font11"
                :class="{ 'text-grey': items.saved_store === false }"
              >
                <template v-for="cuisine in items.cuisine_name" :key="cuisine">
                  <span class="q-mr-xs">{{ cuisine.cuisine_name }},</span>
                </template>
              </q-item-label>
            </q-item-section>
          </q-item>
          <q-item>
            <q-item-section>
              <q-item-label>
                <q-chip
                  size="sm"
                  color="white"
                  :text-color="
                    items.saved_store === false ? 'grey' : 'secondary'
                  "
                  icon="star"
                  class="no-padding transparent"
                >
                  <span
                    class="text-weight-medium text-dark"
                    :class="{
                      'text-grey': items.saved_store === false,
                      'text-white': $q.dark.mode,
                    }"
                    >{{ items.ratings.rating }}</span
                  >
                </q-chip>
              </q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-item-label>
                <template v-if="estimation[items.merchant_id]">
                  <template v-if="services[items.merchant_id]">
                    <template
                      v-for="(service_name, index_service) in services[
                        items.merchant_id
                      ]"
                    >
                      <template v-if="index_service <= 0">
                        <!-- eslint-disable-next-line -->
                        <q-chip
                          v-if="service_name == 'delivery'"
                          size="sm"
                          icon="fiber_manual_record"
                          class="no-padding transparent"
                          :text-color="
                            items.saved_store === false ? 'grey' : 'mygrey'
                          "
                        >
                          <span
                            class="text-weight-medium"
                            :class="{
                              'text-grey300': $q.dark.mode,
                              'text-dark': !$q.dark.mode,
                            }"
                          >
                            <template
                              v-if="
                                estimation[items.merchant_id][service_name][
                                  items.charge_type
                                ]
                              "
                            >
                              {{
                                estimation[items.merchant_id][service_name][
                                  items.charge_type
                                ].estimation
                              }}
                              {{ $t("min") }}
                            </template>
                          </span>
                        </q-chip>
                      </template>
                    </template>
                  </template>
                </template>
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
      </q-card>
    </div>
  </div>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";

export default {
  name: "FavouriteRestuarant",
  props: ["is_done"],
  data() {
    return {
      loading: true,
      data: [],
      estimation: [],
      services: [],
    };
  },
  components: {
    FavsResto: defineAsyncComponent(() => import("components/FavsResto.vue")),
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
  },
  mounted() {
    this.saveStoreList();
  },
  watch: {
    is_done(newval, oldval) {
      this.saveStoreList();
    },
  },
  methods: {
    saveStoreList() {
      if (APIinterface.empty(this.is_done)) {
        this.loading = true;
      }
      APIinterface.saveStoreList()
        .then((data) => {
          this.data = data.details.data;
          this.estimation = data.details.estimation;
          this.services = data.details.services;
        })
        .catch((error) => {
          this.data = [];
          this.estimation = [];
          this.services = [];
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(this.is_done)) {
            this.is_done();
          }
        });
    },
    afterSavefav(data, added) {
      data.saved_store = added;
    },
  },
};
</script>
