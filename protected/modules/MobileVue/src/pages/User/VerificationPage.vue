<template>

  <q-footer class="transparent text-dark"  >

    <q-card flat>
    <div class="text-center">

      <q-img
        src="logo.jpg"
        style="height: 35; max-width: 100px"
        lazy
        class="q-mb-xl"
      />

      <div class="w-75 margin-auto">

         <div class="row q-col-gutter-md">
           <q-input v-model="code1" mask="#" input-class="text-center text-weight-bold" class="col" />
           <q-input v-model="code2" mask="#" input-class="text-center text-weight-bold" class="col" />
           <q-input v-model="code3" mask="#" input-class="text-center text-weight-bold" class="col"/>
           <q-input v-model="code4" mask="#" input-class="text-center text-weight-bold" class="col" />
         </div>

         <q-space class="q-pa-md"></q-space>

         <q-card-actions vertical align="center">
          <q-btn label="Verify Now" unelevated color="primary" text-color="dark" class="full-width text-weight-bold"  />
        </q-card-actions>

        <q-space class="q-pa-lg"></q-space>
        <q-space class="q-pa-lg"></q-space>
        <q-space class="q-pa-md"></q-space>

      </div>
      <!-- w 75 -->

    </div>
    <!-- center -->
    </q-card>

  </q-footer>

</template>

<script>
export default {
  name: 'VerificationPage',
  data () {
    return {
      code1: 1,
      code2: 7,
      code3: 3,
      code4: 6
    }
  }
}
</script>
