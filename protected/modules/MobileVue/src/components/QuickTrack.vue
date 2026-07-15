<template>
  <q-dialog v-model="modal" position="bottom" :seamless="true">
    <q-card class="q-pa-md curve2" style="padding-bottom: 0px">
      <div class="row items-center">
        <div class="col">
          <div class="row inline items-center">
            <div class="borde q-pr-sm">
              <q-badge rounded color="secondary" />
            </div>
            <div class="text-weight-bold">{{ merchant.restaurant_name }}</div>
          </div>
        </div>
        <!-- col -->
        <div class="col-2 text-right">
          <q-btn
            @click="modal = false"
            round
            color="white"
            text-color="secondary"
            icon="eva-arrow-ios-downward-outline"
            dense
            size="xs"
            unelevated
          />
        </div>
      </div>
      <!-- row -->

      <div class="row items-center q-mt-sm">
        <div class="col">
          <q-linear-progress
            indeterminate
            v-if="order_progress < 4"
            color="secondary"
            class="q-mb-sm"
          />
          <div class="text-weight-bold">{{ order_status }}</div>
          <p class="font12 text-weight-medium">{{ order_status_details }}</p>
        </div>
        <div class="col-4 text-right" v-if="order_progress < 4">
          <q-btn
            :to="`/account/trackorder?order_uuid=${order_uuid}`"
            unelevated
            no-caps
            :label="$t('Track')"
            color="primary"
          ></q-btn>
        </div>
      </div>
      <!-- row -->
    </q-card>
  </q-dialog>

  <ComponentsRealtime
    ref="realtime"
    getevent="tracking"
    @after-receive="afterReceive"
  />
</template>

<script>
// https://github.com/capacitor-community/native-audio
import { defineAsyncComponent } from "vue";

export default {
  name: "QuickTrack",
  components: {
    ComponentsRealtime: defineAsyncComponent(() =>
      import("components/ComponentsRealtime.vue")
    ),
  },
  data() {
    return {
      modal: false,
      merchant: [],
      order_progress: 0,
      order_status: "",
      order_status_details: "",
      order_id: "",
      order_uuid: "",
      test_data: {
        order_progress: 4,
        order_status: "Order Complete",
        order_status_details: "Your order is completed. Enjoy!",
        order_id: "10146",
        order_uuid: "5ea2b465-4e88-11ed-afba-9c5c8e164c2c",
        merchant: {
          merchant_id: "3",
          merchant_uuid: "7dedd05f-809a-11ec-859e-99479722e411",
          restaurant_name: "McDonald's",
          restaurant_slug: "mcdonalds",
          address: "2810 South Figueroa Street, Los Angeles, CA, USA",
          distance_unit: "mi",
          delivery_distance_covered: "100.00",
          latitude: "14.563190",
          longitude: "121.045418",
          merchant_type: "2",
          commision_type: "percentage",
          commission: "5.00",
          logo: "1782c36c-809c-11ec-859e-99479722e411.jpeg",
          path: "upload/3",
          contact_phone: "+12243333333",
          contact_email: "mcdo@yahoo.cm",
          cuisine_name:
            "American;#bad5f2;#444444,Barbeque;#b27c45;white,Chinese;#f6b26b;white,Deli;#d87f22;white,Diner;#3d85c6;#5b5b5b,Greek;#b45f06;white,Indian;#e69138;#999999,Italian;#a2c4c9;#5b5b5b,Korean;#f9cb9c;#5b5b5b,Mediterranean;#ffd966;#999999,Middle Eastern;#45818e;white",
          url_logo:
            "http://localhost/kmrs2/upload/3/1782c36c-809c-11ec-859e-99479722e411.jpeg",
          restaurant_url: "http://localhost/kmrs2/mcdonalds",
          restaurant_direction:
            "https://www.google.com/maps/dir/?api=1&destination=14.563190,121.045418",
          cuisine: [
            {
              cuisine_name: "American",
              bgcolor: "#bad5f2",
              fncolor: "#444444",
            },
            {
              cuisine_name: "Barbeque",
              bgcolor: "#b27c45",
              fncolor: "white",
            },
            {
              cuisine_name: "Chinese",
              bgcolor: "#f6b26b",
              fncolor: "white",
            },
            {
              cuisine_name: "Deli",
              bgcolor: "#d87f22",
              fncolor: "white",
            },
            {
              cuisine_name: "Diner",
              bgcolor: "#3d85c6",
              fncolor: "#5b5b5b",
            },
            {
              cuisine_name: "Greek",
              bgcolor: "#b45f06",
              fncolor: "white",
            },
            {
              cuisine_name: "Indian",
              bgcolor: "#e69138",
              fncolor: "#999999",
            },
            {
              cuisine_name: "Italian",
              bgcolor: "#a2c4c9",
              fncolor: "#5b5b5b",
            },
            {
              cuisine_name: "Korean",
              bgcolor: "#f9cb9c",
              fncolor: "#5b5b5b",
            },
            {
              cuisine_name: "Mediterranean",
              bgcolor: "#ffd966",
              fncolor: "#999999",
            },
            {
              cuisine_name: "Middle Eastern",
              bgcolor: "#45818e",
              fncolor: "white",
            },
          ],
          merchant_address: "2810 South Figueroa Street, Los Angeles, CA, USA",
        },
      },
    };
  },
  created() {
    //this.afterReceive(this.test_data);
  },
  methods: {
    afterReceive(data) {
      this.modal = true;
      this.merchant = data.merchant;
      this.order_progress = data.order_progress;
      this.order_status = data.order_status;
      this.order_status_details = data.order_status_details;
      this.order_id = data.order_id;
      this.order_uuid = data.order_uuid;
    },
  },
};
</script>
