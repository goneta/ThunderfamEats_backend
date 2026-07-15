<template>
  <q-btn
    to="/account/notifications"
    flat
    round
    dense
    icon="las la-bell"
    class="q-mr-smx"
    :color="$q.dark.mode ? 'white' : 'dark'"
  >
    <transition
      v-if="hasData"
      appear
      enter-active-class="animated zoomIn"
      leave-active-class="animated zoomOut"
    >
      <q-badge floating color="primary2" rounded style="top: 2px" />
    </transition>
  </q-btn>

  <ComponentsRealtime
    ref="realtime"
    getevent="notification_event"
    @after-receive="afterReceive"
  />
</template>

<script>
import { defineAsyncComponent } from "vue";

export default {
  name: "NotiButton",
  components: {
    ComponentsRealtime: defineAsyncComponent(() =>
      import("components/ComponentsRealtime.vue")
    ),
  },
  data() {
    return {
      data: [],
    };
  },
  computed: {
    hasData() {
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
  },
  methods: {
    afterReceive(data) {
      this.data = data;
    },
  },
};
</script>
