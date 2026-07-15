<template>
  <q-page padding>
    <div>
      GPS positionx:
      <pre>{{position}}</pre>
    </div>

    <div>Model: {{ model }}</div>
    <div>Manufacturer: {{ manufacturer }}</div>
    <div>platform: {{ platform }}</div>

    <br/>
    <div>Firebase : {{ token }}</div>

  </q-page>
</template>

<script>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Geolocation } from '@capacitor/geolocation'
import { Device } from '@capacitor/device'
import { PushNotifications } from '@capacitor/push-notifications'

export default {
  name: 'TestPage',
  setup () {
    const position = ref('determining...')
    const model = ref('Please wait...')
    const manufacturer = ref('Please wait...')
    const platform = ref('Please wait')
    const token = ref('')

    function getCurrentPosition () {
      alert('getCurrentPosition')
      Geolocation.getCurrentPosition().then(data => {
        // alert(JSON.stringify(data))
        position.value = {
          lat: data.coords.latitude,
          lng: data.coords.longitude
        }
      })
    }

    let geoId

    onMounted(() => {
      Geolocation.checkPermissions().then(data => {
        alert(JSON.stringify(data))
        if (data.location === 'denied') {
          Geolocation.requestPermissions().then(data => {
            alert(JSON.stringify(data))
            if (data.location === 'granted') {
              getCurrentPosition()
            }
          })
        } else if (data.location === 'prompt') {
          Geolocation.requestPermissions().then(data => {
            alert(JSON.stringify(data))
            if (data.location === 'granted') {
              getCurrentPosition()
            }
          })
        } else if (data.location === 'granted') {
          getCurrentPosition()
        }
      })

      Device.getInfo().then(info => {
        console.debug(info)
        model.value = info.model
        manufacturer.value = info.manufacturer
        platform.value = info.platform
      })

      PushNotifications.requestPermissions().then(result => {
        if (result.receive === 'granted') {
          PushNotifications.register()
        } else {
          alert('error push permission')
        }
      })

      PushNotifications.addListener('registration', (Token) => {
        token.value = Token.value
      })

      PushNotifications.addListener('registrationError', (error) => {
        alert('Error on registration: ' + JSON.stringify(error))
      })

      PushNotifications.addListener('pushNotificationReceived', (notification) => {
        alert('Push received: ' + JSON.stringify(notification))
      })

      //
    })

    onBeforeUnmount(() => {
      // we do cleanup
      // Geolocation.clearWatch(geoId)
    })

    return {
      position,
      model,
      manufacturer,
      platform,
      token
    }
  }
}
</script>
