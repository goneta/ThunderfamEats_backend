import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const useFavoriteStore = defineStore("favorite", {
  state: () => ({
    loading: false,
    items_data: {},
    items_done: false,
  }),

  getters: {
    hasData() {
      if (APIinterface.empty(this.items_data)) {
        return false;
      } else {
        if (Object.keys(this.items_data).length > 0) {
          return true;
        }
      }
      return false;
    },
  },

  actions: {
    getItemFavorites(merchantSlug) {
      this.loading = true;
      APIinterface.fetchDataByTokenPost(
        "getItemFavorites",
        "slug=" + merchantSlug
      )
        .then((data) => {
          this.items_data[merchantSlug] = data.details.item_ids;
        })
        .catch((error) => {
          this.items_data[merchantSlug] = {};
        })
        .then((data) => {
          this.loading = false;
          this.items_done = true;
        });
    },
    hadData() {
      if (APIinterface.empty(this.items_data)) {
        return false;
      } else {
        if (Object.keys(this.items_data).length > 0) {
          return true;
        }
      }
      return false;
    },
  },
});
