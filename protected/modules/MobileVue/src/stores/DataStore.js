import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";
import jwt_decode from "jwt-decode";

export const useDataStore = defineStore("datastore", {
  state: () => ({
    loading_cuisine: true,
    cuisine: [],
    price_range_data: [],
    sort_by: [],
    max_delivery_data: [],
    sort_list: [],
    banner_loading: true,
    banner: [],
    featured_data: [],
    filters: [],
    car_loading: [true, true],
    car_data: [],
    car_cuisine: [],
    car_reviews: [],
    car_estimation: [],
    car_services: [],
    list_loading: true,
    list_loading_handle: false,
    list_data: [],
    list_cuisine: [],
    list_reviews: [],
    list_estimation: [],
    list_services: [],
    list_featured_id: "",
    phone_prefix_data: [],
    phone_default_data: [],
    tips_data: [],
    maps_config: "",
    language_data: [],
    money_config: [],
    realtime_data: [],
    promos: [],
    legal_menu: {
      page_privacy_policy: "Privacy Policy",
      page_terms: "Terms and condition",
      page_aboutus: "About us",
    },
    loading_page: false,
    page_data: [],
    dark_mode: false,
    invite_friend_settings: [],
    fb_flag: false,
    google_login_enabled: false,
    enabled_language: false,
    loading: false,
    multicurrency_enabled: false,
    multicurrency_hide_payment: false,
    multicurrency_enabled_force: false,
    default_currency_code: "",
    currency_list: "",
    points_enabled: false,
    captcha_settings: [],
    booking_status_list: [],
    food_list: [],
    merchant_list: [],
    cuisine_list: [],
  }),

  getters: {
    hasCuisine() {
      if (Object.keys(this.cuisine).length > 0) {
        return true;
      }
      return false;
    },
    hasFeed() {
      if (Object.keys(this.list_data).length > 0) {
        return true;
      }
      return false;
    },
    hasPage() {
      if (Object.keys(this.page_data).length > 0) {
        return true;
      }
      return false;
    },
    hasMapConfig() {
      if (Object.keys(this.maps_config).length > 0) {
        return true;
      }
      return false;
    },
    getBookingSettings(state) {
      return state.captcha_settings;
    },
    getBookingStatusList(state) {
      return state.booking_status_list;
    },
  },

  //persist: true,

  actions: {
    CuisineList() {
      this.loading_cuisine = true;
      APIinterface.CuisineList(4, "")
        .then((data) => {
          this.cuisine = data.details.data;
        })
        .catch((error) => {
          this.cuisine = [];
        })
        .then((data) => {
          this.loading_cuisine = false;
        });
    },
    hasDataCuisine() {
      if (Object.keys(this.cuisine).length > 0) {
        return true;
      }
      return false;
    },
    searchAttributes() {
      APIinterface.searchAttributes()
        .then((data) => {
          this.price_range_data = data.details.price_range;
          this.sort_by = data.details.sort_by;
          this.max_delivery_data = data.details.max_delivery_fee;
          this.sort_list = data.details.sort_list;
        })
        .catch((error) => {
          this.price_range_data = [];
          this.sort_by = [];
          this.max_delivery_data = [];
          this.sort_list = [];
        })
        .then((data) => {
          //
        });
    },
    getBanner() {
      this.banner_loading = true;
      APIinterface.getBanner()
        .then((data) => {
          this.banner = data.details.data;
          this.food_list = data.details.food_list;
          this.merchant_list = data.details.merchant_list;
          this.cuisine_list = data.details.cuisine_list;
        })
        // eslint-disable-next-line
        .catch((error) => {
          this.banner = [];
          this.food_list = [];
          this.merchant_list = [];
          this.cuisine_list = [];
        })
        .then((data) => {
          this.banner_loading = false;
        });
    },
    getFeaturedList() {
      APIinterface.getFeaturedList()
        .then((data) => {
          this.featured_data = data.details.data;
        })
        // eslint-disable-next-line
        .catch((error) => {
          this.featured_data = [];
        })
        .then((data) => {});
    },
    getCarouselData(params, index) {
      this.car_loading[index] = true;
      APIinterface.getMerchantFeed(params)
        .then((data) => {
          this.car_data[index] = data.details.data;
          this.car_cuisine[index] = data.details.cuisine;
          this.car_reviews[index] = data.details.reviews;
          this.car_estimation[index] = data.details.estimation;
          this.car_services[index] = data.details.services;
        })
        .catch((error) => {
          this.car_data[index] = [];
        })
        .then((data) => {
          this.car_loading[index] = false;
        });
    },
    hasCarData(index) {
      if (APIinterface.empty(this.car_data[index])) {
        return false;
      } else {
        if (Object.keys(this.car_data[index]).length > 0) {
          return true;
        }
      }
      return false;
    },
    getMerchantFeed(params) {
      this.list_loading = true;
      APIinterface.getMerchantFeed(params)
        .then((data) => {
          this.list_data = data.details.data;
          this.list_cuisine = data.details.cuisine;
          this.list_reviews = data.details.reviews;
          this.list_estimation = data.details.estimation;
          this.list_services = data.details.services;
          this.promos = data.details.promos;
        })
        .catch((error) => {
          this.list_data = [];
          this.list_cuisine = [];
          this.list_reviews = [];
          this.list_estimation = [];
          this.list_services = [];
          this.promos = [];
        })
        .then((data) => {
          this.list_loading = false;
          this.list_loading_handle = !this.list_loading_handle;
        });
    },
    hadPrefix() {
      if (APIinterface.empty(this.phone_prefix_data)) {
        return false;
      } else {
        if (Object.keys(this.phone_prefix_data).length > 0) {
          return true;
        }
      }
      return false;
    },
    getAttributes(done) {
      this.loading = true;
      APIinterface.fetchDataPost("getAttributes", "")
        .then((data) => {
          this.phone_default_data = data.details.phone_default_data;
          this.tips_data = data.details.tips_list;
          this.maps_config = jwt_decode(data.details.maps_config);
          this.language_data = jwt_decode(data.details.language_data);
          this.money_config = data.details.money_config;
          this.realtime_data = jwt_decode(data.details.realtime);
          this.invite_friend_settings = data.details.invite_friend_settings;
          this.enabled_language = data.details.enabled_language;

          this.fb_flag = data.details.fb_flag;
          this.google_login_enabled = data.details.google_login_enabled;

          this.multicurrency_enabled = data.details.multicurrency_enabled;
          this.multicurrency_hide_payment =
            data.details.multicurrency_hide_payment;
          this.multicurrency_enabled_force =
            data.details.multicurrency_enabled_force;

          this.default_currency_code = data.details.default_currency_code;
          this.currency_list = data.details.currency_list;

          this.points_enabled = data.details.points_enabled;
          this.captcha_settings = data.details.captcha_settings;
          this.booking_status_list = data.details.booking_status_list;

          if (Object.keys(data.details.phone_prefix_data).length > 0) {
            Object.entries(data.details.phone_prefix_data).forEach(
              ([key, items]) => {
                this.phone_prefix_data.push({
                  label: "+" + items.phonecode + " " + items.country_name,
                  value: "+" + items.phonecode,
                  flag: items.flag,
                });
              }
            );
          }
        })
        .catch((error) => {
          this.phone_prefix_data = [];
          this.tips_data = [];
          this.maps_config = "";
          this.language_data = [];
          this.money_config = [];
          this.realtime_data = [];
          this.invite_friend_settings = [];
          this.enabled_language = false;
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    getPage(done, pageId) {
      if (APIinterface.empty(done)) {
        this.loading_page = true;
      }
      APIinterface.fetchDataPost("getPage", "page_id=" + pageId)
        .then((data) => {
          this.page_data = data.details;
        })
        .catch((error) => {
          this.page_data = [];
        })
        .then((data) => {
          this.loading_page = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
  },
});
