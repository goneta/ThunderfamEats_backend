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
              v-for="items in DataStore.language_data.data"
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
              <q-item-section avatar>
                <q-avatar square>
                  <q-img
                    :src="items.flag"
                    spinner-color="secondary"
                    style="height: 25px; max-width: 40px"
                    spinner-size="sm"
                  />
                </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label lines="1">{{ items.title }}</q-item-label>
                <q-item-label lines="1" caption>{{
                  items.description
                }}</q-item-label>
              </q-item-section>
              <q-item-section side>
                <q-radio v-model="language" :val="items.code" />
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
          @click="setLanguage"
          :loading="DataStore.loading"
        />
      </q-footer>
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { useDataStore } from "stores/DataStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";
import { useMenuStore } from "stores/MenuStore";
import { useI18n } from "vue-i18n";
import { api } from "boot/axios";

export default {
  name: "LanguagePage",
  setup() {
    const DataStore = useDataStore();
    const DataStorePersisted = useDataStorePersisted();
    const MenuStore = useMenuStore();
    return { DataStore, DataStorePersisted, MenuStore };
  },
  data() {
    return {
      language: "",
      loading: false,
    };
  },
  created() {
    this.language = this.DataStorePersisted.app_language;
  },
  methods: {
    setLanguage() {
      this.DataStorePersisted.app_language = this.language;
      this.$i18n.locale = this.language;
      api.defaults.params = {};
      api.defaults.params["language"] = this.$i18n.locale;

      this.setRTL();
      this.DataStore.getAttributes();

      // Clear menu data
      this.MenuStore.data_info = [];
      this.MenuStore.data_category = [];

      this.$router.replace("/home");
    },
    refresh(done) {
      this.DataStore.getAttributes(done);
    },
    setRTL() {
      if (Object.keys(this.DataStore.language_data).length > 0) {
        Object.entries(this.DataStore.language_data.data).forEach(
          ([key, items]) => {
            if (this.language == items.code) {
              if (items.rtl == 1) {
                this.$q.lang.set({ rtl: true });
                this.DataStorePersisted.rtl = true;
              } else {
                this.$q.lang.set({ rtl: false });
                this.DataStorePersisted.rtl = false;
              }
            }
          }
        );
      }
    },
  },
};
</script>
