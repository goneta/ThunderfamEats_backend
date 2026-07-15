<template>
  <q-header>
    <q-toolbar class="bg-white text-dark">
      <q-btn
        @click="$router.back()"
        color="white"
        rounded
        unelevated
        text-color="dark"
        icon="arrow_back"
        dense
        no-caps
      />

      <q-input
        v-model="q"
        ref="items"
        :label="$t('Search menu')"
        dense
        outlined
        color="grey"
        bg-color="white"
        class="full-width input-borderless"
        :loading="awaitingSearch"
      >
        <template v-slot:prepend>
          <q-icon name="search" />
        </template>

        <template v-if="!awaitingSearch" v-slot:append>
          <q-icon
            v-if="isSearch"
            @click="clearField()"
            class="cursor-pointer"
            name="close"
          />
        </template>
      </q-input>
    </q-toolbar>
  </q-header>

  <q-page>
    <q-space class="q-pa-xs bg-grey-2"></q-space>

    <template v-if="!awaitingSearch">
      <template v-if="isSearch">
        <div class="q-pa-md">
          <template v-if="hasData">
            <h5 class="no-margin text-weight-bold q-pb-sm">
              {{ $t("Search results for") }} “{{ q }}” ({{ totalFound }})
            </h5>
          </template>
          <template v-else>
            <h5 class="no-margin text-weight-bold">
              {{ $t("No results for") }} “{{ q }}”
            </h5>
            <p class="text-grey font13">
              {{
                $t(
                  "Sorry, no product matched for your search. Please try again."
                )
              }}
            </p>
          </template>
        </div>
      </template>
    </template>

    <q-list v-if="!awaitingSearch">
      <template v-for="items in data" :key="items">
        <q-item clickable v-ripple @click="onClickitem(items)">
          <q-item-section avatar>
            <q-img
              :src="items.url_image"
              lazy
              fit="cover"
              style="height: 50px; width: 50px"
              class="rounded-borders"
              spinner-color="amber"
              spinner-size="20px"
            />
          </q-item-section>
          <q-item-section>
            <q-item-label lines="1" class="font12 text-weight-bold q-mb-sm">
              {{ items.item_name }}
            </q-item-label>
            <q-item-label class="font12 text-weight-medium">
              <template v-if="items.price[0]">
                <template v-if="items.price[0].discount <= 0">
                  <span class="q-mr-sm"
                    >{{ items.price[0].size_name }}
                    {{ items.price[0].pretty_price }}</span
                  >
                </template>
                <template v-else>
                  <span class="q-mr-sm"
                    >{{ items.price[0].size_name }}
                    <del>{{ items.price[0].pretty_price }}</del>
                    {{ items.price[0].pretty_price_after_discount }}</span
                  >
                </template>
              </template>
            </q-item-label>
          </q-item-section>
        </q-item>
        <q-separator inset></q-separator>
      </template>
    </q-list>

    <ItemDetails
      ref="item_details"
      :slug="slug"
      :money_config="money_config"
      @after-additems="afterAdditems"
    />
  </q-page>

  <q-footer
    v-if="CartStore.hasData"
    reveal
    class="bg-white q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
  >
    <q-btn
      to="/cart"
      :loading="CartStore.loading"
      unelevated
      color="primary"
      text-color="dark"
      no-caps
      class="radius8 full-width"
    >
      <div class="row full-width items-center">
        <div class="col text-left">
          <q-avatar
            square
            size="24px"
            color="amber-2"
            text-color="dark"
            class="text-weight-bold rounded-borders"
          >
            <template v-if="CartStore.data">
              {{ CartStore.data.items_count }}
            </template>
          </q-avatar>
        </div>
        <div class="col text-weight-600">View Order</div>
        <div class="col text-right text-weight-bold">
          <template v-if="CartStore.data">
            {{ CartStore.data.data.subtotal.value }}
          </template>
        </div>
      </div>
    </q-btn>
  </q-footer>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "stores/CartStore";

export default {
  name: "MenuSearchPage",
  data() {
    return {
      q: "",
      data: [],
      slug: "",
      loading: false,
      awaitingSearch: false,
      money_config: [],
    };
  },
  setup() {
    const CartStore = useCartStore();
    return { CartStore };
  },
  components: {
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
  },
  mounted() {
    this.slug = this.$route.params.slug;
    this.Focus();
    this.getMoneyConfig();
  },
  computed: {
    totalFound() {
      return Object.keys(this.data).length;
    },
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
    isSearch() {
      if (!APIinterface.empty(this.q)) {
        return true;
      }
      return false;
    },
  },
  watch: {
    q(newdata, oldata) {
      if (!this.awaitingSearch) {
        if (
          typeof this.q === "undefined" ||
          this.q === null ||
          this.q === "" ||
          this.q === "null" ||
          this.q === "undefined"
        ) {
          this.data = [];
          return false;
        }
        setTimeout(() => {
          APIinterface.menuSearch(this.q, this.slug)
            .then((data) => {
              this.data = data.details.data;
            })
            // eslint-disable-next-line
            .catch((error) => {
              this.data = [];
            })
            .then((data) => {
              this.awaitingSearch = false;
            });
        }, 1000); // 1 sec delay
      }
      this.awaitingSearch = true;
    },
  },
  methods: {
    Focus() {
      this.$refs.items.focus();
    },
    clearField() {
      this.q = "";
      this.Focus();
    },
    onSearch(loading) {
      this.loading = loading;
    },
    onClickitem(data) {
      const $params = {
        cat_id: data.cat_id,
        item_uuid: data.item_uuid,
      };
      console.debug(data);
      this.$refs.item_details.showItem2($params, this.slug);
    },
    afterAdditems() {
      console.debug("afterAdditems");
      this.CartStore.getCartCount();
    },
    getMoneyConfig() {
      APIinterface.getMoneyConfig()
        .then((data) => {
          this.money_config = data.details;
        })
        // eslint-disable-next-line
        .catch((error) => {})
        .then((data) => {});
    },
  },
};
</script>
