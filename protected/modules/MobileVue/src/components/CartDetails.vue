<template>
  <!-- LOADING -->
  <DIV v-if="CartStore.cart_loading">
    <q-inner-loading
      :showing="true"
      color="primary"
      size="md"
      label-class="dark"
    />
  </DIV>
  <!-- LOADING -->

  <DIV v-else>
    <template v-if="CartStore.items_count > 0">
      <q-list separator dense>
        <template
          v-for="(items, index) in CartStore.cart_items"
          :key="items.item_id"
        >
          <q-slide-item
            @right="(opt) => onRight(opt, index)"
            left-color="white"
            :right-color="$q.dark.mode ? 'grey600' : 'white'"
          >
            <template v-slot:right>
              <div class="row items-center inline q-gutter-md">
                <q-btn
                  round
                  unelevated
                  color="lightprimary"
                  text-color="primary"
                  size="sm"
                  icon="las la-times"
                  @click="closeSlide(index)"
                />
                <FavsItem
                  ref="favs"
                  :layout="2"
                  :item_token="items.item_token"
                  :cat_id="items.cat_id"
                  :active="false"
                  size="md"
                  @after-savefav="afterSavefav(items)"
                />
                <q-btn
                  round
                  unelevated
                  color="lightprimary"
                  text-color="primary"
                  size="sm"
                  icon="las la-trash-alt"
                  @click="removeItem(items)"
                />
              </div>
            </template>
            <template v-slot:default>
              <q-item
                :class="{
                  'bg-mydark text-white': $q.dark.mode,
                  'bg-white text-black': !$q.dark.mode,
                }"
              >
                <q-item-section avatar>
                  <q-img
                    :src="items.url_image"
                    lazy
                    fit="cover"
                    style="height: 70px; width: 70px"
                    class="radius8"
                    spinner-color="secondary"
                    spinner-size="sm"
                    placeholder-src="placeholder.png"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    <div class="font13 text-weight-bold no-margin line-normal">
                      {{ items.item_name }}
                      <template v-if="items.price.size_name != ''">
                        ({{ items.price.size_name }})
                      </template>
                    </div>

                    <!-- details -->
                    <div class="text-grey ellipsis-2-linesx font11 line-normal">
                      <template
                        v-for="attributes in items.attributes"
                        :key="attributes"
                      >
                        <template
                          v-for="attributes_data in attributes"
                          :key="attributes_data"
                        >
                          <span class="q-mr-xs">{{ attributes_data }},</span>
                        </template>
                      </template>

                      <template v-for="addons in items.addons" :key="addons">
                        <div
                          v-for="addon_items in addons.addon_items"
                          :key="addon_items"
                        >
                          {{ addon_items.sub_item_name }} (+{{
                            addon_items.pretty_addons_total
                          }})
                        </div>
                      </template>

                      <div
                        v-if="items.special_instructions != ''"
                        class="text-dark"
                      >
                        "{{ items.special_instructions }}"
                      </div>
                    </div>
                    <!-- end details -->

                    <div class="row justify-between items-center">
                      <div class="text-grey-7 font12 text-weight-medium">
                        <template v-if="items.price.discount <= 0">
                          {{ items.price.pretty_total }}
                        </template>
                        <template v-else>
                          {{ items.price.pretty_total_after_discount }}
                        </template>
                      </div>
                      <div class="col-5">
                        <div class="row items-center justify-center">
                          <div class="col no-padding text-center">
                            <q-btn
                              v-if="items.qty == 1"
                              unelevated
                              dense
                              size="11px"
                              icon="delete"
                              color="primary"
                              class="radius8"
                              @click="removeItem(items)"
                            />
                            <q-btn
                              v-else
                              unelevated
                              dense
                              size="11px"
                              icon="remove"
                              color="primary"
                              class="radius8"
                              @click="updateCartQty(-1, items.qty, items)"
                            />
                            <!-- @click="items.qty > 1 ? items.qty-- : 1" -->
                          </div>
                          <div
                            class="col no-padding text-center text-weight-medium"
                          >
                            {{ items.qty }}
                          </div>
                          <div class="col no-padding text-center">
                            <q-btn
                              unelevated
                              dense
                              size="11px"
                              color="primary"
                              icon="add"
                              class="radius8"
                              @click="updateCartQty(1, items.qty, items)"
                            />
                            <!-- @click="items.qty++" -->
                          </div>
                        </div>
                      </div>
                    </div>
                  </q-item-label>
                </q-item-section>
              </q-item>
            </template>
          </q-slide-item>
        </template>
      </q-list>
    </template>
    <!-- end items count -->

    <!-- <template v-else> You don't have any orders here! </template> -->
  </DIV>
  <!-- end loading card -->
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "src/stores/CartStore";

export default {
  name: "CartDetails",
  props: ["payload", "is_checkout", "page"],
  components: {
    FavsItem: defineAsyncComponent(() => import("components/FavsItem.vue")),
  },
  data() {
    return {
      loading: false,
      items_count: 0,
      cart_loading: true,
      cart_reloading: false,
      cart_uuid: "",
      cart_items: [],
      cart_summary: [],
      cart_merchant: [],
      cart_total: [],
      cart_subtotal: [],
      error: [],
      qty_options: [1, 2, 3, 4, 5, 6, 7, 8, 9],
      transaction_data: [],
      delivery_option: [],
      services_list: [],
      out_of_range: false,
      is_close_slide: false,
      data_slide: {},
    };
  },
  setup() {
    const CartStore = useCartStore();
    return { CartStore };
  },
  methods: {
    updateCartQty(Qty, itemQty, item) {
      let QtyTotal = itemQty + Qty;
      item.qty = QtyTotal;
      this.updateCartItems(QtyTotal, item);
    },
    updateCartItems(itemQty, item) {
      this.loading = true;
      APIinterface.updateCartItems(
        this.CartStore.cart_uuid,
        item.cart_row,
        itemQty
      )
        .then((data) => {
          this.CartStore.getCart(false, this.payload);
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    closeSlide(index) {
      console.log(index);
      if (this.data_slide[index]) {
        console.log(this.data_slide[index]);
        this.data_slide[index].reset();
      }
    },
    onRight(details, index) {
      this.data_slide[index] = details;
    },
    removeItem(items) {
      this.loading = true;
      APIinterface.removeCartItem(this.CartStore.cart_uuid, items.cart_row)
        .then((data) => {
          this.CartStore.getCart(false, this.payload);
          this.$emit("afterRemoveitem");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    clearCart() {
      this.loading = false;
      APIinterface.clearCart(APIinterface.getStorage("cart_uuid"))
        .then((data) => {
          this.CartStore.getCart(false, this.payload);
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterSavefav(item) {
      this.removeItem(item);
    },
    lineItemTotal(qty, price) {
      console.log(qty + "x" + price);
      return parseFloat(price) * parseInt(qty);
    },
  },
};
</script>
