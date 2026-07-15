<template>
  <template v-if="active">
    <template v-if="layout === 1">
      <q-btn
        @click="addTofav()"
        round
        color="transparent"
        text-color="primary"
        size="sm"
        icon="favorite"
        :loading="loading"
        unelevated
      />
    </template>
    <template v-else-if="layout === 2">
      <q-btn
        @click="addTofav()"
        :loading="loading"
        unelevated
        round
        color="primary"
        text-color="white"
        icon="eva-heart-outline"
        size="sm"
        dense
      />
    </template>
    <template v-else-if="layout === 3">
      <q-btn
        @click="addTofav()"
        :loading="loading"
        round
        unelevated
        color="primary"
        text-color="mygrey"
        size="7px"
        icon="favorite"
      />
    </template>
  </template>
  <template v-else>
    <template v-if="layout === 1">
      <q-btn
        @click="addTofav()"
        round
        color="transparent"
        text-color="grey-5"
        size="sm"
        icon="favorite"
        :loading="loading"
        unelevated
      />
    </template>
    <template v-else-if="layout === 2">
      <q-btn
        @click="addTofav()"
        :loading="loading"
        round
        unelevated
        color="lightprimary"
        text-color="primary"
        size="sm"
        icon="favorite_border"
      />
    </template>
    <template v-else-if="layout === 3">
      <q-btn
        @click="addTofav()"
        :loading="loading"
        round
        unelevated
        color="mygrey"
        text-color="dark"
        size="7px"
        icon="favorite_border"
      />
    </template>
  </template>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";

export default {
  name: "FavsItem",
  props: ["layout", "item_token", "cat_id", "active", "size", "data"],
  data() {
    return {
      loading: false,
    };
  },
  methods: {
    addTofav() {
      /* eslint-disable */
      if (auth.authenticated()) {
        this.loading = true;
        APIinterface.addTofav(this.item_token, this.cat_id)
          .then((data) => {
            this.$emit("afterSavefav", this.data, data.details.found);
          })
          .catch((error) => {
            APIinterface.notify("grey-5", error, "error_outline", this.$q);
          })
          .then((data) => {
            this.loading = false;
          });
      } else {
        APIinterface.notify(
          "dark",
          "Login to save this to your favorites",
          "eva-info-outline",
          this.$q
        );
      }
    },
  },
};
</script>
