<template>
  <q-dialog v-model="modal" position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title class="text-dark text-weight-bold">
          {{ $t("Categories") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="modal = !true"
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
      </q-toolbar>
      <template v-if="MenuStore.loading_menu">
        <q-list dense>
          <q-item v-for="x in 5" :key="x">
            <q-item-section> <q-skeleton type="text" /></q-item-section>
            <q-item-section side>
              <q-skeleton type="text" style="width: 50px"
            /></q-item-section>
          </q-item>
        </q-list>
      </template>
      <template v-else>
        <q-list v-if="MenuStore.data_category[slug]" dense>
          <q-item
            v-for="items in MenuStore.data_category[slug]"
            :key="items"
            clickable
            @click="this.$emit('afterCategoryselect', items.category_uiid)"
          >
            <q-item-section> {{ items.category_name }} </q-item-section>
            <q-item-section side>
              <q-icon color="grey" size="14px" name="las la-angle-right" />
            </q-item-section>
          </q-item>
        </q-list>
      </template>
    </q-card>
  </q-dialog>
</template>

<script>
import { useMenuStore } from "stores/MenuStore";

export default {
  name: "CategoriesModal",
  props: ["slug"],
  data() {
    return {
      modal: false,
    };
  },
  setup() {
    const MenuStore = useMenuStore();
    return { MenuStore };
  },
};
</script>
