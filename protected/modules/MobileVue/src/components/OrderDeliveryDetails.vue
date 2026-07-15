<template>
  <q-dialog @show="show" @hide="hide" v-model="dialog" position="bottom">
    <q-card class="rounded-borders-top">
      <q-toolbar class="text-primary top-toolbar q-pl-md" dense>
        <q-toolbar-title
          class="text-weight-bold"
          :class="{
            'text-white': $q.dark.mode,
            'text-dark': !$q.dark.mode,
          }"
        >
          {{ $t("Delivery Details") }}
        </q-toolbar-title>
        <q-space></q-space>
        <q-btn
          @click="dialog = !true"
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

      <div class="q-pl-md q-pr-md q-pb-sm">
        <div
          class="q-pa-sm text-white radius8"
          :class="{
            'bg-red': progress.order_progress == 0,
            'bg-green': progress.order_progress == 4,
            'bg-blue': progress.order_progress > 0,
          }"
        >
          <div class="text-weight-bold">{{ progress.order_status }}</div>
          <div>{{ progress.order_status_details }}</div>
        </div>

        <div class="timeline-modified q-pl-sm">
          <q-timeline :color="$q.dark.mode ? 'grey600' : 'primary'">
            <template v-for="(items, index) in data" :key="items">
              <template v-if="index == 0">
                <q-timeline-entry icon="check">
                  <template v-slot:title> {{ items.created_at }}</template>
                  <template v-slot:subtitle>
                    <span
                      v-if="order_status[items.status]"
                      :class="{
                        'text-white': $q.dark.mode,
                        'text-dark': !$q.dark.mode,
                      }"
                    >
                      {{ order_status[items.status] }}
                    </span>
                    <span v-else> items.status </span>
                  </template>
                  <div>{{ items.remarks }}</div>
                </q-timeline-entry>
              </template>
              <template v-else>
                <q-timeline-entry color="mygrey">
                  <template v-slot:title> {{ items.created_at }}</template>
                  <template v-slot:subtitle>
                    <span
                      v-if="order_status[items.status]"
                      :class="{
                        'text-white': $q.dark.mode,
                        'text-dark': !$q.dark.mode,
                      }"
                    >
                      {{ order_status[items.status] }}
                    </span>
                    <span v-else> items.status </span>
                  </template>
                  <div class="text-grey">{{ items.remarks }}</div>
                </q-timeline-entry>
              </template>
            </template>
          </q-timeline>
        </div>
      </div>
    </q-card>
  </q-dialog>
</template>

<script>
export default {
  name: "OrderDeliveryDetails",
  props: ["data", "order_status", "progress"],
  data() {
    return {
      dialog: false,
    };
  },
  setup() {
    return {};
  },
};
</script>
