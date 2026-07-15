<template>
  <q-dialog v-model="item_dialog" position="bottom">
    <q-card class="rounded-borders-top">
      <template v-if="loading">
        <div class="text-center flex flex-center" style="height: 200px">
          <q-circular-progress
            indeterminate
            rounded
            size="md"
            color="secondary"
            class="q-ma-md"
          />
        </div>
      </template>
      <DIV v-else class="q-pl-md q-pr-md q-pb-sm q-pt-md">
        <div class="row items-start q-gutter-sm q-mb-sm">
          <div class="col-3">
            <q-img
              :src="this.image_featured ? this.image_featured : items.url_image"
              lazy
              fit="cover"
              style="height: 80px; width: 80px"
              class="radius8"
              spinner-color="secondary"
              spinner-size="sm"
            />
          </div>
          <div class="col">
            <div class="font13 text-weight-bold no-margin line-normal">
              {{ items.item_name }}
            </div>
            <div class="text-grey ellipsis-2-lines font12 line-normal">
              <span v-html="items.item_description"></span>
            </div>
            <q-chip
              size="sm"
              color="secondary"
              text-color="secondary"
              icon="star"
              class="no-padding transparent"
            >
              <span class="text-weight-medium text-dark">0.0</span>
            </q-chip>
          </div>
          <div class="col-2 text-center row items-stretch">
            <div class="column items-center col-12">
              <div class="col q-mb-xs">
                <q-btn
                  @click="item_dialog = !true"
                  color="white"
                  square
                  unelevated
                  text-color="grey"
                  icon="las la-times"
                  dense
                  no-caps
                  size="sm"
                  class="border-grey radius8"
                />
              </div>
              <div class="col">
                <FavsItem
                  ref="favs"
                  :layout="1"
                  :item_token="items.item_token"
                  :cat_id="items.cat_id"
                  :active="items.save_item"
                  @after-savefav="afterSavefav(items)"
                />
              </div>
            </div>
          </div>
        </div>

        <ItemGallery
          :item_gallery="item_gallery"
          @after-selectimage="afterSelectimage"
        ></ItemGallery>

        <!-- SIZE  -->
        <div class="q-mb-sm">
          <div class="font13 text-weight-bold no-margin line-normal q-pb-sm">
            {{ $t("Size") }}
          </div>
          <q-btn-toggle
            v-model="item_size_id"
            toggle-color="secondary"
            color="mygrey"
            text-color="dark"
            no-caps
            no-wrap
            unelevated
            :options="size_data"
            class="rounded-group2 small text-weight-bold line-1"
          />
        </div>
        <!-- SIZE  -->

        <!-- Cooking Reference  -->
        <div v-if="cooking_data.length > 0" class="q-mb-sm">
          <div class="font13 text-weight-bold no-margin line-normal">
            {{ $t("Cooking Reference") }}
          </div>
          <div
            v-if="items.cooking_ref_required"
            class="text-red font12 text-weight-medium q-mb-sm"
          >
            ({{ $t("Required") }})
          </div>
          <div v-else class="text-grey font12 text-weight-medium q-mb-sm">
            {{ $t("Optional") }}
          </div>
          <q-btn-toggle
            v-model="cooking_ref"
            toggle-color="secondary"
            color="mygrey"
            text-color="dark"
            no-caps
            no-wrap
            unelevated
            :options="cooking_data"
            class="rounded-group2 small text-weight-bold line-1"
          />
        </div>
        <!-- Cooking Reference  -->

        <!-- Ingredients  -->
        <div v-if="ingredients_data.length > 0" class="q-mb-sm">
          <div class="font13 text-weight-bold no-margin line-normal">
            {{ $t("Ingredients") }}
          </div>
          <div class="text-grey font12 text-weight-medium q-mb-sm">
            {{ $t("Optional") }}
          </div>

          <q-btn-group
            no-caps
            no-wrap
            unelevated
            class="rounded-group2 text-weight-bold line-1"
          >
            <q-btn
              no-caps
              v-for="(button, index) in ingredients_data"
              :key="index"
              :color="button.color"
              :text-color="button.text_color"
              :label="button.label"
              @click="setActive(button, index)"
            ></q-btn>
          </q-btn-group>
          <!-- <q-btn-toggle
            v-model="ingredients"
            toggle-color="secondary"
            color="mygrey"
            text-color="dark"
            no-caps
            no-wrap
            unelevated
            :options="ingredients_data"
            class="rounded-group2 small text-weight-bold line-1"
          /> -->
        </div>
        <!-- Ingredients  -->

        <!-- ADDONS -->
        <!-- <pre>{{ addons }}</pre> -->
        <template v-if="addons[item_size_id]">
          <template
            v-for="addons in addons[item_size_id]"
            :key="addons.subcat_id"
          >
            <DIV class="q-mb-md">
              <div class="font13 text-weight-bold no-margin line-normal">
                {{ addons.subcategory_name }}
              </div>
              <div class="text-grey font12 text-weight-medium q-mb-sm">
                <template v-if="addons.multi_option === 'one'">
                  {{ $t("Select 1") }}
                </template>
                <template v-else-if="addons.multi_option === 'multiple'">
                  <template v-if="addons.multi_option_min > 0">
                    {{
                      $t("Select minimum {min} to maximum {max}", {
                        min: addons.multi_option_min,
                        max: addons.multi_option_value,
                      })
                    }}
                  </template>
                  <template v-else>
                    {{ $t("Choose up to") }} {{ addons.multi_option_value }}
                  </template>
                </template>
                <template v-else-if="addons.multi_option === 'custom'">
                  <template v-if="addons.multi_option_min > 0">
                    {{
                      $t("Select minimum {min} to maximum {max}", {
                        min: addons.multi_option_min,
                        max: addons.multi_option_value,
                      })
                    }}
                  </template>
                  <template v-else>
                    {{ $t("Choose up to") }} {{ addons.multi_option_value }}
                  </template>
                </template>
                <template v-if="addons.require_addon == 1">
                  <span class="q-ml-sm text-red">({{ $t("Required") }})</span>
                </template>
                <template v-else>
                  <span class="q-ml-sm">({{ $t("Optional") }})</span>
                </template>
              </div>

              <!-- addons -->
              <div class="row q-gutter-none addon-carousel q-col-gutter-sm">
                <div
                  v-for="sub_items in addons.sub_items"
                  :key="sub_items.sub_item_id"
                  class="col-lg-3 col-md-3 col-sm-6 col-xs-4 text-center"
                >
                  <q-card flat class="column items-center full-height">
                    <!-- =>{{ addons.multi_option }} -->
                    <template v-if="addons.multi_option === 'one'">
                      <q-radio
                        v-model="addons.sub_items_checked"
                        :val="sub_items.sub_item_id"
                        label=""
                      >
                        <q-avatar size="50px" square class="col self-center">
                          <q-img
                            fit="contain"
                            :src="
                              sub_items.hasimage
                                ? sub_items.url_image
                                : 'default-addon.png'
                            "
                            class="no-margin"
                            height="50px"
                            loading="lazy"
                            placeholder-src="placeholder.png"
                            spinner-color="secondary"
                            spinner-size="sm"
                          />
                        </q-avatar>
                      </q-radio>
                    </template>

                    <template v-else-if="addons.multi_option === 'multiple'">
                      <div>
                        <q-avatar size="50px" square class="col self-center">
                          <q-img
                            fit="contain"
                            :src="
                              sub_items.hasimage
                                ? sub_items.url_image
                                : 'default-addon.png'
                            "
                            class="no-margin"
                            height="50px"
                            loading="lazy"
                            placeholder-src="placeholder.png"
                            spinner-color="secondary"
                            spinner-size="sm"
                          />
                        </q-avatar>
                      </div>
                    </template>

                    <template v-else-if="addons.multi_option === 'custom'">
                      <q-checkbox
                        v-model="sub_items.checked"
                        :val="sub_items.sub_item_id"
                        label=""
                        :disable="sub_items.disabled"
                      >
                        <q-avatar size="50px" square class="col self-center">
                          <q-img
                            fit="contain"
                            :src="
                              sub_items.hasimage
                                ? sub_items.url_image
                                : 'default-addon.png'
                            "
                            class="no-margin"
                            height="50px"
                            loading="lazy"
                            spinner-color="secondary"
                            spinner-size="sm"
                            placeholder-src="placeholder.png"
                          />
                        </q-avatar>
                      </q-checkbox>
                    </template>

                    <p
                      class="font12 q-mb-none col ellipsis-2-lines line-normal q-pb-sm text-weight-medium"
                    >
                      {{ sub_items.sub_item_name }}
                    </p>
                    <p class="no-margin col font11 text-weight-medium">
                      {{ sub_items.pretty_price }}
                    </p>

                    <!-- multiple button -->
                    <div
                      v-if="addons.multi_option === 'multiple'"
                      class="col-auto w-75"
                    >
                      <div
                        v-if="sub_items.checked == true"
                        class="row items-center justify-center"
                      >
                        <div class="col no-padding text-center">
                          <q-btn
                            @click="
                              sub_items.qty > 1
                                ? sub_items.qty--
                                : (sub_items.checked = false)
                            "
                            unelevated
                            dense
                            size="11px"
                            icon="remove"
                            color="primary"
                            class="radius8"
                          />
                        </div>
                        <div
                          class="col no-padding text-center text-weight-medium"
                        >
                          {{ sub_items.qty }}
                        </div>
                        <div class="col no-padding text-center">
                          <q-btn
                            @click="sub_items.qty++"
                            unelevated
                            dense
                            size="11px"
                            color="primary"
                            icon="add"
                            class="radius8"
                            :disable="sub_items.disabled"
                          />
                        </div>
                      </div>
                      <div v-else>
                        <q-btn
                          @click="sub_items.checked = true"
                          unelevated
                          dense
                          size="11px"
                          icon="add"
                          color="mygrey"
                          text-color="dark"
                          class="radius8"
                          :disable="sub_items.disabled"
                        />
                      </div>
                    </div>
                    <!-- multiple button -->
                  </q-card>
                </div>
                <!-- col -->
              </div>
              <!-- row -->

              <!-- end addons -->
            </DIV>
          </template>
        </template>
        <!-- ADDONS -->

        <div class="text-weight-bold font13 q-mt-sm">
          {{ $t("Special Instructions") }}
        </div>
        <q-input
          v-model="special_instructions"
          autogrow
          outlined
          class="q-pa-none"
        />

        <div class="text-weight-bold font13 q-mt-sm">
          {{ $t("If sold out") }}
        </div>
        <q-select
          outlined
          dense
          v-model="if_sold_out"
          :options="sold_out_options"
          color="secondary"
          class="q-mb-md"
          transition-show="scale"
          transition-hide="scale"
        />

        <q-space class="q-pa-xl"></q-space>

        <div
          class="fixed-bottom q-pa-md border-grey-top row"
          :class="{
            'bg-dark': $q.dark.mode,
            'bg-white': !$q.dark.mode,
          }"
        >
          <div class="col-5">
            <q-btn-group unelevated class="radius8">
              <q-btn
                @click="item_qty > 1 ? item_qty-- : 1"
                :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :text-color="$q.dark.mode ? 'grey300' : 'grey'"
                icon="o_remove"
                size="md"
                dense
                class="q-pa-sm"
              />
              <q-btn
                :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :text-color="$q.dark.mode ? 'grey300' : 'dark'"
                dense
                :label="item_qty"
                class="no-pointer-events text-weight-medium"
                style="min-width: 30px"
              />
              <q-btn
                @click="item_qty++"
                :color="$q.dark.mode ? 'grey600' : 'mygrey'"
                :text-color="$q.dark.mode ? 'grey300' : 'grey'"
                icon="o_add"
                size="md"
                dense
                class="q-pa-sm"
              />
            </q-btn-group>
          </div>
          <div class="col-7">
            <q-btn
              unelevated
              color="primary"
              text-color="white"
              class="fit radius8 q-pa-sm"
              no-caps
              @click="CheckaddCartItems"
              :disable="disabled_cart"
              :loading="loading_add"
              size="md"
              dense
            >
              <div class="row justify-between items-center fit">
                <div class="text-weight-medium">
                  <template v-if="items.not_for_sale">
                    {{ $t("Not for sale") }}
                  </template>
                  <template v-else>
                    {{ $t("Add to cart") }}
                  </template>
                </div>
                <div class="text-weight-bold">
                  <NumberFormat
                    :amount="item_total"
                    :money_config="money_config"
                  ></NumberFormat>
                </div>
              </div>
            </q-btn>
          </div>
        </div>
      </DIV>
    </q-card>
  </q-dialog>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
