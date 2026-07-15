<template>
  <q-layout view="lHh Lpr lFf">
    <q-footer bordered class="bg-white text-dark">
      <q-tabs
        v-model="tab"
        dense
        indicator-color="transparent"
        active-color="secondary"
        :class="{
          'bg-black text-white': $q.dark.mode,
          'text-dark': !$q.dark.mode,
        }"
      >
        <q-route-tab
          name="home"
          icon="las la-home"
          :label="$t('Home')"
          no-caps
          to="/home"
        />
        <q-route-tab
          name="browse"
          icon="search"
          :label="$t('Search')"
          no-caps
          to="/search"
        />
        <q-route-tab
          name="cart"
          icon="las la-shopping-bag"
          :label="$t('Cart')"
          no-caps
          to="/cart"
        >
          <q-badge v-if="CartStore.hasItem" color="yellow-9" floating rounded>
            {{ CartStore.items_count }}
          </q-badge>
        </q-route-tab>
        <!-- <q-route-tab
          name="orders"
          icon="las la-file-alt"
          label="Orders"
          no-caps
          to="/orders"
        /> -->
        <q-route-tab
          name="account"
          icon="las la-user-alt"
          :label="$t('Account')"
          no-caps
          to="/account-menu"
        />
      </q-tabs>
    </q-footer>

    <q-page-container>
      <router-view />
    </q-page-container>

    <QuickTrack ref="quick_track" />
  </q-layout>
</template>

<script>
import { defineComponent, defineAsyncComponent } from "vue";
import { useCartStore } from "stores/CartStore";

export default defineComponent({
  name: "NotopnavLayout",
  data() {
    return {
      tab: "home",
    };
  },
  components: {
    QuickTrack: defineAsyncComponent(() => import("components/QuickTrack.vue")),
  },
  setup() {
    const CartStore = useCartStore();
    return { CartStore };
  },
});
</script>
