<template>
  <q-header
    :class="{
      'bg-mydark text-white': $q.dark.mode,
      'bg-white text-dark': !$q.dark.mode,
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
        $t("Forgot Password")
      }}</q-toolbar-title>
    </q-toolbar>
  </q-header>
  <q-page padding class="flex flex-center">
    <div class="full-width q-pa-md">
      <h5 class="text-weight-bold">{{ $t("Let's Get your account back") }}!</h5>

      <p class="text-weight-medium q-ma-none" v-if="steps == 1">
        {{
          $t(
            "Enter your email to receive instructions for resetting your password."
          )
        }}
      </p>
      <p v-else class="rounded-borders border-green q-pa-sm">
        {{ data.msg }}
      </p>
      <q-btn
        flat
        :label="$t('Already have an account?')"
        no-caps
        class="q-pa-none text-weight-bold min-height q-mb-md"
        color="secondary"
        to="/user/login"
      />

      <q-form @submit="onSubmit" v-if="steps == 1">
        <q-input
          v-model="email"
          :label="$t('Email')"
          :rules="[
            (val) =>
              (val && val.length > 0) || this.$t('This field is required'),
          ]"
          outlined
          lazy-rules
          :bg-color="$q.dark.mode ? 'grey600' : 'input'"
          :label-color="$q.dark.mode ? 'grey300' : 'grey'"
          borderless
          class="input-borderless"
        />

        <q-btn
          :loading="loading"
          type="submit"
          :label="$t('Reset Password')"
          unelevated
          no-caps
          color="primary text-white"
          class="full-width text-weight-bold"
          size="lg"
        />
      </q-form>

      <div v-else>
        <q-space class="q-pa-xs"></q-space>
        <template v-if="counter === 0">
          <q-btn
            @click="resendEmail"
            flat
            dense
            color="myblue"
            no-caps
            class="font13 q-ma-none"
            ><u>{{ $t("Resend reset email") }}</u></q-btn
          >
          <q-btn
            @click="steps = 1"
            flat
            dense
            color="secondary"
            no-caps
            class="font13 q-ma-none"
            ><u>{{ $t("Enter email again") }}</u></q-btn
          >
        </template>
        <p v-else class="font11 q-ma-none">
          <u>{{ $t("Resend Code in") }} {{ counter }}</u>
        </p>
      </div>
    </div>
    <!-- full-width -->
  </q-page>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "ForgotPassword",
  data() {
    return {
      loading: false,
      email: "",
      password: "",
      steps: 1,
      data: [],
      maxCounter: 20,
      counter: this.maxCounter,
      timer: undefined,
    };
  },
  watch: {
    counter(newval, oldval) {
      if (newval <= 0) {
        this.stopTimer();
      }
    },
  },
  beforeUnmount() {
    this.stopTimer();
  },
  methods: {
    onSubmit() {
      this.loading = true;
      APIinterface.requestResetPassword(this.email)
        .then((data) => {
          this.steps = 2;
          this.startTimer();
          this.data = data;
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    stopTimer() {
      clearInterval(this.timer);
    },
    startTimer() {
      this.stopTimer();
      this.counter = this.maxCounter;
      this.timer = setInterval(() => {
        this.counter--;
      }, 1000);
    },
    resendEmail() {
      this.loading = true;
      APIinterface.resendResetEmail(this.data.details.uuid)
        .then((data) => {
          this.steps = 2;
          this.startTimer();
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
