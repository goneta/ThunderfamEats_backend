<template>
  <q-btn
  v-if="active"
  round
  class="q-mr-sm"
  color="amber"
  unelevated
  text-color="white"
  icon="favorite_border"
  dense
  size="sm"
  @click="addTofav()"
  :loading="loading"
  />

  <q-btn
  v-else
  round
  class="q-mr-sm"
  color="white"
  unelevated
  text-color="amber"
  icon="favorite_border"
  dense
  size="sm"
  @click="addTofav()"
  :loading="loading"
  />
</template>

<script>
import APIinterface from 'src/api/APIinterface'
import auth from 'src/api/auth'

export default {
  name: 'FavsMerchant',
  props: ['merchant_id', 'active'],
  data () {
    return {
      loading: false
    }
  },
  methods: {
    addTofav () {
      if (auth.authenticated()) {
        APIinterface.SaveStore(this.merchant_id)
          .then(data => {
            this.$emit('afterSavefav', data.details.found)
          })
          .catch(error => {
            APIinterface.notify('red-5', error, 'error_outline', this.$q)
          })
          .then(data => {

          })
      } else {
        APIinterface.notify('red-5', 'Login to save this to your favorites', 'eva-info-outline', this.$q)
      }
    }
  }
}
</script>
