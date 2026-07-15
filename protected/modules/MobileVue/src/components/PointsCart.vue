<template>
  <template v-if="getData">
    <div class="relative-position">
      <div
        class="q-pl-md q-pr-md q-mt-sm ellipsis font13 text-weight-bold q-pt-xs border-grey-top"
      >
        <div class="font13">{{ $t("Points discount") }}</div>
      </div>

      <q-inner-loading :showing="loading" color="primary" size="md" />
      <q-list>
        <q-item clickable @click.stop="dialog = !dialog">
          <q-item-section avatar>
            <q-avatar color="secondary" size="md" text-color="white">
              <q-icon name="card_giftcard" size="21px"></q-icon>
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <q-item-label>
              <template v-if="data.discount > 0">
                {{ data.discount_label }}
              </template>
              <template v-else>
                {{ data.redeem_discount }}
              </template>
            </q-item-label>
            <q-item-label caption lines="2" v-if="data.discount <= 0">
              {{ data.redeem_label }}
            </q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn
              v-if="data.discount > 0"
              @click.stop="removePoints"
              flat
              :color="$q.dark.mode ? 'secondary' : 'blue'"
              no-caps
              :label="$t('Remove')"
              dense
              size="md"
              :loading="loading_remove"
            />
            <q-icon v-else name="navigate_next" />
          </q-item-section>
        </q-item>
      </q-list>
    </div>
  </template>

  <q-dialog v-model="dialog" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("Apply discount") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="dialog = !true"
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
      <q-card-section>
        <q-form @submit="applyPoints" class="q-gutter-sm">
          <div>
            <q-input
              v-model="points"
              :label="$t('Enter points to convert to discount')"
              outlined
              lazy-rules
              :bg-color="$q.dark.mode ? 'grey600' : 'input'"
              :label-color="$q.dark.mode ? 'grey300' : 'grey'"
              borderless
              class="input-borderless"
            />
          </div>
          <div>
            <q-btn
              :loading="loading_apply"
              :disable="points > 0 ? false : true"
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
        </q-form>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  props: ["currency_code"],
  name: "PointsCart",
  data() {
    return {
      loading: false,
      loading_apply: false,
      loading_remove: false,
      data: [],
      dialog: false,
      points: 0,
    };
  },
  mounted() {
    this.getCartpoints();
  },
  computed: {
    getData() {
      if (Object.keys(this.data).length > 0) {
        return this.data;
      }
      return false;
    },
  },
  methods: {
    getCartpoints() {
      this.loading = true;
      APIinterface.fetchDataByTokenPost(
        "getCartpoints",
        "cart_uuid=" +
          APIinterface.getStorage("cart_uuid") +
          "&currency_code=" +
          this.currency_code
      )
        .then((data) => {
          this.data = data.details;
        })
        .catch((error) => {
          this.data = [];
        })
        .then((data) => {
          this.loading = false;
        });
    },
    applyPoints() {
      this.loading_apply = true;
      APIinterface.fetchDataByTokenPost(
        "applyPoints",
        "cart_uuid=" +
          APIinterface.getStorage("cart_uuid") +
          "&currency_code=" +
          this.currency_code +
          "&points=" +
          this.points
      )
        .then((data) => {
          this.dialog = false;
          this.$emit("afterApplypoints");
          this.getCartpoints();
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading_apply = false;
        });
    },
    removePoints() {
      this.loading_remove = true;
      APIinterface.fetchDataByTokenPost(
        "removePoints",
        "cart_uuid=" + APIinterface.getStorage("cart_uuid")
      )
        .then((data) => {
          this.$emit("afterApplypoints");
          this.getCartpoints();
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading_remove = false;
        });
    },
  },
};
</script>
