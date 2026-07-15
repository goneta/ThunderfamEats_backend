<template>
  <q-header
    reveal
    reveal-offset="10"
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-black': !$q.dark.mode,
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
        $t("Search menu items")
      }}</q-toolbar-title>
    </q-toolbar>
  </q-header>
  <q-page class="q-pl-md q-pr-md row items-stretch">
    <div class="col-12">
      <q-input
        v-model="q"
        :label="$t('Search')"
        outlined
        lazy-rules
        :bg-color="$q.dark.mode ? 'grey600' : 'input'"
        label-color="grey"
        borderless
        class="input-borderless"
      >
        <template v-slot:prepend>
          <q-icon name="eva-search-outline" size="sm" />
        </template>
        <template v-slot:append>
          <q-icon
            v-if="hasFilter"
            class="cursor-pointer"
            name="close"
            color="grey"
            @click="this.q = ''"
          />
        </template>
      </q-input>

      <template v-if="hasResults && hasFilter && !awaitingSearch">
        <q-list separator>
          <template v-for="items in food_list" :key="items">
            <q-item @click="onClickitem(items)" clickable>
              <SearchListFood
                :items="items"
                :merchant_list="merchant_list"
              ></SearchListFood>
            </q-item>
          </template>
        </q-list>
      </template>
      <template v-else-if="hasFilter && awaitingSearch">
        <q-list>
          <q-item v-for="i in 8" :key="i">
            <q-item-section avatar>
              <q-skeleton width="80px" height="80px" />
            </q-item-section>
            <q-item-section>
              <q-skeleton type="text" />
              <q-skeleton type="text" class="w-50" />
              <q-skeleton type="text" class="w-70" />
              <q-skeleton type="text" class="w-25" />
            </q-item-section>
          </q-item>
        </q-list>
      </template>

      <template v-else>
        <div class="flex flex-center" style="min-height: 89%">
          <div class="text-center full-width">
            <template v-if="hasFilter && !awaitingSearch">
              <div class="text-h5 text-weight-bold">
                {{ $t("No items found") }}
              </div>
              <p class="text-grey font12">
                {{ $t("Sorry, we couldn't find any results") }}
              </p>
            </template>
            <template v-else>
              <q-img
                src="search.png"
                fit="fill"
                spinner-color="primary"
                style="height: 80px; max-width: 80px"
              />
              <div class="text-h5 text-weight-bold">
                {{ $t("Search Items") }}
              </div>
              <p class="text-grey font12">
                {{ $t("Search items from") }}
                <template v-if="MenuStore.data_info[slug]">
                  {{ MenuStore.data_info[slug].restaurant_name }}
                </template>
              </p>
            </template>
          </div>
        </div>
      </template>
    </div>
    <!-- col-12 -->

    <ItemDetails ref="refItem" :slug="slug" @after-additems="afterAdditems" />
  </q-page>

  <q-footer
    v-if="CartStore.items_count > 0 && item_added == true"
    reveal
    class="q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
    :class="{
      'bg-primary': !CartStore.cart_reloading,
      'bg-grey-5': CartStore.cart_reloading,
    }"
  >
    <q-btn
      to="/checkout"
      :loading="CartStore.cart_loading"
      :disable="!CartStore.canProceed"
      unelevated
      text-color="white"
      no-caps
      class="radius10 fit"
      :color="{
        primary: !CartStore.cart_reloading,
        'grey-5': CartStore.cart_reloading,
      }"
    >
      <div class="row items-center justify-between fit">
        <div class="text-weight-bold">{{ $t("Checkout") }}</div>
        <div class="text-weight-bold">
          {{ CartStore.cart_subtotal.value }}
        </div>
      </div>
    </q-btn>
  </q-footer>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useMenuStore } from "stores/MenuStore";
import { useCartStore } from "src/stores/CartStore";

export default {
  name: "SearchItems",
  components: {
    SearchListFood: defineAsyncComponent(() =>
      import("components/SearchListFood.vue")
    ),
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
  },
  data() {
    return {
      slug: "",
      q: "",
      food_list: [],
      awaitingSearch: false,
      item_added: false,
    };
  },
  setup() {
    const MenuStore = useMenuStore();
    const CartStore = useCartStore();
    return { MenuStore, CartStore };
  },
  created() {
    this.slug = this.$route.query.slug;
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
          return false;
        }
        setTimeout(() => {
          APIinterface.fetchDataPost(
            "searchItems",
            "q=" + this.q + "&slug=" + this.slug
          )
            .then((data) => {
              console.debug(data);
              this.food_list = data.details.data;
            })
            // eslint-disable-next-line
            .catch((error) => {
              this.food_list = [];
            })
            .then((data) => {
              this.awaitingSearch = false;
            });
        }, 1000); // 1 sec delay
      }
      this.awaitingSearch = true;
    },
  },
  computed: {
    hasFilter() {
      if (!APIinterface.empty(this.q)) {
        return true;
      }
      return false;
    },
    hasResults() {
      if (Object.keys(this.food_list).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    onClickitem(data) {
      this.item_added = false;
      const $params = {
        cat_id: data.cat_id,
        item_uuid: data.item_uuid,
      };
      this.$refs.refItem.showItem2($params, this.slug);
    },
    afterAdditems() {
      this.item_added = true;
      this.CartStore.getCart(false, this.CartStore.cart_payload);
    },
  },
};
</script>
