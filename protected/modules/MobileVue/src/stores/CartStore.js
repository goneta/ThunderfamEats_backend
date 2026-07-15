import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";
import { SessionStorage } from "quasar";
import { useDataStorePersisted } from "stores/DataStorePersisted";

const DataStorePersisted = useDataStorePersisted();

export const useCartStore = defineStore("cartstore", {
  state: () => ({
    loading: false,
    items_count: 0,
    cart_loading: true,
    cart_reloading: false,
    cart_uuid: "",
    cart_items: [],
    cart_summary: [],
    transaction_data: [],
    data: 0,
    cart_subtotal: 0,
    cart_total: 0,
    error: [],
    out_of_range: false,
    cart_merchant: [],
    address_component: [],
    phone_details: [],
    resp_opening: [],
    transaction_info: [],
    data_transaction: [],
    $q: undefined,
    tips_data: [],
    enabled_tip: false,
    enabled_voucher: false,
    cart_payload: [
      "items",
      "subtotal",
      "distance_local",
      "items_count",
      "merchant_info",
      "check_opening",
      "transaction_info",
    ],
    points_data: [],
  }),
  getters: {
    canProceed() {
      let $return = true;
      if (this.items_count <= 0) {
        $return = false;
      }
      if (this.out_of_range === true) {
        $return = false;
      }
      // Object.keys(data.details.data).length
      if (this.error.length > 0) {
        $return = false;
      }
      return $return;
    },
    hasData() {
      if (APIinterface.empty(this.cart_items)) {
        //
      } else {
        if (Object.keys(this.cart_items).length > 0) {
          return true;
        }
      }
      return false;
    },
    hasItem() {
      if (this.items_count > 0) {
        return true;
      }
      return false;
    },
    getErrorMsg() {
      let errorMsg = "";
      if (this.error.length > 0) {
        Object.entries(this.error).forEach(([key, items]) => {
          errorMsg += items + "\n";
        });
      }
      return errorMsg;
    },
  },
  actions: {
    // getCartCount() {
    //   const $params = {
    //     cart_uuid: APIinterface.getStorage("cart_uuid"),
    //     place_id: APIinterface.getStorage("place_id"),
    //     payload: ["subtotal", "distance_local", "items_count", "merchant_info"],
    //   };
    //   this.loading = true;
    //   APIinterface.getCart($params, true)
    //     .then((data) => {
    //       this.data = data.details;
    //     })
    //     .catch((error) => {
    //       this.data = [];
    //     })
    //     .then((data) => {
    //       this.loading = false;
    //     });
    // },
    getCount() {
      const $params = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        place_id: APIinterface.getStorage("place_id"),
        payload: ["items_count"],
      };
      this.loading = true;
      APIinterface.getCart($params, true)
        .then((data) => {
          this.items_count = data.details.items_count;
        })
        .catch((error) => {
          this.items_count = 0;
        })
        .then((data) => {
          this.loading = false;
        });
    },
    getCart(cardLoading, payload) {
      const $data = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        place_id: APIinterface.getStorage("place_id"),
        currency_code: DataStorePersisted.getUseCurrency(),
        payload,
        choosen_delivery: APIinterface.getStorage("choosen_delivery"),
      };

      if (cardLoading) {
        this.cart_loading = true;
      } else {
        this.cart_reloading = true;
      }

      let $isCheckout = true;
      if (auth.authenticated()) {
        $isCheckout = false;
      }

      APIinterface.getCart($data, $isCheckout)
        .then((data) => {
          this.items_count = data.details.items_count;
          this.cart_uuid = data.details.cart_uuid;
          this.cart_items = data.details.data.items;
          this.cart_summary = data.details.data.summary;
          this.cart_total = data.details.data.total;
          this.error = data.details.error;
          this.cart_subtotal = data.details.data.subtotal;
          this.out_of_range = data.details.out_of_range;
          this.transaction_info = data.details.transaction_info;
          this.data_transaction = data.details.data_transaction;
          this.tips_data = data.details.tips_data;
          this.enabled_tip = data.details.enabled_tip;
          this.enabled_voucher = data.details.enabled_voucher;

          if (!APIinterface.empty(data.details.data.merchant)) {
            this.cart_merchant = data.details.data.merchant;
            SessionStorage.set("cart_merchant_slug", this.cart_merchant.slug);
          }

          this.address_component = data.details.address_component;
          this.phone_details = data.details.phone_details;
          this.resp_opening = data.details.resp_opening;

          this.points_data = data.details.points_data;
        })
        // eslint-disable-next-line
        .catch((error) => {
          this.resetData();
        })
        .then((data) => {
          this.cart_loading = false;
          this.cart_reloading = false;
        });
    },
    resetData() {
      this.items_count = 0;
      this.cart_items = [];
      this.cart_summary = [];
      this.cart_total = [];
      this.error = [];
      this.cart_subtotal = [];
      this.cart_merchant = [];
      this.address_component = [];
      this.phone_details = [];
      this.transaction_info = [];
      this.data_transaction = [];
      this.tips_data = [];
      this.enabled_tip = false;
      this.enabled_voucher = false;
    },
    hadItem() {
      if (this.items_count > 0) {
        return true;
      }
      return false;
    },
    hadData() {
      if (APIinterface.empty(this.cart_items)) {
        return false;
      } else {
        if (Object.keys(this.cart_items).length > 0) {
          return true;
        }
      }
      return false;
    },
    canCheckout() {
      let $return = true;
      if (this.items_count <= 0) {
        $return = false;
      }
      if (this.out_of_range === true) {
        $return = false;
      }
      if (this.error.length > 0) {
        $return = false;
      }
      return $return;
    },
    getError() {
      let errorMsg = "";
      if (this.error.length > 0) {
        Object.entries(this.error).forEach(([key, items]) => {
          errorMsg += items + "\n";
        });
      }
      return errorMsg;
    },
  },
});
