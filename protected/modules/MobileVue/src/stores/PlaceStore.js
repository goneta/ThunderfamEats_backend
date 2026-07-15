import { defineStore } from "pinia";
import APIinterface from "src/api/APIinterface";

export const usePlaceStore = defineStore("placeStore", {
  state: () => ({
    data: [],
    address: "",
  }),

  actions: {
    getPlace() {
      const $placeData = APIinterface.getStorage("place_data");
      if (typeof $placeData !== "undefined" && $placeData !== null) {
        this.address = $placeData.address.formatted_address;
        this.data = $placeData;
      } else {
        this.data = [];
        this.address = "";
      }
    },
  },
});
