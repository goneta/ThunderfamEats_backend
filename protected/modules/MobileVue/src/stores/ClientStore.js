import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const useClientStore = defineStore("client", {
  state: () => ({
    loading: false,
    data: [],
    user_settings: [],
    push_off: false,
    push_notifications: false,
  }),

  persist: true,

  getters: {
    hasData() {
      if (APIinterface.empty(this.data)) {
        //
      } else {
        if (Object.keys(this.data).length > 0) {
          return true;
        }
      }
      return false;
    },
  },

  actions: {
    getAddress() {
      this.loading = true;
      APIinterface.clientAddresses()
        .then((data) => {
          this.data = data.details.addresses;
        })
        .catch((error) => {
          this.data = [];
        })
        .then((data) => {
          this.loading = false;
        });
    },
    hadData() {
      if (APIinterface.empty(this.data)) {
        return false;
      } else {
        if (Object.keys(this.data).length > 0) {
          return true;
        }
      }
      return false;
    },
  },
});
