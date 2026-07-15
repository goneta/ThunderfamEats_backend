<template>
  <q-dialog v-model="show_modal" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("Add a promo") }}
        </q-toolbar-title>
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
      <q-card-section v-if="enabled_voucher == 1">
        <q-form @submit="onApplyPromocode">
          <div class="row items-center q-col-gutter-sm">
            <div class="col b">
              <q-input
                v-model="promo_code"
                :label="$t('Enter promo code')"
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
              />
            </div>
            <div class="col-4">
              <q-btn
                :loading="loading_apply"
                type="submit"
                unelevated
                color="primary"
                text-color="white"
                no-caps
                class="full-width"
                :label="$t('Apply')"
                size="lg"
              />
            </div>
          </div>
        </q-form>

        <h6 class="text-weight-bold q-ma-none q-pt-md">
          {{ $t("Available promotions") }}
        </h6>
      </q-card-section>

      <q-list separator>
        <template
          v-for="items in PromoStore.data[merchant_id]"
          :key="items.promo_id"
        >
          <q-item
            clickable
            v-ripple
            class="q-mb-sm"
            @click.stop="usePromo(items)"
          >
            <q-item-section>
              <q-item-label>{{ items.title }}</q-item-label>
              <q-item-label caption class="font11">{{
                items.sub_title
              }}</q-item-label>
              <q-item-label caption class="font11">{{
                items.valid_to
              }}</q-item-label>
            </q-item-section>
            <q-item-section side>
              <q-btn
                @click.stop="usePromo(items)"
                :loading="loading"
                flat
                :color="$q.dark.mode ? 'secondary' : 'blue'"
                no-caps
                :label="$t('Use now')"
                dense
                size="md"
              />
            </q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { usePromoStore } from "stores/PromoStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";

export default {
  name: "PromoList",
  props: ["merchant_id", "enabled_voucher"],
  data() {
    return {
      promo_code: "",
      show_modal: false,
      loading_apply: false,
      loading: false,
    };
  },
  setup() {
    const PromoStore = usePromoStore();
    const DataStorePersisted = useDataStorePersisted();
    return { PromoStore, DataStorePersisted };
  },
  updated() {
    this.promo_code = "";
  },
  created() {
    this.PromoStore.loadPromo(this.merchant_id);
    // if (Object.keys(this.PromoStore.data).length <= 0) {
    //   this.PromoStore.loadPromo(this.merchant_id);
    // } else {
    //   if (!this.PromoStore.data[this.merchant_id]) {
    //     this.PromoStore.loadPromo(this.merchant_id);
    //   }
    // }
  },
  methods: {
    showModal(data) {
      this.show_modal = data;
    },
    usePromo(data) {
      this.loading = true;

      const $params = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        promo_id: data.promo_id,
        promo_type: data.promo_type,
        currency_code: this.DataStorePersisted.getUseCurrency(),
      };
      APIinterface.applyPromo($params)
        .then((data) => {
          this.show_modal = false;
          this.PromoStore.loadPromo(this.merchant_id);
          this.$emit("afterApplypromo");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    removePromo(data) {
      this.loading = true;
      const $params = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        promo_id: data.promo_id,
        promo_type: data.promo_type,
      };
      APIinterface.removePromo($params)
        .then((data) => {
          this.show_modal = false;
          this.promo_selected = [];
          this.PromoStore.loadPromo(this.merchant_id);
          this.$emit("afterRemovepromo");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    onApplyPromocode() {
      this.loading_apply = true;
      const $params = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        promo_code: this.promo_code,
      };
      APIinterface.applyPromoCode($params)
        .then((data) => {
          this.show_modal = false;
          this.promo_code = "";
          this.PromoStore.loadPromo(this.merchant_id);
          this.$emit("afterApplypromo");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading_apply = false;
        });
    },
  },
};
</script>
