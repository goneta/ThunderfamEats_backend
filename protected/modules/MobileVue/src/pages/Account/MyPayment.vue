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
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :text-color="$q.dark.mode ? 'white' : 'dark'"
          unelevated
        />
        <q-toolbar-title class="text-weight-bold">{{
          $t("Payment")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>
    <q-page
      :class="{
        'flex flex-center': !hasData && !loading,
        'row items-stretch ': hasData && !loading,
        'bg-mydark': $q.dark.mode,
        'bg-grey-1': !$q.dark.mode,
      }"
    >
      <template v-if="!hasData && !loading">
        <div class="min-height-inherit flex flex-center">
          <div class="full-width text-center q-pb-xl">
            <div class="text-h5 text-weight-bold">
              {{ $t("No Payment available") }}
            </div>
            <p class="text-grey font12">
              {{ $t("you have not added payment yet") }}
            </p>
            <q-btn
              flat
              color="blue"
              no-caps
              :label="$t('Add new payment')"
              dense
              size="sm"
              to="/account/payments/new"
            />
          </div>
        </div>
      </template>
      <template v-else>
        <q-inner-loading
          v-if="loading"
          :showing="true"
          color="primary"
          size="md"
          label-class="dark"
          class="transparent"
        />

        <template v-else>
          <q-card
            flat
            class="radius8 full-width"
            :class="{
              'bg-mydark text-white': $q.dark.mode,
              'bg-white text-black': !$q.dark.mode,
            }"
          >
            <q-card-section>
              <q-list>
                <transition
                  v-for="(items, index) in data"
                  :key="items.payment_uuid"
                  appear
                  leave-active-class="animated fadeOut"
                  :class="{
                    'bg-mydark ': $q.dark.mode,
                    'bg-white ': !$q.dark.mode,
                  }"
                >
                  <q-slide-item
                    @action="deletePayment(index, items)"
                    :right-color="$q.dark.mode ? 'mydark' : 'white'"
                  >
                    <template v-slot:right>
                      <q-btn
                        unelevated
                        round
                        color="red-5"
                        text-color="white"
                        icon="eva-trash-outline"
                        dense
                      />
                    </template>
                    <q-item
                      @click.stop="setDefault(items.payment_uuid)"
                      tag="label"
                      clickable
                      v-ripple
                      class="border-grey radius10 q-mb-sm"
                      :class="{
                        'bg-dark text-white': $q.dark.mode,
                        'bg-white text-black': !$q.dark.mode,
                      }"
                    >
                      <q-item-section avatar>
                        <q-radio
                          v-model="payment_uuid"
                          :val="items.payment_uuid"
                          color="primary"
                          class="hidden"
                        />
                        <template v-if="items.logo_type === 'icon'">
                          <q-icon color="warning" name="credit_card" />
                        </template>
                        <template v-else>
                          <q-img
                            :src="items.logo_image"
                            fit="contain"
                            style="height: 30px; max-width: 30px"
                          />
                        </template>
                      </q-item-section>

                      <q-item-section>
                        <q-item-label lines="1">
                          <span class="text-weight-medium">{{
                            items.payment_name
                          }}</span>
                        </q-item-label>
                        <q-item-label caption lines="1">
                          {{ items.attr2 }}
                        </q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-radio
                          v-model="payment_uuid"
                          :val="items.payment_uuid"
                          @click.stop="setDefault(items.payment_uuid)"
                        />
                      </q-item-section>
                    </q-item>
                  </q-slide-item>
                </transition>
              </q-list>
            </q-card-section>
          </q-card>

          <q-footer
            reveal
            class="bg-grey-1 q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
          >
            <q-btn
              type="submit"
              :label="$t('Add new payment')"
              unelevated
              no-caps
              color="primary text-white"
              class="full-width text-weight-bold"
              size="lg"
              to="/account/payments/new"
              :loading="loading"
            />
          </q-footer>
        </template>
      </template>
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { defineAsyncComponent } from "vue";

export default {
  name: "MyPayment",
  // components: {
  //   NotiButton: defineAsyncComponent(() => import("components/NotiButton")),
  // },
  data() {
    return {
      data: [],
      loading: false,
      inner_loading: false,
      payment_data: [],
      payment_uuid: "",
    };
  },
  mounted() {
    this.MyPayments();
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    refresh(done) {
      this.MyPayments(done);
    },
    MyPayments(done) {
      if (APIinterface.empty(done)) {
        this.loading = true;
      }
      /* eslint-disable */
      APIinterface.MyPayments()
        .then((data) => {
          this.payment_uuid = data.details.default_payment_uuid;
          this.data = data.details.data;
        })
        .catch((error) => {
          this.data = [];
        })
        .then((data) => {
          if (!APIinterface.empty(done)) {
            done();
          } else {
            this.loading = false;
          }
        });
    },
    deletePayment(index, data) {
      this.inner_loading = true;
      APIinterface.deletePayment(data.payment_uuid)
        .then((data) => {
          this.data.splice(index, 1);
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.inner_loading = false;
        });
    },
    setDefault(paymentUuid) {
      APIinterface.showLoadingBox("", this.$q);
      APIinterface.setDefaultPayment(paymentUuid)
        .then((data) => {
          this.payment_uuid = paymentUuid;
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          APIinterface.hideLoadingBox(this.$q);
        });
    },
  },
};
</script>
