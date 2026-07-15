import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const useStoreOpen = defineStore("store_open", {
  state: () => ({
    loading: true,
    message: [],
    store_close: false,
    next_opening: "",
  }),
  actions: {
    checkStoreOpen() {
      this.loading = true;
      const $cartUiid = APIinterface.getStorage("cart_uuid");
      APIinterface.checkStoreOpen($cartUiid)
        .then((data) => {
          this.message = data.msg;
          if (data.details.merchant_open_status <= 0) {
            this.store_close = true;
          } else {
            this.store_close = true;
          }
        })
        // eslint-disable-next-line
        .catch((error) => {})
        .then((data) => {
          this.loading = false;
        });
    },
    checkStoreOpen2(data) {
      this.loading = true;

      let ChoosenDelivery = APIinterface.getStorage("choosen_delivery");

      APIinterface.fetchData("checkStoreOpen2", {
        slug: data,
        choosen_delivery: ChoosenDelivery,
      })
        .then((data) => {
          this.message = data.msg;

          this.next_opening = data.details.next_opening;
          if (data.details.merchant_open_status <= 0) {
            this.store_close = true;
          } else {
            this.store_close = false;
          }

          if (data.details.time_already_passed) {
            APIinterface.setStorage("choosen_delivery", "");
          }
        })
        // eslint-disable-next-line
        .catch((error) => {
          this.message = error;
          this.store_close = true;
          this.next_opening = "";
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
});
