<template>
  <q-list v-if="loading" class="qlist-no-padding">
    <q-item v-for="i in 10" :key="i">
      <q-item-section avatar>
        <q-skeleton type="circle" size="30px" />
      </q-item-section>
      <q-item-section>
        <q-skeleton type="text" style="width: 100px" />
        <div class="row">
          <div v-for="y in 5" :key="y" class="col-1">
            <q-skeleton type="circle" size="10px" />
          </div>
        </div>
        <q-skeleton type="text" style="width: 90px" />
      </q-item-section>
      <q-item-section side>
        <q-skeleton type="text" style="width: 80px" />
      </q-item-section>
    </q-item>
  </q-list>

  <template v-else>
    <q-list v-if="hasData" class="qlist-no-padding">
      <template v-for="data_item in data" :key="data_item">
        <q-item v-for="items in data_item" :key="items">
          <q-item-section avatar>
            <q-avatar>
              <q-img :src="items.url_image" lazy />
            </q-avatar>
          </q-item-section>
          <q-item-section>
            <div class="font12 text-weight-bold line-normal ellipsis">
              <template v-if="items.as_anonymous === 1">
                {{ items.hidden_fullname }}
              </template>
              <template v-else>
                {{ items.fullname }}
              </template>
            </div>
            <q-rating
              v-model="items.rating"
              size="xs"
              :max="5"
              color="grey"
              color-selected="primary"
              readonly
              class="q-mb-xs"
            />
            <div
              class="font12 full-width text-dark ellipsis-2-lines text-weight-light"
            >
              {{ items.review }}
            </div>
          </q-item-section>
          <q-item-section side>
            <div class="font12 full-width text-grey text-weight-thin">
              {{ items.date_created }}
            </div>
          </q-item-section>
        </q-item>
      </template>
    </q-list>

    <div v-else class="fit q-mt-xl text-center q-pa-md">
      <q-img
        src="feedback.png"
        style="height: 80px; max-width: 80px"
        class="light-dimmed"
      />
      <div>
        <p class="font11 text-grey">
          {{ $t("This store has no reviews yet!") }}
        </p>
      </div>
    </div>
  </template>

  <div class="text-center">
    <q-btn
      v-if="show_next_page"
      :label="$t('Load more')"
      unelevated
      color="primary"
      text-color="dark"
      no-caps
      class="q-mt-md"
      @click="loadMore"
      :loading="loading"
    />
  </div>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "ReviewList",
  props: ["slug"],
  data() {
    return {
      ratingModel: 4,
      data: [],
      page: 0,
      show_next_page: false,
      loading: false,
    };
  },
  mounted() {
    this.getReview(0);
  },
  computed: {
    hasData() {
      if (this.data.length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    getReview(page) {
      this.loading = true;
      APIinterface.getReview(this.slug, page)
        .then((data) => {
          this.data.push(data.details.data);
          this.page = data.details.page;
          this.show_next_page = data.details.show_next_page;
        })
        // eslint-disable-next-line
        .catch((error) => {
          //
        })
        .then((data) => {
          this.loading = false;
        });
    },
    loadMore() {
      this.getReview(this.page);
    },
  },
};
</script>
