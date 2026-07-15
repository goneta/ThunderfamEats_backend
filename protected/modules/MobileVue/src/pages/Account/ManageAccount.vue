<template>
  <q-header class="bg-white">
    <q-toolbar>
      <q-btn
        to="/account/profile"
        flat
        round
        dense
        icon="arrow_back"
        color="dark"
        :disable="account_deleted"
      />
      <q-toolbar-title class="text-dark text-center text-weight-bold">
        Manage Account
      </q-toolbar-title>
      <div></div>
    </q-toolbar>
  </q-header>

  <q-page padding class="bg-grey-2">
    <q-space class="q-pa-xs"></q-space>
    <q-card flat class="radius8">
      <q-card-section class="q-pa-md">
        <template v-if="account_deleted">
          <div class="text-h6 text-weight-bold">
            Your account is being deleted
          </div>
          <p class="font12">
            You will be automatically logged out. Your account will be deleted
            in the next few minutes.
          </p>
          <p class="font12">
            Note: We may retain some information when permitted by law.
          </p>
        </template>

        <template v-else>
          <div class="text-h6">Account Data</div>
          <p class="font12">
            You can request an archive of your personal information. We'll
            notify you when it's ready to download.
          </p>
          <q-btn
            @click="requestData"
            label="Request Archive"
            size="md"
            flat
            dense
            text-color="primary"
            no-caps
          />
          <q-separator spaced></q-separator>
          <div class="text-h6">Delete Account</div>
          <p class="font12">
            You can request to have your account deleted and personal
            information removed. If you have both a karenderia and Caviar
            account, then the information associated with both will be affected
            to the extent we can identify that the accounts are owned by the
            same user.
          </p>
          <q-btn
            @click="beforeDelete"
            label="Request Delete Account"
            size="md"
            flat
            dense
            text-color="primary"
            no-caps
          />
        </template>
      </q-card-section>

      <q-inner-loading :showing="loading" color="primary" size="md" />
    </q-card>

    <StepsVerification
      ref="steps_verification"
      :sent_message="sent_message"
      :phone_prefix="phone_prefix"
      :phone_number="phone_number"
      @after-verifycode="afterVerifycode"
    />
  </q-page>
</template>

<script>
import { defineAsyncComponent } from "vue";
import APIinterface from "src/api/APIinterface";
import auth from "src/api/auth";

export default {
  name: "ManageAccount",
  components: {
    StepsVerification: defineAsyncComponent(() =>
      import("components/StepsVerification.vue")
    ),
  },
  data() {
    return {
      loading: false,
      code: "",
      account_deleted: false,
    };
  },
  methods: {
    requestData() {
      APIinterface.requestData()
        .then((data) => {
          //
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {});
    },
    beforeDelete() {
      this.loading = true;
      this.code = "";
      APIinterface.RequestEmailCode()
        .then((data) => {
          this.sent_message = data.msg;
          this.show_modal = false;
          this.$refs.steps_verification.show_modal = true;
        })
        .catch((error) => {
          APIinterface.notify("negative", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    afterVerifycode(code) {
      this.code = code;
      APIinterface.verifyAccountDelete(code)
        .then((data) => {
          this.$refs.steps_verification.show_modal = false;
          this.confirmDeletion();
        })
        .catch((error) => {
          APIinterface.notify("red-5", error, "error_outline", this.$q);
        })
        .then((data) => {
          this.loading = false;
        });
    },
    confirmDeletion() {
      this.$q
        .dialog({
          title: "Confirm account deletion",
          message:
            "Are you sure you want to delete your account and customer data? \n  This action is permanent and cannot be undone.",
          persistent: true,
          position: "bottom",
          ok: {
            unelevated: true,
            color: "warning",
            rounded: false,
            "text-color": "black",
            size: "md",
            label: "Yes delete my account",
            "no-caps": true,
          },
          cancel: {
            unelevated: true,
            rounded: false,
            color: "grey-3",
            "text-color": "black",
            size: "md",
            label: "Cancel",
            "no-caps": true,
          },
        })
        .onOk(() => {
          this.deleteAccount();
        })
        .onOk(() => {
          // console.log('>>>> second OK catcher')
        })
        .onCancel(() => {
          // console.log('>>>> Cancel')
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    },
    deleteAccount() {
      this.loading = true;
      APIinterface.deleteAccount(this.code)
        .then((data) => {
          this.account_deleted = true;
          setTimeout(() => {
            auth.logout();
            this.$router.push("/home");
          }, 5000);
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
