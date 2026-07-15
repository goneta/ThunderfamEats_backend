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
          flat
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
          to="/home"
          replace="true"
        />
        <q-toolbar-title class="text-weight-bold"
          >{{ $t("Your Order Successfully") }}!</q-toolbar-title
        >
      </q-toolbar>
    </q-header>
    <q-page
      padding
      class="q-pl-md q-pr-md"
      :class="{ 'flex flex-center': !hasData && !loading }"
    >
      <template v-if="loading">
        <q-inner-loading
          :showing="true"
          color="primary"
          size="md"
          label-class="dark"
          class="transparent"
        />
      </template>
      <template v-else-if="!loading && !hasData">
        <div class="text-center full-width">
          <div class="text-h5 text-weight-bold">
            {{ $t("No results data") }}
          </div>
          <p class="text-grey font12">
            {{ $t("Sorry we cannot find what your looking for.") }}
          </p>
        </div>
      </template>
      <template v-else>
        <div class="text-center q-mb-md">
          <div class="text-grey text-weight-600">
            {{ estimatedLabel(order_info.service_code) }}
          </div>
          <div
            class="text-weight-bold text-h5"
            :class="{
              'text-white': $q.dark.mode,
              'text-dark': !$q.dark.mode,
            }"
          >
            <template v-if="order_info.whento_deliver == 'now'">
              <template v-if="estimation[order_info.service_code]">
                <template
                  v-if="estimation[order_info.service_code][charge_type]"
                >
                  {{
                    estimation[order_info.service_code][charge_type].estimation
                  }}
                  {{ $t("mins") }}
                </template>
              </template>
            </template>
            <template v-else>
              {{ order_info.delivery_date }} {{ order_info.delivery_time }}
            </template>
          </div>
          <q-space class="q-pa-sm"></q-space>
          <q-img
            src="onboarding-3.png"
            spinner-color="primary"
            style="max-width: 100%; height: 150px"
            fit="contain"
          />
        </div>
        <div class="border-grey-top"></div>

        <div class="text-h5 text-weight-bold">{{ $t("Order Details") }}</div>
        <div class="row items-start justify-between">
          <div class="text-grey">{{ $t("Order #") }}</div>
          <div class="text-weight-bold">{{ order_info.order_id }}</div>
        </div>
        <div class="row items-start justify-between">
          <div class="text-grey">{{ $t("Order from") }}</div>
          <div class="text-weight-bold">{{ merchant.restaurant_name }}</div>
        </div>
        <div class="row items-start justify-between">
          <div class="text-grey col-2">{{ $t("Delivery") }}</div>
          <div class="col-8 text-right">
            {{ order_info.delivery_address }}
          </div>
        </div>
        <div class="row items-start justify-between">
          <div class="text-grey col text-weight-bold">{{ $t("Total") }}</div>
          <div class="col text-right text-weight-bold">
            {{ order_info.total_from_used_currency_to_based_currency_pretty }}
          </div>
        </div>

        <q-space class="q-pa-sm"></q-space>

        <q-list class="qlist-no-padding border-grey-top border-bottom q-mb-md">
          <q-expansion-item expand-separator>
            <template v-slot:header>
              <q-item-section>
                <q-item-label>
                  <span class="text-weight-bold">{{ $t("View Details") }}</span>
                  <span class="text-grey font13 q-ml-sm"
                    >({{ order_items.length }}
                    <template v-if="order_items.length > 2">
                      {{ $t("items") }}
                    </template>
                    <template v-else> {{ $t("item") }} </template>
                    )</span
                  >
                </q-item-label>
              </q-item-section>
            </template>
            <template v-slot:default>
              <q-list>
                <q-item v-for="items in order_items" :key="items">
                  <q-item-section avatar top>
                    <q-img
                      :src="items.url_image"
                      lazy
                      fit="cover"
                      style="height: 50px; width: 50px"
                      class="rounded-borders"
                    />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-bold q-mb-xs font12">
                      <p class="no-margin">
                        {{ items.qty }} x
                        {{ items.item_name }}
                        <template v-if="items.price.size_name != ''">
                          ({{ items.price.size_name }})
                        </template>

                        <template v-if="items.item_changes == 'replacement'">
                          <div class="m-0 text-grey">
                            {{ $t("Replace") }} "{{ items.item_name_replace }}"
                          </div>
                          <q-badge
                            color="primary"
                            text-color="white"
                            label="Replacement"
                          />
                        </template></p
                    ></q-item-label>

                    <q-item-label caption class="text-weight-medium font12">
                      <template v-if="items.price.discount > 0">
                        <p class="no-margin">
                          <del>{{ items.price.pretty_price }}</del>
                          {{ items.price.pretty_price_after_discount }}
                        </p>
                      </template>
                      <template v-else>
                        <p class="no-margin">{{ items.price.pretty_price }}</p>
                      </template>

                      <p
                        class="no-margin"
                        v-if="items.special_instructions != ''"
                      >
                        {{ items.special_instructions }}
                      </p>

                      <template v-if="items.attributes != ''">
                        <template
                          v-for="attributes in items.attributes"
                          :key="attributes"
                        >
                          <p class="no-margin">
                            <template
                              v-for="(
                                attributes_data, attributes_index
                              ) in attributes"
                            >
                              {{ attributes_data
                              }}<template
                                v-if="attributes_index < attributes.length - 1"
                                >,
                              </template>
                            </template>
                          </p>
                        </template>
                      </template>
                    </q-item-label>
                  </q-item-section>

                  <q-item-section side top>
                    <q-item-label caption class="text-weight-bold font12">
                      <template v-if="items.price.discount <= 0">
                        <p class="no-margin">{{ items.price.pretty_total }}</p>
                      </template>
                      <template v-else>
                        <p class="no-margin">
                          {{ items.price.pretty_total_after_discount }}
                        </p>
                      </template>
                    </q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </template>
          </q-expansion-item>
        </q-list>

        <q-footer
          reveal
          class="q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark bg-white"
        >
          <q-btn
            unelevated
            no-caps
            size="lg"
            :label="$t('Track your order')"
            color="primary"
            text-color="white"
            class="full-width"
            :to="{
              path: '/account/trackorder',
              query: {
                order_uuid: order_info.order_uuid,
              },
            }"
          ></q-btn>
        </q-footer>
      </template>
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "OrderSuccess",
  data() {
    return {
      order_uuid: "",
      loading: true,
      data: [],
      order_items: [],
      order_info: [],
      merchant: [],
      estimation: [],
      charge_type: "",
      payload: [
        "merchant_info",
        "items",
        "order_info",
        "estimation",
        "charge_type",
      ],
    };
  },
  // beforeRouteLeave(to, from, next) {
  //   console.log(next);
  // },
  created() {
    this.order_uuid = this.$route.query.order_uuid;
    this.orderDetails();
  },
  computed: {
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    refresh(done) {
      this.orderDetails(done);
    },
    orderDetails(done) {
      this.loading = true;
      APIinterface.fetchDataByToken("orderDetails", {
        order_uuid: this.order_uuid,
        payload: this.payload,
      })
        .then((data) => {
          this.data = data.details.data;
          this.order_items = data.details.data.items;
          this.order_info = data.details.data.order.order_info;
          this.merchant = data.details.data.merchant;
          this.estimation = data.details.data.estimation;
          this.charge_type = data.details.data.charge_type;
        })
        .catch((error) => {
          this.order_items = [];
          this.order_info = [];
          this.merchant = [];
          this.estimation = [];
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    estimatedLabel(service_code) {
      if (service_code == "pickup") {
        return this.$t("Estimated Pickup Time");
      } else if (service_code == "dinein") {
        return this.$t("Estimated Dinein Time");
      }
      return this.$t("Estimated Delivery Time");
    },
  },
};
</script>
