<template>
  <!-- <pre>{{ items }}</pre> -->
  <q-item-section avatar>
    <q-img
      :src="items.url_image"
      lazy
      fit="cover"
      style="height: 80px; width: 80px"
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
      </div>
      <div class="text-grey ellipsis-2-lines font12 line-normal">
        <span v-html="items.item_description"></span>
      </div>
      <div v-if="items.price" class="text-grey-7 font12 text-weight-medium">
        <template v-for="price in items.price" :key="price">
          <template v-if="price.discount > 0">
            {{ price.size_name }}
            <span class="text-strike">{{ price.pretty_price }}</span>
            {{ price.pretty_price_after_discount }}
          </template>
          <template v-else>
            {{ price.size_name }} {{ price.pretty_price }}</template
          ><span class="q-pr-sm"></span>
        </template>
      </div>

      <div class="row items-center justify-between">
        <q-chip size="sm" color="mygrey">
          <span class="text-dark">{{ $t("Food") }}</span>
        </q-chip>
      </div>
    </q-item-label>
  </q-item-section>
</template>

<script>
export default {
  name: "SearchListFood",
  props: ["items", "merchant_list", "money_config"],
  setup() {
    return {};
  },
};
</script>
