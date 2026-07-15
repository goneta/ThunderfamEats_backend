<template>
  <q-page padding class="flex flex-center">
    <div class="full-width">
      <swiper
        ref="swiperRef"
        :slides-per-view="1"
        :space-between="10"
        @swiper="onSwiper"
        @slideChange="onSlideChange"
        class="q-mb-md"
        :pagination="{
          dynamicBullets: true,
        }"
        :modules="modules"
      >
        <swiper-slide
          v-for="items in data"
          :key="items"
          class="row"
          style="height: 350px"
        >
          <q-img
            :src="items.image"
            style="max-width: 100%; height: 150px"
            fit="contain"
          />
          <div class="text-center fit q-pt-lg">
            <div class="font16 text-weight-bold q-mb-md line-normal">
              {{ items.title }}
            </div>
            <div class="text-weight-medium font14 text-grey line-normal">
              {{ items.sub_title }}
            </div>
          </div>
        </swiper-slide>
      </swiper>
    </div>

    <q-footer
      reveal
      class="transparent q-pl-md q-pr-md q-pb-md q-pt-md text-dark row items-center justify-between"
    >
      <q-btn
        flat
        text-color="grey"
        no-caps
        size="lg"
        :label="$t('Skip')"
        @click="home"
      ></q-btn>

      <q-btn
        v-if="slide == 2"
        no-caps
        size="lg"
        :label="$t('Get Started')"
        unelevated
        color="primary"
        text-color="white"
        @click="login"
      ></q-btn>
      <q-btn
        v-else
        no-caps
        size="lg"
        :label="$t('Next')"
        unelevated
        color="primary"
        text-color="white"
        style="min-width: 120px"
        @click="nextSlide"
      ></q-btn>
    </q-footer>
  </q-page>
</template>

<script>
import { Swiper, SwiperSlide, useSwiper } from "swiper/vue";
import { ref, onMounted } from "vue";
import "swiper/css";
import "swiper/css/pagination";
import { Pagination } from "swiper";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";
import { useDataStorePersisted } from "stores/DataStorePersisted";

export default {
  name: "ScreenPage",
  components: {
    Swiper,
    SwiperSlide,
  },
  data() {
    return {
      data: [
        {
          image: "onboarding-1.png",
          title: this.$t("Discover Places near you"),
          sub_title: this.$t("onboarding_sub_title1"),
        },
        {
          image: "onboarding-2.png",
          title: this.$t("Order your customized items"),
          sub_title: this.$t("onboarding_sub_title2"),
        },
        {
          image: "onboarding-3.png",
          title: this.$t("Faster delivery"),
          sub_title: this.$t("onboarding_sub_title3"),
        },
      ],
    };
  },
  created() {
    this.$i18n.locale = this.$i18n.locale;
  },
  setup() {
    const DataStore = useDataStore();
    const DataStorePersisted = useDataStorePersisted();
    const swiperRef = ref();
    const slide = ref(0);

    const nextSlide = () => {
      swiperRef.value.$el.swiper.slideNext();
    };

    const onSlideChange = (data) => {
      slide.value = data.activeIndex;
    };

    return {
      slide,
      swiperRef,
      nextSlide,
      onSlideChange,
      modules: [Pagination],
      DataStore,
      DataStorePersisted,
    };
  },
  methods: {
    home() {
      APIinterface.setStorage("intro", 1);

      if (
        this.DataStorePersisted.choose_language == false &&
        this.DataStore.enabled_language == true
      ) {
        this.$router.replace("/select-language");
      } else {
        this.$router.replace("/home");
      }
    },
    login() {
      APIinterface.setStorage("intro", 1);
      if (
        this.DataStorePersisted.choose_language == false &&
        this.DataStore.enabled_language == true
      ) {
        this.$router.replace("/select-language");
      } else {
        this.$router.replace("/user/login");
      }
    },
  },
};
</script>
<style>
.swiper-pagination-bullet-active {
  background: #ff724c !important;
}
</style>
