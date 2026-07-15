<template>
  <q-pull-to-refresh @refresh="refresh">
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
        <q-toolbar-title class="text-weight-bold">
          <template v-if="hold"
            >{{ itemToDelete }} {{ $t("selected") }}</template
          >
          <template v-else>{{ $t("Notifications") }}</template>
        </q-toolbar-title>
        <q-btn
          v-if="hold"
          @click="hold = false"
          flat
          round
          dense
          icon="close"
          :color="$q.dark.mode ? 'white' : 'dark'"
        />
        <template v-else>
          <q-btn
            v-if="hasData"
            @click="deleteAll"
            flat
            round
            dense
            icon="las la-trash"
            :color="$q.dark.mode ? 'white' : 'dark'"
          />
        </template>
      </q-toolbar>
    </q-header>

    <q-page
      class="bg-transparent"
      :class="{ 'flex flex-center': !hasData && !loading }"
    >
      <q-infinite-scroll ref="nscroll" @load="getNotifications" :offset="250">
        <q-list>
          <template v-for="(item, index1) in data" :key="item">
            <q-slide-item
              v-for="(items, index2) in item"
              :key="items"
              @action="deleteNotification(index1, index2, items)"
              :right-color="$q.dark.mode ? 'grey600' : 'mygrey'"
            >
              <template v-slot:right>
                <q-btn
                  round
                  color="lightprimary"
                  text-color="primary"
                  icon="las la-trash"
                  unelevated
                  size="sm"
                />
              </template>
              <q-item
                v-touch-hold.mouse="handleHold"
                tag="label"
                :class="{
                  'bg-mydark ': $q.dark.mode,
                  'bg-white ': !$q.dark.mode,
                }"
              >
                <q-item-section avatar>
                  <template v-if="hold">
                    <q-checkbox
                      v-model="notification_uuids"
                      :val="items.notification_uuid"
                    />
                  </template>
                  <template v-else>
                    <q-avatar>
                      <img
                        v-if="items.image_type == 'image'"
                        :src="items.image"
                      />
                      <q-avatar
                        v-else
                        color="primary"
                        text-color="white"
                        icon="notifications"
                        size="md"
                      />
                    </q-avatar>
                  </template>
                </q-item-section>
                <q-item-section>
                  <q-item-label lines="2" class="text-weight-medium font13">
                    {{ items.message }}
                  </q-item-label>
                  <q-item-label caption class="font11">
                    {{ items.date }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-badge rounded color="mygrey" />
                </q-item-section>
              </q-item>
            </q-slide-item>
          </template>
        </q-list>

        <template v-slot:loading>
          <q-inner-loading
            v-if="page <= 0"
            :showing="true"
            color="primary"
            size="md"
          />
          <div v-else class="row justify-center q-my-md">
            <q-spinner-dots color="primary" size="40px" />
          </div>
        </template>
      </q-infinite-scroll>

      <template v-if="!loading">
        <template v-if="hasData">
          <template v-if="data_done && data.length >= 10">
            <div class="row justify-center q-my-sm">
              <p class="font12 text-grey">{{ $t("end of results") }}</p>
            </div>
          </template>
        </template>
        <template v-else>
          <div class="full-width text-center">
            <q-img
              src="no-notification.png"
              fit="fill"
              spinner-color="primary"
              style="height: 150px; max-width: 200px"
            />
            <div class="text-h5 text-weight-bold">
              {{ $t("No notifications") }}
            </div>
            <p class="text-grey font12">
              {{ $t("If there is notification it will show here") }}
            </p>
          </div>
        </template>
      </template>

      <q-page-scroller
        position="bottom-right"
        :scroll-offset="150"
        :offset="[18, 18]"
      >
        <q-btn
          fab
          icon="keyboard_arrow_up"
          color="mygrey"
          text-color="dark"
          dense
          padding="3px"
        />
      </q-page-scroller>
    </q-page>
  </q-pull-to-refresh>

  <q-footer v-if="hold && itemToDelete > 0" class="bg-lightprimary">
    <q-tabs
      v-model="tab"
      class="text-grey-8"
      no-caps
      dense
      shrink
      narrow-indicator
      stretch
      align="left"
      :breakpoint="0"
      indicator-color="lightprimary"
    >
      <q-tab
        name="delete"
        icon="las la-trash"
        :label="$t('Delete')"
        @click="deleteAllNotification"
      />
    </q-tabs>
  </q-footer>
</template>

<script>
import APIinterface from "src/api/APIinterface";
import { useCartStore } from "stores/CartStore";

export default {
  name: "NotificationsPage",
  data() {
    return {
      loading: true,
      data: [],
      page: 0,
      data_done: false,
      is_refresh: undefined,
      hold: false,
      notification_uuids: [],
      tab: "delete",
    };
  },
  setup() {
    const CartStore = useCartStore();
    return { CartStore };
  },
  watch: {
    hold(newval, oldval) {
      if (!newval) {
        this.notification_uuids = [];
      }
    },
  },
  computed: {
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
    itemToDelete() {
      return Object.keys(this.notification_uuids).length;
    },
  },
  methods: {
    refresh(done) {
      this.resetPagination();
      this.is_refresh = done;
    },
    getNotifications(index, done) {
      this.loading = true;
      APIinterface.getNotification(index)
        .then((data) => {
          this.page = index;
          this.data.push(data.details.data);
          console.log(this.data);
        })
        .catch((error) => {
          this.data_done = true;
          this.$refs.nscroll.stop();
        })
        .then((data) => {
          done();
          this.loading = false;
          if (!APIinterface.empty(this.is_refresh)) {
            this.is_refresh();
          }
        });
    },
    resetPagination() {
      this.page = 0;
      this.data = [];
      this.$refs.nscroll.reset();
      this.$refs.nscroll.resume();
      this.$refs.nscroll.trigger();
    },
    deleteNotification(index1, index2, items) {
      this.data[index1].splice(index2, 1);
      APIinterface.deleteNotification(items.notification_uuid).then((data) => {
        //
      });
    },
    handleHold(event) {
      this.hold = true;
    },
    resetHold() {
      this.hold = false;
      this.notification_uuids = [];
    },
    deleteAllNotification() {
      APIinterface.fetchDataByToken("deleteAllNotification", {
        notification_uuids: this.notification_uuids,
      })
        .then((data) => {
          this.hold = false;
          this.resetPagination();
        })
        .catch((error) => {})
        .then((data) => {});
    },
    deleteAll() {
      this.$q
        .dialog({
          title: this.$t("Delete all"),
          message: this.$t("Are you sure want to delete all notifications?"),
          transitionShow: "fade",
          transitionHide: "fade",
          ok: {
            unelevated: true,
            color: "primary",
            rounded: false,
            "text-color": "white",
            size: "md",
            label: this.$t("Okay"),
            "no-caps": true,
          },
        })
        .onOk(() => {
          APIinterface.fetchDataByToken("deleteNotifications", {})
            .then((data) => {
              this.hold = false;
              this.resetPagination();
            })
            .catch((error) => {})
            .then((data) => {});
        })
        .onCancel(() => {
          // console.log('Cancel')
        })
        .onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        });
    },
  },
};
</script>
