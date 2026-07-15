<template>
  <template v-if="PromoStore.loading">
    <div class="row q-gutter-sm q-mb-sm">
      <q-skeleton height="50px" square class="col-6 radius8" />
      <q-skeleton height="50px" square class="col-5 radius8" />
    </div>
  </template>
  <template v-else>
    <swiper
      v-if="PromoStore.data[merchant_id]"
      :slides-per-view="1.3"
      :space-between="10"
      @swiper="onSwiper"
      @slideChange="onSlideChange"
      class="q-mb-md"
    >
      <swiper-slide
        v-for="(items, index) in PromoStore.data[merchant_id]"
        :key="items"
        class="row"
      >
        <q-btn
          v-model="category"
          unelevated
          no-caps
          :color="$q.dark.mode ? 'grey600' : 'mygrey'"
          :text-color="$q.dark.mode ? 'grey300' : 'dark'"
          class="radius8 fit q-pa-sm"
          size="sm"
          @click="showDetails(index)"
        >
          <div class="row fit items-center">
            <div class="col-2">
              <q-btn
                round
                color="secondary"
                :icon="
                  items.promo_type == 'voucher'
                    ? 'local_offer'
                    : 'las la-percent'
                "
                unelevated
                size="xs"
                class="radius8"
              />
            </div>
            <div class="col text-left">
              <div class="text-weight-bold no-margin line-normal ellipsis">
                {{ items.title }}
              </div>
              <div class="text-grey ellipsis">
                {{ items.valid_to }}
              </div>
            </div>
          </div>
        </q-btn>
      </swiper-slide>
    </swiper>
  </template>

  <q-dialog v-model="modal" position="bottom">
    <q-card>
      <q-card-section class="q-pl-md">
        <div
          v-if="PromoStore.data[merchant_id][selected_index]"
          class="row items-center q-gutter-sm q-mb-sm q-pb-sm border-bottom"
        >
          <q-btn
            round
            color="secondary"
            icon="las la-percent"
            unelevated
            size="xs"
            class="radius8"
          />
          <div>
            <div class="text-weight-bold">
              {{ PromoStore.data[merchant_id][selected_index].title }}
            </div>
            <div class="text-weight-medium font12">
              {{ PromoStore.data[merchant_id][selected_index].sub_title }}
            </div>
          </div>
        </div>
        <div class="text-grey font13">
          {{ PromoStore.data[merchant_id][selected_index].valid_to }}
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import { usePromoStore } from "stores/PromoStore";

export default {
  name: "MerchantPromoSlide",
  props: ["merchant_id"],
  components: {
    Swiper,
    SwiperSlide,
  },
  setup() {
    const PromoStore = usePromoStore();
    return { PromoStore };
  },
  data() {
    return {
      modal: false,
      selected_index: 0,
    };
  },
  created() {
    // if (Object.keys(this.PromoStore.data).length <= 0) {
    //   this.PromoStore.loadPromo(this.merchant_id);
    // } else {
    //   if (!this.PromoStore.data[this.merchant_id]) {
    //     this.PromoStore.loadPromo(this.merchant_id);
    //   }
    // }
    this.PromoStore.loadPromo(this.merchant_id);
  },
  methods: {
    showDetails(index) {
      this.selected_index = index;
      this.modal = true;
    },
    refresh() {
      this.PromoStore.loadPromo(this.merchant_id);
    },
  },
};
</script>
