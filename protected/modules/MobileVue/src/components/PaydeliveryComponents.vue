<template>
  <q-dialog
    v-model="show_modal"
    persistent
    transition-show="fade"
    transition-hide="fade"
    full-width
  >
    <q-card style="width: 500px; max-width: 80vw">
      <q-toolbar class="top-toolbar q-pl-md" dense>
        <q-toolbar-title>{{ title }}</q-toolbar-title>
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
        <template v-if="hasData">
          <div class="row q-gutter-none addon-carousel q-col-gutter-sm">
            <div
              v-for="items in getData"
              :key="items"
              class="col-lg-3 col-md-3 col-sm-6 col-xs-4 text-center"
            >
              <q-radio v-model="payment_id" :val="items.id" label="">
                <q-avatar size="50px" square class="col self-center">
                  <q-img
                    fit="contain"
                    :src="items.url_image"
                    class="no-margin"
                    height="50px"
                    loading="lazy"
                    placeholder-src="placeholder.png"
                    spinner-color="secondary"
                    spinner-size="sm"
                  />
                </q-avatar>
              </q-radio>
            </div>
          </div>
        </template>
        <template v-else>
          <div class="flex flex-center">
            <p class="text-grey">{{ $t("No available data") }}</p>
          </div>
        </template>
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
          :disable="hasSelected"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "PaydeliveryComponents",
  props: ["title", "label", "payment_code", "payment_credentials"],
  data() {
    return {
      show_modal: false,
      data: [],
      loading: false,
      payment_id: "",
    };
  },
  mounted() {
    this.getPaydelivery();
  },
  computed: {
    getData() {
      return this.data;
    },
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
    hasSelected() {
      if (this.payment_id > 0) {
        return false;
      }
      return true;
    },
  },
  methods: {
    showPaymentForm() {
      this.show_modal = true;
    },
    close() {
      this.show_modal = false;
    },
    onSubmit() {
      this.loading = true;
      APIinterface.fetchDataByTokenPost(
        "savedPaydelivery",
        "payment_id=" + this.payment_id + "&payment_code=" + this.payment_code
      )
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
    getPaydelivery() {
      APIinterface.fetchDataByTokenPost("getPaydelivery")
        .then((data) => {
          this.data = data.details.data;
        })
        .catch((error) => {})
        .then((data) => {});
    },
  },
};
</script>
