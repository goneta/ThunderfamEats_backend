<template>
  <q-header
    reveal
    reveal-offset="50"
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
        $t("Delete Account")
      }}</q-toolbar-title>
    </q-toolbar>
  </q-header>
  <q-page
    padding
    class="q-pl-md q-pr-md row items-stretch"
    :class="{
      'bg-grey-dark': $q.dark.mode,
      'bg-grey-1': !$q.dark.mode,
    }"
  >
    <q-card
      flat
      class="col-12"
      :class="{
        'bg-mydark ': $q.dark.mode,
        'bg-white ': !$q.dark.mode,
      }"
    >
      <q-card-section>
        <h5 class="text-weight-bold q-ma-none">
          {{
            $t(
              "You are requesting to have your account deleted and personal information removed"
            )
          }}.
        </h5>
        <q-space class="q-pa-sm"></q-space>
        <p class="text-weight-medium q-ma-none">
          {{
            $t(
              "You will permanently lose all your orders, reviews,favorites and profile information, there is no turning back"
            )
          }}.
        </p>
      </q-card-section>
    </q-card>

    <q-footer
      reveal
      class="bg-grey-1 q-pl-md q-pr-md q-pb-sm q-pt-sm text-dark"
    >
      <q-btn
        :label="$t('Confirm Deletion')"
        unelevated
        no-caps
        color="primary text-white"
        class="full-width text-weight-bold"
        size="lg"
        :loading="loading"
        @click="RequestEmailCode"
      />
    </q-footer>

    <StepsVerification
      ref="steps_verification"
      :sent_message="sent_message"
      @after-verifycode="afterVerifycode"
    />
  </q-page>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";

export default {
  name: "DeleteAccount",
  components: {
    StepsVerification: defineAsyncComponent(() =>
      import("components/StepsVerification.vue")
    ),
  },
  data() {
    return {
      loading: false,
      sent_message: "",
    };
  },
  methods: {
    RequestEmailCode() {
      this.loading = true;
      APIinterface.RequestEmailCode()
        .then((data) => {
          this.sent_message = data.msg;
          this.show_modal = false;
          this.$refs.steps_verification.show_modal = true;
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterVerifycode(code) {
      this.loading = true;
      APIinterface.showLoadingBox("", this.$q);
      APIinterface.fetchDataByTokenPost("deleteAccount", "code=" + code)
        .then((data) => {
          this.$refs.steps_verification.show_modal = false;
          auth.logout();
          this.$router.push("/home");
        })
        .catch((error) => {
          APIinterface.notify("dark", error, "error", this.$q);
        })
        .then((data) => {
          this.loading = false;
          APIinterface.hideLoadingBox(this.$q);
        });
    },
  },
};
</script>
