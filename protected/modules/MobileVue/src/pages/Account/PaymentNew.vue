<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header
      reveal
      reveal-offset="50"
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
          $t("New Payment")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page
      class="q-pl-md q-pr-md"
      :class="{
        'flex flex-center': !PaymentStore.hasData && !PaymentStore.loading2,
        'row items-stretch ': PaymentStore.hasData && !PaymentStore.loading2,
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-dark': !$q.dark.mode,
      }"
    >
      <template v-if="!PaymentStore.hasData && !PaymentStore.loading2">
        <div class="min-height-inherit flex flex-center">
          <div class="full-width text-center q-pb-xl">
            <div class="text-h5 text-weight-bold">
              {{ $t("No Payment available") }}
            </div>
            <p class="text-grey font12">
              {{ $t("There is no payment available") }}
            </p>
          </div>
        </div>
      </template>

      <template v-else>
        <q-inner-loading
          v-if="PaymentStore.loading2"
          :showing="true"
          color="primary"
          size="md"
          label-class="dark"
          class="transparent"
        />
        <template v-else>
          <!-- <pre>{{ PaymentStore.payment_list }}</pre> -->
          <q-card
            flat
            class="radius8 col-12"
            :class="{
              'bg-mydark ': $q.dark.mode,
              'bg-white ': !$q.dark.mode,
            }"
          >
            <q-card-section>
              <q-list>
                <transition
                  v-for="items in PaymentStore.payment_list"
                  :key="items.payment_code"
                  appear
                  leave-active-class="animated fadeOut"
                >
                  <q-item
                    @click="onchoosePayment(items)"
                    clickable
                    v-ripple
                    class="border-grey radius10 q-mb-sm"
                  >
                    <q-item-section avatar>
                      <template v-if="items.logo_type === 'icon'">
                        <q-icon color="warning" name="credit_card" />
                      </template>
                      <template v-else>
                        <q-img
                          :src="items.logo_image"
                          fit="contain"
                          style="height: 35px; max-width: 35px"
                        />
                      </template>
                    </q-item-section>
                    <q-item-section>{{ items.payment_name }}</q-item-section>
                  </q-item>
                </transition>
              </q-list>
            </q-card-section>
          </q-card>
        </template>
      </template>

      <!-- PAYMENTS COMPONENTS -->
      <codComponents
        ref="cod"
        payment_code="cod"
        :title="$t('Add Cash On delivery')"
        :label="{
          submit: this.$t('Add Cash'),
          notes: this.$t(
            'Cash on Delivery or COD is a payment method that allows pay for the items you have ordered only when it gets delivered.'
          ),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <ocrComponents
        ref="ocr"
        payment_code="ocr"
        :title="$t('Add Credit card')"
        :label="{
          submit: this.$t('Add Card'),
          notes: '',
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <StripeComponents
        ref="stripe"
        payment_code="stripe"
        :title="$t('Add Stripe')"
        :label="{
          submit: this.$t('Add Stripe'),
          notes: this.$t('Add your card account'),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <PaypalComponents
        ref="paypal"
        payment_code="paypal"
        :title="$t('Add Paypal')"
        :label="{
          submit: this.$t('Add Paypal'),
          notes: this.$t('Pay using your paypal account'),
          payment_title: this.$t('Pay using Paypal'),
          payment_subtitle: this.$t(
            'You will re-direct to paypal account to login to your account.'
          ),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <RazorpayComponents
        ref="razorpay"
        payment_code="razorpay"
        :title="$t('Add Razorpay')"
        :label="{
          submit: this.$t('Submit'),
          notes: this.$t('Pay using your Razorpay account'),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <MercadopagoComponents
        ref="mercadopago"
        payment_code="mercadopago"
        :title="$t('Add Mercadopago')"
        :label="{
          submit: this.$t('Add Mercadopago'),
          notes: this.$t('Pay using your mercadopago account'),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <BankComponents
        ref="bank"
        payment_code="bank"
        :title="$t('Add Bank Transfer')"
        :label="{
          submit: this.$t('Add Payment'),
          notes: this.$t('Pay using bank Transfer'),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      />

      <PaydeliveryComponents
        ref="paydelivery"
        payment_code="paydelivery"
        :title="$t('Add Payment')"
        :label="{
          submit: this.$t('Saved'),
          notes: this.$t('Pay using different card'),
        }"
        :payment_credentials="PaymentStore.payment_credentials"
        @after-addpayment="afterAddpayment"
      >
      </PaydeliveryComponents>



      <!-- END PAYMENTS COMPONENTS -->
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { defineAsyncComponent } from "vue";
import { usePaymentStore } from "stores/PaymentStore";

export default {
  name: "PaymentNew",
  data() {
    return {
      params: "",
    };
  },
  setup() {
    const PaymentStore = usePaymentStore();
    return { PaymentStore };
  },
  components: {
    codComponents: defineAsyncComponent(() =>
      import("components/codComponents.vue")
    ),
    ocrComponents: defineAsyncComponent(() =>
      import("components/ocrComponents.vue")
    ),
    StripeComponents: defineAsyncComponent(() =>
      import("components/StripeComponents.vue")
    ),
    PaypalComponents: defineAsyncComponent(() =>
      import("components/PaypalComponents.vue")
    ),
    RazorpayComponents: defineAsyncComponent(() =>
      import("components/RazorpayComponents.vue")
    ),
    MercadopagoComponents: defineAsyncComponent(() =>
      import("components/MercadopagoComponents.vue")
    ),
    BankComponents: defineAsyncComponent(() =>
      import("components/BankComponents.vue")
    ),
    PaydeliveryComponents: defineAsyncComponent(() =>
      import("components/PaydeliveryComponents.vue")
    ),
    // custom   
  },
  created() {
    if (this.$route.query.redirect == "/checkout") {
      this.params = "cart_uuid=" + APIinterface.getStorage("cart_uuid");
    }
    this.PaymentStore.PaymentMethod(null, this.params);
  },
  methods: {
    refresh(done) {
      this.PaymentStore.PaymentMethod(done, this.params);
    },
    onchoosePayment(data) {
      try {
        this.$refs[data.payment_code].showPaymentForm();
      } catch (error) {
        APIinterface.notify("grey-8", error, "error_outline", this.$q);
      }
    },
    afterAddpayment() {
      if (this.$route.query.redirect == "/checkout") {
        this.$router.push("/checkout?refresh_payment=1");
      } else {
        this.$router.back();
      }
    },
  },
};
</script>
