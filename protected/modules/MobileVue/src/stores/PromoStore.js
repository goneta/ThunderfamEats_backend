import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";
import { useDataStorePersisted } from "stores/DataStorePersisted";

const DataStorePersisted = useDataStorePersisted();

export const usePromoStore = defineStore("promostore", {
  state: () => ({
    data: {},
    loading: true,
    promo_selected: {},
    total_promo: {},
  }),
  persist: true,
  getters: {
    hasData() {
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
  actions: {
    loadPromo(merchantID) {
      let currency_code = DataStorePersisted.getUseCurrency();
      this.loading = true;
      APIinterface.fetchDataPost(
        "loadPromo",
        "merchant_id=" +
          merchantID +
          "&cart_uuid=" +
          APIinterface.getStorage("cart_uuid") +
          "&currency_code=" +
          currency_code
      )
        .then((data) => {
          this.data[merchantID] = data.details.data;
          this.promo_selected[merchantID] = data.details.promo_selected;
          this.total_promo[merchantID] = data.details.count;
        })
        .catch((error) => {
          this.data = [];
          this.promo_selected = [];
          this.total_promo = [];
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
