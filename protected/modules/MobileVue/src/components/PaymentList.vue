<template>
  <q-list v-if="loading">
     <q-item v-for="i in 4" :key="i" >
       <q-item-section avatar ><q-skeleton type="QRadio" size="25px" /></q-item-section>
       <q-item-section><q-skeleton type="text"></q-skeleton></q-item-section>
       <q-item-section  side><q-skeleton type="QRadio" size="25px" /></q-item-section>
      </q-item>
   </q-list>
   <q-list v-else>
    <template v-for="items in data" :key="items.payment_code" >
    <q-item clickable v-ripple @click="this.$emit('onchoosePayment', items  )">
      <q-item-section avatar >
         <template v-if="items.logo_type==='icon'">
            <q-icon color="warning" name="credit_card" />
         </template>
         <template v-else>
              <q-img
                :src="items.logo_image"
                fit="contain"
                style="height: 35px; max-width: 35px"
              />
         </template>
      </q-item-section>
      <q-item-section>{{items.payment_name}}</q-item-section>
    </q-item>
    <!-- <q-separator spaced inset /> -->
    </template>
  </q-list>
</template>

<script>
import APIinterface from 'src/api/APIinterface'
export default {
  name: 'PaymentList',
  data () {
    return {
      loading: false,
      data: [],
      credentials: []
    }
  },
  mounted () {
    this.PaymentList()
  },
  methods: {
    PaymentList () {
      this.loading = true
      APIinterface.PaymentList(APIinterface.getStorage('cart_uuid'))
        .then(data => {
          this.data = data.details.data
          this.credentials = data.details.credentials
          this.$emit('setPaymentcredentials', this.credentials)
        })
        .catch(error => {
          APIinterface.notify('negative', error, 'error_outline', this.$q)
        })
        .then(data => {
          this.loading = false
        })
    }
  }
}
</script>
