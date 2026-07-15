import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

let $openingHours = [];
let $openingDates = [];

export const useDeliveryschedStore = defineStore("deliverysched", {
  state: () => ({
    loading: false,
    loading_sched: false,
    transaction_type: "",
    transaction_list: [],
    delivery_options: [],
    trans_data: [],
    delivery_date_list: [],
    delivery_time_list: [],
    delivery_date: "",
    delivery_time: "",
    whento_deliver: "now",
    new_transaction_type: "",
    new_whento_deliver: "",
    filters: {},
    main_layout_header: true,
    data: [],
  }),
  persist: true,
  actions: {
    getDeliverySched(cartUuid, Slug, queryType) {
      this.loading_sched = true;
      this.transaction_list = [];
      this.delivery_options = [];

      APIinterface.fetchDataPost(
        "getDeliveryDetails",
        "cart_uuid=" +
          cartUuid +
          "&slug=" +
          Slug +
          "&transaction_type=" +
          this.transaction_type +
          "&query_type=" +
          queryType
      )
        .then((data) => {
          this.transaction_type = data.details.transaction_type;
          this.data = data.details.data;
          if (Object.keys(data.details.data).length > 0) {
            Object.entries(data.details.data).forEach(([key, items]) => {
              this.transaction_list.push({
                label: items.service_name,
                value: items.service_code,
              });
            });
          }

          if (Object.keys(data.details.delivery_option).length > 0) {
            Object.entries(data.details.delivery_option).forEach(
              ([key, items]) => {
                this.delivery_options.push({
                  label: items.name,
                  value: items.value,
                });
              }
            );
          }

          this.whento_deliver = data.details.whento_deliver;
          this.trans_data = {
            delivery_date: data.details.delivery_date,
            delivery_date_pretty: data.details.delivery_date_pretty,
            delivery_time: data.details.delivery_time,
            whento_deliver: data.details.whento_deliver,
          };

          //
        })
        .catch((error) => {
          this.transaction_list = [];
        })
        .then((data) => {
          this.loading_sched = false;
        });
    },
    hadTransactionList() {
      if (APIinterface.empty(this.transaction_list)) {
        return false;
      } else {
        if (Object.keys(this.transaction_list).length > 0) {
          return true;
        }
      }
      return false;
    },
    clearData() {
      this.delivery_date_list = [];
      this.delivery_time_list = [];
    },
    getDeliveryTimes(slug) {
      this.clearData();
      this.loading = true;
      APIinterface.fetchDataPost(
        "getDeliveryTimes",
        "cart_uuid=" + APIinterface.getStorage("cart_uuid") + "&slug=" + slug
      )
        .then((data) => {
          if (Object.keys(data.details.opening_hours).length > 0) {
            Object.entries(data.details.opening_hours.dates).forEach(
              ([key, items]) => {
                this.delivery_date_list.push({
                  label: items.name,
                  value: items.value,
                });
              }
            );
          }

          $openingDates = data.details.opening_hours.dates;
          $openingHours = data.details.opening_hours.time_ranges;

          const keys = Object.keys($openingDates);
          this.delivery_date = keys[0];

          this.getTimeList(this.delivery_date);
          if (!APIinterface.empty($openingHours[this.delivery_date])) {
            const keystime = $openingHours[this.delivery_date][0];
            this.delivery_time = {
              label: keystime.pretty_time,
              value: keystime.end_time,
              start_time: keystime.start_time,
              end_time: keystime.end_time,
              pretty_time: keystime.pretty_time,
            };
          }

          if (!APIinterface.empty(this.trans_data.delivery_date)) {
            this.delivery_date = this.trans_data.delivery_date;
            this.delivery_time = this.trans_data.delivery_time;
            // const temptime = JSON.parse(this.trans_data.delivery_time);
            // if (Object.keys(temptime).length > 0) {
            //   this.delivery_time = temptime;
            // }
          }

          //
        })
        .catch((error) => {
          console.debug(error);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    getTimeList(dateSelected) {
      if (!APIinterface.empty($openingHours[dateSelected])) {
        Object.entries($openingHours[dateSelected]).forEach(([key, items]) => {
          this.delivery_time_list.push({
            label: items.pretty_time,
            value: items.end_time,
            start_time: items.start_time,
            end_time: items.end_time,
            pretty_time: items.pretty_time,
          });
        });
      }
    },
  },
});
