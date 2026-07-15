<template>
  <q-header class="bg-white">
    <q-toolbar>
      <q-btn
        to="/account-menu"
        flat
        round
        dense
        icon="arrow_back"
        color="dark"
      />
      <q-toolbar-title class="text-dark text-center text-weight-bold">
        Profile
      </q-toolbar-title>
      <NotiButton></NotiButton>
    </q-toolbar>
  </q-header>

  <q-page padding class="bg-grey-2">
    <q-space class="q-pa-xs"></q-space>
    <q-card flat class="radius8">
      <q-card-section>
        <div class="text-center q-mb-lg">
          <q-avatar>
            <q-img
              :src="data.avatar"
              lazy
              style="height: 50px; max-width: 50px"
              spinner-color="amber"
              spinner-size="20px"
            />
          </q-avatar>
          <div class="text-h6 text-weight-bold line-normal">
            {{ data.first_name }} {{ data.last_name }}
          </div>
          <div class="font12 text-grey">{{ data.email_address }}</div>
          <q-space class="q-pa-xs"></q-space>

          <q-btn
            to="/account/edit-profile"
            label="Edit Profile"
            flat
            dense
            text-color="amber-14"
            no-caps
          />
        </div>
        <!-- center -->

        <q-separator></q-separator>

        <q-list>
          <q-item clickable to="/account/settings">
            <q-item-section avatar style="min-width: auto">
              <!-- <q-icon color="grey" name="eva-settings-outline" size="xs" /> -->
              <q-avatar
                rounded
                color="amber-2"
                text-color="orange-5"
                icon="eva-settings-outline"
                size="md"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label
                class="text-weight-medium line-normal ellipsis full-width"
                >Settings</q-item-label
              >
            </q-item-section>
            <q-item-section side>
              <q-btn
                round
                unelevated
                text-color="dark"
                icon="chevron_right"
                dense
              />
            </q-item-section>
          </q-item>

          <q-item clickable to="/account/change-password">
            <q-item-section avatar style="min-width: auto">
              <!-- <q-icon color="grey" name="las la-lock-open" size="xs" /> -->
              <q-avatar
                rounded
                color="amber-2"
                text-color="orange-5"
                icon="las la-lock-open"
                size="md"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label
                class="text-weight-medium line-normal ellipsis full-width"
                >Change Password</q-item-label
              >
            </q-item-section>
            <q-item-section side>
              <q-btn
                round
                unelevated
                text-color="dark"
                icon="chevron_right"
                dense
              />
            </q-item-section>
          </q-item>

          <q-item clickable to="/account/manage-account">
            <q-item-section avatar style="min-width: auto">
              <!-- <q-icon color="grey" name="person_outline" size="xs" /> -->
              <q-avatar
                rounded
                color="amber-2"
                text-color="orange-5"
                icon="person_outline"
                size="md"
              />
            </q-item-section>
            <q-item-section>
              <q-item-label
                class="text-weight-medium line-normal ellipsis full-width"
                >Manage Account</q-item-label
              >
            </q-item-section>
            <q-item-section side>
              <q-btn
                round
                unelevated
                text-color="dark"
                icon="chevron_right"
                dense
              />
            </q-item-section>
          </q-item>

          <!-- <q-separator spaced></q-separator> -->

          <!-- <q-item clickable @click="logout" >
                  <q-item-section avatar style="min-width:auto;"  >
                      <q-avatar rounded color="amber-14" text-color="white" icon="eva-log-out-outline" size="md" />
                  </q-item-section>
                  <q-item-section>
                      <q-item-label class="text-weight-medium line-normal ellipsis full-width">Log out</q-item-label>
                  </q-item-section>
                  <q-item-section side>
                    <q-btn round  unelevated text-color="dark" icon="chevron_right" dense />
                  </q-item-section>
                </q-item> -->
        </q-list>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script>
import auth from "src/api/auth";
import { defineAsyncComponent } from "vue";

export default {
  name: "ProfilePage",
  data() {
    return {
      data: [],
    };
  },
  components: {
    NotiButton: defineAsyncComponent(() => import("components/NotiButton.vue")),
  },
  mounted() {
    this.data = auth.getUser();
  },
  methods: {
    logout() {
      this.$q
        .dialog({
          title: "Logout",
          message: "Are you sure you want to logout?",
          persistent: true,
          position: "bottom",
          ok: {
            unelevated: true,
            color: "warning",
            rounded: false,
            "text-color": "black",
            size: "md",
            label: "Yes",
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
          auth.logout();
          this.$router.push("/home");
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
  },
};
</script>
