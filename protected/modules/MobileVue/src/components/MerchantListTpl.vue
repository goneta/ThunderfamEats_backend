<template>
  <q-item-section side>
    <div class="relative-position">
      <q-img
        :src="items.url_logo"
        lazy
        fit="cover"
        style="height: 80px; width: 80px"
        class="radius8"
        spinner-color="amber"
        spinner-size="sm"
      />
      <template v-if="items.open_status == 0">
        <div class="absolute-top fit dimmed"></div>
        <div
          class="absolute-top fit z-top text-weight-medium flex flex-center text-white font11"
        >
          {{ $t("Closed") }}
        </div>
      </template>
    </div>
  </q-item-section>
  <q-item-section>
    <div class="row items-center justify-between">
      <div class="col">
        <div
          v-if="promos[items.merchant_id]"
          class="font8 text-primary text-weight-bold"
        >
          {{ $t("PROMO") }}
        </div>
        <div class="font13 text-weight-bold no-margin line-normal">
          {{ items.restaurant_name }}
        </div>
      </div>
      <div class="">
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
    <!-- row -->

    <div class="row items-center justify-between">
      <div class="col">
        <div class="text-grey ellipsis-2-lines font12 line-normal">
          <template
            v-for="cuisine_index in items.cuisine_group"
            :key="cuisine_index"
          >
            <template v-if="cuisine[cuisine_index]"
              >{{ cuisine[cuisine_index].name }},
            </template>
          </template>
        </div>

        <template v-if="promos[items.merchant_id]">
          <q-chip
            v-for="promo in promos[items.merchant_id]"
            :key="promo"
            dense
            :color="promo.discount_type == 'voucher' ? 'lightprimary' : 'green'"
            :text-color="promo.discount_type == 'voucher' ? 'dark' : 'white'"
            class="font8 ellipsis q-ma-none"
            >{{ promo.discount_name }}</q-chip
          >
        </template>
      </div>
      <div class="col-5 text-right">
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
    <!-- row -->

    <div class="row items-center justify-between">
      <div class="col">
        <q-chip
          size="sm"
          color="secondary"
          :text-color="$q.dark.mode ? 'grey300' : 'secondary'"
          icon="star"
          class="no-padding transparent"
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
      <div class="col text-right font11 text-grey">
        <span
          :class="{
            'text-grey300': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ items.distance_pretty }}
        </span>
      </div>
    </div>
  </q-item-section>
</template>

<script>
import { defineAsyncComponent } from "vue";
export default {
  name: "MerchantListTpl",
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
