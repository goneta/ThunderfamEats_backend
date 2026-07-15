<template>
  <q-space class="q-pa-xs"></q-space>
     <q-card flat class="no-border-radius" >
      <q-list>
          <q-item clickable>
            <q-item-section>
               <q-skeleton type="text" />
               <q-skeleton type="text" style="width:50%" />
            </q-item-section>
            <q-item-section side top>
               <q-skeleton type="QRadio" />
          </q-item-section>
          </q-item>
      </q-list>
    </q-card>

    <q-space class="q-pa-xs"></q-space>

    <q-card flat class="no-border-radius q-pl-md" >
      <q-skeleton type="text" style="width:100px" />
      <q-item v-for="i in 4" :key="i">
        <q-item-section side>
          <q-skeleton type="QAvatar" size="35px" />
        </q-item-section>
        <q-item-section>
          <q-skeleton type="text" />
          <q-skeleton type="text" style="width:50%" />
        </q-item-section>
        <q-item-section side top>
          <q-skeleton type="text" style="width:50px" />
        </q-item-section>
      </q-item>
    </q-card>

    <q-space class="q-pa-xs"></q-space>

    <q-card flat class="no-border-radius timeline-modified" >
      <q-list>
        <q-item v-for="i in 3" :key="i">
          <q-item-section side>
             <q-skeleton type="QAvatar" />
          </q-item-section>
          <q-item-section>
            <q-skeleton type="text" />
            <q-skeleton type="text" style="width:50%" />
          </q-item-section>
          <q-item-section side top>
            <q-skeleton type="text" style="width:20px" />
          </q-item-section>
        </q-item>

        <q-item v-for="i in 3" :key="i">
          <q-item-section>
            <q-skeleton type="text" style="width:80%" />
          </q-item-section>
          <q-item-section side top>
            <q-skeleton type="text" style="width:20px" />
          </q-item-section>
        </q-item>

      </q-list>
    </q-card>

    <q-footer class="bg-grey-2 q-pa-md">
       <q-skeleton type="QBtn" class="full-width" />
    </q-footer>

</template>

<script>
export default {
  name: 'OrderDetailsSkeleton',
  setup () {
    return {}
  }
}
</script>
