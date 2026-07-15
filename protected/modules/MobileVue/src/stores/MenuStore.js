import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

import { useDataStorePersisted } from "stores/DataStorePersisted";
const DataStorePersisted = useDataStorePersisted();

export const useMenuStore = defineStore("menu", {
  state: () => ({
    data_info: {},
    data_share: [],
    open_at: {},
    opening_hours: {},
    money_config: [],
    maps_config: [],
    loadin_info: true,
    data_charge_type: {},
    data_estimation: {},
    data_distance: {},
    loading_menu: true,
    data_category: {},
    data_items: {},
    data_gallery: {},
    gallery_images: {},
    data_similar_items: {},
    loading_similar_items: false,
    items_not_available: [],
    category_not_available: [],
    merchant_uuid: "",
    restaurant_slug: "",
    booking_settings: [],
  }),

  persist: true,

  getters: {
    isBookingEnabled(state) {
      return state.booking_settings.booking_enabled;
    },
    getBookingTc(state) {
      return state.booking_settings.booking_reservation_terms;
    },
    getBookingCustomMessage(state) {
      return state.booking_settings.booking_reservation_custom_message;
    },
    allowChooseTable(state) {
      return state.booking_settings.allowed_choose_table;
    },
    isBookingCaptcha(state) {
      return state.booking_settings.captcha_enabled;
    },
  },

  actions: {
    getMerchantInfo(merchantSlug, currency_code) {
      this.loadin_info = true;

      this.data_info = [];
      this.data_charge_type = [];
      this.data_estimation = [];
      this.data_distance = [];
      this.opening_hours = [];
      this.open_at = [];
      this.data_gallery = [];
      this.merchant_uuid = "";
      this.booking_enabled = false;

      APIinterface.getMerchantInfo(
        merchantSlug,
        APIinterface.getStorage("place_id"),
        currency_code
      )
        .then((data) => {
          this.data_info[merchantSlug] = data.details.data;
          this.data_charge_type[merchantSlug] = data.details.charge_type;
          this.data_estimation[merchantSlug] = data.details.estimation;
          this.data_distance[merchantSlug] = data.details.distance;
          this.opening_hours[merchantSlug] = data.details.opening_hours;
          this.open_at[merchantSlug] = data.details.open_at;
          this.data_gallery[merchantSlug] = data.details.gallery;

          this.money_config = data.details.config;
          this.merchant_uuid = data.details.data.merchant_uuid;
          this.booking_settings = data.details.booking_settings;

          let Gallery = [];
          if (Object.keys(data.details.gallery).length > 0) {
            Object.entries(data.details.gallery).forEach(([key, items]) => {
              Gallery.push(items.image_url);
            });
            this.gallery_images[merchantSlug] = Gallery;
          }
        })
        .catch((error) => {
          this.data_info = [];
        })
        .then((data) => {
          this.loadin_info = false;
        });
    },
    geStoreMenu(merchantSlug, useCurrencyCode) {
      this.loading_menu = true;
      this.data_items = [];
      this.data_category = [];
      this.items_not_available = [];
      APIinterface.geStoreMenu(merchantSlug, useCurrencyCode)
        .then((data) => {
          this.data_category[merchantSlug] = data.details.data.category;
          this.data_items[merchantSlug] = data.details.data.items;
          this.items_not_available = data.details.data.items_not_available;
          this.category_not_available =
            data.details.data.category_not_available;
        })
        .catch((error) => {
          this.data_items = [];
          this.data_category = [];
        })
        .then((data) => {
          this.loading_menu = false;
        });
    },
    getSimilarItems(merchantId) {
      this.loading_similar_items = true;
      APIinterface.fetchDataPost(
        "SimilarItems",
        "merchant_id=" +
          merchantId +
          "&currency_code=" +
          DataStorePersisted.use_currency_code
      )
        .then((data) => {
          console.log(data);
          this.data_similar_items[merchantId] = data.details.data;
        })
        .catch((error) => {
          this.data_similar_items = {};
        })
        .then((data) => {
          this.loading_similar_items = false;
        });
    },
  },
});
