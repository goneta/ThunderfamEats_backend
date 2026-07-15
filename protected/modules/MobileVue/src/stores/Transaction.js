import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const useTransactionStore = defineStore("transaction", {
  state: () => ({
    transaction_data: [],
    delivery_option: [],
    services_list: [],
    loading: false,
    transaction_type: "",
    transaction_list: [],
    new_transaction_type: "",
    filters: {},
  }),
  actions: {
    TransactionInfo() {
      const $params = {
        cart_uuid: APIinterface.getStorage("cart_uuid"),
        local_id: APIinterface.getStorage("place_id"),
        choosen_delivery: [],
      };
      this.loading = true;
      this.transaction_list = [];
      APIinterface.TransactionInfo($params)
        .then((data) => {
          this.transaction_data = data.details;
          this.delivery_option = data.details.delivery_option;
          this.services_list = data.details.services_list;
          this.transaction_type = data.details.transaction_type;

          if (Object.keys(data.details.services_list).length > 0) {
            Object.entries(data.details.services_list).forEach(
              ([key, items]) => {
                this.transaction_list.push({
                  label: items.service_name,
                  value: items.service_code,
                });
              }
            );
          }
        })
        .catch((error) => {
          console.debug(error);
          this.transaction_list = [];
        })
        .then((data) => {
          this.loading = false;
        });
    },
    hadData() {
      if (APIinterface.empty(this.transaction_data)) {
        return false;
      } else {
        if (Object.keys(this.transaction_data).length > 0) {
          return true;
        }
      }
      return false;
    },
  },
});
