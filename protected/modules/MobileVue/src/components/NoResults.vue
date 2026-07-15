<template>
  <div class="full-width text-center">
    <q-img
      src="cuttery.png"
      fit="fill"
      spinner-color="primary"
      style="height: 160px; max-width: 150px"
    />
    <div class="text-h5 text-weight-bold">{{ $t("No Results") }}</div>
    <p class="text-grey font12">
      {{
        $t("We're sorry. We were not able to find a match with your filters.")
      }}
    </p>
    <q-btn
      :label="$t('Filter again')"
      unelevated
      no-caps
      :color="$q.dark.mode ? 'grey600' : 'mygrey'"
      :text-color="$q.dark.mode ? 'grey300' : 'dark'"
      class="radius8 text-weight-medium"
      @click="this.$emit('filterAgain')"
    />
  </div>
</template>

<script>
export default {
  name: "NoResults",
  props: ["message", "description"],
  setup() {
    return {};
  },
};
</script>
