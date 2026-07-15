import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const usePaymentStore = defineStore("paymentstore", {
  state: () => ({
    loading: false,
    loading2: false,
    data: {},
    credentials: {},
    payment_uuid: {},
    payment_list: [],
    payment_credentials: [],
    data2: [],
  }),

  getters: {
    hasData() {
      if (APIinterface.empty(this.payment_list)) {
        return false;
      } else {
        if (Object.keys(this.payment_list).length > 0) {
          return true;
        }
      }
      return false;
    },
    getPaymentList(state) {
      return state.data2;
    },
  },

  actions: {
    SavedPaymentList(merchantID) {
      this.loading = true;
      APIinterface.SavedPaymentList(APIinterface.getStorage("cart_uuid"))
        .then((data) => {
          this.data[merchantID] = data.details.data;
          this.data2 = data.details.data;
          this.credentials[merchantID] = data.details.credentials;
          this.payment_uuid[merchantID] = data.details.default_payment_uuid;
        })
        .catch((error) => {
          this.data = {};
          this.data2 = [];
          this.credentials = {};
          this.payment_uuid = {};
        })
        .then((data) => {
          this.loading = false;
        });
    },
    PaymentMethod(done, params) {
      if (APIinterface.empty(done)) {
        this.loading2 = true;
      }
      APIinterface.fetchDataByTokenPost("PaymentMethod", params)
        .then((data) => {
          this.payment_list = data.details.data;
          this.payment_credentials = data.details.credentials;
        })
        .catch((error) => {
          this.payment_list = [];
          this.payment_credentials = [];
        })
        .then((data) => {
          this.loading2 = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    hadData() {
      if (APIinterface.empty(this.payment_list)) {
        return false;
      } else {
        if (Object.keys(this.payment_list).length > 0) {
          return true;
        }
      }
      return false;
    },
  },
});
