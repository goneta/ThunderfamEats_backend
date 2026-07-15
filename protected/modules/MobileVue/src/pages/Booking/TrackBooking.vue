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
          v-if="hasSlug"
          :to="{
            name: 'menu',
            params: { slug: this.slug },
          }"
          flat
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
        <q-btn
          v-else
          @click="$router.back()"
          flat
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
        <q-toolbar-title class="text-weight-bold">{{
          $t("Track Booking")
        }}</q-toolbar-title>
        <template v-if="BookingStore.hasBookingData">
          <q-btn
            v-if="BookingStore.CanCancelReservation"
            dense
            flat
            unelevated
            color="red-5"
            round
            icon="las la-trash"
            class="q-ml-md"
            :to="{
              path: '/booking/cancel',
              query: { id: this.reservation_uuid },
            }"
          />
        </template>
      </q-toolbar>
    </q-header>
    <q-page
      class="q-pl-md q-pr-md"
      :class="{ 'flex flex-center': BookingStore.getErrors }"
    >
      <template v-if="BookingStore.getErrors">
        <div>
          {{ BookingStore.getErrors }}
        </div>
      </template>
      <template v-if="BookingStore.hasBookingData">
        <div
          class="q-pa-sm bg-mygreyx radius10"
          :class="{
            'bg-grey600 ': $q.dark.mode,
            'bg-mygrey ': !$q.dark.mode,
          }"
        >
          <q-stepper
            v-model="steps"
            ref="stepper"
            contracted
            animated
            flat
            class="bg-transparent"
            done-color="primary"
            active-color="primary"
            :inactive-color="$q.dark.mode ? 'grey300' : 'white'"
          >
            <q-step
              :name="1"
              icon="pending_actions"
              active-icon="pending_actions"
              done-icon="pending_actions"
              done-color="green"
              color="grey300"
              :done="
                BookingStore.getBookingStatusSteps >= 1 ||
                BookingStore.getBookingStatusSteps <= 0
              "
            />
            <q-step
              :name="2"
              icon="restaurant"
              active-icon="restaurant"
              done-icon="restaurant"
              :done-color="
                BookingStore.getBookingStatusSteps >= 2 ? 'primary' : 'red'
              "
              color="grey300"
              :done="
                BookingStore.getBookingStatusSteps >= 2 ||
                BookingStore.getBookingStatusSteps <= 0
              "
            />
            <q-step
              :name="3"
              icon="flag"
              active-icon="flag"
              done-icon="flag"
              :done-color="
                BookingStore.getBookingStatusSteps >= 3 ? 'primary' : 'red'
              "
              color="grey300"
              :done="
                BookingStore.getBookingStatusSteps >= 3 ||
                BookingStore.getBookingStatusSteps <= 0
              "
            />
          </q-stepper>
        </div>

        <template v-if="BookingStore.getBookingStatusSteps <= 0">
          <div class="text-center">
            <div class="q-pa-sm" :class="BookingStore.bookingStatusColor">
              {{ BookingStore.booking_data.data.status_pretty }}
            </div>
          </div>
        </template>
        <template v-else>
          <q-space class="q-pa-sm"></q-space>
        </template>

        <q-list>
          <q-item>
            <q-item-section avatar>
              <q-avatar rounded>
                <q-img
                  :src="BookingStore.getBooking.merchant.logo"
                  spinner-size="xs"
                  spinner-color="primary"
                  style="width: 80px; height: 80px"
                  fit="cover"
                ></q-img>
              </q-avatar>
            </q-item-section>
            <q-item-section top>
              <q-item-label>{{
                BookingStore.getBooking.merchant.restaurant_name
              }}</q-item-label>
              <q-item-label caption>
                {{ BookingStore.getBooking.merchant.address }}
              </q-item-label>
            </q-item-section>
          </q-item>
        </q-list>
        <q-space class="q-pa-sm"></q-space>
        <div class="q-pa-sm radius10">
          <div
            class="text-weight-medium font15 q-pa-sm bg-mygreyx radius10"
            :class="{
              'bg-grey600 ': $q.dark.mode,
              'bg-mygrey ': !$q.dark.mode,
            }"
          >
            {{ $t("Reservation details") }}
          </div>
          <q-list separator>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Reservation ID") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.reservation_id }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Guest") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.guest_number }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Date") }} :</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.reservation_date }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Time") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.reservation_time }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
          <q-space class="q-pa-sm"></q-space>

          <div
            class="text-weight-medium font15 q-pa-sm bg-mygreyx radius10"
            :class="{
              'bg-grey600 ': $q.dark.mode,
              'bg-mygrey ': !$q.dark.mode,
            }"
          >
            {{ $t("Your Details") }}
          </div>
          <q-list separator>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Name") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.full_name }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Email address") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.email_address }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Contact number") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.contact_phone }}
                </q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="q-pa-none">
              <q-item-section>
                <q-item-label>{{ $t("Special request") }}</q-item-label>
                <q-item-label caption>
                  {{ BookingStore.getBooking.data.special_request }}
                </q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </div>

        <q-footer
          reveal
          class="bg-primary text-dark"
          :class="{
            'bg-mydark ': $q.dark.mode,
            'bg-white ': !$q.dark.mode,
          }"
          v-if="BookingStore.CanCancelReservation"
        >
          <q-btn
            unelevated
            color="primary"
            :label="$t('Modify Reservation')"
            class="radius20 fit"
            no-caps
            size="lg"
            :to="{
              path: '/booking/update',
              query: { id: this.reservation_uuid },
            }"
          />
        </q-footer>
      </template>

      <template v-if="BookingStore.isLoading">
        <q-inner-loading
          :showing="true"
          :color="$q.dark.mode ? 'grey300' : 'primary'"
        />
      </template>

      <ComponentsRealtime
        ref="realtime"
        getevent="notification_event"
        @after-receive="afterReceive"
      />
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { defineAsyncComponent } from "vue";
import { useBookingStore } from "stores/BookingStore";
import APIinterface from "src/api/APIinterface";

export default {
  name: "TrackBooking",
  components: {
    ComponentsRealtime: defineAsyncComponent(() =>
      import("components/ComponentsRealtime.vue")
    ),
  },
  data() {
    return {
      reservation_uuid: "",
      steps: 1,
      slug: "",
    };
  },
  setup() {
    const BookingStore = useBookingStore();
    return { BookingStore };
  },
  created() {
    this.reservation_uuid = this.$route.query.id;
    this.slug = this.$route.query.slug;
    this.BookingDetails();
  },
  computed: {
    hasSlug() {
      if (APIinterface.empty(this.slug)) {
        return false;
      }
      return true;
    },
  },
  methods: {
    refresh(done) {
      this.BookingStore.getBookingDetails(this.reservation_uuid, done);
    },
    BookingDetails() {
      this.BookingStore.getBookingDetails(this.reservation_uuid, null);
    },
    afterReceive(data) {
      if (data.notification_type == "booking") {
        this.BookingDetails();
      }
    },
  },
};
</script>
