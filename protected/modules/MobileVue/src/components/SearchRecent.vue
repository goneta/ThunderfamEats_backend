<template>
  <q-list v-if="loading">
    <q-item v-for="i in 7" :key="i">
      <q-item-section avatar>
        <q-skeleton type="QAvatar" size="15px" />
      </q-item-section>
      <q-item-section>
        <q-skeleton type="text" />
      </q-item-section>
    </q-item>
  </q-list>

  <q-list dense v-else>
    <template v-if="hasData">
      <q-item-label header class="text-weight-medium">{{
        $t("Recent searches")
      }}</q-item-label>

      <template v-for="(item, index) in data_recent" :key="item">
        <q-item clickable v-ripple @click.stop="toMenu(item)">
          <q-item-section avatar>
            <q-icon color="dark" name="search" size="xs" />
          </q-item-section>
          <q-item-section>
            <q-item-label lines="1">{{ item }}</q-item-label>
          </q-item-section>
          <q-item-section side>
            <q-btn
              @click.stop="removeHistory(index)"
              round
              color="primary"
              icon="eva-close-outline"
              dense
              no-caps
              size="sm"
              flat
            />
          </q-item-section>
        </q-item>
        <q-separator spaced inset="item" />
      </template>
    </template>

    <q-item-label header class="text-grey font12 text-weight-light">{{
      $t("Cuisine")
    }}</q-item-label>
    <template v-for="items in data" :key="items">
      <q-item
        :to="{ name: 'quicksearch', query: { q: items.cuisine_name } }"
        clickable
        v-ripple
      >
        <q-item-section avatar>
          <q-icon color="dark" name="search" size="xs" />
        </q-item-section>
        <q-item-section>
          <q-item-label lines="1">{{ items.cuisine_name }}</q-item-label>
        </q-item-section>
      </q-item>
      <q-separator spaced inset="item" />
    </template>
  </q-list>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "SearchRecent",
  data() {
    return {
      data: [],
      data_recent: [],
      loading: false,
    };
  },
  computed: {
    hasData() {
      if (this.data_recent.length > 0) {
        return true;
      }
      return false;
    },
  },
  mounted() {
    this.getSearchHistory();
    this.getCuisine();
  },
  methods: {
    getCuisine() {
      this.loading = true;
      APIinterface.CuisineList(0, "")
        .then((data) => {
          this.data = data.details.data;
        })
        // eslint-disable-next-line
        .catch((error) => {
          //
        })
        .then((data) => {
          this.loading = false;
        });
    },
    getSearchHistory() {
      const history = APIinterface.getStorage("search_history");
      if (!APIinterface.empty(history)) {
        this.data_recent = JSON.parse(history);
      }
    },
    removeHistory(index) {
      if (this.data_recent[index]) {
        this.data_recent.splice(index, 1);
        APIinterface.setStorage(
          "search_history",
          JSON.stringify(this.data_recent)
        );
      }
    },
    toMenu(data) {
      this.$router.push({ name: "quicksearch", query: { q: data } });
    },
  },
};
</script>
