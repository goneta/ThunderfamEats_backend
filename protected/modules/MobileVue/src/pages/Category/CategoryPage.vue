<template>
  <q-header reveal class="bg-white">
    <q-toolbar>
      <q-btn to="/home" flat round dense icon="arrow_back" color="dark" />

      <q-input
        :label="$t('Search category')"
        dense
        outlined
        color="grey"
        bg-color="white"
        class="full-width input-borderless"
        @click="dialog = true"
        readonly
      >
        <template v-slot:prepend>
          <q-icon name="search" />
        </template>
      </q-input>
    </q-toolbar>
  </q-header>
  <!-- banner -->

  <q-page padding class="bg-grey-2">
    <q-space class="q-pa-xs"></q-space>

    <CuisineList ref="cuisine_list" :q="q" @on-search="onSearch" />

    <q-space class="q-mt-xl q-mb-xl"></q-space>

    <q-dialog
      v-model="dialog"
      position="top"
      transition-show="fade"
      @show="Focus()"
    >
      <q-card class="transparent no-shadow">
        <q-card-section>
          <q-input
            v-model="q"
            ref="category"
            :label="$t('Search category')"
            dense
            outlined
            color="grey"
            bg-color="white"
            class="full-width input-borderless"
            :loading="loading"
          >
            <template v-slot:prepend>
              <q-icon name="search" />
            </template>
          </q-input>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script>
import { defineAsyncComponent } from "vue";

export default {
  name: "CategoryPage",
  data() {
    return {
      q: "",
      loading: false,
      dialog: false,
    };
  },
  components: {
    CuisineList: defineAsyncComponent(() =>
      import("components/CuisineList.vue")
    ),
  },
  methods: {
    Focus() {
      this.$refs.category.focus();
    },
    onSearch(loading) {
      this.loading = loading;
    },
  },
};
</script>
