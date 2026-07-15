<template>
  <q-pull-to-refresh @refresh="refresh">
    <q-header :class="classObject">
      <q-toolbar>
        <q-btn
          @click="$router.back()"
          round
          dense
          icon="las la-angle-left"
          class="q-mr-sm"
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          size="sm"
          unelevated
        />
        <q-toolbar-title v-if="headerChangeColor" class="text-weight-bold">
          <template v-if="MenuStore.data_info[slug]">
            {{ MenuStore.data_info[slug].restaurant_name }}
          </template></q-toolbar-title
        >
        <q-space></q-space>
        <div v-if="MenuStore.data_info[slug]">
          <FavsResto
            ref="favs"
            :data="MenuStore.data_info[slug]"
            :active="MenuStore.data_info[slug].saved_store"
            :merchant_id="MenuStore.data_info[slug].merchant_id"
            :layout="1"
            size="xs"
            @after-savefav="afterSavefav"
          />
          <ShareComponents
            v-if="MenuStore.data_info[slug].share"
            ref="share"
            :title="MenuStore.data_info[slug].share.title"
            :text="MenuStore.data_info[slug].share.text"
            :url="MenuStore.data_info[slug].share.url"
            :dialogTitle="MenuStore.data_info[slug].share.dialogTitle"
          />
        </div>
      </q-toolbar>
    </q-header>
    <q-page>
      <q-scroll-observer @scroll="onScroll" />

      <template v-if="MenuStore.loadin_info">
        <q-inner-loading
          :showing="true"
          color="primary"
          size="md"
          label-class="dark"
        />
      </template>

      <div
        style="height: 170px"
        :style="headerBackground"
        :class="{
          'relative-position': $q.dark.mode,
          '': !$q.dark.mode,
        }"
      >
        <div v-if="this.$q.dark.mode" class="absolute-top fit dimmed"></div>
      </div>

      <template v-if="MenuStore.data_info[slug]">
        <div
          class="row items-top q-pl-md q-pr-md q-pt-sm curve2 relative-position"
          :class="{
            'bg-mydark text-white': $q.dark.mode,
            'bg-white text-dark': !$q.dark.mode,
          }"
          style="margin-top: -20px"
        >
          <div class="col">
            <div class="font16 text-weight-bold no-margin line-normal ellipsis">
              {{ MenuStore.data_info[slug].restaurant_name }}
            </div>
          </div>
          <div class="col-3">
            <template v-if="StoreOpen.loading">
              <q-skeleton type="text" style="width: 40px" />
            </template>
            <template v-else>
              <span
                v-if="StoreOpen.store_close"
                class="font13 text-green text-weight-bold"
                >{{ $t("Close") }}</span
              >
              <span v-else class="font13 text-weight-bold text-green">{{
                $t("Open")
              }}</span>
            </template>
          </div>
          <div class="col-3 text-right">
            <q-btn
              :color="$q.dark.mode ? 'grey600' : 'mygrey'"
              :text-color="$q.dark.mode ? 'grey300' : 'dark'"
              icon-right="las la-angle-right"
              :label="$t('INFO')"
              size="sm"
              unelevated
              no-caps
              dense
              class="radius10 q-pl-sm"
              :to="{
                name: 'info',
                query: {
                  slug: this.slug,
                },
              }"
            />
          </div>
        </div>

        <div class="q-pl-md q-pr-md">
          <div class="row items-center font12 text-grey">
            <q-chip
              v-if="MenuStore.data_info[slug].cuisine"
              dense
              color="transparent"
              :text-color="$q.dark.mode ? 'secondary' : 'grey-4'"
              class="q-pa-none col-3"
              icon-right="fiber_manual_record"
            >
              <span class="text-grey">
                {{ MenuStore.data_info[slug].cuisine[0] }}
              </span>
            </q-chip>

            <q-chip
              dense
              color="transparent"
              text-color="primary"
              class="q-pa-none col"
              icon="las la-map-marker-alt"
            >
              <span class="text-grey ellipsis fit">{{
                MenuStore.data_info[slug].address
              }}</span>
            </q-chip>
          </div>
          <!-- row -->

          <div class="row items-center justify-between">
            <div
              @click="
                this.$router.push({
                  name: 'storereview',
                  query: { slug: this.slug },
                })
              "
            >
              <q-chip
                size="sm"
                color="secondary"
                text-color="secondary"
                icon="star"
                class="no-padding transparent cursor-pointer"
              >
                <span class="text-weight-medium text-dark font12 text-grey">
                  <span
                    class="text-weight-bold"
                    :class="{
                      'text-grey300': $q.dark.mode,
                      'text-dark': !$q.dark.mode,
                    }"
                    >{{ MenuStore.data_info[slug].ratings }}</span
                  >
                  +{{ MenuStore.data_info[slug].review_count }} ratings</span
                >
                <q-icon name="las la-angle-right" color="dark" />
              </q-chip>
            </div>

            <q-chip
              size="sm"
              color="secondary"
              text-color="secondary"
              icon="las la-clock"
              class="no-padding transparent cursor-pointer"
              v-if="MenuStore.data_charge_type[slug]"
            >
              <span
                class="text-weight-medium text-dark font12 text-grey line-normal"
                v-if="MenuStore.data_estimation[slug]"
              >
                <template
                  v-if="
                    MenuStore.data_estimation[slug][
                      DeliveryschedStore.transaction_type
                    ]
                  "
                >
                  <template
                    v-if="
                      MenuStore.data_estimation[slug][
                        DeliveryschedStore.transaction_type
                      ][MenuStore.data_charge_type[slug]]
                    "
                  >
                    {{
                      MenuStore.data_estimation[slug][
                        DeliveryschedStore.transaction_type
                      ][MenuStore.data_charge_type[slug]].estimation
                    }}
                    {{ $t("min") }}
                  </template>
                  <template v-else> {{ $t("N/A") }} </template>
                </template>
              </span>
              <!-- <q-icon name="las la-angle-right" color="dark" /> -->
            </q-chip>

            <div
              v-if="MenuStore.data_distance[slug]"
              class="font12 text-grey line-normal col-4"
            >
              <q-chip
                size="sm"
                color="secondary"
                text-color="secondary"
                icon="las la-map-marker"
                class="no-padding transparent cursor-pointer"
              >
                <span
                  class="text-weight-medium text-dark font12 text-grey ellipsis fit"
                >
                  {{ MenuStore.data_distance[slug].label }}</span
                >
              </q-chip>
            </div>
          </div>

          <q-separator class="q-mb-sm"></q-separator>

          <template v-if="StoreOpen.store_close">
            <div
              class="q-pa-md text-center q-mb-sm radius8"
              :class="{
                'bg-grey600 text-grey300': $q.dark.mode,
                'bg-yellow': !$q.dark.mode,
              }"
            >
              <div class="text-weight-medium text-h5 line-normal">
                {{ $t("Store is close") }}
              </div>
              <div class="font12">{{ StoreOpen.message }}</div>
              <q-btn
                flat
                :color="$q.dark.mode ? 'secondary' : 'blue'"
                no-caps
                :label="$t('Schedule Order')"
                dense
                size="sm"
                @click="this.$refs.delivery_sched.showSched(true)"
              />
            </div>
          </template>

          <!-- Change transaction and time -->
          <template v-else>
            <div
              v-if="
                DeliveryschedStore.data[DeliveryschedStore.transaction_type]
              "
              class="row items-center justify-between q-mb-sm"
            >
              <div class="text-weight-bold ellipsis col-9">
                {{
                  DeliveryschedStore.data[DeliveryschedStore.transaction_type]
                    .service_name
                }}
                <span class="text-weight-medium font12">
                  <!-- in 20 - 30 min -->
                  {{ getEstimation }}
                </span>
              </div>
              <q-btn
                @click="this.$refs.delivery_sched.showSched(true)"
                :label="$t('Change')"
                unelevated
                flat
                no-caps
                color="primary"
                dense
              />
            </div>
          </template>

          <!-- <q-separator class="q-mb-md"></q-separator> -->

          <MerchantPromoSlide
            ref="merchantPromoSlide"
            :merchant_id="MenuStore.data_info[slug].merchant_id"
          ></MerchantPromoSlide>

          <div class="row items-center q-gutter-sm">
            <div class="col">
              <q-input
                v-model="q"
                :label="$t('Search food and restaurants')"
                outlined
                lazy-rules
                :bg-color="$q.dark.mode ? 'grey600' : 'input'"
                :label-color="$q.dark.mode ? 'grey300' : 'grey'"
                borderless
                class="input-borderless"
                dense
                readonly
                @click="goSearch"
              >
                <template v-slot:prepend>
                  <q-icon name="eva-search-outline" size="sm" />
                </template>
              </q-input>
            </div>
            <div class="col-4">
              <q-btn
                color="secondary"
                unelevated
                dense
                no-caps
                class="fit rows items-center"
                flat
                @click="
                  this.$refs.categories_modal.modal =
                    !this.$refs.categories_modal.modal
                "
              >
                <div class="q-mr-xs">{{ $t("Categories") }}</div>
                <q-icon name="las la-angle-down" color="dark" size="14px" />
              </q-btn>
            </div>
          </div>
          <!-- row -->
        </div>
        <!-- padding -->

        <q-space class="q-ma-md"></q-space>

        <!-- MENU STARTS HERE -->
        <template v-if="MenuStore.data_category[slug]">
          <template
            v-for="category in MenuStore.data_category[slug]"
            :key="category"
          >
            <div
              :id="category.category_uiid"
              class="text-weight-bold no-margin line-normal q-pl-md q-pb-sm"
              style="font-size: 17px"
            >
              {{ category.category_name }}
            </div>
            <div class="q-pl-md q-pr-md">
              <swiper
                :slides-per-view="2.1"
                :space-between="10"
                @swiper="onSwiper"
                @slideChange="onSlideChange"
              >
                <template v-for="items_id in category.items" :key="items_id">
                  <swiper-slide>
                    <div
                      class="q-mb-sm relative-position rounded-borders bg-mygrey q-pl-sm q-pr-sm"
                    >
                      <q-img
                        :src="MenuStore.data_items[slug][items_id].url_image"
                        placeholder-src="placeholder.png"
                        lazy
                        fit="cover"
                        style="height: 150px"
                        class="radius8"
                        spinner-color="secondary"
                        spinner-size="sm"
                      />
                      <div class="absolute-bottom-right q-pa-sm">
                        <q-btn
                          dense
                          square
                          color="white"
                          text-color="primary"
                          icon="add"
                          class="rounded-borders q-pl-sm q-pr-sm"
                          unelevated
                          @click.stop="
                            showItemdetails(
                              category.cat_id,
                              MenuStore.data_items[slug][items_id].item_uuid
                            )
                          "
                        />
                      </div>
                    </div>
                    <div>
                      <div
                        class="font15 text-weight-medium no-margin line-normal"
                      >
                        {{ MenuStore.data_items[slug][items_id].item_name }}
                      </div>
                      <div
                        v-if="MenuStore.data_items[slug][items_id].price"
                        class="font13 text-weight-bold no-margin line-normal"
                      >
                        <template
                          v-for="price in MenuStore.data_items[slug][items_id]
                            .price"
                          :key="price"
                        >
                          <template v-if="price.discount > 0">
                            {{ price.size_name }}
                            <span class="text-strike">{{
                              price.pretty_price
                            }}</span>
                            {{ price.pretty_price_after_discount }}
                          </template>
                          <template v-else>
                            {{ price.size_name }}
                            {{ price.pretty_price }}</template
                          ><span class="q-pr-sm"></span>
                        </template>
                      </div>
                    </div>
                  </swiper-slide>
                </template>
              </swiper>
            </div>
            <q-space class="q-pa-sm"></q-space>
          </template>
        </template>
        <!-- END MENU STARTS HERE -->
      </template>
      <!-- end MenuStore.data_info[slug] -->

      <q-space class="q-pa-md"></q-space>

      <CategoriesModal
        ref="categories_modal"
        :slug="slug"
        @after-categoryselect="afterCategoryselect"
      ></CategoriesModal>

      <ItemDetails
        ref="item_details"
        :slug="slug"
        :money_config="MenuStore.money_config"
        @after-additems="afterAdditems"
      />

      <q-page-scroller
        position="bottom-right"
        :scroll-offset="150"
        :offset="[18, 18]"
      >
        <q-btn
          fab
          icon="keyboard_arrow_up"
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          dense
          padding="3px"
        />
      </q-page-scroller>
    </q-page>

    <q-footer
      v-if="CartStore.hasItem && !CartStore.cart_loading"
      reveal
      class="bg-primary q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
    >
      <q-btn
        to="/cart"
        :loading="CartStore.cart_loading"
        :disable="StoreOpen.store_close"
        unelevated
        color="primary"
        text-color="white"
        no-caps
        class="radius10 fit"
      >
        <div class="row items-center justify-between fit">
          <div>{{ $t("View Order") }}</div>
          <div class="text-weight-bold">
            {{ CartStore.cart_subtotal.value }}
          </div>
        </div>
      </q-btn>
    </q-footer>
  </q-pull-to-refresh>

  <DeliverySched
    ref="delivery_sched"
    :slug="slug"
    @after-savetrans="afterSavetrans"
  />
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "stores/CartStore";
import { useMenuStore } from "stores/MenuStore";
import { useStoreOpen } from "stores/StoreOpen";
import { useFavoriteStore } from "stores/FavoriteStore";
import { useDeliveryschedStore } from "stores/DeliverySched";
import { scroll } from "quasar";
import auth from "src/api/auth";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";