//import { format } from "v-money3";
import { useCartStore } from "stores/CartStore";
import { useFavoriteStore } from "stores/FavoriteStore";
import { useDeliveryschedStore } from "stores/DeliverySched";

const empty = function (data) {
  if (
    typeof data === "undefined" ||
    data === null ||
    data === "" ||
    data === "null" ||
    data === "undefined"
  ) {
    return true;
  }
  return false;
};

export default {
  name: "ItemDetails",
  props: ["slug", "money_config", "currency_code"],
  components: {
    FavsItem: defineAsyncComponent(() => import("components/FavsItem.vue")),
    NumberFormat: defineAsyncComponent(() =>
      import("components/NumberFormat.vue")
    ),
    ItemGallery: defineAsyncComponent(() =>
      import("components/ItemGallery.vue")
    ),
  },
  setup() {
    const CartStore = useCartStore();
    const FavoriteStore = useFavoriteStore();
    const schedStore = useDeliveryschedStore();
    return { CartStore, FavoriteStore, schedStore };
  },
  data() {
    return {
      item_dialog: false,
      loading: true,
      loading_add: false,
      item_qty: 1,
      items: [],
      item_size_id: 0,
      size_data: [],
      size_datas: [],
      cooking_ref: 0,
      cooking_data: [],
      ingredients: [],
      ingredients_data: [],
      addons: {},
      special_instructions: "",
      transaction_type: "",
      if_sold_out: "",
      sold_out_options: [],
      item_total: 0,
      disabled_cart: true,
      slide_items: 0,
      favorites: [],
      // config: [],
      restaurant_name: "",
      merchant_id: "",
      data_cat_id: "",
      data_item_token: "",
      merchantSlug: "",
      item_gallery: [],
      image_featured: "",
    };
  },
  updated() {
    // this.resetData()
    // this.merchantSlug = this.slug
  },
  mounted() {
    this.merchantSlug = this.slug;
  },
  watch: {
    addons: {
      handler(newValue, oldValue) {
        this.ItemSummary();
      },
      deep: true,
    },
    item_size_id() {
      this.ItemSummary();
    },
    cooking_ref() {
      this.ItemSummary();
    },
    ingredients() {
      this.ItemSummary();
    },
    item_qty() {
      this.ItemSummary();
    },
    ingredients_data: {
      handler(newValue, oldValue) {
        this.ingredients = [];
        if (Object.keys(this.ingredients_data).length > 0) {
          Object.entries(this.ingredients_data).forEach(([key, items]) => {
            if (items.onOff) {
              this.ingredients.push(items.value);
            }
          });
        }
      },
      deep: true,
    },
  },
  methods: {
    isClickable(data) {
      if (data.multi_option === "multiple") {
        return "";
      }
      return "label";
    },
    isRipple(data) {
      if (data.multi_option === "multiple") {
        return false;
      }
      return true;
    },
    resetData() {
      this.item_qty = 1;
      this.items = [];
      this.item_size_id = 0;
      this.size_data = [];
      this.size_datas = [];
      this.cooking_ref = 0;
      this.cooking_data = [];
      this.ingredients = [];
      this.ingredients_data = [];
      this.addons = {};
      this.special_instructions = "";
      this.transaction_type = "";
      this.if_sold_out = "";
      this.sold_out_options = [];
      this.item_total = 0;
      this.disabled_cart = true;
      this.slide_items = 0;
      this.data_cat_id = "";
      this.data_item_token = "";
      this.image_featured = "";
    },
    showItem2(data, slug) {
      if (typeof slug !== "undefined" && slug !== null) {
        this.merchantSlug = slug;
        this.showItem(data);
      }
    },
    showItem(data) {
      this.resetData();
      this.item_dialog = true;
      this.loading = true;

      this.data_cat_id = data.cat_id;
      this.data_item_token = data.item_uuid;

      if (
        typeof this.merchantSlug !== "undefined" &&
        this.merchantSlug !== null
      ) {
        //
      } else {
        return;
      }

      APIinterface.getMenuItem(
        data.cat_id,
        data.item_uuid,
        this.merchantSlug,
        this.currency_code
      )
        .then((data) => {
          this.merchant_id = data.details.merchant_id;
          this.restaurant_name = data.details.restaurant_name;
          this.items = data.details.data.items;
          this.size_datas = data.details.data.items.price;
          const soldOutData = data.details.sold_out_options;

          this.if_sold_out = data.details.default_sold_out_options;

          const prices = data.details.data.items.price;
          const metaCookingRef = data.details.data.meta
            ? data.details.data.meta.cooking_ref
            : {};
          const metaCookingRefDetails = data.details.data.meta
            ? data.details.data.meta_details.cooking_ref
            : {};

          const metaIngredients = data.details.data.meta
            ? data.details.data.meta.ingredients
            : {};
          const metaIngredientsDetails = data.details.data.meta
            ? data.details.data.meta_details.ingredients
            : {};

          this.item_gallery = data.details.data.meta
            ? data.details.data.meta.item_gallery
            : {};

          const addons = data.details.data ? data.details.data.addons : {};
          const addonItems = data.details.data
            ? data.details.data.addon_items
            : {};

          if (Object.keys(soldOutData).length > 0) {
            Object.entries(soldOutData).forEach(
              ([itemSoldKey, itemsSoldData]) => {
                this.sold_out_options.push({
                  label: itemsSoldData,
                  value: itemSoldKey,
                });
              }
            );
          }

          if (Object.keys(prices).length > 0) {
            Object.entries(prices).forEach(([key, items]) => {
              if (items.discount <= 0) {
                this.size_data.push({
                  label: items.size_name + " " + items.pretty_price,
                  value: parseInt(items.item_size_id),
                });
              } else {
                this.size_data.push({
                  label:
                    items.size_name + " " + items.pretty_price_after_discount,
                  value: parseInt(items.item_size_id),
                });
              }
            });
            this.item_size_id = parseInt(Object.keys(prices)[0]);
          }

          if (
            typeof metaCookingRef !== "undefined" &&
            metaCookingRef !== null
          ) {
            if (metaCookingRef.length > 0) {
              Object.entries(metaCookingRef).forEach(([key, value]) => {
                this.cooking_data.push({
                  label: metaCookingRefDetails[value].meta_name,
                  value: metaCookingRefDetails[value].meta_id,
                });
              });
            }
          }

          if (
            typeof metaIngredients !== "undefined" &&
            metaIngredients !== null
          ) {
            if (metaIngredients.length > 0) {
              Object.entries(metaIngredients).forEach(([key, value]) => {
                this.ingredients_data.push({
                  label: metaIngredientsDetails[value].meta_name,
                  value: metaIngredientsDetails[value].meta_id,
                  color: this.items.ingredients_preselected
                    ? "primary"
                    : "mygrey",
                  text_color: this.items.ingredients_preselected
                    ? "white"
                    : "dark",
                  onOff: this.items.ingredients_preselected ? true : false,
                });
              });
            }
          }

          // addons
          // const addonsData = []
          console.log(this.items.item_addons);
          if (Object.keys(this.items.item_addons).length > 0) {
            Object.entries(this.items.item_addons).forEach(
              ([sizeId, SubcatID]) => {
                const addOnsAdded = [];
                Object.entries(SubcatID).forEach(([key, child]) => {
                  if (!APIinterface.empty(addons[sizeId])) {
                    if (!APIinterface.empty(addons[sizeId][child])) {
                      const addonDetails = addons[sizeId][child];

                      const subItems = [];
                      Object.entries(addonDetails.sub_items).forEach(
                        ([key2, subItemsID]) => {
                          if (addonItems[subItemsID]) {
                            const subItemsAdd = addonItems[subItemsID];
                            addonItems[subItemsID].checked = false;
                            addonItems[subItemsID].disabled = false;
                            addonItems[subItemsID].qty = 1;
                            subItems.push(subItemsAdd);
                          }
                        }
                      );

                      const subdata = {
                        subcat_id: addonDetails.subcat_id,
                        subcategory_name: addonDetails.subcategory_name,
                        subcategory_description:
                          addonDetails.subcategory_description,
                        multi_option: addonDetails.multi_option,
                        multi_option_min: addonDetails.multi_option_min,
                        multi_option_value: addonDetails.multi_option_value,
                        require_addon: addonDetails.require_addon,
                        pre_selected: addonDetails.pre_selected,
                        sub_items_checked: "",
                        sub_items: subItems,
                      };
                      addOnsAdded.push(subdata);
                    }
                  }
                });
                this.addons[sizeId] = addOnsAdded;
              }
            );
          }
          //
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
          this.item_dialog = false;
        })
        .then((data) => {
          this.loading = false;
        });
    },
    ItemSummary() {
      let $itemTotal = 0;
      const $requiredAddon = [];
      const $requiredAddonAdded = [];
      let $min_addon = [];
      let $min_addon_added = [];

      if (!empty(this.size_datas[this.item_size_id])) {
        const item = this.size_datas[this.item_size_id];
        if (item.discount > 0) {
          $itemTotal += this.item_qty * parseFloat(item.price_after_discount);
        } else $itemTotal += this.item_qty * parseFloat(item.price);
      }

      if (!empty(this.addons[this.item_size_id])) {
        this.addons[this.item_size_id].forEach((item, index) => {
          if (item.require_addon === "1") {
            $requiredAddon.push(item.subcat_id);
          }

          if (item.multi_option === "custom") {
            let totalCheck = 0;
            const multiOptionValue = item.multi_option_value;
            let multi_option_min = item.multi_option_min;

            if (multiOptionValue > 0) {
              $min_addon.push({
                subcat_id: item.subcat_id,
                min: multi_option_min,
                max: multiOptionValue,
              });
            }

            const itemIndex = [];
            const itemIndex2 = [];
            item.sub_items.forEach((item2, index2) => {
              if (item2.checked === true) {
                totalCheck++;
                $itemTotal += this.item_qty * parseFloat(item2.price);
                $requiredAddonAdded.push(item.subcat_id);
              } else itemIndex.push(index2);

              if (item2.disabled === true) {
                itemIndex2.push(index2);
              }
            });

            $min_addon_added[item.subcat_id] = {
              total: totalCheck,
            };

            if (totalCheck >= multiOptionValue) {
              itemIndex.forEach((item3, index3) => {
                item.sub_items[item3].disabled = true;
              });
            } else {
              itemIndex2.forEach((item3, index3) => {
                item.sub_items[item3].disabled = false;
              });
            }
          } else if (item.multi_option === "one") {
            item.sub_items.forEach((item2, index2) => {
              if (item2.sub_item_id === item.sub_items_checked) {
                $itemTotal += this.item_qty * parseFloat(item2.price);
                $requiredAddonAdded.push(item.subcat_id);
              }
            });
          } else if (item.multi_option === "multiple") {
            let multi_option_min = item.multi_option_min;
            const multiOptionValue = item.multi_option_value;

            if (multiOptionValue > 0) {
              $min_addon.push({
                subcat_id: item.subcat_id,
                min: multi_option_min,
                max: multiOptionValue,
              });
            }

            let TotalMultiQty = 0;
            const itemIndex = [];
            item.sub_items.forEach((item2, index2) => {
              if (item2.checked === true) {
                $itemTotal += item2.qty * parseFloat(item2.price);
                TotalMultiQty += item2.qty;
                $requiredAddonAdded.push(item.subcat_id);
              } else itemIndex.push(index2);
            });

            $min_addon_added[item.subcat_id] = {
              total: TotalMultiQty,
            };

            console.log("TotalMultiQty=>" + TotalMultiQty);
            if (TotalMultiQty >= multiOptionValue) {
              item.sub_items.forEach((sub_items3, sub_items_index3) => {
                sub_items3.disabled = true;
              });
            } else {
              item.sub_items.forEach((sub_items3, sub_items_index3) => {
                sub_items3.disabled = false;
              });
            }
          } /* endif custom */
        });
        // end loop addons
      }

      //if (Object.keys(this.money_config).length > 0) {
      this.item_total = $itemTotal;
      //}

      let $requiredMeet = true;
      // console.log("requiredAddonAdded");
      // console.log($requiredAddonAdded);
      if ($requiredAddon.length > 0) {
        $requiredAddon.forEach((requiedItem, requiredIndex) => {
          if ($requiredAddonAdded.includes(requiedItem) === false) {
            $requiredMeet = false;
            return false;
          }
        });
      }

      // CHECK COOKING REF
      if (this.items.cooking_ref_required) {
        if (this.cooking_ref > 0) {
          $requiredMeet = true;
        } else {
          $requiredMeet = false;
        }
      }

      // CHECK ADDON MINIMUM AND MAXIMUM
      if (Object.keys($min_addon).length > 0) {
        let min_value, min_selected;
        Object.entries($min_addon).forEach(
          ([key_min_addon, items_min_addon]) => {
            min_value = parseInt(items_min_addon.min);
            if ($min_addon_added[items_min_addon.subcat_id]) {
              min_selected = parseInt(
                $min_addon_added[items_min_addon.subcat_id].total
              );
            }
            if (min_selected > 0) {
              if (min_value > min_selected) {
                $requiredMeet = false;
              }
            }
          }
        );
      }

      if (this.items.not_for_sale) {
        $requiredMeet = false;
      }

      if ($requiredMeet) {
        this.disabled_cart = false;
      } else this.disabled_cart = true;
    },
    CheckaddCartItems() {
      console.log("CheckaddCartItems");
      let $cartMerchantID = "";
      let $cartMerchantName = "";
      console.log(this.CartStore.cart_merchant);
      console.log(this.CartStore.cart_merchant.merchant_id);
      console.log(this.CartStore.cart_merchant.restaurant_name);

      if (!APIinterface.empty(this.CartStore.cart_merchant)) {
        $cartMerchantID = this.CartStore.cart_merchant.merchant_id;
        $cartMerchantName = this.CartStore.cart_merchant.restaurant_name;
      }

      console.log($cartMerchantID + "=>" + this.merchant_id);

      if (!APIinterface.empty($cartMerchantID)) {
        if ($cartMerchantID !== this.merchant_id) {
          let $message =
            "Your order contains items from {{restaurant_name}}. Create a new order to add items.";
          $message = $message.replace("{{restaurant_name}}", $cartMerchantName);
          this.$q
            .dialog({
              title: "Create new order?",
              message: $message,
              persistent: true,
              position: "standard",
              transitionShow: "fade",
              transitionHide: "fade",
              ok: {
                unelevated: true,
                color: "primary",
                rounded: false,
                "text-color": "white",
                size: "md",
                label: "New order",
                "no-caps": true,
                class: "radius8",
              },
              cancel: {
                unelevated: true,
                rounded: false,
                color: "mygrey",
                "text-color": "black",
                size: "md",
                label: "Cancel",
                "no-caps": true,
                class: "radius8",
              },
            })
            .onOk(() => {
              this.clearCart();
            })
            .onCancel(() => {
              // console.log('>>>> Cancel')
            })
            .onDismiss(() => {
              // console.log('I am triggered on both OK and Cancel')
            });
        } else {
          this.AddToCart();
        }
      } else {
        this.AddToCart();
      }
    },
    clearCart() {
      const $cartUuid = APIinterface.getStorage("cart_uuid");
      APIinterface.clearCart($cartUuid)
        .then((data) => {
          this.AddToCart();
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {});
    },
    AddToCart() {
      const $ingredients = [];
      if (this.ingredients.length > 0) {
        this.ingredients.forEach((ingredientsId, index) => {
          $ingredients.push({
            meta_id: ingredientsId,
            checked: true,
            meta_name: "",
          });
        });
      }

      const $meta = {
        cooking_ref: [
          {
            meta_id: this.cooking_ref,
            checked: this.cooking_ref,
            meta_name: "",
          },
        ],
        ingredients: $ingredients,
      };
      const $cartUuid = APIinterface.getStorage("cart_uuid");
      const $data = {
        slug: this.merchantSlug,
        cart_uuid: $cartUuid,
        cat_id: this.data_cat_id,
        item_size_id: this.item_size_id,
        item_token: this.data_item_token,
        item_qty: this.item_qty,
        special_instructions: this.special_instructions,
        if_sold_out: this.if_sold_out.value,
        transaction_type: this.schedStore.transaction_type,
        meta: $meta,
        item_addons: !empty(this.addons[this.item_size_id])
          ? this.addons[this.item_size_id]
          : [],
      };

      this.loading_add = true;
      APIinterface.AddToCart($data)
        .then((data) => {
          if (empty($cartUuid)) {
            APIinterface.setStorage("cart_uuid", data.details.cart_uuid);
          }
          this.$emit("afterAdditems");
          this.item_dialog = false;
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading_add = false;
        });
    },
    afterSavefav(item) {
      item.save_item = !item.save_item;
      this.FavoriteStore.getItemFavorites(this.slug);
    },
    setActive(button, index) {
      if (button.onOff) {
        this.ingredients_data[index].color = "mygrey";
        this.ingredients_data[index].text_color = "dark";
        this.ingredients_data[index].onOff = false;
      } else {
        this.ingredients_data[index].color = "primary";
        this.ingredients_data[index].text_color = "white";
        this.ingredients_data[index].onOff = true;
      }
    },
    afterSelectimage(data) {
      this.image_featured = data;
    },
  },
};
</script>
