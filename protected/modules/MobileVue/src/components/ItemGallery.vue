<template>
  <swiper
    :slidesPerView="4.2"
    :spaceBetween="5"
    @swiper="onSwiper"
    class="q-mb-sm"
  >
    <swiper-slide v-for="items in getData" :key="items" class="row">
      <div
        class="radius8 border-grey300 cursor-pointer"
        style="height: 55px; width: 55px; overflow: hidden"
        @click="setImage(items)"
      >
        <q-img
          :src="items"
          spinner-color="primary"
          spinner-size="sm"
          style="height: 55px; width: 55px"
          fit="cover"
        />
      </div>
    </swiper-slide>
  </swiper>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";

export default {
  name: "ItemGallery",
  props: ["item_gallery"],
  components: {
    Swiper,
    SwiperSlide,
  },
  computed: {
    getData() {
      return this.item_gallery;
    },
  },
  methods: {
    setImage(data) {
      this.$emit("afterSelectimage", data);
    },
  },
};
</script>
