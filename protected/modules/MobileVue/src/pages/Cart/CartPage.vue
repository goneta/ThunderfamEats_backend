<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header
      reveal
      reveal-offset="10"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-white text-black': !$q.dark.mode,
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
          $t("Your Order")
        }}</q-toolbar-title>
        <q-btn
          v-if="CartStore.hasItem"
          @click="this.$refs.cart_details.clearCart()"
          flat
          round
          dense
          icon="las la-trash-alt"
          color="secondary"
        />
      </q-toolbar>
    </q-header>
    <q-page
      :class="{
        'flex flex-center': !CartStore.hasItem && !CartStore.cart_loading,
      }"
    >
      <template v-if="!CartStore.hasItem && !CartStore.cart_loading">
        <div class="text-center full-width">
          <q-img
            src="cart-empty.png"
            fit="fill"
            spinner-color="primary"
            style="height: 80px; max-width: 80px"
          />
          <div class="text-h5 text-weight-bold">
            {{ $t("Your cart is empty") }}
          </div>
          <p class="text-grey font12">
            {{ $t("You don't have any orders here! let's change that!") }}
          </p>
        </div>
      </template>

      <template v-else>
        <CartMerchantInfo
          ref="cart_merchantinfo"
          :show_transinfo="true"
          @on-clickchange="onClickchange"
        />

        <div
          v-if="!CartStore.canProceed && !CartStore.cart_loading"
          class="q-pl-md q-pr-md"
        >
          <div
            class="q-pa-md radius8 font12"
            style="bottom: 51px"
            :class="{
              'bg-grey600 text-grey300': $q.dark.mode,
              'bg-yellow text-dark': !$q.dark.mode,
            }"
          >
            {{ CartStore.getErrorMsg }}
          </div>
        </div>

        <CartDetails
          ref="cart_details"
          :is_checkout="true"
          :payload="payload"
        />

        <q-list v-if="!CartStore.cart_loading" class="border-grey-top">
          <q-item>
            <q-item-section>
              <div class="text-weight-bold">{{ $t("Cutlery") }}</div>
              <div class="text-grey">
                {{ $t("Include utensils, napkins, etc.") }}
              </div>
            </q-item-section>
            <q-item-section side>
              <q-toggle
                v-model="include_utensils"
                @update:model-value="setUtensil"
              />
            </q-item-section>
          </q-item>
        </q-list>

        <DIV v-if="CartStore.hasItem" class="q-pl-md q-pr-md border-grey-top">
          <div class="row justify-end">
            <q-btn
              unelevated
              :color="$q.dark.mode ? 'grey600' : 'mygrey'"
              :text-color="$q.dark.mode ? 'grey300' : 'grey'"
              no-caps
              size="md"
              class="radius8 q-mt-sm q-mb-md"
              :to="{
                name: 'menu',
                params: { slug: CartStore.cart_merchant.slug },
              }"
            >
              <q-icon name="las la-plus" size="15px"></q-icon>
              <div class="q-pl-sm">{{ $t("Add more items") }}</div>
            </q-btn>
          </div>

          <SimilarItems
            v-if="CartStore.cart_merchant.merchant_id"
            ref="similar_items"
            :title="$t('Most Order Items')"
            :merchant_id="CartStore.cart_merchant.merchant_id"
          />
        </DIV>
      </template>
    </q-page>
  </q-pull-to-refresh>

  <q-inner-loading
    :showing="CartStore.cart_reloading"
    color="primary"
    size="md"
    label-class="dark"
  />

  <q-footer
    v-if="CartStore.items_count > 0"
    reveal
    class="q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
    :class="{
      'bg-primary': !CartStore.cart_reloading,
      'bg-grey-5': CartStore.cart_reloading,
    }"
  >
    <q-btn
      @click="checkBeforeCheckout"
      :loading="CartStore.cart_loading"
      :disable="!CartStore.canProceed"
      unelevated
      text-color="white"
      no-caps
      class="radius10 fit"
      :color="{
        primary: !CartStore.cart_reloading,
        'grey-5': CartStore.cart_reloading,
      }"
    >
      <div class="row items-center justify-between fit">
        <div class="text-weight-bold">{{ $t("Checkout") }}</div>
        <div class="text-weight-bold">
          {{ CartStore.cart_subtotal.value }}
        </div>
      </div>
    </q-btn>
  </q-footer>

  <DeliveryDetails
    ref="delivery_details"
    :back_url="back_url"
    @show-sched="showSched"
    @after-savetrans="afterSavetrans"
    @after-savetranstype="afterSavetranstype"
    @after-setplaceid="afterSetplaceid"
  />
  <DeliverySched
    ref="delivery_sched"
    :slug="CartStore.cart_merchant.slug"
    @after-savetrans="afterSavetrans"
  />

  <template v-if="getMerchantUUID">
    <ComponentsRealtime
      ref="realtime"
      getevent="cart"
      :subscribe_to="getMerchantUUID"
      @after-receive="afterReceive"
    >
    </ComponentsRealtime>
  </template>
</template>

<script>
import { defineAsyncComponent } from "vue";
import { useCartStore } from "stores/CartStore";
//import { useTransactionStore } from "stores/Transaction.js";
import APIinterface from "src/api/APIinterface";
import { useDeliveryschedStore } from "stores/DeliverySched";

