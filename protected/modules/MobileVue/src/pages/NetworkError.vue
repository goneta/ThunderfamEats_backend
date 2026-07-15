<template>
  <q-page class="q-pl-md q-pr-md flex flex-center">
    <div class="full-width text-center">
      <q-img
        src="network-error.png"
        fit="fill"
        spinner-color="primary"
        style="max-width: 150px"
      />
      <q-space class="q-pa-sm"></q-space>
      <div class="text-h5 text-weight-bold">We're having trouble loading</div>
      <div class="text-grey font12">
        xPlease check your Network connectivity and try again
      </div>
      <q-space class="q-pa-sm"></q-space>
      <q-btn
        @click="CheckNetwork"
        outline
        style="color: dark"
        label="Try Again"
        no-caps
      />
    </div>
  </q-page>
</template>

<script>
import { Network } from "@capacitor/network";
import APIinterface from "src/api/APIinterface";

export default {
  name: "NetworkError",
  methods: {
    async CheckNetwork() {
      APIinterface.showLoadingBox("", this.$q);
      const status = await Network.getStatus();
      if (status.connected === true) {
        APIinterface.hideLoadingBox(this.$q);
        this.$router.push("/home");
      } else {
        APIinterface.hideLoadingBox(this.$q);
      }
    },
  },
};
</script>
