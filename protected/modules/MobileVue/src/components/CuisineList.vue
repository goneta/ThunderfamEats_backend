<template>

   <template v-if="loading">
      <div class="row items-center q-col-gutter-sm q-mb-sm">
          <div v-for="i in 21" :key="i" class="col-4">
              <q-card flat class="q-pa-sm relative-position" >
                 <q-skeleton height="90px" square />
              </q-card>
          </div>
      </div>
   </template>

   <div v-else class="row items-center q-col-gutter-sm q-mb-sm">
         <div v-for="items in data" :key="items" class="col-4">
           <router-link :to="{name: 'quicksearch', query: {q : items.cuisine_name } }" >
           <q-card flat class="q-pa-sm relative-position" >
                <q-img
                :src="items.featured_image"
                style="height:90px;"
                lazy
                fit="cover"
                class="no-border-radius"
                spinner-color="amber"
                spinner-size="sm"
              />

              <div class="text-white absolute-bottom-left q-pl-md text-h6 text-weight-bold q-mb-md line-normal ellipsis">
                {{items.cuisine_name}}
              </div>
           </q-card>
           </router-link>
         </div>
         <!-- col -->
     </div>
     <!-- row -->

</template>

<script>
import APIinterface from 'src/api/APIinterface'

export default {
  name: 'CuisineList',
  props: ['q'],
  data () {
    return {
      data: [],
      loading: false,
      awaitingSearch: false
    }
  },
  mounted () {
    this.CuisineList()
  },
  watch: {
    q (newdata, oldata) {
      if (!this.awaitingSearch) {
        if (typeof this.q === 'undefined' || this.q === null || this.q === '' || this.q === 'null' || this.q === 'undefined') {
          this.CuisineList()
          return false
        }
        setTimeout(() => {
          APIinterface.CuisineList(0, this.q)
            .then(data => {
              this.data = data.details.data
            })
            // eslint-disable-next-line
            .catch(error => {
              this.data = []
            })
            .then(data => {
              this.awaitingSearch = false
              this.$emit('onSearch', this.awaitingSearch)
            })
        }, 1000) // 1 sec delay
      }
      this.awaitingSearch = true
      this.$emit('onSearch', this.awaitingSearch)
    }
  },
  methods: {
    CuisineList () {
      this.loading = true
      APIinterface.CuisineList(0, '')
        .then(data => {
          this.data = data.details.data
        })
        // eslint-disable-next-line
        .catch(error => {

        })
        .then(data => {
          this.loading = false
        })
    }
  }
}
</script>
