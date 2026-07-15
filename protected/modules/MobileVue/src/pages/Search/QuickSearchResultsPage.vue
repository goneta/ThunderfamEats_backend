<template>
  <q-page padding>
     <q-input
      v-model="q"
      label="Food, groceries, drinks etc"
      rounded
      dense
      outlined
      color="grey"
      bg-color="grey-1"
      borderless
      class="full-width font12 input-borderless q-mb-md"
      >
      <template v-slot:prepend>
          <q-icon name="search" />
      </template>
    </q-input>

     <q-tabs
      v-model="tab"
      dense
      class="text-grey"
      active-color="dark"
      indicator-color="dark"
      align="justify"
      narrow-indicator
    >
      <q-tab name="all" label="All" no-caps />
      <q-tab name="restaurant" label="Restaurants" no-caps />
      <q-tab name="food" label="Food" no-caps />
    </q-tabs>
     <q-separator />

     <q-tab-panels v-model="tab" animated transition-next="fade" transition-prev="fade">
       <q-tab-panel name="all" class="q-pl-none q-pr-none">
          <q-list dense class="">
            <q-item-label header>Mexican</q-item-label>
            <template v-for="i in 2" :key="i" >
            <q-item clickable v-ripple>
              <q-item-section avatar >
                <q-avatar square>
                   <q-img
                    src="food1.png"
                    fit="contain"
                    loading="lazy"
                  />
                </q-avatar>
              </q-item-section>
              <q-item-section class="font12">
                <div class="font12 text-weight-medium q-mb-xs">Chipotle Mexican Grill</div>
                 <p class="font11 text-grey height-normal no-margin ellipsis-2-lines">Chinese, american, italian &bull; $$</p>
              </q-item-section>
            </q-item>
            <q-separator spaced inset="item" />
            </template>

          </q-list>
       </q-tab-panel>
       <q-tab-panel name="restaurant">

       </q-tab-panel>
       <q-tab-panel name="food">

       </q-tab-panel>
     </q-tab-panels>

  </q-page>
</template>

<script>
export default {
  name: 'QuickSearchResultsPage',
  data () {
    return {
      tab: 'all'
    }
  }
}
</script>
