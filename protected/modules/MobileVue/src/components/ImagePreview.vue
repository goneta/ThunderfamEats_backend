<template>
  <q-dialog
    v-model="modal"
    persistent
    :maximized="true"
    class="z-max"
    transition-show="fade"
    transition-hide="fade"
  >
    <q-card class="bg-dark">
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title class="text-white text-weight-bold">
          {{ title }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="modal = !true"
          color="dark"
          unelevated
          text-color="white"
          icon="las la-times"
          dense
          no-caps
          size="sm"
          rounded
        />
      </q-toolbar>
      <q-card-section
        class="flex flex-center text-white"
        style="min-height: 90%"
        v-if="hasData"
      >
        <div class="fit">
          <q-carousel
            animated
            v-model="slide"
            arrows
            navigation
            infinite
            swipeable
          >
            <template v-for="(items, index) in gallery" :key="items">
              <q-carousel-slide :name="index" :img-src="items" />
            </template>
          </q-carousel>
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: "ImagePreview",
  props: ["gallery", "title"],
  data() {
    return {
      modal: false,
      slide: 0,
    };
  },
  setup() {
    return {};
  },
  computed: {
    hasData() {
      if (Object.keys(this.gallery).length > 0) {
        return true;
      }
      return false;
    },
  },
};
</script>
