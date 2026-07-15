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

      <q-form @submit="onSubmit">
        <q-card-section class="q-pa-md">
          <h5 class="text-weight-bold no-margin">{{ title }}</h5>
          <div class="q-ma-sm">
            <p class="font12">{{ label.notes }}</p>
          </div>

          <div class="column q-col-gutter-sm">
            <q-input
              dense
              :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
              :color="$q.dark.mode ? 'grey300' : 'primary'"
              outlined
              v-model="credit_card_number"
              :label="$t('Card number')"
              :rules="[
                (val) => (val && val.length > 0) || 'this field is required',
              ]"
              mask="#### #### #### ####"
            />
          </div>

          <div class="row q-col-gutter-md">
            <div class="col">
              <q-input
                dense
                :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :color="$q.dark.mode ? 'grey300' : 'primary'"
                outlined
                v-model="expiry_date"
                :label="$t('Exp. date')"
                class="no-margin"
                :rules="[
                  (val) => (val && val.length > 0) || 'this field is required',
                ]"
                mask="##/##"
              />
            </div>
            <div class="col">
              <q-input
                dense
                :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :color="$q.dark.mode ? 'grey300' : 'primary'"
                outlined
                v-model="cvv"
                :label="$t('Security Code')"
                class="no-margin"
                :rules="[
                  (val) => (val && val.length > 0) || 'this field is required',
                ]"
                mask="####"
              />
            </div>
          </div>
          <!-- row -->

          <div class="row q-col-gutter-md">
            <div class="col-12">
              <q-input
                dense
                :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :color="$q.dark.mode ? 'grey300' : 'primary'"
                outlined
                v-model="card_name"
                class="no-margin"
                :label="$t('Card name')"
                :rules="[
                  (val) => (val && val.length > 0) || 'this field is required',
                ]"
              />
            </div>
          </div>
          <!-- row -->

          <div class="row q-col-gutter-md">
            <div class="col">
              <q-select
                v-model="identification_type"
                :options="identification_type_list"
                :label="$t('Identitification Type')"
                dense
                outlined
                :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :color="$q.dark.mode ? 'grey300' : 'primary'"
              />
            </div>
            <div class="col">
              <q-input
                dense
                :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :color="$q.dark.mode ? 'grey300' : 'primary'"
                outlined
                v-model="identification_number"
                :label="$t('Identification Number')"
                class="no-margin"
                :rules="[
                  (val) => (val && val.length > 0) || 'this field is required',
                ]"
                mask="####"
              />
            </div>
          </div>
          <!-- row -->
        </q-card-section>

        <q-separator spaced />
        <q-card-actions>
          <q-btn
            type="submit"
            :label="label.submit"
            :loading="loading"
            unelevated
            no-caps
            color="primary text-white"
            class="full-width text-weight-bold"
            size="lg"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>

  <q-dialog
    v-model="modal_cvv"
    persistent
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card style="width: 500px; max-width: 80vw">
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-space></q-space>
        <q-btn
          v-close-popup
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

      <q-form @submit="SubmitPayment">
        <q-card-section class="q-pa-md">
          <h5 class="text-weight-bold no-margin">{{ $t("Verification") }}</h5>
          <div class="q-ma-sm">
            <p class="font12">
              {{ $t("Enter CVV for card") }} {{ card_number }}
            </p>
          </div>

          <q-input
            dense
            bg-color="mygrey"
            color="warning"
            outlined
            v-model="verify_cvv"
            :label="$t('Security Code')"
            class="no-margin"
            :rules="[
              (val) =>
                (val && val.length > 0) || this.$t('this field is required'),
            ]"
            mask="####"
          />
        </q-card-section>

        <q-separator spaced />
        <q-card-actions>
          <q-btn
            type="submit"
            :label="label.submit_form"
            :loading="loading"
            color="primary"
            text-color="white"
            unelevated
            class="fit"
            no-caps
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { loadScript } from "vue-plugin-load-script";

