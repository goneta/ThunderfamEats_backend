<template>
  <q-dialog v-model="PushStore.push_modal" position="top" :seamless="true">
    <q-card style="width: 75% !important; margin-top: 20px">
      <q-card-section class="q-pa-sm">
        <div class="row items-center fit">
          <div class="col">
            <div class="text-weight-bold">
              {{ PushStore.push_message.title }}
            </div>
          </div>
          <div class="col-2 text-right">
            <q-btn
              @click="PushStore.push_modal = false"
              round
              color="grey"
              icon="close"
              size="7px"
              unelevated
            />
          </div>
        </div>
        <q-space class="q-pa-sm"></q-space>
        <div class="font11 text-weight-medium">
          {{ PushStore.push_message.body }}
        </div>
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script>
import { usePushStore } from "stores/PushStore";
export default {
  name: "PushDialog",
  setup() {
    const PushStore = usePushStore();
    return { PushStore };
  },
};
</script>
