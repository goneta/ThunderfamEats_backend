<template>
  <q-header
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-grey-1 text-dark': !$q.dark.mode,
    }"
  >
    <q-toolbar>
      <q-btn
        @click="$router.back()"
        flat
        round
        dense
        icon="las la-angle-left"
        class="q-mr-sm"
        :color="$q.dark.mode ? 'white' : 'dark'"
      />
      <q-toolbar-title class="text-weight-bold">{{
        $t("Change Password")
      }}</q-toolbar-title>
    </q-toolbar>
  </q-header>
  <q-page
    padding
    class="q-pl-md q-pr-md row items-stretch"
    :class="{
      'bg-mydark ': $q.dark.mode,
      'bg-grey-1': !$q.dark.mode,
    }"
  >
    <q-card
      flat
      class="col-12"
      :class="{
        'bg-mydark text-white': $q.dark.mode,
        'bg-white text-black': !$q.dark.mode,
      }"
    >
      <q-card-section>
        <q-form @submit="onSubmit">
          <q-input
            v-model="old_password"
            type="password"
            :label="$t('Current Password')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            :rules="[
              (val) => (val && val.length > 0) || 'This field is required',
            ]"
          />

          <q-input
            v-model="new_password"
            type="password"
            :label="$t('New Password')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            :rules="[
              (val) => (val && val.length > 0) || 'This field is required',
            ]"
          />

          <q-input
            v-model="confirm_password"
            type="password"
            :label="$t('Retype New Password')"
            outlined
            lazy-rules
            :bg-color="$q.dark.mode ? 'grey600' : 'input'"
            :label-color="$q.dark.mode ? 'grey300' : 'grey'"
            :rules="[
              (val) => (val && val.length > 0) || 'This field is required',
            ]"
          />

          <q-footer
            reveal
            class="bg-grey-1 q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
          >
            <q-btn
              type="submit"
              :label="$t('Save')"
              unelevated
              no-caps
              color="primary text-white"
              class="full-width text-weight-bold"
              size="lg"
              :loading="loading"
            />
          </q-footer>
        </q-form>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "ChangePassword",
  data() {
    return {
      loading: false,
      old_password: "",
      new_password: "",
      confirm_password: "",
    };
  },
  methods: {
    onSubmit() {
      const params = {
        old_password: this.old_password,
        new_password: this.new_password,
        confirm_password: this.confirm_password,
      };
      this.loading = true;
      APIinterface.showLoadingBox("", this.$q);
      APIinterface.updatePassword(params)
        .then((data) => {
          APIinterface.notify("light-green", data.msg, "check_circle", this.$q);
          this.onReset();
        })
        .catch((error) => {
          APIinterface.notify("grey-8", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
          APIinterface.hideLoadingBox(this.$q);
        });
    },
    onReset() {
      this.old_password.value = "";
      this.new_password.value = "";
      this.confirm_password.value = "";
    },
  },
};
</script>
