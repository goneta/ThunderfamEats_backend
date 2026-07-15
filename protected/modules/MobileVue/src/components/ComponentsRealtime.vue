<template>
  <div style="display: none"></div>
</template>

<script>
import { loadScript } from "vue-plugin-load-script";
import auth from "src/api/auth";
import { useDataStore } from "stores/DataStore";
import APIinterface from "src/api/APIinterface";

export default {
  name: "ComponentsRealtime",
  props: ["getevent", "subscribe_to"],
  setup() {
    const DataStore = useDataStore();
    return { DataStore };
  },

  data() {
    return {
      data: [],
      pusher: undefined,
      channel: undefined,
    };
  },
  watch: {
    DataStore: {
      immediate: true,
      deep: true,
      handler(newValue, oldValue) {
        if (Object.keys(newValue.realtime_data).length > 0) {
          if (auth.authenticated()) {
            this.data = newValue.realtime_data;
            if (this.data.realtime_app_enabled == 1) {
              this.initProvider();
            }
          }
        }
      },
    },
  },
  methods: {
    initProvider() {
      switch (this.data.realtime_provider) {
        case "pusher":
          loadScript("https://js.pusher.com/7.0/pusher.min.js")
            .then(() => {
              this.initPusher();
            })
            .catch(() => {
              console.debug("failed loading realtime script");
            });
          break;
      }
    },
    initPusher() {
      if (APIinterface.empty(this.pusher)) {
        console.log("initPusher");
        Pusher.logToConsole = false;
        this.pusher = new Pusher(this.data.pusher_key, {
          cluster: this.data.pusher_cluster,
        });
        const userData = auth.getUser();
        console.log("subscribe_to=>" + this.subscribe_to);
        console.log("Client ID =>" + userData.client_uuid);

        let subscribeTo = userData.client_uuid;
        if (
          typeof this.subscribe_to !== "undefined" &&
          this.subscribe_to !== null
        ) {
          subscribeTo = this.subscribe_to;
        }

        console.log("subscribeTo=>" + subscribeTo);
        //this.channel = this.pusher.subscribe(userData.client_uuid);
        this.channel = this.pusher.subscribe(subscribeTo);
        console.log(this.data.event);
        console.log("getevent=>" + this.getevent);
        console.log("=>" + this.data.event[this.getevent]);
        this.channel.bind(this.data.event[this.getevent], (data) => {
          this.$emit("afterReceive", data);
        });
      }
    },
  },
};
</script>