let mp;
export default {
  name: "MercadopagoComponents",
  props: ["title", "label", "payment_code", "payment_credentials"],
  data() {
    return {
      show_modal: false,
      data: [],
      loading: false,
      credentials: [],
      identification_type_list: [],
      identification_type: "DNI",
      identification_number: "12334566",
      credit_card_number: "5031755734530604",
      expiry_date: "11/22",
      cvv: "123",
      card_name: "basti",
      customer_id: "",
      payment_uuid: "",
      order_uuid: "",
      modal_cvv: false,
      card_number: "",
      verify_cvv: "123",
    };
  },
  methods: {
    showPaymentForm() {
      this.show_modal = true;
      this.setCredentials();
      this.includeScript(true);
    },
    close() {
      this.show_modal = false;
    },
    closePayment() {
      this.$emit("afterCancelPayment");
    },
    setCredentials() {
      if (
        typeof this.payment_credentials[this.payment_code] !== "undefined" &&
        this.payment_credentials[this.payment_code] !== null
      ) {
        this.credentials = this.payment_credentials[this.payment_code];
      }
    },
    includeScript(initFunc) {
      loadScript("https://sdk.mercadopago.com/js/v2")
        .then(() => {
          if (initFunc) {
            this.initPayment();
            this.createCustomer();
          }
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
    initPayment() {
      mp = new MercadoPago(this.credentials.attr1);
      this.identification_type_list = [];
      mp.getIdentificationTypes()
        .then((result) => {
          Object.entries(result).forEach(([key, items]) => {
            this.identification_type_list.push({
              label: items.name,
              value: items.id,
            });
          });
        })
        .catch((e) => {
          APIinterface.notify("dark", e.message, "error", this.$q);
        });
    },
    createCustomer() {
      const $params = {
        payment_code: this.payment_code,
        merchant_id: this.credentials.merchant_id,
        merchant_type: this.credentials.merchant_type,
      };
      this.loading = true;
      APIinterface.MercadopagoCustomer($params)
        .then((data) => {
          this.customer_id = data.details.customer_id;
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    onSubmit() {
      /* eslint-disable */
      var str = this.expiry_date;
      var expiry = str.split("/");
      var expiry_month = expiry[0];
      var expiry_year = expiry[1];

      const str_card = this.credit_card_number;
      const card_number = str_card.replace(/ /g, "");

      this.is_loading = true;
      mp.createCardToken({
        cardNumber: card_number,
        cardholderName: this.card_name,
        cardExpirationMonth: expiry_month,
        cardExpirationYear: expiry_year,
        securityCode: this.cvv,
        identificationType: this.identification_type,
        identificationNumber: this.identification_number,
      })
        .then((result) => {
          this.AddCard(result);
        })
        .catch((error) => {
          this.is_loading = false;
          if (typeof error.message !== "undefined" && error.message !== null) {
            APIinterface.notify(
              "negative",
              error.message,
              "error_outline",
              this.$q
            );
          } else {
            let error;
            error.forEach((item, index) => {
              error += item.message;
            });
            APIinterface.notify("dark", error, "error", this.$q);
          }
        });
    },
    AddCard(data) {
      let $params = {
        payment_code: this.payment_code,
        merchant_id: this.credentials.merchant_id,
        merchant_type: this.credentials.merchant_type,
        id: data.id,
        card_name: this.card_name,
        customer_id: this.customer_id,
      };
      this.loading = true;
      APIinterface.MercadopagoAddcard($params)
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
      this.payment_uuid = data.payment_uuid;
      this.order_uuid = data.order_uuid;
      let $data = {
        payment_uuid: this.payment_uuid,
      };
      APIinterface.showLoadingBox(
        "Getting payment information..<br/>don't close this window",
        this.$q
      );
      APIinterface.MercadopagoGetcard($data)
        .then((data) => {
          this.card_number = data.details.card_number;
          this.card_id = data.details.card_id;
          this.includeScript(false);
          this.setCredentials();
          this.modal_cvv = true;
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          APIinterface.hideLoadingBox(this.$q);
        });
    },
    SubmitPayment() {
      this.loading = true;
      mp = new MercadoPago(this.credentials.attr2);
      mp.createCardToken({
        cardId: this.card_id,
        securityCode: this.verify_cvv,
      })
        .then((result) => {
          this.loading = false;
          this.capturePayment(result.id);
        })
        .catch((error) => {
          this.loading = false;
          if (typeof error.message !== "undefined" && error.message !== null) {
            APIinterface.notify(
              "negative",
              error.message,
              "error_outline",
              this.$q
            );
          } else {
            let err;
            error.forEach((item, index) => {
              err += item.message;
            });
            APIinterface.notify("dark", err, "error", this.$q);
          }
        });
    },
    capturePayment(card_token) {
      var $params = {
        payment_code: this.payment_code,
        merchant_id: this.credentials.merchant_id,
        merchant_type: this.credentials.merchant_type,
        order_uuid: this.order_uuid,
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        payment_uuid: this.payment_uuid,
        card_token: card_token,
      };
      APIinterface.showLoadingBox(
        "Processing payment..<br/>don't close this window",
        this.$q
      );
      APIinterface.MercadopagoCapturePayment($params)
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
