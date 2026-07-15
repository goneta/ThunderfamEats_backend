import { defineStore } from "pinia";

export const useDataStorePersisted = defineStore("datastorepersisted", {
  state: () => ({
    dark_mode: false,
    app_language: "en",
    rtl: false,
    choose_language: false,
    use_currency_code: "",
  }),

  // getters: {
  //   doubleCount(state) {
  //     return state.counter * 2;
  //   },
  // },

  actions: {
    getUseCurrency() {
      if (
        typeof this.use_currency_code === "undefined" ||
        this.use_currency_code === null ||
        this.use_currency_code === "" ||
        this.use_currency_code === "null" ||
        this.use_currency_code === "undefined"
      ) {
        return "";
      } else {
        return this.use_currency_code;
      }
    },
  },
  persist: true,
});
