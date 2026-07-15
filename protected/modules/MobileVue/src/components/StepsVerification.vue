<template>
  <!-- <q-dialog v-model="show_modal" persistent transition-show="scale" transition-hide="scale" position="bottom" > -->
  <q-dialog v-model="show_modal" persistent position="bottom">
    <q-card>
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-space></q-space>
        <q-btn
          @click="show_modal = !true"
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

      <q-form @submit="onSubmit">
        <q-card-section class="text-center">
          <h4 class="text-weight-bold q-mt-none">
            {{ $t("2-Step Verification") }}
          </h4>
          <p class="text-weight-medium line-normal">
            {{ $t("For your security, we want to make sure it's really you.") }}
          </p>

          <p v-if="sent_message2" class="text-weight-bold font11">
            {{ sent_message2 }}
          </p>

          <q-input
            :bg-color="$q.dark.mode ? 'grey600' : 'mygrey'"
            :color="$q.dark.mode ? 'grey300' : 'primary'"
            outlined
            v-model="code"
            :label="$t('Code')"
            maxlength="6"
            lazy-rules
            :rules="[(val) => (val && val.length > 0) || 'Code is required']"
            mask="######"
          />

          <q-btn
            type="submit"
            unelevated
            no-caps
            class="full-width q-mb-md"
            :label="$t('Submit')"
            size="lg"
            :disabled="hasCode"
            :loading="loading"
            :color="hasCode == false ? 'primary' : 'grey-5'"
            :text-color="hasCode == false ? 'white' : 'dark'"
          >
          </q-btn>

          <div class="q-mb-sm text-center">
            <q-btn
              @click="resendCode"
              v-if="counter === 0"
              flat
              color="blue"
              no-caps
              :label="$t('Resend')"
              dense
              size="sm"
            />
            <p v-else class="font11 q-ma-none">
              <u>{{ $t("Resend Code in") }} {{ counter }}</u>
            </p>
          </div>
        </q-card-section>
      </q-form>

      <q-inner-loading
        :showing="visible"
        color="primary"
        size="md"
        label-class="dark"
      />
    </q-card>
  </q-dialog>
</template>

<script>
import APIinterface from "src/api/APIinterface";

export default {
  name: "StepsVerification",
  props: ["sent_message"],
  data() {
    return {
      show_modal: false,
      loading: false,
      visible: false,
      code: "",
      counter: 20,
      timer: undefined,
      sent_message2: "",
    };
  },
  computed: {
    hasCode() {
      if (!APIinterface.empty(this.code)) {
        return false;
      }
      return true;
    },
  },
  updated() {
    this.code = "";
  },
  watch: {
    counter(newval, oldval) {
      if (newval <= 0) {
        this.stopTimer();
      }
    },
    show_modal(newval, oldval) {
      if (newval) {
        this.startTimer();
      }
    },
    sent_message(newval, oldval) {
      this.sent_message2 = newval;
    },
  },
  methods: {
    onSubmit() {
      this.$emit("afterVerifycode", this.code);
    },
    stopTimer() {
      clearInterval(this.timer);
    },
    startTimer() {
      this.stopTimer();
      this.counter = 20;
      this.timer = setInterval(() => {
        this.counter--;
      }, 1000);
    },
    resendCode() {
      this.loading = true;
      APIinterface.RequestEmailCode()
        .then((data) => {
          this.sent_message2 = data.msg;
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
