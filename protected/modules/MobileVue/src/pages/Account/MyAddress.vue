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
          $t("Address")
        }}</q-toolbar-title>
      </q-toolbar>
    </q-header>

    <q-page
      padding
      class="q-pl-md q-pr-md"
      :class="{
        'flex flex-center': !hasData && !loading,
        'row items-stretch ': hasData && !loading,
        'bg-mydark text-white': $q.dark.mode,
        'bg-grey-1 text-dark': !$q.dark.mode,
      }"
    >
      <template v-if="loading">
        <q-inner-loading
          :showing="true"
          color="primary"
          size="md"
          label-class="dark"
          class="transparent"
        />
      </template>
      <template v-else-if="!hasData && !loading">
        <div class="full-width text-center">
          <div class="text-h5 text-weight-bold line-normal">
            {{ $t("You don't have address added yet") }}
          </div>
          <p class="text-grey font12 no-margin">
            {{ $t("Let's change that") }}!
          </p>
          <q-btn
            flat
            color="blue"
            no-caps
            :label="$t('Add new address')"
            dense
            size="sm"
            :to="{
              path: '/location/map',
              query: { url: '/account/my-address' },
            }"
          />
        </div>
      </template>

      <template v-else>
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
              <transition
                v-for="(items, index) in data"
                :key="items.address_uuid"
                appear
                leave-active-class="animated fadeOut"
              >
                <q-slide-item
                  @action="deleteAdress(index, items)"
                  :right-color="$q.dark.mode ? 'mydark' : 'white'"
                  :class="{
                    'bg-mydark ': $q.dark.mode,
                    'bg-white ': !$q.dark.mode,
                  }"
                >
                  <template v-slot:right>
                    <q-btn
                      unelevated
                      round
                      color="lightprimary"
                      text-color="primary"
                      icon="las la-trash"
                      dense
                    />
                  </template>
                  <q-item
                    @click="editAddress(items)"
                    clickable
                    v-ripple
                    class="radius10 q-mb-sm"
                    :class="{
                      'border-grey300 ': $q.dark.mode,
                      'border-grey': !$q.dark.mode,
                    }"
                  >
                    <q-item-section top>
                      <q-item-label lines="1">
                        <span class="text-weight-medium">{{
                          items.attributes.address_label
                        }}</span>
                      </q-item-label>
                      <q-item-label caption lines="1">
                        {{ items.address.formatted_address }}
                      </q-item-label>
                    </q-item-section>
                  </q-item>
                </q-slide-item>
              </transition>
            </q-list>
          </q-card-section>
        </q-card>

        <q-footer
          reveal
          class="bg-grey-1 q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
        >
          <q-btn
            :loading="loading"
            :to="{
              path: '/location/map',
              query: { url: '/account/my-address' },
            }"
            :label="$t('Add new address')"
            unelevated
            no-caps
            color="primary text-white"
            class="full-width text-weight-bold"
            size="lg"
          />
        </q-footer>
      </template>
      <AddressDetails
        ref="address_details"
        :maps_config="DataStore.maps_config"
        @after-saveaddress="afterSaveaddress"
      />
    </q-page>
  </q-pull-to-refresh>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";
import { useClientStore } from "stores/ClientStore";

export default {
  name: "MyAddress",
  components: {
    AddressDetails: defineAsyncComponent(() =>
      import("components/AddressDetails.vue")
    ),
  },
  data() {
    return {
      data: [],
      loading: false,
      inner_loading: false,
      location_data: [],
    };
  },
  setup() {
    const DataStore = useDataStore();
    const ClientStore = useClientStore();
    return { DataStore, ClientStore };
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
  },
  created() {
    this.getAddresses();
  },
  methods: {
    deleteAdress(index, data) {
      this.deleteAddress(data.address_uuid, index);
    },
    refresh(done) {
      this.getAddresses(done);
    },
    getAddresses(done) {
      if (APIinterface.empty(done)) {
        this.loading = true;
      }

      APIinterface.getAddresses()
        .then((data) => {
          this.data = data.details.data;
        })
        .catch((error) => {
          this.data = [];
        })
        .then((data) => {
          this.loading = false;
          if (!APIinterface.empty(done)) {
            done();
          }
        });
    },
    deleteAddress(addressUuid, index) {
      this.inner_loading = true;
      APIinterface.deleteAddress(addressUuid)
        .then((data) => {
          this.data.splice(index, 1);
          this.ClientStore.getAddress();
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.inner_loading = false;
        });
    },
    editAddress(data) {
      console.debug(data);
      this.$refs.address_details.location_data = data;
      this.$refs.address_details.showModal();
    },
    afterSaveaddress() {
      console.debug("afterSaveaddress");
      this.getAddresses();
    },
  },
};
</script>
