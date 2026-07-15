<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header
      reveal
      reveal-offset="50"
      class=""
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-dark': !$q.dark.mode,
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
          $t("Orders Details")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page
      class="b q-pl-md q-pr-md q-pb-md"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-dark': !$q.dark.mode,
      }"
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
      <template v-else>
        <q-list>
          <q-item class="q-pl-none q-pr-none">
            <q-item-section>
              <q-item-label class="text-dark text-weight-bold">{{
                $t("Order ID")
              }}</q-item-label>
              <q-item-label caption class="font12">
                <span class="q-mr-sm text-weight-bold"
                  >#{{ order_info.order_id }}</span
                >

                <q-chip
                  :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                  :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                  size="sm"
                  class="q-ma-none"
                  >{{ order_services.service_name }}</q-chip
                >
              </q-item-label>
              <q-item-label caption class="font12">
                <div class="text-weight-bold">
                  {{ order_info.payment_name }}
                </div>
                <div class="text-weight-light font11">
                  {{ order_info.place_on }}
                </div>
              </q-item-label>
            </q-item-section>
            <q-item-section side top>
              <q-chip
                color="mygrey"
                text-color="dark"
                size="sm"
                :style="`color:${order_status.font_color_hex} !important; background-color:${order_status.background_color_hex} !important; `"
                class="q-ma-none"
              >
                {{ order_status.status }}
              </q-chip>
            </q-item-section>
          </q-item>

          <q-item
            @click="this.$refs.OrderDeliveryDetails.dialog = true"
            clickable
            class="q-pl-none q-pr-none q-pt-none"
          >
            <q-item-section>
              <q-item-label caption class="font12">
                <div class="text-weight-bold">{{ progress_order_status }}</div>
                <div class="text-weight-light font11">
                  {{ progress_order_status_details }}
                </div>
              </q-item-label>
            </q-item-section>
            <q-item-section avatar>
              <q-icon color="grey" size="15px" name="las la-angle-right" />
            </q-item-section>
          </q-item>

          <template v-if="allowed_to_review">
            <q-item
              :to="{
                name: 'write-review',
                params: {
                  order_uuid: order_info.order_uuid,
                  back_url: '/orders',
                },
              }"
              clickable
              class="q-pl-none q-pr-none q-pt-none"
            >
              <q-item-section>
                <q-item-label caption class="font12">
                  <div class="text-weight-bold">{{ $t("Write Review") }}</div>
                  <div class="text-weight-light font11">
                    {{ $t("Rate your order") }}!
                  </div>
                </q-item-label>
              </q-item-section>
              <q-item-section avatar>
                <q-icon color="grey" size="15px" name="las la-angle-right" />
              </q-item-section>
            </q-item>
          </template>

          <q-item
            v-if="order_info.upload_deposit_link"
            :to="{
              path: '/account/upload-deposit',
              query: { order_uuid: this.order_uuid },
            }"
            clickable
            class="q-pl-none q-pr-none q-pt-none"
          >
            <q-item-section caption class="font12">
              <div class="text-weight-bold text-blue">
                {{ $t("Upload bank deposit") }}
              </div>
            </q-item-section>
            <q-item-section avatar>
              <q-icon color="grey" size="15px" name="las la-angle-right" />
            </q-item-section>
          </q-item>
        </q-list>

        <template v-if="hasRefund">
          <q-list class="qlist-no-padding">
            <q-expansion-item expand-separator label="Refund Issued">
              <q-list>
                <q-item>
                  <q-item-section>
                    <template
                      v-for="refund in refund_transaction"
                      :key="refund"
                    >
                      <q-item-label caption class="font12"
                        >{{ $t("Description") }}:
                        {{ refund.description }}</q-item-label
                      >
                      <q-item-label caption class="font12"
                        >{{ $t("Amount") }}:
                        {{ refund.trans_amount }}</q-item-label
                      >
                      <q-item-label caption class="font12"
                        >{{ $t("Issued to") }}:
                        {{ refund.used_card }}</q-item-label
                      >
                      <q-item-label caption class="font12"
                        >{{ $t("Date issued") }}:
                        {{ refund.date }}</q-item-label
                      >
                    </template>
                  </q-item-section>
                </q-item>
              </q-list>
            </q-expansion-item>
          </q-list>
        </template>

        <q-list v-if="hasTableInfo">
          <q-item class="q-pl-none q-pr-none q-pt-none">
            <q-item-label caption class="font12">
              <div class="text-weight-bold">{{ $t("Table information") }}</div>
              <div class="text-weight-light">
                {{ $t("Guest") }} : {{ order_table_data.guest_number }}
              </div>
              <div class="text-weight-light">
                {{ $t("Room name") }} : {{ order_table_data.room_name }}
              </div>
              <div class="text-weight-light">
                {{ $t("Table name") }} : {{ order_table_data.table_name }}
              </div>
            </q-item-label>
          </q-item>
        </q-list>

        <q-card flat class="radius8">
          <q-list class="q-pb-md">
            <q-item>
              <q-item-section>
                <q-item-label
                  lines="1"
                  class="text-weight-medium text-grey font13"
                >
                  {{ order_label.your_order_from }} :</q-item-label
                >
              </q-item-section>
              <q-item-section side>
                <q-item-label lines="1" class="text-weight-bold font13">
                  <q-btn
                    flat
                    unelevated
                    no-caps
                    class="q-pa-none min-height"
                    :to="{
                      name: 'menu',
                      params: { slug: merchant.restaurant_slug },
                    }"
                    >{{ merchant.restaurant_name }}</q-btn
                  >
                </q-item-label>
              </q-item-section>
            </q-item>

            <template v-for="items in order_items" :key="items.item_id">
              <q-item>
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
                          :label="$t('Replacement')"
                        />
                      </template>
                    </p>
                  </q-item-label>
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

              <template v-for="addons in items.addons" :key="addons">
                <q-item class="q-item-small">
                  <q-item-section avatar></q-item-section>
                  <q-item-section>
                    <q-item-label class="text-weight-bold font12">{{
                      addons.subcategory_name
                    }}</q-item-label>
                  </q-item-section>
                  <q-item-section side top></q-item-section>
                </q-item>

                <q-item
                  v-for="addon_items in addons.addon_items"
                  :key="addon_items"
                  class="q-item-small"
                >
                  <q-item-section avatar></q-item-section>
                  <q-item-section>
                    <q-item-label lines="1" class="font12 text-weight-medium"
                      >{{ addon_items.qty }} x {{ addon_items.pretty_price }}
                      {{ addon_items.sub_item_name }}</q-item-label
                    >
                  </q-item-section>
                  <q-item-section side top>
                    <p class="no-margin font12 text-weight-bold">
                      {{ addon_items.pretty_addons_total }}
                    </p>
                  </q-item-section>
                </q-item>
              </template>
              <!-- addons -->
              <q-separator inset />
            </template>
            <!-- end items -->

            <q-item class="q-pb-none" style="min-height: auto">
              <q-item-section class="text-weight-medium font13">
                {{ $t("Summary") }}
              </q-item-section>
            </q-item>

            <template v-for="summary in order_summary" :key="summary.name">
              <template v-if="summary.type == 'total'">
                <q-separator spaced inset />
                <q-item dense class="q-pb-none" style="min-height: auto">
                  <q-item-section class="font13 text-weight-medium">{{
                    summary.name
                  }}</q-item-section>
                  <q-item-section side class="font13 text-weight-medium">{{
                    summary.value
                  }}</q-item-section>
                </q-item>
              </template>
              <template v-else>
                <q-item dense class="q-pb-none" style="min-height: auto">
                  <q-item-section class="font13 text-weight-medium">{{
                    summary.name
                  }}</q-item-section>
                  <q-item-section side class="font13 text-weight-medium">{{
                    summary.value
                  }}</q-item-section>
                </q-item>
              </template>
            </template>
          </q-list>

          <q-separator />
          <q-card-actions>
            <div class="row full-width items-center text-center">
              <div class="col">
                <q-btn
                  flat
                  no-caps
                  class="q-pa-none text-weight-bold line-1 min-height"
                  color="secondary"
                  :disable="!allowed_to_cancel"
                  @click="this.$refs.cancel_order.showModal(this.order_uuid)"
                >
                  <div>{{ $t("Cancel") }}</div>
                  <q-icon
                    right
                    name="las la-angle-right"
                    size="15px"
                    class="text-grey q-ml-sm"
                  />
                </q-btn>
              </div>
              <div class="col">
                <q-btn
                  flat
                  no-caps
                  class="q-pa-none text-weight-bold line-1 min-height"
                  color="grey"
                  :href="pdf_link"
                  target="_blank"
                >
                  <div>{{ $t("Download PDF") }}</div>
                  <q-icon
                    right
                    name="las la-angle-right"
                    size="15px"
                    class="text-grey q-ml-sm"
                  />
                </q-btn>
              </div>
            </div>
          </q-card-actions>
        </q-card>
      </template>
    </q-page>

    <CancelOrder ref="cancel_order" @after-cancelorder="afterCancelorder" />
    <OrderDeliveryDetails
      ref="OrderDeliveryDetails"
      :data="delivery_timeline"
      :order_status="order_delivery_status"
      :progress="data_progress"
    ></OrderDeliveryDetails>

    <q-footer
      v-if="!loading"
      class="bg-grey-1 row q-gutter-md q-pl-md q-pr-md q-pb-sm"
    >
      <q-btn
        :label="order_label.buy_again"
        unelevated
        no-caps
        color="mygrey"
        text-color="dark"
        class="col"
        @click="Buyagain()"
        size="lg"
      />
      <q-btn
        :to="{
          path: '/account/trackorder',
          query: { order_uuid: this.order_uuid, back_url: 1 },
        }"
        :label="order_label.track"
        unelevated
        no-caps
        color="primary text-white"
        class="col"
        size="lg"
      />
    </q-footer>
  </q-pull-to-refresh>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useDeliveryschedStore } from "stores/DeliverySched";

