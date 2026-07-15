import { Geolocation } from "@capacitor/geolocation";
import { LocationAccuracy } from "@awesome-cordova-plugins/location-accuracy";

const AppLocation = {
  async islocationEnabled() {

    let myPromise = new Promise(function (resolve, reject) {
      Geolocation.checkPermissions()
        .then((data) => {
          console.debug(data);
          if (data.location === "denied") {
            Geolocation.requestPermissions().then((data) => {
              if (data.location === "granted") {
                resolve("granted");
              } else {
                resolve("denied");
              }
            });
          } else if (data.location === "prompt") {
            Geolocation.requestPermissions().then((data) => {
              if (data.location === "granted") {
                resolve("granted");
              } else {
                resolve("denied");
              }
            });
          } else if (data.location === "granted") {
            resolve("granted");
          } else if (data.location === "prompt-with-rationale") {
            Geolocation.requestPermissions().then((data) => {
              if (data.location === "granted") {
                resolve("granted");
              } else {
                resolve("denied");
              }
            });
          }
          //
        })
        .catch((error) => {
          reject();
        });
    });

    return await myPromise;
  },

  async checkAccuracy() {
    let accuracyPromise = new Promise(function (resolve, reject) {
      LocationAccuracy.canRequest()
        .then((data) => {
          LocationAccuracy.request(
            LocationAccuracy.REQUEST_PRIORITY_HIGH_ACCURACY
          ).then(
            () => {
              resolve(true);
            },
            (error) => {
              reject(error);
            }
          );
        })
        .catch((error) => {
          LocationAccuracy.request(
            LocationAccuracy.REQUEST_PRIORITY_HIGH_ACCURACY
          ).then(
            () => {
              resolve(true);
            },
            (error) => {
              reject(error);
            }
          );
        });
    });
    return await accuracyPromise;
  },

  async getPosition() {
    let positionPromise = new Promise(function (resolve, reject) {
      Geolocation.getCurrentPosition()
        .then((data) => {
          console.debug(data);
          let position = {
            lat: data.coords.latitude,
            lng: data.coords.longitude,
          };
          resolve(position);
        })
        .catch((error) => {
          reject(error);
        });
    });
    return await positionPromise;
  },
};
export default AppLocation;
