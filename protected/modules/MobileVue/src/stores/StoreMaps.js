import { defineStore } from 'pinia'
import APIinterface from 'src/api/APIinterface'
// eslint-disable-next-line
import jwt_decode from 'jwt-decode'

export const useMapsStore = defineStore('maps_store', {
  state: () => ({
    loading: false,
    maps_config: [],
    marker_position: []
  }),
  actions: {
    getMapconfig () {
      this.loading = true
      APIinterface.getMapconfig()
        .then(data => {
          this.maps_config = jwt_decode(data.details)
          this.marker_position = { lat: parseFloat(this.maps_config.default_lat), lng: parseFloat(this.maps_config.default_lng) }
        })
        // eslint-disable-next-line
        .catch(error => {
        })
        .then(data => {
          this.loading = false
        })
    }
  }
})