export default {
  name: "OrderDetails",
  components: {
    CancelOrder: defineAsyncComponent(() =>
      import("components/CancelOrder.vue")
    ),
    OrderDeliveryDetails: defineAsyncComponent(() =>
      import("components/OrderDeliveryDetails.vue")
    ),
  },
  setup() {
    const DeliveryschedStore = useDeliveryschedStore();
    return { DeliveryschedStore };
  },
  data() {
    return {
      order_uuid: "",
      order_data: [],
      loading: false,
      order_items: [],
      order_summary: [],
      order_info: [],
      order_label: [],
      refund_transaction: [],
      order_status: [],
      order_services: [],
      merchant: [],
      progress: 0,
      data_progress: [],
      progress_order_status: "",
      progress_order_status_details: "",
      allowed_to_cancel: false,
      pdf_link: "",
      delivery_timeline: [],
      order_delivery_status: [],
      allowed_to_review: false,
      payload: [
        "merchant_info",
        "items",
        "summary",
        "order_info",
        "progress",
        "refund_transaction",
        "status_allowed_cancelled",
        "pdf_link",
        "delivery_timeline",
        "order_delivery_status",
        "allowed_to_review",
      ],
      order_table_data: [],
    };
  },
  created() {
    this.order_uuid = this.$route.query.order_uuid;
    this.orderDetails();
  },
  computed: {
    hasRefund() {
      if (this.refund_transaction.length > 0) {
        return true;
      }
      return false;
    },
    hasTableInfo() {
      if (Object.keys(this.order_table_data).length > 0) {
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
      //this.order_uuid
      APIinterface.fetchDataByToken("orderDetails", {
        order_uuid: this.order_uuid,
        payload: this.payload,
      })
        .then((data) => {
          this.order_data = data.details;
          this.merchant = data.details.data.merchant;
          this.order_items = data.details.data.items;
          this.order_summary = data.details.data.summary;
          this.order_info = data.details.data.order.order_info;
          this.order_label = data.details.data.label;
          this.refund_transaction = data.details.data.refund_transaction;
          this.order_status = data.details.data.order.status;
          this.order_services = data.details.data.order.services;
          this.data_progress = data.details.data.progress;
          this.progress = data.details.data.progress.order_progress;
          this.allowed_to_cancel = data.details.data.allowed_to_cancel;
          this.pdf_link = data.details.data.pdf_link;
          this.delivery_timeline = data.details.data.delivery_timeline;
          this.order_delivery_status = data.details.data.order_delivery_status;
          this.allowed_to_review = data.details.data.allowed_to_review;

          this.order_table_data = data.details.data.order_table_data;

          this.progress_order_status = data.details.data.progress.order_status;
          this.progress_order_status_details =
            data.details.data.progress.order_status_details;

          this.order_services =
            this.order_services[this.order_info.service_code];
          this.order_status = this.order_status[this.order_info.status];
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    afterReceive(data) {
      if (data.order_id !== this.order_info.order_id) {
        return false;
      }
      if (data.order_progress === 0) {
        this.progress = data.order_progress;
        this.progress_order_status = data.order_status;
        this.progress_order_status_details = data.order_status_details;
      } else if (data.order_progress === -1) {
        // do nothing
      } else {
        this.progress = data.order_progress;
        this.progress_order_status = data.order_status;
        this.progress_order_status_details = data.order_status_details;
      }
    },
    defineColors(data) {
      if (this.progress <= 0) {
        return "grey-8";
      } else {
        return this.progress > data ? "primary" : "grey-4";
      }
    },
    copyClipboard(text) {
      navigator.clipboard.writeText(text);
      APIinterface.notify("light-green", "Copied", "check_circle", this.$q);
    },
    Buyagain() {
      const $params = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        order_uuid: this.order_uuid,
      };
      APIinterface.showLoadingBox("", this.$q);
      APIinterface.orderBuyAgain($params)
        .then((data) => {
          this.DeliveryschedStore.transaction_type =
            data.details.restaurant_slug;
          APIinterface.setStorage("cart_uuid", data.details.cart_uuid);
          this.$router.push("/cart");
        })
        .catch((error) => {
          APIinterface.notify("grey-8", error, "error_outline", this.$q);
        })
        .then((data) => {
          APIinterface.hideLoadingBox(this.$q);
        });
    },
    afterCancelorder() {
      console.debug("afterCancelorder");
      this.$refs.cancel_order.show_modal = false;
      this.orderDetails();
    },
  },
};
</script>

<style lang="scss">
.q-focus-helper {
  visibility: hidden;
}
</style>
