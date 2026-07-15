<template>
  <q-dialog
    v-model="show_modal"
    persistent
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card style="width: 500px; max-width: 80vw">
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-space></q-space>
        <q-btn
          @click="show_modal = !true"
          color="white"
          square
          unelevated
          text-color="grey"
          icon="las la-times"
          dense
          no-caps
          size="sm"
          class="border-grey radius8"
        />
      </q-toolbar>

      <q-card-section class="q-pa-md">
        <h5 class="text-weight-bold no-margin">{{ title }}</h5>
        <div class="q-ma-sm">
          <p class="font12">{{ label.notes }}</p>
        </div>
      </q-card-section>

      <q-separator spaced />
      <q-card-actions>
        <q-btn
          :label="label.submit"
          :loading="loading"
          @click="onSubmit()"
          unelevated
          no-caps
          color="primary text-white"
          class="full-width text-weight-bold"
          size="lg"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>

  <q-dialog
    v-model="payment_modal"
    persistent
    transition-show="scale"
    transition-hide="scale"
  >
    <q-card style="width: 500px; max-width: 80vw">
      <q-card-section class="row items-center q-pb-none q-pa-none">
        <q-space />
        <q-btn icon="eva-close-outline" flat round dense v-close-popup />
      </q-card-section>

      <q-card-section class="q-pa-md">
        <h5 class="text-weight-bold no-margin">{{ label.payment_title }}</h5>
        <div class="q-ma-sm">
          <p class="font12">{{ label.payment_subtitle }}</p>
        </div>
      </q-card-section>

      <q-separator spaced />
      <q-card-actions>
        <div ref="paypal_button" class="margin-auto full-width" />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { loadScript } from "vue-plugin-load-script";

let paypalHandle;
export default {
  name: "PaypalComponents",
  props: ["title", "label", "payment_code", "payment_credentials"],
  data() {
    return {
      show_modal: false,
      data: [],
      loading: false,
      payment_modal: false,
      client_id: "",
    };
  },
  methods: {
    showPaymentForm() {
      this.show_modal = true;
    },
    close() {
      this.show_modal = false;
    },
    onSubmit() {
      let merchantId = 0;
      if (
        typeof this.payment_credentials[this.payment_code] !== "undefined" &&
        this.payment_credentials[this.payment_code] !== null
      ) {
        merchantId = this.payment_credentials[this.payment_code].merchant_id;
      }
      const $data = {
        merchant_id: merchantId,
        payment_code: this.payment_code,
      };
      this.loading = true;
      APIinterface.SavedPaymentProvider($data)
        .then((data) => {
          this.close();
          this.$emit("afterAddpayment");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    PaymentRender(data) {
      this.data = data;
      this.payment_modal = true;
      if (
        typeof this.payment_credentials[data.payment_code] !== "undefined" &&
        this.payment_credentials[data.payment_code] !== null
      ) {
        this.client_id = this.payment_credentials[data.payment_code].attr2;
      }
      this.initPaypal();
    },
    initPaypal() {
      loadScript(
        "https://www.paypal.com/sdk/js?client-id=" +
          this.client_id +
          "&currency=" +
          this.data.currency +
          "&disable-funding=credit,card"
      )
        .then(() => {
          this.renderPaypal();
        })
        .catch(() => {
          APIinterface.notify(
            "negative",
            "failed loading script",
            "error_outline",
            this.$q
          );
        });
    },
    renderPaypal() {
      /* eslint-disable */
      paypalHandle = paypal.Buttons({
        createOrder: (data, actions) => {
          return actions.order.create({
            purchase_units: [
              {
                amount: {
                  value: this.data.total,
                },
              },
            ],
          });
        },
        onCancel: (data) => {
          //
        },
        onError: (error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        },
        onApprove: (data, actions) => {
          return actions.order.capture().then((orderData) => {
            const transaction =
              orderData.purchase_units[0].payments.captures[0];
            this.CompletePaymentRequest(
              transaction.status,
              transaction.id,
              orderData.id
            );
          });
        },
      });
      paypalHandle.render(this.$refs.paypal_button);
    },
    CompletePaymentRequest(status, transaction_id, order_id) {
      let $params = {
        transaction_id: transaction_id,
        order_id: order_id,
        order_uuid: this.data.order_uuid,
        cart_uuid: this.data.cart_uuid,
      };
      APIinterface.showLoadingBox(
        "Processing payment..<br/>don't close this window",
        this.$q
      );
      APIinterface.PaypalVerifyPayment($params)
        .then((data) => {
          this.$emit("afterPayment", data.details);
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          APIinterface.hideLoadingBox(this.$q);
        });
    },
  },
};
</script>
