<template>
  <q-form @submit="onSubmit">
    <q-card-section class="q-pb-none">
      <h4 class="text-weight-bold q-mt-none q-mb-md">
        {{ $t("Write A Review") }}
      </h4>

      <div class="q-mb-md">
        <q-rating
          v-model="rating_value"
          size="md"
          :max="5"
          color="grey-5"
          color-selected="warning"
          class="q-mb-xs"
        />
      </div>

      <h6 class="text-weight-bold no-margin q-pb-sm">
        {{ $t("What did you like?") }}
      </h6>
      <q-input
        v-model="tags_like"
        outlined
        class="q-mb-md full-width"
        color="warning"
        :label="$t('Describe in few words')"
        dense
        :rules="[
          (val) =>
            val.length <= 50 || this.$t('Please use maximum 50 characters'),
        ]"
      />

      <h6 class="text-weight-bold no-margin q-pb-sm">
        {{ $t("What did you not like?") }}
      </h6>
      <q-input
        v-model="tags_not_like"
        outlined
        class="q-mb-md full-width"
        color="warning"
        :label="$t('Describe in few words')"
        dense
        :rules="[
          (val) =>
            val.length <= 50 || this.$t('Please use maximum 50 characters'),
        ]"
      />

      <h6 class="text-weight-bold no-margin q-pb-sm">{{ $t("Add Photos") }}</h6>
      <q-uploader
        :url="upload_api"
        :label="$t('Drop files here to upload')"
        color="warning"
        text-color="dark"
        no-thumbnails
        class="full-width q-mb-md"
        flat
        accept=".jpg, image/*"
        bordered
        auto-upload
        max-total-size="1048576"
        @rejected="onRejectedFiles"
        :headers="[
          { name: 'Authorization', value: `token ${this.getToken()}` },
        ]"
        field-name="file"
        :form-fields="[{ name: 'merchant_id', value: 3 }]"
        @uploaded="afterUploaded"
      />

      <h6 class="text-weight-bold no-margin q-pb-sm">
        {{ $t("Write your review") }}
      </h6>
      <q-input
        v-model="review_content"
        autogrow
        outlined
        class="full-width"
        color="warning"
        :label="$t('Your review helps us to make better choices')"
        :rules="[
          (val) => (val && val.length > 0) || this.$t('This field is required'),
        ]"
      />

      <div class="q-pb-sm">
        <q-checkbox
          v-model="as_anonymous"
          size="sm"
          color="warning"
          :label="$t('post review as anonymous')"
        />
      </div>
    </q-card-section>
    <q-card-actions class="q-mb-md">
      <q-btn
        type="submit"
        unelevated
        rounded
        color="warning"
        text-color="black"
        no-caps
        class="full-width"
        :loading="loading"
        style="letter-spacing: 2px"
      >
        {{ $t("Add Review") }}
      </q-btn>
    </q-card-actions>
  </q-form>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import config from "src/api/config";
import auth from "src/api/auth";

export default {
  name: "WriteReview",
  props: ["order_uuid"],
  data() {
    return {
      show_modal: false,
      loading: false,
      upload_api: config.api_base_url + "/uploadReview",
      rating_value: 0,
      as_anonymous: false,
      review_content: "",
      tags_like: "",
      tags_not_like: "",
      upload_images: "",
    };
  },
  methods: {
    onRejectedFiles(rejectedEntries) {
      APIinterface.notify(
        "negative",
        `${rejectedEntries.length} file(s) did not pass validation constraints`,
        "error_outline",
        this.$q
      );
    },
    getToken() {
      return auth.getToken();
    },
    onSubmit() {
      const params = {
        order_uuid: this.order_uuid,
        review_content: this.review_content,
        rating_value: this.rating_value,
        as_anonymous: this.as_anonymous,
        tags_like: [this.tags_like],
        tags_not_like: [this.tags_not_like],
        upload_images: this.upload_images,
      };
      this.loading = true;
      APIinterface.addReview(params)
        .then((data) => {
          APIinterface.notify("positive", data.msg, "check_circle", this.$q);
          this.$emit("afterAddreview");
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterUploaded(files) {
      const response = JSON.parse(files.xhr.responseText);
      if (response.code === 1) {
        this.upload_images = response.details;
      } else {
        APIinterface.notify("negative", response.msg, "error_outline", this.$q);
      }
    },
  },
};
</script>
