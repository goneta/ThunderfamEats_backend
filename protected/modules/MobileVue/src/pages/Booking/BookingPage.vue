<template>
  <q-header
    reveal
    reveal-offset="10"
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-black': !$q.dark.mode,
    }"
  >
    <q-toolbar>
      <template v-if="BookingStore.steps != 3">
        <q-btn
          @click="
            BookingStore.steps == 1 ? $router.back() : (BookingStore.steps = 1)
          "
          flat
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
      </template>
      <q-toolbar-title
        v-if="BookingStore.steps != 3"
        class="text-weight-bold"
        >{{ $t("Table Booking") }}</q-toolbar-title
      >
    </q-toolbar>
  </q-header>
  <q-page class="q-pl-md q-pr-md">
    <template v-if="BookingStore.hasData">
      <template v-if="BookingStore.getSteps == 1">
        <q-select
          v-model="BookingStore.guest"
          :options="BookingStore.guest_list"
          @update:model-value="
            BookingStore.getTimeslot(MenuStore.merchant_uuid)
          "
          :label="$t('Guest')"
          color="primary"
          class="q-mb-md col-xs-12 col-sm-12 col-md-4"
          transition-show="scale"
          transition-hide="scale"
          emit-value
          outlined
          :bg-color="$q.dark.mode ? 'grey600' : 'input'"
          :label-color="$q.dark.mode ? 'grey300' : 'grey'"
        />

        <q-select
          v-model="BookingStore.reservation_date"
          :options="BookingStore.date_list"
          @update:model-value="
            BookingStore.getTimeslot(MenuStore.merchant_uuid)
          "
          :label="$t('Date')"
          color="primary"
          class="q-mb-md col-xs-12 col-sm-12 col-md-4"
          transition-show="scale"
          transition-hide="scale"
          emit-value
          map-options
          outlined
          :bg-color="$q.dark.mode ? 'grey600' : 'input'"
          :label-color="$q.dark.mode ? 'grey300' : 'grey'"
        />

        <q-input
          v-model="BookingStore.reservation_time"
          :label="$t('Time')"
          outlined
          :bg-color="$q.dark.mode ? 'grey600' : 'input'"
          :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          disable
        />

        <q-space class="q-pa-sm"></q-space>

        <div v-if="BookingStore.hasTimeSlot" class="row q-gutter-sm">
          <template v-for="items in BookingStore.getTimeList" :key="items">
            <template v-for="(item, index) in items" :key="item">
              <div class="col-2 text-center">
                <q-btn
                  unelevated
                  :label="item"
                  class="full-width"
                  :outline="BookingStore.isSelected(index) ? false : true"
                  :color="
                    BookingStore.isSelected(index)
                      ? $q.dark.mode
                        ? 'grey300'
                        : 'primary'
                      : BookingStore.isNotavailable(index)
                      ? 'grey'
                      : $q.dark.mode
                      ? 'grey300'
                      : 'black'
                  "
                  @click="BookingStore.reservation_time = index"
                  :disabled="BookingStore.isNotavailable(index)"
                />
              </div>
            </template>
          </template>
        </div>

        <template v-if="MenuStore.getBookingTc">
          <div class="text-weight-bold font15 q-mt-md">
            {{ $t("Restaurant Terms & Conditions") }}
          </div>
          <div class="text-grey" v-html="MenuStore.getBookingTc"></div>
        </template>

        <q-space class="q-pa-md"></q-space>
        <q-footer reveal class="bg-primary text-dark">
          <q-btn
            color="primary"
            unelevated
            no-caps
            class="fit"
            size="lg"
            :disabled="!BookingStore.bookingValid"
            :loading="BookingStore.loading"
            @click="SetBooking"
            >{{ $t("Continue") }}</q-btn
          >
        </q-footer>
      </template>
      <!-- end steps 1 -->

      <template v-if="BookingStore.getSteps == 2">
        <q-form @submit="onSubmit">
          <div class="q-mt-sm text-weight-bold font15">
            {{ $t("Reservation details") }}
          </div>
          <div class="text-grey">
            <div>{{ BookingStore.reservation_info.full_time }}</div>
            <div>{{ BookingStore.reservation_info.guest }}</div>
          </div>

          <div class="q-mt-sm text-weight-bold font15">
            {{ $t("Personal details") }}
          </div>

          <q-input
            v-model="BookingStore.first_name"
            :label="$t('First name')"
            lazy-rules
            :rules="[
              (val) => (val && val.length > 0) || $t('This field is required'),
            ]"
            outlined
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          />
          <q-input
            v-model="BookingStore.last_name"
            :label="$t('Last name')"
            lazy-rules
            :rules="[
              (val) => (val && val.length > 0) || $t('This field is required'),
            ]"
            outlined
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          />
          <q-input
            v-model="BookingStore.email_address"
            :label="$t('Email address')"
            lazy-rules
            :rules="[
              (val, rules) =>
                rules.email(val) || $t('Please enter a valid email address'),
            ]"
            outlined
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          />

          <q-input
            v-model="BookingStore.mobile_number"
            mask="##############"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            borderless
            class="input-borderless"
            :rules="[
              (val) =>
                (val && val.length > 0) || this.$t('This field is required'),
            ]"
          >
            <template v-slot:prepend>
              <q-select
                dense
                v-model="BookingStore.mobile_prefix"
                :options="DataStore.phone_prefix_data"
                @filter="filterFn"
                behavior="dialog"
                input-debounce="700"
                style="border: none"
                emit-value
                borderlessx
                class="myq-field"
              >
                <template v-slot:option="{ itemProps, opt }">
                  <q-item v-bind="itemProps">
                    <q-item-section avatar>
                      <q-img
                        :src="opt.flag"
                        style="height: 15px; max-width: 20px"
                      />
                    </q-item-section>
                    <q-item-section>
                      <q-item-label>{{ opt.label }}</q-item-label>
                    </q-item-section>
                  </q-item>
                </template>
                <template v-slot:no-option>
                  <q-item>
                    <q-item-section class="text-grey">
                      {{ $t("No results") }}
                    </q-item-section>
                  </q-item>
                </template>
              </q-select>
            </template>
          </q-input>

          <template v-if="MenuStore.allowChooseTable">
            <q-select
              v-model="BookingStore.room_uuid"
              :options="BookingStore.room_list"
              @update:model-value="BookingStore.table_uuid = ''"
              :label="$t('Room name')"
              transition-show="scale"
              transition-hide="scale"
              emit-value
              map-options
              :rules="[
                (val) =>
                  (val && val.length > 0) || $t('This field is required'),
              ]"
            />
            <q-select
              v-model="BookingStore.table_uuid"
              :options="BookingStore.table_list[BookingStore.room_uuid]"
              :label="$t('Table name')"
              transition-show="scale"
              transition-hide="scale"
              emit-value
              map-options
              :rules="[
                (val) =>
                  (val && val.length > 0) || $t('This field is required'),
              ]"
            />
          </template>

          <div class="q-mt-sm text-weight-medium font15">
            {{ $t("Special request") }}
          </div>

          <q-input
            v-model="BookingStore.special_request"
            autogrow
            color="warning"
            outlined
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          />

          <template v-if="MenuStore.isBookingCaptcha">
            <q-space class="q-pa-sm"></q-space>
            <componentsRecaptcha
              ref="recapcha"
              is_enabled="1"
              size="normal"
              theme="light"
              :tabindex="0"
              :sitekey="DataStore.getBookingSettings.site_key"
              :language_code="DataStore.getBookingSettings.language"
              @verify="recaptchaVerified"
              @expire="recaptchaExpired"
              @fail="recaptchaFailed"
            />
          </template>

          <p class="text-grey q-mt-md q-mb-md">
            {{
              $t(
                "By continuing, you agree to Terms of Service and Privacy Policy"
              )
            }}.
          </p>

          <q-footer reveal class="bg-primary text-dark">
            <q-btn
              type="submit"
              color="primary"
              unelevated
              no-caps
              class="fit"
              size="lg"
              :loading="loading"
              >{{ $t("Reserve") }}</q-btn
            >
          </q-footer>
        </q-form>
      </template>
      <!-- END STEPS 2 -->

      <template v-if="BookingStore.getSteps == 3">
        <div class="text-center q-pa-xl">
          <svg
            class="checkmark"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 52 52"
          >
            <circle
              class="checkmark__circle"
              cx="26"
              cy="26"
              r="25"
              fill="none"
            />
            <path
              class="checkmark__check"
              fill="none"
              d="M14.1 27.2l7.1 7.2 16.7-16.8"
            />
          </svg>

          <template v-if="MenuStore.getBookingCustomMessage">
            <h4 class="font16 q-mb-none">
              <span v-html="MenuStore.getBookingCustomMessage"></span>
            </h4>
          </template>
          <template v-else>
            <h4 class="font16 q-mb-none">
              {{ $t("Your reservation successfully placed") }}.
            </h4>
            <p class="text-grey">
              {{
                $t(
                  "You will receive another email once your reservation is confirm"
                )
              }}.
            </p>
          </template>
          <h5 class="font15 line-normal q-mb-none">
            {{ success_data.full_time }}
          </h5>
          <div class="text-grey">
            <div>{{ success_data.guest }}</div>
            <div>
              {{ $t("Reservation ID") }}#
              <span class="text-success">{{
                success_data.reservation_id
              }}</span>
            </div>
          </div>

          <q-space class="q-pa-md"></q-space>
          <q-btn
            type="submit"
            color="blue"
            unelevated
            no-caps
            flat
            :to="{
              name: 'menu',
              params: { slug: MenuStore.restaurant_slug },
            }"
            >{{ $t("Reserved again") }}</q-btn
          >

          <q-space class="q-pa-md"></q-space>
        </div>
        <q-footer reveal class="bg-primary text-dark">
          <q-btn
            color="primary"
            unelevated
            no-caps
            class="fit"
            size="lg"
            :to="{
              path: '/booking/track',
              query: {
                id: success_data.reservation_uuid,
                slug: MenuStore.restaurant_slug,
              },
            }"
            replace="true"
            >{{ $t("Track") }}</q-btn
          >
        </q-footer>
      </template>
      <!-- END STEPS 3 -->
    </template>
    <!-- end has booking data -->

    <template v-if="BookingStore.loading">
      <q-inner-loading
        :showing="true"
        :color="$q.dark.mode ? 'grey300' : 'primary'"
      />
    </template>
  </q-page>
