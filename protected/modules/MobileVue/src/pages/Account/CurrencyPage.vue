<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header
      reveal
      reveal-offset="50"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-dark': !$q.dark.mode,
      }"
    >
      <q-toolbar>
        <q-btn
          @click="$router.back()"
          flat
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
        <q-toolbar-title class="text-weight-bold">{{
          $t("Language")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>
    <q-page
      padding
      class="q-pl-md q-pr-md row items-stretch"
      :class="{
        'bg-mydark': $q.dark.mode,
        'bg-grey-1': !$q.dark.mode,
      }"
    >
      <q-card
        flat
        class="radius8 col-12"
        :class="{
          'bg-mydark ': $q.dark.mode,
          'bg-white ': !$q.dark.mode,
        }"
      >
        <q-card-section>
          <q-list>
            <q-item
              v-for="(items, code) in DataStore.currency_list"
              :key="items"
              tag="label"
              clickable
              v-ripple
              class="border-grey radius10 q-mb-sm"
              :class="{
                'bg-dark text-white': $q.dark.mode,
                'bg-white text-black': !$q.dark.mode,
              }"
            >
              <q-item-section>
                <q-item-label lines="1">{{ items }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-radio v-model="currency_code" :val="code" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card-section>
      </q-card>

      <q-footer
        reveal
        class="bg-grey-1 q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
      >
        <q-btn
          :label="$t('Save')"
          unelevated
          no-caps
          color="primary text-white"
          class="full-width text-weight-bold"
          size="lg"
          @click="setCurrency"
          :loading="loading"
        />
      </q-footer>
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { useDataStore } from "stores/DataStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";

export default {
  name: "CurrencyPage",
  setup() {
    const DataStore = useDataStore();
    const DataStorePersisted = useDataStorePersisted();
    return { DataStore, DataStorePersisted };
  },
  data() {
    return {
      currency_code: "",
      loading: false,
    };
  },
  created() {
    this.currency_code = this.getCurrency();
  },
  methods: {
    refresh(done) {
      this.DataStore.getAttributes(done);
    },
    getCurrency() {
      if (Object.keys(this.DataStore.currency_list).length > 0) {
        let Currency = this.DataStorePersisted.use_currency_code
          ? this.DataStorePersisted.use_currency_code
          : this.DataStore.default_currency_code;
        return Currency;
      }
      return false;
    },
    setCurrency() {
      this.loading = true;
      this.DataStorePersisted.use_currency_code = this.currency_code;
      setTimeout(() => {
        this.$router.replace("/account-menu");
      }, 500);
    },
  },
};
</script>
