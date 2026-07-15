<template>
  <template v-if="loading">
     <template v-for="i in 3" :key="i">
        <q-skeleton type="text" style="width:150px" />
        <q-list class="qlist-no-padding" >
          <template v-for="y in 4" :key="y">
            <q-item>
              <q-item-section avatar>
                 <q-skeleton style="height:50px; width:50px;" square />
              </q-item-section>
              <q-item-section>
                  <q-skeleton type="text" style="width:150px" />
                  <q-skeleton type="text" style="width:50px" />
              </q-item-section>
              <q-item-section side>
                  <q-skeleton type="QToggle" style="height:30px;width:30px;" />
              </q-item-section>
            </q-item>
          </template>
        </q-list>
     </template>
  </template>

  <template v-else v-for="category in data_category" :key="category.cat_id">
     <div :id="category.category_uiid" class="text-h6 text-weight-medium">{{category.category_name}}</div>

     <q-list class="qlist-no-padding" >
       <template v-for="items_id in category.items" :key="items_id">
       <q-item
       clickable
       v-ripple
       @click.stop="this.$emit('onClickitem',{cat_id : category.cat_id, item_uuid : data_items[items_id].item_uuid })"
       >
         <q-item-section avatar>
            <q-img
            :src="data_items[items_id].url_image"
            lazy
            fit="cover"
            style="height:50px; width:50px;"
            class="rounded-borders"
            spinner-color="amber"
            spinner-size="20px"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label lines="1" class="font12 text-weight-bold q-mb-sm">
            {{ data_items[items_id].item_name }}
          </q-item-label>
          <q-item-label v-if="data_items[items_id]"  class="font12 text-weight-medium">

            <template v-if="data_items[items_id].price[0]">
              <template v-if="data_items[items_id].price[0].discount <=0">
                <span class="q-mr-sm">{{data_items[items_id].price[0].size_name}} {{data_items[items_id].price[0].pretty_price}}</span>
              </template>
              <template v-else>
                <span class="q-mr-sm">{{data_items[items_id].price[0].size_name}} <del>{{data_items[items_id].price[0].pretty_price}}</del> {{data_items[items_id].price[0].pretty_price_after_discount}}</span>
              </template>
            </template>

          </q-item-label>
        </q-item-section>
        <q-item-section side>
          <q-btn
            color="primary"
            text-color="dark"
            dense icon="eva-plus-outline"
            size="sm"
            class="q-pl-xs q-pr-xs"
            unelevated
            @click.stop="this.$emit('onClickitem',{cat_id : category.cat_id, item_uuid : data_items[items_id].item_uuid })"
            />
        </q-item-section>
       </q-item>
       <q-separator></q-separator>
       </template>

     </q-list>

  </template>
</template>

<script>
import APIinterface from 'src/api/APIinterface'
import { scroll } from 'quasar'
const { getScrollTarget, setVerticalScrollPosition, getVerticalScrollPosition } = scroll

export default {
  name: 'MenuList',
  props: ['slug'],
  data () {
    return {
      data: [],
      data_category: [],
      data_items: [],
      loading: false,
      category_seleted: '',
      itemRefs: [],
      category_position: []
    }
  },
  update () {
    console.debug('updated' + this.slug)
  },
  watch: {
    slug (newval, oldval) {
      console.debug('watch' + newval)
      this.geStoreMenu()
    },
    deep: true
  },
  mounted () {
    this.geStoreMenu()
  },
  methods: {
    geStoreMenu () {
      if (APIinterface.empty(this.slug)) {
        return
      }
      this.loading = true
      APIinterface.geStoreMenu(this.slug)
        .then(data => {
          this.data_category = data.details.data.category
          this.data_items = data.details.data.items
          this.$emit('afterCategory', this.data_category)
        })
        .catch(error => {
          console.debug(error)
        })
        .then(data => {
          this.loading = false
        })
    }
  }
}
</script>