const {
  getScrollTarget,
  setVerticalScrollPosition,
  getVerticalScrollPosition,
} = scroll;

export default {
  name: "MenuPage",
  components: {
    Swiper,
    SwiperSlide,
    ShareComponents: defineAsyncComponent(() =>
      import("components/ShareComponents.vue")
    ),
    FavsResto: defineAsyncComponent(() => import("components/FavsResto.vue")),
    CategoriesModal: defineAsyncComponent(() =>
      import("components/CategoriesModal.vue")
    ),
    ItemDetails: defineAsyncComponent(() =>
      import("components/ItemDetails.vue")
    ),
    //FavsItem: defineAsyncComponent(() => import("components/FavsItem.vue")),
    MerchantPromoSlide: defineAsyncComponent(() =>
      import("components/MerchantPromoSlide.vue")
    ),
    DeliverySched: defineAsyncComponent(() =>
      import("components/DeliverySched.vue")
    ),
  },
  data() {
    return {
      slug: "",
      category: 1,
      //transactionType: "",
      headerChangeColor: false,
      isFixed: false,
      stickyPosition: 0,
      payload: [
        "items",
        "subtotal",
        "distance_local",
        "items_count",
        "merchant_info",
        "check_opening",
        "transaction_info",
      ],
    };
  },
  setup() {
    const CartStore = useCartStore();
    const MenuStore = useMenuStore();
    const StoreOpen = useStoreOpen();
    const FavoriteStore = useFavoriteStore();
    const DeliveryschedStore = useDeliveryschedStore();
    return {
      CartStore,
      MenuStore,
      StoreOpen,
      FavoriteStore,
      DeliveryschedStore,
    };
  },
  // watch: {
  //   DeliveryschedStore: {
  //     immediate: true,
  //     deep: true,
  //     handler(newValue, oldValue) {
  //       if (!APIinterface.empty(newValue.transaction_type)) {
  //         this.transactionType = newValue.transaction_type;
  //       }
  //     },
  //   },
  // },
  mounted() {
    //this.transactionType = APIinterface.getStorage("transaction_type");
    this.slug = this.$route.params.slug;

    if (!this.CartStore.hadData()) {
      this.CartStore.getCart(true, this.payload);
    }

    if (Object.keys(this.MenuStore.data_info).length <= 0) {
      this.MenuStore.getMerchantInfo(this.slug);
    } else {
      if (!this.MenuStore.data_info[this.slug]) {
        this.MenuStore.getMerchantInfo(this.slug);
      }
    }

    if (Object.keys(this.MenuStore.data_category).length <= 0) {
      this.MenuStore.geStoreMenu(this.slug);
    } else {
      if (!this.MenuStore.data_category[this.slug]) {
        this.MenuStore.geStoreMenu(this.slug);
      }
    }

    this.StoreOpen.checkStoreOpen2(this.slug);

    if (auth.authenticated()) {
      if (Object.keys(this.FavoriteStore.items_data).length <= 0) {
        this.FavoriteStore.getItemFavorites(this.slug);
      } else {
        if (!this.FavoriteStore.items_data[this.slug]) {
          this.FavoriteStore.getItemFavorites(this.slug);
        }
      }
    }

    this.DeliveryschedStore.getDeliverySched(
      APIinterface.getStorage("cart_uuid"),
      this.slug
    );
  },
  computed: {
    classObject() {
      let $class_name = "";
      if (this.headerChangeColor) {
        $class_name = this.$q.dark.mode
          ? "bg-mydark text-white"
          : "bg-white text-black";
      } else if (!this.headerChangeColor) {
        $class_name = "bg-transparent text-black";
      }
      return $class_name;
    },
    headerBackground() {
      let bg = "";
      if (this.MenuStore.data_info[this.slug] && !this.MenuStore.loadin_info) {
        let HeaderImage = this.MenuStore.data_info[this.slug].url_logo;
        if (this.MenuStore.data_info[this.slug].has_header) {
          HeaderImage = this.MenuStore.data_info[this.slug].url_header;
        }

        bg =
          "background-image:url(" +
          HeaderImage +
          ") !important; ;background-size:cover!important;";
        return bg;
      } else return "";
    },
    getEstimation() {
      let result = "";
      let prefix = "in";
      let mins = "min";
      let transactionType = this.DeliveryschedStore.transaction_type;

      if (this.DeliveryschedStore.whento_deliver == "schedule") {
        prefix = "from";
        mins = "";
        result = this.DeliveryschedStore.trans_data.delivery_time.pretty_time;
      } else {
        if (this.MenuStore.data_estimation[this.slug]) {
          if (this.MenuStore.data_estimation[this.slug][transactionType]) {
            if (
              this.MenuStore.data_estimation[this.slug][transactionType][
                this.MenuStore.data_charge_type[this.slug]
              ]
            ) {
              result =
                this.MenuStore.data_estimation[this.slug][transactionType][
                  this.MenuStore.data_charge_type[this.slug]
                ].estimation;
            }
          }
        }
      }

      if (!APIinterface.empty(result)) {
        return prefix + " " + result + " " + mins;
      }
      return "";
    },
  },
  methods: {
    afterCategoryselect(data) {
      this.$refs.categories_modal.modal = false;
      this.scrollToElement(data);
    },
    scrollToElement(id) {
      const ele = document.getElementById(id);
      const target = getScrollTarget(ele);
      const offset = ele.offsetTop;
      const duration = 500;
      setVerticalScrollPosition(target, offset - 50, duration);
    },
    goSearch() {
      this.$router.push({
        name: "items",
        query: { slug: this.slug },
      });
    },
    onScroll(info) {
      if (info.direction == "down") {
        if (info.position.top <= 140) {
          this.headerChangeColor = true;
        }
      } else {
        if (info.position.top <= 140) {
          this.headerChangeColor = false;
        }
      }
    },
    refresh(done) {
      done();
      this.MenuStore.data_info = {};
      this.CartStore.getCart(true, this.payload);
      this.MenuStore.getMerchantInfo(this.slug);
      this.MenuStore.geStoreMenu(this.slug);
      this.$refs.merchantPromoSlide.refresh();
      if (auth.authenticated()) {
        this.FavoriteStore.getItemFavorites(this.slug);
      }
    },
    showItemdetails(cat_id, item_uuid) {
      const params = { cat_id: cat_id, item_uuid: item_uuid };
      this.$refs.item_details.showItem2(params, this.slug);
    },
    afterAdditems() {
      APIinterface.setStorage("merchant_slug", this.slug);
      //this.CartStore.cart_items = [];
      this.CartStore.getCart(true, this.payload);
    },
    // afterSavefav(item) {
    //   item.saved_store = !item.saved_store;
    // },
    afterSavefav(data, added) {
      data.saved_store = added;
    },
    itemsFav(item_id) {
      let saveItems = [];
      if (this.FavoriteStore.items_data[this.slug]) {
        saveItems = this.FavoriteStore.items_data[this.slug];
      }
      if (Object.keys(saveItems).length > 0) {
        if (saveItems.includes(item_id)) {
          return true;
        }
      }
      return false;
    },
    afterSavefavItem(data, items) {
      this.FavoriteStore.getItemFavorites(this.slug);
    },
    afterSavetrans() {
      this.DeliveryschedStore.getDeliverySched(
        APIinterface.getStorage("cart_uuid"),
        this.slug
      );
      this.StoreOpen.checkStoreOpen2(this.slug);
    },
  },
};
</script>
