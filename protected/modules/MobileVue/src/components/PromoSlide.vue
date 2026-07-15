<template>
  <swiper
    :slides-per-view="1.3"
    :space-between="10"
    @swiper="onSwiper"
    @slideChange="onSlideChange"
    class="q-mb-md"
  >
    <swiper-slide v-for="items in data" :key="items" class="row">
      <q-btn
        unelevated
        size="md"
        class="radius8 fit"
        color="mygrey"
        text-color="dark"
        no-caps
      >
        <div class="row fit items-center">test</div>
      </q-btn>
    </swiper-slide>
  </swiper>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import { usePromoStore } from "stores/PromoStore";

export default {
  name: "PromoSlide",
  props: ["data"],
  components: {
    Swiper,
    SwiperSlide,
  },
  setup() {
    return {};
  },
};
</script>