</template>

<script>
import { defineAsyncComponent } from "vue";
import { useMenuStore } from "stores/MenuStore";
import { useBookingStore } from "stores/BookingStore";
import { useDataStore } from "stores/DataStore";
import APIinterface from "src/api/APIinterface";

export default {
  name: "BookingPage",
  components: {
    componentsRecaptcha: defineAsyncComponent(() =>
      import("components/componentsRecaptcha.vue")
    ),
  },
  data() {
    return {
      loading: false,
      recaptcha_response: "",
      success_data: [],
    };
  },
  setup() {
    const BookingStore = useBookingStore();
    const MenuStore = useMenuStore();
    const DataStore = useDataStore();
    return {
      MenuStore,
      BookingStore,
      DataStore,
    };
  },
  created() {
    this.BookingStore.steps = 1;
    this.BookingStore.reservation_date = "";
    this.BookingStore.reservation_time = "";
    this.Getbookingattributes();
  },
  watch: {
    DataStore: {
      immediate: true,
      deep: true,
      handler(newValue, oldValue) {
        if (Object.keys(newValue.phone_default_data).length > 0) {
          this.BookingStore.mobile_prefix =
            "+" + newValue.phone_default_data.phonecode;
        }
      },
    },
  },
  methods: {
    Getbookingattributes() {
      this.BookingStore.Getbookingattributes(this.MenuStore.merchant_uuid, "");
    },
    SetBooking() {
      this.BookingStore.SetBooking(this.MenuStore.merchant_uuid, "", this.$q);
    },
    recaptchaExpired() {
      if (APIinterface.empty(this.$refs.recapcha)) {
        this.$refs.recapcha.reset();
      }
    },
    recaptchaFailed() {},
    recaptchaVerified(response) {
      this.recaptcha_response = response;
    },
    onSubmit() {
      this.loading = true;
      let $params = "merchant_uuid=" + this.MenuStore.merchant_uuid;
      $params += "&reservation_date=" + this.BookingStore.reservation_date;
      $params += "&reservation_time=" + this.BookingStore.reservation_time;
      $params += "&guest=" + this.BookingStore.guest;
      $params += "&first_name=" + this.BookingStore.first_name;
      $params += "&last_name=" + this.BookingStore.last_name;
      $params += "&email_address=" + this.BookingStore.email_address;
      $params += "&mobile_prefix=" + this.BookingStore.mobile_prefix;
      $params += "&mobile_number=" + this.BookingStore.mobile_number;
      $params += "&room_uuid=" + this.BookingStore.room_uuid;
      $params += "&table_uuid=" + this.BookingStore.table_uuid;
      $params += "&special_request=" + this.BookingStore.special_request;
      $params += "&recaptcha_response=" + this.recaptcha_response;
      $params += "&id=" + this.BookingStore.reservation_uuid;

      APIinterface.fetchDataPostTable2("ReserveTable", $params)
        .then((response) => {
          this.success_data = response.details;
          this.BookingStore.steps = 3;
          this.BookingStore.room_uuid = "";
          this.BookingStore.table_uuid = "";
        })
        .catch((error) => {
          this.success_data = [];
          APIinterface.notify("dark", error, "error_outline", this.$q);
          this.recaptchaExpired();
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
