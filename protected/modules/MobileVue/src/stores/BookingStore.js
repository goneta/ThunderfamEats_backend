import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const useBookingStore = defineStore("BookingStore", {
  state: () => ({
    has_data: false,
    loading: true,
    guest_list: [],
    date_list: [],
    time_slot: [],
    all_time_slot: [],
    reservation_date: "",
    tc: "",
    allowed_choose_table: false,
    room_list: [],
    not_available_time: [],
    guest: 1,
    reservation_time: "",
    reservation_uuid: "",
    steps: 1,
    reservation_info: [],
    table_list: [],
    room_uuid: "",
    table_uuid: "",
    first_name: "",
    last_name: "",
    email_address: "",
    mobile_number: "",
    mobile_prefix: "",
    special_request: "",
    booking_data: [],
    cancel_reason: [],
    merchant_uuid: "",
    errors: "",
    summary: [],
    summary_loading: false,
  }),

  getters: {
    getSummary(state) {
      return state.summary;
    },
    hasError(state) {
      if (APIinterface.empty(state.errors)) {
        return true;
      }
      return false;
    },
    getErrors(state) {
      return state.errors;
    },
    hasData(state) {
      return state.has_data;
    },
    isLoading(state) {
      return state.loading;
    },
    hasTimeSlot(state) {
      if (Object.keys(state.time_slot).length > 0) {
        return true;
      }
      return false;
    },
    getTimeList(state) {
      return state.time_slot;
    },
    bookingValid() {
      let $pass = true;
      if (this.guest <= 0) {
        $pass = false;
      }
      if (APIinterface.empty(this.reservation_date)) {
        $pass = false;
      }
      if (APIinterface.empty(this.reservation_time)) {
        $pass = false;
      }
      return $pass;
    },
    getSteps(state) {
      return state.steps;
    },
    getBooking(state) {
      return state.booking_data;
    },
    hasBookingData(state) {
      if (Object.keys(state.booking_data).length > 0) {
        return true;
      }
      return false;
    },
    getCancelReasonData(state) {
      return state.cancel_reason;
    },
    CanCancelReservation(state) {
      if (
        state.booking_data.cancel_reservation_stats.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return false;
      }
      return true;
    },
    bookingStatusColor(state) {
      if (
        state.booking_data.cancel_reservation_stats2.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return "text-red";
      } else if (
        state.booking_data.pending_reservation_stats.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return "text-blue";
      } else if (
        state.booking_data.completed_reservation_stats.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return "text-orange";
      } else {
        return "text-green";
      }
    },
    getBookingStatusSteps(state) {
      if (
        state.booking_data.cancel_reservation_stats2.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return 0;
      }

      if (
        state.booking_data.confirm_reservation_stats.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return 2;
      } else if (
        state.booking_data.completed_reservation_stats.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return 3;
      } else if (
        state.booking_data.cancel_reservation_stats.includes(
          state.booking_data.data.status
        ) === true
      ) {
        return 4;
      } else {
        return 1;
      }
    },
  },

  actions: {
    Getbookingattributes(merchant_uuid, id) {
      this.loading = true;
      APIinterface.fetchDataPostTable(
        "Getbookingattributes",
        "merchant_uuid=" + merchant_uuid + "&id=" + id
      )
        .then((response) => {
          this.has_data = true;
          this.guest_list = response.details.guest_list;
          this.date_list = response.details.date_list;
          this.time_slot = response.details.time_slot;
          this.all_time_slot = response.details.all_time_slot;
          this.reservation_date = response.details.default_date;
          this.tc = response.details.tc;
          this.allowed_choose_table = response.details.allowed_choose_table;
          this.room_list = response.details.room_list;
          this.not_available_time = response.details.not_available_time;
          this.guest = response.details.default_guest;

          if (!APIinterface.empty(id)) {
            this.guest = response.details.data_booking.guest_number_raw;
            this.reservation_time =
              response.details.data_booking.reservation_time_raw;
            this.first_name = response.details.data_booking.first_name;
            this.last_name = response.details.data_booking.last_name;
            this.email_address = response.details.data_booking.email_address;
            this.special_request =
              response.details.data_booking.special_request;

            this.mobile_prefix = response.details.data_booking.phone_prefix;
            this.mobile_number =
              response.details.data_booking.contact_phone_without_prefix;
          }
        })
        .catch((error) => {
          this.guest_list = [];
          this.date_list = [];
          this.time_slot = [];
          this.all_time_slot = [];
          this.reservation_date = "";
          this.tc = "";
          this.allowed_choose_table = false;
          this.room_list = [];
          this.not_available_time = [];
        })
        .then((data) => {
          this.loading = false;
        });
    },
    Getbookingdetails(id) {
      this.loading = true;
      this.errors = "";
      APIinterface.fetchDataPostTable2("Getbookingdetails", "&id=" + id)
        .then((response) => {
          this.has_data = true;
          this.guest_list = response.details.guest_list;
          this.date_list = response.details.date_list;
          this.time_slot = response.details.time_slot;
          this.all_time_slot = response.details.all_time_slot;
          this.reservation_date = response.details.default_date;
          this.tc = response.details.tc;
          this.allowed_choose_table = response.details.allowed_choose_table;
          this.room_list = response.details.room_list;
          this.not_available_time = response.details.not_available_time;
          this.guest = response.details.default_guest;
          this.merchant_uuid = response.details.merchant_uuid;

          if (!APIinterface.empty(id)) {
            this.guest = response.details.data_booking.guest_number_raw;
            this.reservation_time =
              response.details.data_booking.reservation_time_raw;
            this.first_name = response.details.data_booking.first_name;
            this.last_name = response.details.data_booking.last_name;
            this.email_address = response.details.data_booking.email_address;
            this.special_request =
              response.details.data_booking.special_request;

            this.mobile_prefix = response.details.data_booking.phone_prefix;
            this.mobile_number =
              response.details.data_booking.contact_phone_without_prefix;
          }
        })
        .catch((error) => {
          this.guest_list = [];
          this.date_list = [];
          this.time_slot = [];
          this.all_time_slot = [];
          this.reservation_date = "";
          this.tc = "";
          this.allowed_choose_table = false;
          this.room_list = [];
          this.not_available_time = [];
          this.errors = error;
        })
        .then((data) => {
          this.loading = false;
        });
    },
    getTimeslot(merchant_uuid, id) {
      this.loading = true;
      this.reservation_time = "";
      let $params = "merchant_uuid=" + merchant_uuid;
      $params += "&reservation_date=" + this.reservation_date;
      $params += "&guest=" + this.guest;
      $params += "&id=" + id;

      APIinterface.fetchDataPostTable("Gettimeslot", $params)
        .then((response) => {
          this.time_slot = response.details.time_slot;
          this.all_time_slot = response.details.all_time_slot;
          this.not_available_time = response.details.not_available_time;
        })
        .catch((error) => {
          this.time_slot = [];
          this.all_time_slot = [];
          this.not_available_time = [];
        })
        .then((data) => {
          this.loading = false;
        });
    },
    isSelected(data) {
      if (this.reservation_time == data) {
        return true;
      } else {
        return false;
      }
    },
    isNotavailable(bookingTime) {
      if (Object.keys(this.not_available_time).length > 0) {
        if (this.not_available_time.includes(bookingTime) === true) {
          return true;
        }
      }
      return false;
    },
    SetBooking(merchant_uuid, id, $q) {
      this.loading = true;
      let $params = "merchant_uuid=" + merchant_uuid;
      $params += "&reservation_date=" + this.reservation_date;
      $params += "&reservation_time=" + this.reservation_time;
      $params += "&guest=" + this.guest;
      $params += "&id=" + id;
      APIinterface.fetchDataPostTable2("SetBooking", $params)
        .then((response) => {
          this.steps = 2;
          this.reservation_info = response.details;
          this.table_list = response.details.table_list;
          if (!APIinterface.empty(id)) {
            this.room_uuid = response.details.room_uuid;
            this.table_uuid = response.details.table_uuid;
          } else {
            this.room_uuid = "";
            this.table_uuid = "";
          }

          if (!APIinterface.empty(response.details.user_data)) {
            if (Object.keys(response.details.user_data).length > 0) {
              this.first_name = response.details.user_data.first_name;
              this.last_name = response.details.user_data.last_name;
              this.email_address = response.details.user_data.email_address;
              this.mobile_prefix = response.details.user_data.phone_prefix;
              this.mobile_number =
                response.details.user_data.contact_number_without_prefix;
            }
          }
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", $q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    resetReservation(merchant_uuid) {
      this.steps = 1;
      this.Getbookingattributes(merchant_uuid);
      this.guest = 1;
      this.reservation_date = "";
      this.reservation_time = "";
    },
    getBookingDetails(id, done) {
      this.loading = true;
      this.errors = "";
      APIinterface.fetchDataPostTable2("BookingDetails", "id=" + id)
        .then((data) => {
          this.booking_data = data.details;
        })
        .catch((error) => {
          this.booking_data = [];
          this.errors = error;
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    getCancelreason(id, done) {
      this.loading = true;
      APIinterface.fetchDataPostTable("getCancelreason", "id=" + id)
        .then((data) => {
          this.cancel_reason = data.details.data;
        })
        .catch((error) => {
          this.cancel_reason = [];
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    getBookingSummary() {
      this.summary_loading = true;
      APIinterface.fetchDataPostTable2("BookingSummary")
        .then((data) => {
          this.summary = data.details.summary;
        })
        .catch((error) => {
          this.summary = [];
        })
        .then((data) => {
          this.summary_loading = false;
        });
    },
    //
  },
});
