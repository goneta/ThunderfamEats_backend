<template>
  <template v-if="active">
    <q-btn
      round
      @click.stop="addTofav()"
      color="secondary"
      unelevated
      text-color="white"
      icon="favorite_border"
      dense
      :size="size"
      :loading="loading"
    />
  </template>
  <template v-else>
    <q-btn
      round
      @click.stop="addTofav()"
      color="mygrey"
      unelevated
      text-color="dark"
      icon="favorite_border"
      dense
      :size="size"
      :loading="loading"
    />
  </template>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";

export default {
  name: "FavsResto",
  props: ["merchant_id", "active", "layout", "size", "data"],
  data() {
    return {
      loading: false,
    };
  },
  methods: {
    addTofav() {
      if (!auth.authenticated()) {
        APIinterface.notify(
          "dark",
          "Login to save it to your favorites",
          "error",
          this.$q
        );
        return;
      }
      this.loading = true;
      APIinterface.SaveStore(this.merchant_id)
        .then((data) => {
          this.$emit("afterSavefav", this.data, data.details.found);
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
