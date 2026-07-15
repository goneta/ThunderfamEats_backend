<template>
  <template v-if="MenuStore.loading_similar_items">
    <q-skeleton type="text" style="width: 80px" />
    <div class="row q-gutter-sm">
      <div class="col-9">
        <q-skeleton height="60px" square class="radius8" />
      </div>
      <div class="col-2">
        <q-skeleton height="60px" square class="radius8" />
      </div>
    </div>
  </template>
  <template v-else>
    <div class="text-weight-bold q-mb-xs">{{ title }}</div>
    <swiper
      v-if="MenuStore.data_similar_items[merchant_id]"
      :slides-per-view="1.2"
      :space-between="10"
      @swiper="onSwiper"
      @slideChange="onSlideChange"
      class="q-mb-md"
    >
      <swiper-slide
        v-for="items in MenuStore.data_similar_items[merchant_id]"
        :key="items"
        class="row"
      >
        <q-btn
          unelevated
          no-caps
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          class="radius8 fit line-normal"
          size="md"
          @click="onClickitem(items)"
        >
          <div class="row fit items-center q-gutter-sm">
            <div class="col-6 text-left">
              <div class="font13 text-weight-bold ellipsis">
                {{ items.item_name }}
              </div>
              <div
                class="font12 text-weight-light ellipsis-2-lines fit"
                v-html="items.item_description"
              ></div>
              <div class="text-grey-7 font12 text-weight-medium">
                <template v-if="items.price[0]">
                  <template v-if="items.price[0].discount > 0">
                    {{ items.price[0].pretty_price_after_discount }}
                  </template>
                  <template v-else>
                    {{ items.price[0].pretty_price }}
                  </template>
                </template>
              </div>
            </div>
            <div class="col-3">
              <q-img
                :src="items.url_image"
                lazy
                fit="cover"
                style="height: 60px; width: 60px"
                spinner-color="secondary"
                spinner-size="sm"
                placeholder-src="placeholder.png"
              />
            </div>
            <div class="col-1">
              <q-btn
                round
                unelevated
                color="primary"
                text-color="white"
                size="7px"
                icon="las la-plus"
              />
            </div>
          </div>
        </q-btn>
      </swiper-slide>
    </swiper>
  </template>

  <ItemDetails
    ref="refItem"
    :slug="CartStore.cart_merchant.slug"
    :money_config="MenuStore.money_config"
    @after-additems="afterAdditems"
  />
  <div class="q-pa-sm"></div>
</template>

<script>
import { defineAsyncComponent, ref, onMounted } from "vue";
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "src/stores/CartStore";
import { useMenuStore } from "src/stores/MenuStore";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";

export default {
  name: "SimilarItems",
  props: ["title", "bg", "merchant_id"],
  components: {
    Swiper,
    SwiperSlide,
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
  },
  setup(props) {
    const slide = ref(0);
    const loading = ref(false);
    const data = ref([]);
    const rows = ref(2);
    const moneyConfig = ref([]);
    const refItem = ref(null);
    const payload = ref([
      "items",
      "subtotal",
      "distance_local",
      "items_count",
      "merchant_info",
      "check_opening",
      "transaction_info",
    ]);

    const CartStore = useCartStore();
    const MenuStore = useMenuStore();

    onMounted(() => {
      if (Object.keys(MenuStore.data_similar_items).length <= 0) {
        MenuStore.getSimilarItems(props.merchant_id);
      } else {
        if (!MenuStore.data_similar_items[props.merchant_id]) {
          MenuStore.getSimilarItems(props.merchant_id);
        }
      }
    });

    const getSimilarItems = () => {
      loading.value = true;
      APIinterface.SimilarItems(
        APIinterface.getStorage("cart_uuid"),
        rows.value
      )
        .then((results) => {
          data.value = results.details;
        })
        .catch((error) => {
          console.debug(error);
        })
        .then((data) => {
          loading.value = false;
        });
    };

    const onClickitem = (data) => {
      const $params = {
        cat_id: data.cat_id,
        item_uuid: data.item_uuid,
      };
      refItem.value.showItem2($params, CartStore.cart_merchant.slug);
    };

    const afterAdditems = (data) => {
      CartStore.getCart(false, payload.value);
    };

    const getMoneyConfig = () => {
      APIinterface.getMoneyConfig()
        .then((data) => {
          moneyConfig.value = data.details;
        })
        // eslint-disable-next-line
        .catch((error) => {})
        .then((data) => {});
    };

    return {
      slide,
      loading,
      data,
      getSimilarItems,
      onClickitem,
      getMoneyConfig,
      moneyConfig,
      CartStore,
      afterAdditems,
      refItem,
      MenuStore,
    };
  },
};
</script>
