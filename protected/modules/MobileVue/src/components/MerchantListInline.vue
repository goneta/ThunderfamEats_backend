<template>
  <q-item-section>
    <div class="relative-position">
      <q-img
        :src="items.url_logo"
        lazy
        fit="cover"
        style="height: 100px"
        class="radius8"
        spinner-color="amber"
        spinner-size="sm"
      />

      <div v-if="promos[items.merchant_id]" class="absolute-top-left">
        <div v-for="promo in promos[items.merchant_id]" :key="promo">
          <q-chip
            dense
            :color="promo.discount_type == 'voucher' ? 'light-green' : 'purple'"
            text-color="white"
            class="font11 text-weight-600 ellipsis"
            >{{ promo.discount_name }}</q-chip
          >
        </div>

        <q-chip
          v-if="items.open_status == 0"
          dense
          color="red"
          text-color="white"
          class="font11 text-weight-600 ellipsis"
          >{{ $t("Closed") }}</q-chip
        >
      </div>
      <div v-else class="absolute-top-left">
        <q-chip
          v-if="items.open_status == 0"
          dense
          color="red"
          text-color="white"
          class="font11 text-weight-600 ellipsis"
          >{{ $t("Closed") }}</q-chip
        >
      </div>
    </div>
    <q-item-label>
      <div class="row no-wrap items-center q-pt-sm">
        <div class="col font13 text-weight-bold no-margin line-normal">
          <div class="ellipsis fit">
            {{ items.restaurant_name }}
          </div>
        </div>
        <div class="col-3 text-right">
          <FavsResto
            ref="favs"
            :data="items"
            :active="items.saved_store == 1 ? true : false"
            :merchant_id="items.merchant_id"
            size="7px"
            @after-savefav="afterSavefav"
          />
        </div>
      </div>

      <div class="row no-wrap items-center">
        <div class="col font12 text-grey ellipsis text-weight-light">
          <template
            v-for="cuisine_index in items.cuisine_group"
            :key="cuisine_index"
          >
            <template v-if="cuisine[cuisine_index]"
              >{{ cuisine[cuisine_index].name }},
            </template>
          </template>
        </div>
        <div class="col-4 text-right">
          <template v-if="estimation[items.merchant_id]">
            <template v-if="services[items.merchant_id]">
              <template v-for="service_name in services[items.merchant_id]">
                <!-- eslint-disable-next-line -->
                <q-chip
                  v-if="service_name == 'delivery'"
                  size="sm"
                  :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                  :text-color="$q.dark.mode ? 'grey300' : 'secondary'"
                  icon="fiber_manual_record"
                  class="q-ma-none transparent q-pa-none"
                >
                  <span
                    :class="{
                      'text-grey300': $q.dark.mode,
                      'text-primary': !$q.dark.mode,
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
        </div>
      </div>

      <div class="row no-wrap items-center">
        <div class="col font13 text-weight-bold no-margin line-normal">
          <q-chip
            size="sm"
            color="secondary"
            :text-color="$q.dark.mode ? 'grey300' : 'secondary'"
            icon="star"
            class="no-padding q-ma-none transparent"
          >
            <span
              v-if="reviews[items.merchant_id]"
              class="text-weight-medium"
              :class="{
                'text-grey300': $q.dark.mode,
                'text-dark': !$q.dark.mode,
              }"
            >
              {{ reviews[items.merchant_id].ratings }}
            </span>
            <span
              v-else
              class="text-weight-medium"
              :class="{
                'text-grey300': $q.dark.mode,
                'text-dark': !$q.dark.mode,
              }"
            >
              0.0
            </span>
          </q-chip>
        </div>
        <div class="col-4 text-right font11 text-grey">
          <q-chip
            size="sm"
            :color="$q.dark.mode ? 'grey600' : 'mygrey'"
            :text-color="$q.dark.mode ? 'grey300' : 'mygrey'"
            icon="fiber_manual_record"
            class="q-ma-none transparent q-pa-none"
          >
            <span
              :class="{
                'text-grey300': $q.dark.mode,
                'text-dark': !$q.dark.mode,
              }"
            >
              {{ items.distance_pretty }}
            </span>
          </q-chip>
        </div>
      </div>
    </q-item-label>
  </q-item-section>
</template>

<script>
import { defineAsyncComponent } from "vue";
export default {
  name: "MerchantListInline",
  components: {
    FavsResto: defineAsyncComponent(() => import("components/FavsResto.vue")),
  },
  props: [
    "items",
    "cuisine",
    "reviews",
    "estimation",
    "services",
    "items_min_max",
    "promos",
  ],
  methods: {
    afterSavefav(data, added) {
      data.saved_store = added;
    },
  },
};
</script>