export default {
  name: "CartPage",
  components: {
    CartMerchantInfo: defineAsyncComponent(() =>
      import("components/CartMerchantInfo.vue")
    ),
    CartDetails: defineAsyncComponent(() =>
      import("components/CartDetails.vue")
    ),
    DeliveryDetails: defineAsyncComponent(() =>
      import("components/DeliveryDetails.vue")
    ),
    DeliverySched: defineAsyncComponent(() =>
      import("components/DeliverySched.vue")
    ),
    SimilarItems: defineAsyncComponent(() =>
      import("components/SimilarItems.vue")
    ),
    ComponentsRealtime: defineAsyncComponent(() =>
      import("components/ComponentsRealtime.vue")
    ),
  },
  data() {
    return {
      payload: [
        "items",
        "subtotal",
        "distance_local",
        "items_count",
        "merchant_info",
        "check_opening",
        "transaction_info",
      ],
      back_url: "/cart?refresh=1",
      lastPath: "",
      data_slide: {},
      include_utensils: false,
    };
  },
  setup() {
    const CartStore = useCartStore();
    const DeliveryschedStore = useDeliveryschedStore();
    return { CartStore, DeliveryschedStore };
  },
  created() {
    // if (!this.CartStore.hadData()) {
    //   this.CartStore.getCart(true, this.payload);
    // } else {
    //   if (this.$route.query.refresh == 1) {
    //     this.CartStore.getCart(true, this.payload);
    //   }
    // }
    this.CartStore.getCart(true, this.payload);

    const includeUtensils = APIinterface.getStorage("include_utensils");
    if (!APIinterface.empty(includeUtensils)) {
      this.include_utensils = includeUtensils;
    }

    this.DeliveryschedStore.getDeliverySched(
      APIinterface.getStorage("cart_uuid"),
      APIinterface.getStorage("merchant_slug"),
      "cart"
    );
  },
  mounted() {
    this.lastPath = this.$router.options.history.state.back;
  },
  computed: {
    getMerchantUUID() {
      if (Object.keys(this.CartStore.cart_merchant).length > 0) {
        return this.CartStore.cart_merchant.merchant_uuid;
      }
      return false;
    },
  },
  methods: {
    backPage() {
      const $find = this.lastPath.search("map");
      if ($find > 0) {
        this.$router.push({
          name: "menu",
          params: { slug: this.CartStore.cart_merchant.slug },
        });
      } else {
        this.$router.back();
      }
    },
    afterSavetrans() {
      this.CartStore.getCart(false, this.payload);
      console.log("afterSavetrans");
      this.DeliveryschedStore.getDeliverySched(
        APIinterface.getStorage("cart_uuid"),
        APIinterface.getStorage("merchant_slug")
      );
    },
    afterSavetranstype() {
      this.CartStore.getCart(false, this.payload);
    },
    onClickchange() {
      this.$refs.delivery_details.showModal(true);
    },
    showSched() {
      this.$refs.delivery_details.showModal(false);
      this.$refs.delivery_sched.showSched(true);
    },
    onRight(details, index) {
      this.data_slide[index] = details;
    },
    closeSlide(index) {
      if (this.data_slide[index]) {
        this.data_slide[index].reset();
      }
    },
    setUtensil(value) {
      APIinterface.setStorage("include_utensils", value);
    },
    refresh(done) {
      this.CartStore.getCart(false, this.payload);
      done();
    },
    checkBeforeCheckout() {
      console.log(this.CartStore.transaction_info.whento_deliver);
      if (this.CartStore.transaction_info.whento_deliver == "schedule") {
        let $message =
          "You are placing and order for {date} at {time}. Would you like to continue?";

        $message = $message.replace(
          "{date}",
          this.CartStore.transaction_info.delivery_date_pretty
        );
        $message = $message.replace(
          "{time}",
          this.CartStore.transaction_info.delivery_time.pretty_time
        );

        this.$q
          .dialog({
            title: "Ordering for later?",
            message: $message,
            persistent: true,
            position: "standard",
            transitionShow: "fade",
            transitionHide: "fade",
            ok: {
              unelevated: true,
              color: "primary",
              rounded: false,
              "text-color": "white",
              size: "md",
              label: "Continue",
              "no-caps": true,
            },
            cancel: {
              unelevated: true,
              rounded: false,
              color: "grey-3",
              "text-color": "black",
              size: "md",
              label: "Cancel",
              "no-caps": true,
            },
          })
          .onOk(() => {
            this.$router.push("/checkout");
          })
          .onOk(() => {
            // console.log('>>>> second OK catcher')
          })
          .onCancel(() => {
            // console.log('>>>> Cancel')
          })
          .onDismiss(() => {
            // console.log('I am triggered on both OK and Cancel')
          });
      } else {
        this.$router.push("/checkout");
      }
    },
    afterSetplaceid() {
      this.CartStore.getCart(true, this.payload);
    },
    afterReceive(data) {
      console.log("afterReceive");
      console.log(data);
      let message = JSON.parse(data.message);
      console.log(message);
      APIinterface.fetchDataPost(
        "validateCartItems",
        "item_id=" +
          message.item_id +
          "&cart_uuid=" +
          APIinterface.getStorage("cart_uuid")
      )
        .then((data) => {
          //
          this.$q
            .dialog({
              title: this.$t("Items"),
              message: data.msg,
              persistent: true,
            })
            .onOk(() => {
              this.CartStore.getCart(true, this.payload);
            })
            .onCancel(() => {
              // console.log('Cancel')
            })
            .onDismiss(() => {
              // console.log('I am triggered on both OK and Cancel')
            });
          //
        })
        .catch((error) => {
          //
        })
        .then((data) => {
          //
        });
    },
    //
  },
};
</script>
