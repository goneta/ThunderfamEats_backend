// INCLUDE FIREBASE STORE
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.9.3/firebase-app.js";
import { getFirestore,
  onSnapshot,
  collection,
  doc ,
  getDocs, 
  getDoc,
  query,
  orderBy,
  limit,
  where,
  Timestamp,
  addDoc,
  setDoc,
  updateDoc,
  serverTimestamp,
  deleteDoc
} from "https://www.gstatic.com/firebasejs/9.9.3/firebase-firestore.js";

var empty = function(data){	
	if (typeof data === "undefined" || data==null || data=="" || data=="null" || data=="undefined" ) {	
		return true;
	}
	return false;
};

let $fc = JSON.parse(firebase_configuration);
const firebaseConfig = {
    apiKey: $fc.firebase_apikey,
    authDomain: $fc.firebase_domain,
    projectId: $fc.firebase_projectid,
    storageBucket: $fc.firebase_storagebucket,
    messagingSenderId: $fc.firebase_messagingid,
    appId: $fc.firebase_appid,
};

const firebaseCollectionEnum = {    
    drivers: "drivers",
    limit : 500
};

const firebaasApp = initializeApp(firebaseConfig);
const firebaseDb = getFirestore(firebaasApp);

const app_track_order = Vue.createApp({  
    data() {
        return {
            data :[],
            driver_uuid : null,
            lastSentPosition : null,
            newPosition : null,
            threshold_meters : null
        }
    },
    mounted() {
        if ((typeof threshold_meters !== "undefined") && ( threshold_meters !== null)) {
            this.threshold_meters = threshold_meters;
        } else {
            this.threshold_meters = 50;
        }        
    },    
    watch: {
        driver_uuid(newval,oldval){            
            this.getFirebaseData();
        },
    },
    methods: {
        getFirebaseData(){
            console.log("getFirebaseData");
            const chatDocRef = doc(firebaseDb, firebaseCollectionEnum.drivers, this.driver_uuid);
            const SnapData = onSnapshot(chatDocRef, (docSnapshot) => {
                if (docSnapshot.exists()) {
                    let results = docSnapshot.data();                            
                    console.log("results",results);
                    const { lng, lat } = results;
                    this.newPosition = { lng, lat};
                    
                    if(!this.lastSentPosition || this.isSignificantChange(this.newPosition,this.lastSentPosition , this.threshold_meters) ){                        
                        console.log("update location");
                        this.lastSentPosition = { lng, lat };           
                        window.app_orders_track.setDriverLocation(results);
                    } else {
                        console.log('same location');
                    }                    
                } 
            }, (error) => {
                console.error('Error fetching chat document:', error);
            });          
        },
        isSignificantChange(newStart, oldStart, threshold = 50) {
            const distance = this.getDistance(
              newStart.lat, newStart.lng,
              oldStart.lat, oldStart.lng
            );            
            console.log("distance",distance);
            return distance > threshold; // If the distance is greater than the threshold (e.g., 50 meters)
        },
        getDistance(lat1, lng1, lat2, lng2) {
            const R = 6371e3; // Radius of the Earth in meters
            const φ1 = lat1 * Math.PI / 180; // φ, λ in radians
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
          
            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                      Math.cos(φ1) * Math.cos(φ2) *
                      Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
          
            const d = R * c; // in meters
            return d;
        },        
        //
    },
});
window.vm_track_order = app_track_order.mount('#app-track-order');