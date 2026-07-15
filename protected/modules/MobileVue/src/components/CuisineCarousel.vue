<template>
  <div v-if="DataStore.loading_cuisine" class="row q-gutter-sm items-center">
    <div v-for="i in 3" :key="i" class="col">
      <q-skeleton type="QBtn" class="full-width" height="50px" />
    </div>
  </div>
  <template v-else>
    <swiper
      v-if="DataStore.cuisine"
      :slidesPerView="2.3"
      :spaceBetween="10"
      @swiper="onSwiper"
      class="q-mb-md"
    >
      <swiper-slide
        v-for="(items, key) in DataStore.cuisine"
        :key="items"
        class="row"
      >
        <q-btn
          :color="key <= 0 ? 'primary' : $q.dark.mode ? 'grey600' : 'mygrey'"
          unelevated
          no-caps
          :text-color="key <= 0 ? 'white' : $q.dark.mode ? 'grey300' : 'dark'"
          class="radius8 fit q-pa-sm"
          size="sm"
          :to="{
            name: 'feed',
            query: {
              query: 'all',
              cuisine_id: items.cuisine_id,
              cuisine_name: items.cuisine_name,
            },
          }"
        >
          <div class="row items-center no-wrap full-width">
            <q-avatar size="30px">
              <img :src="items.url_icon" />
            </q-avatar>
            <div
              class="text-center ellipsis font13 q-pl-sm"
              style="max-width: 70px"
            >
              {{ items.cuisine_name }}
            </div>
          </div>
        </q-btn>
      </swiper-slide>
    </swiper>
  </template>
</template>

<script>
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import APIinterface from "src/api/APIinterface";
import { useDataStore } from "stores/DataStore";

export default {
  name: "CuisineCarousel",
  props: ["design"],
  components: {
    Swiper,
    SwiperSlide,
  },
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },
  data() {
    return {
      slide: 0,
      data: [],
      loading: true,
    };
  },
  mounted() {
    if (!this.DataStore.hasDataCuisine()) {
      this.DataStore.CuisineList();
    }
  },
  methods: {
    CuisineList() {
      this.loading = true;
      APIinterface.CuisineList(4, "")
        .then((data) => {
          this.data = data.details.data;
          this.$emit("afterGetdata", data.details.data_raw);
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
  },
};
</script>
