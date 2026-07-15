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

import 'https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js'
import insertText from 'https://cdn.jsdelivr.net/npm/insert-text-at-cursor@0.3.0/index.js'
import WaveSurfer from 'https://cdn.jsdelivr.net/npm/wavesurfer.js@7/dist/wavesurfer.esm.js'
import RecordPlugin from 'https://cdn.jsdelivr.net/npm/wavesurfer.js@7/dist/plugins/record.esm.js';

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
    chats: "chats",
    limit : 50
};

const firebaasApp = initializeApp(firebaseConfig);
const firebaseDb = getFirestore(firebaasApp);

// END OF FIREBASE STORE 

const quasarComponents = {
	empty (data) {
		if (typeof data === 'undefined' || data === null || data === '' || data === 'null' || data === 'undefined') {
		  return true
		}
		return false
	},
    setStorage (key, value) {
		try {
			Quasar.LocalStorage.set(key, value)
		} catch (e) {
		  console.debug(e)
		}
	  },
	getStorage (key) {
		return Quasar.LocalStorage.getItem(key)
	},
	notify (color, message, icon ) {
		const $q = Quasar.Notify;			
		$q.create({
		  message,
		  color,
		  icon,
		  position: 'bottom',
		  html: true,
		  timeout: 3000,
		  multiLine: false,
		  actions: [
			{				  
			  noCaps: true,
			  color: 'white',
			  handler: () => {
			  /* ... */
			  }
			}
		  ]
		})
	},
};

const DateTime = luxon.DateTime;
const LuxonSettings = luxon.Settings;

const componentsTest = {
    props : ['api'],
    created() {
        
    },
    computed: {
        
    },
    methods: {
        
    },
    template :`
    test 
    `
};

const componentsUserSkeleton = {
    props : ['rows'],
    template :`
    <q-list >
      <q-item v-for="items in rows" :key="items">
        <q-item-section avatar>
          <q-skeleton type="QAvatar" />
        </q-item-section>
        <q-item-section>
          <q-item-label>
            <q-skeleton type="text" />
          </q-item-label>
          <q-item-label caption>
            <q-skeleton type="text" />
          </q-item-label>
         </q-item-section>
      </q-item>
    </q-list>    
    `
};

const componentsParticipants = {
    props : ['api' ,'user_uuid' ,'language' ,'label' ,'main_user_type'],
    components: {   
        'components-loader' : componentsUserSkeleton,
    },
    data() {
        return {
            data :[],
            users : [],
            all_users :[],
            users_data :[],
            loading : false,
            loading_user : false,
            last_message_data :{},
            //whoistyping_data : [],
            whoistyping_data : {},
            document_id : ''
        }
    },
    created() {
        this.getParticipants();        
    },
    computed: {        
        getData(){
            return this.data;
        },
        getLastMessageData(){
            return this.last_message_data;
        },
        hasData(){
            if (Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
        hasUserData(){
            if (Object.keys(this.users_data).length > 0) {
                return true;
            }
            return false;
        },
        getShowistyping(){
            return this.whoistyping_data;
        },
    },
    watch: {
      
    },
    methods: {
        getParticipants(){            
            this.loading = true;
            const collectionRef = collection(firebaseDb, firebaseCollectionEnum.chats);
            const q = query(collectionRef, 
              where("participants", 'array-contains',  this.user_uuid),
              orderBy('lastUpdated', 'desc'),
              limit(firebaseCollectionEnum.limit)        
            );          
            const SnapParticipants = onSnapshot(q, (snapshot) => {        
              this.data = []; this.users = []; this.all_users = [];
              this.loading = false;
              snapshot.forEach((doc) => {                          
                let data = doc.data();                  
                let isTyping = data.isTyping || null;                
                let participants = data.participants || null;                
                if(participants){               
                            
                    if (Object.keys(participants).length > 0) {  
                      Object.entries(participants).forEach(([key, items]) => {
                          this.all_users.push(items);
                      });                    
                    }                    

                    let resp_participants =  participants.filter(i=>!i.includes(this.user_uuid));                          
                    let user_uuid = resp_participants[0] ? resp_participants[0]: null;

                    let matchedInfo = null;
                    let from_info = data.from_info || null;
                    let to_info = data.to_info || null;
                    
                    if(from_info){
                      if (from_info.client_uuid === this.user_uuid) {
                        matchedInfo = to_info;
                      } else if (to_info.client_uuid === this.user_uuid) {
                        matchedInfo = from_info;
                      }
                    }                    

                    this.users.push(user_uuid);                    
                    this.data.push({
                        'doc_id': doc.id,
                        'user_uuid': user_uuid,
                        'is_typing' : isTyping[resp_participants[0]] ? isTyping[resp_participants[0]] : false,
                        'orderID' : data.orderID || null,
                        'orderUuid' : data.orderUuid || null,
                        to_info: matchedInfo
                    });                    
                }                
              });        
              
              if (Object.keys(this.users).length > 0) {
                //this.getUser();   
                 this.getLastMessage();  
                 //this.getWhoIsTyping();
              }

            }, (error) => {
                this.loading = false;
                console.log('Error fetching chat documents:', error);
            });
        },
        getUser(){           
            this.loading_user = true;
            axios({
              method: 'post',
              url: this.api+"/getUsers?language="+this.language,
              data : {              
                  main_user_type : this.main_user_type,      
                  users : this.users
              },
              }).then( result => {	 
                    if(result.data.code==1){                     
                      this.users_data = result.data.details;
                    } else {
                      this.users_data = [];
                    }                     
                    this.$emit('setUserdata',this.users_data);
              }).catch(error => {	
              //
              }).then(data => {			     
                  this.loading_user = false;				
            });          
        },
        async getLastMessage(){       
          try {   
            if (Object.keys(this.all_users).length > 0) {
              const batch = this.all_users.splice(0, 10);            
              const conversationsRef = collection(firebaseDb, firebaseCollectionEnum.chats);
              const querySnapshot = await getDocs(query(conversationsRef, where('participants', 'array-contains-any', batch)));
              querySnapshot.forEach(async (doc) => {              
                const conversationID = doc.id;             
                const messagesRef = collection(firebaseDb, firebaseCollectionEnum.chats, conversationID, 'messages');
                const messagesSnapshot = await getDocs(
                  query(messagesRef, 
                    where('senderID', 'in', batch),
                    orderBy('timestamp', 'desc'), 
                    limit(1)
                  )
                );
                messagesSnapshot.forEach((messageDoc) => {                
                    let results = messageDoc.data();                                  
                    let timestamp = results.timestamp.toDate().toISOString();                
                    //this.last_message_data[results.senderID] = {
                    this.last_message_data[conversationID] = {
                      message : results.message,
                      timestamp : timestamp,
                      time :  DateTime.fromISO(timestamp).toFormat("hh:mm a")
                  };
                });
              });
            }
          } catch (error) {          
              console.error('Error fetching last message:', error);
          }   
        },
        async getWhoIsTyping(){          
                                     
          if (Object.keys(this.all_users).length > 0) {
            const batch = this.all_users.splice(0, 10);      
            const q = query(collection(firebaseDb, firebaseCollectionEnum.chats), 
              where("participants", "array-contains-any", batch),
              orderBy('lastUpdated', 'desc'), 
              limit(firebaseCollectionEnum.limit)
            );

            const unsubscribe = onSnapshot(q, (querySnapshot) => {
              querySnapshot.forEach((doc) => {
                  let results = doc.data();
                  let data = results.isTyping || [];                  
                  if (Object.keys(data).length > 0) {
                    Object.entries(data).forEach(([key, items]) => {                       
                        this.whoistyping_data[key] = items;
                    });
                  }
              });        
            });
          }
        },
        isTyping(user_uuid){
          if (Object.keys(this.whoistyping_data).length > 0) {
             let istyping = this.whoistyping_data[user_uuid] || false;               
             return istyping;
          }
          return false;
        },
        onClickChat(doc_id){               
          this.document_id = doc_id;
          this.$emit('afterClickconversation',doc_id);
        },
    },
    template :`            
    <template v-if="loading">
       <components-loader :rows="10"></components-loader>
    </template> 
        
    <template v-if="hasData && !loading">
        <q-list>
          <template v-for="items in getData" :key="items">
            <q-item  clickable v-ripple @click="onClickChat(items.doc_id)" :active="items.doc_id==document_id" active-class="bg-mygrey text-grey-8 q-mb-sm" >        
              <q-item-section avatar>
                <q-avatar v-if="items.to_info">
                  <img :src="items.to_info.photo" />
                </q-avatar>
                <q-avatar v-else color="grey-5" text-color="white"> </q-avatar>
              </q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">
                  <template v-if="items.to_info">
                    {{ items.to_info.first_name }}
                  </template>
                </q-item-label>
                <q-item-label caption>
                  <template v-if="items.orderID">
                    {{label.order_number}} {{ items.orderID }}
                  </template>
                  <template v-else>
                    <template v-if="items.to_info">
                      {{ items.to_info.user_type }}
                    </template>
                  </template>
                </q-item-label>
                <q-item-label
                  caption
                  lines="2"
                  v-if="getLastMessageData[items.doc_id]"
                >
                  <template v-if="items.is_typing">
                    <span class="text-primary"> {{ label.is_typing }} ...</span>
                  </template>
                  <template v-else>
                    {{ getLastMessageData[items.doc_id].message }}
                  </template>
                </q-item-label>
              </q-item-section>
            </q-item>
          </template>
        </q-list>
      </template>
    `
};


const AudioPlayback = {
  name: "AudioPlayback",
  props: ["audio_path", "show_media", "layout", "uploading_status"],
  data() {
    return {
      wavesurfer: null,
      is_playback: false,
      duration: null,
      current_time: null,
      file_complete_loading: false,
      file_percent: null,
      file_error: false,
    };
  },    
  mounted() {
    this.initWave();
  },
  unmounted() {
    if (this.wavesurfer) {
      this.wavesurfer.destroy();
    }
  },
  computed: {
    getPlayTime() {
      if (!this.is_playback) {
        return this.duration;
      }
      return this.current_time;
    },
    formattedTime() {
      const minutes = Math.floor(this.timer / 60);
      const seconds = this.timer % 60;
      return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(
        2,
        "0"
      )}`;
    },
  },
  watch: {
    audio_path(newval, oldval) {
      if (!empty(newval)) {
        this.wavesurfer.load(newval);
        setTimeout(() => {
          //this.wavesurfer.seekTo(0);
        }, 100);
      }
    },
  },
  methods: {
    initWave(){
      try {
        this.wavesurfer = WaveSurfer.create({
          container: this.$refs.ref_waveform,
          waveColor: this.layout == 1 ? "#ffffff" : "#4F4A85",
          progressColor: this.layout == 1 ? "#ffffff" : "#383351",
          barWidth: 2,
          barGap: 1,
          barRadius: 1,
          height: 40,
        });

        if (this.audio_path) {            
          this.wavesurfer.load(this.audio_path);
          setTimeout(() => {
            //this.wavesurfer.seekTo(0);
          }, 100);
        }

        this.wavesurfer.on("load", (url) => {            
          this.file_complete_loading = false;
        });

        this.wavesurfer.on("error", (error) => {          
          this.file_complete_loading = true;
          this.file_error = true;
        });

        this.wavesurfer.on("loading", (percent) => {            
          this.file_percent = percent;
        });

        this.wavesurfer.on("ready", (duration) => {            
          this.duration = this.formatTime(duration);
          this.file_complete_loading = true;
        });
        this.wavesurfer.on("timeupdate", (currentTime) => {          
          this.current_time = this.formatTime(currentTime);
        });

        this.wavesurfer.on("play", () => {            
          this.is_playback = true;
        });
        this.wavesurfer.on("pause", () => {            
          this.is_playback = false;
        });
      } catch (error) {}
    },
    PlayMedia() {
      this.wavesurfer.playPause();
    },
    formatTime(seconds) {
      const minutes = Math.floor(seconds / 60);
      const remainingSeconds = Math.floor(seconds % 60);
      return `${minutes}:${remainingSeconds.toString().padStart(2, "0")}`;
    },
    //
  },
  template : `    
  <div class="hidden">
  <div>file_complete_loading=>{{ file_complete_loading }}</div>
  <div>file_percent=>{{ file_percent }}</div>
  <div>audio_path=>{{ audio_path }}</div>
</div>
<div class="relative-position">
  <div
    class="row items-center q-gutter-x-sm"
    :class="{ hidden: !show_media }"
  >
    <div class="col-2">
      <template v-if="!file_complete_loading">
        <q-spinner :color="layout == 1 ? 'white' : 'green'" size="2.5em" />
      </template>
      <template v-else>
        <template v-if="file_error">
          <q-btn
            flat
            icon="error_outline"
            :color="layout == 1 ? 'white' : 'dark'"
          ></q-btn>
        </template>
        <template v-else>
          <q-btn
            flat
            rounded
            :icon="
              is_playback
                ? 'pause'
                : 'play_arrow'
            "
            size="18px"
            @click="PlayMedia"
            dense
            :color="layout == 1 ? 'white' : 'dark'"
          ></q-btn>
        </template>
      </template>
    </div>
    <div class="col">
      <template v-if="file_error"> </template>        
      <div ref="ref_waveform" style="height: 40px"></div>
    </div>
    <div
      class="col-2"
      :class="{ 'text-white': layout == 1, 'text-dark': layout == 2 }"
    >
      {{ getPlayTime }}
    </div>
  </div>
</div>
  `
};

const componentsMessages = {
  props : ['api', 'user_uuid', 'conversation_id' ,'user_data' ,'label' ,'no_chat_image_url'],
  components: {   
    'AudioPlayback':AudioPlayback,  
  },
  watch: {   	  
    conversation_id(newval,oldval){        
        if(!empty(newval)){          
          this.getMessages(newval);
          this.getWhoIsTyping(newval);
          this.getParticipant(newval);
        } else {
          this.data = [];
          this.user_typing_data = [];
          this.chating_with_user_uuid = '';
        }
    },
  },
  data() {
    return {
      data :[],
      loading : false,
      user_typing_data : [],
      chating_with_user_uuid : '',
      to_info : []
    }
  },  
  computed: {
    getChatmessage(){
      return this.data;
    },
    hasMessage(){
      if (Object.keys(this.data).length > 0) {
        return true;
      }
      return false;
    },
    hasChatDocID(){
      if(!empty(this.chating_with_user_uuid)){
          return true;
      }
      return false;
    },
    hasUserData(){
      if (Object.keys(this.user_data).length > 0) {
        return true;
      }
      return false;
    },
    getUserData(){
      return this.user_data;
    },
    getUserTyping(){
      return this.user_typing_data;
    },
  },
  methods: {    
    getMessages(conversation_id){            
      this.loading = true;
      const chatDocRef = doc(firebaseDb, firebaseCollectionEnum.chats, conversation_id);        
      const messagesQuery = query(
         collection(chatDocRef, 'messages'),
         orderBy('serverTimestamp', 'asc'),
         limit(firebaseCollectionEnum.limit)
      );

      const SnapMessages = onSnapshot(messagesQuery, (querySnapshot) => {
        this.data = [];
        this.loading = false;
        querySnapshot.forEach((doc) => {        
            if (doc.exists()) {          
              const message = doc.data();              
              let timestamp = message.timestamp.toDate().toISOString();    
              this.data.push ({
                messageType: message.messageType,
                fileType : message.fileType,
                fileUrl : message.fileUrl,
                message : message.message,
                senderID : message.senderID,
                timestamp: timestamp,
                time :  DateTime.fromISO(timestamp).toFormat("ccc hh:mm a"),       
                photo : message.photo,         
                sender : message.sender,
              });                         
            } else {
               console.log('Conversation document does not exist');
            }            
        });        
        this.$emit('scrollTobottom');
      }, (error) => {
        this.loading = false;
        console.error('Error fetching messages:', error);
      });

    },    
    getWhoIsTyping(doc_id){          
      const chatDocRef = doc(firebaseDb, firebaseCollectionEnum.chats, doc_id);
      const SnapWhoistyping = onSnapshot(chatDocRef, (docSnapshot) => {
        if (docSnapshot.exists()) {
          let results = docSnapshot.data();
          this.user_typing_data = results.isTyping || [];    
          this.to_info = results.to_info || null;
        } else {              
          this.user_typing_data = []      
          this.to_info = null;
        }        
        this.$emit('scrollTobottom');
      }, (error) => {
        console.error('Error fetching chat document:', error);
      });          
    },
    async getParticipant(doc_id){             
        try {                
          const docRef = doc(firebaseDb, firebaseCollectionEnum.chats, doc_id);
          const dataSnapshot = await getDoc(docRef);        
          if (dataSnapshot.exists()) {
            const participants = dataSnapshot.data().participants;            
            const data = dataSnapshot.data();            
            let resp_participants =  participants.filter(i=>!i.includes(this.user_uuid));                  
            this.chating_with_user_uuid = resp_participants[0] ? resp_participants[0]: null;        
            
            let matchedInfo = null;
            let from_info = data.from_info || null;
            let to_info = data.to_info || null;
            if (from_info && from_info.client_uuid === this.user_uuid) {
              matchedInfo = to_info;
            } else if (to_info && to_info.client_uuid === this.user_uuid) {
              matchedInfo = from_info;
            }            
            this.$emit('setChattingwith',matchedInfo);
          } else {
            console.log('Conversation document does not exist');
            this.$emit('setChattingwith',null);
          }
        } catch (error) {
          console.error('Error getting participants:', error);          
        }
    },
  },
  template :`                     
     <q-inner-loading
        :showing="loading"
        color="primary"
        :label="label.please_wait"
        label-class="text-dark"
        label-style="font-size: 1em"
    >
    </q-inner-loading>    
              

    <template v-for="items in getChatmessage" :key="items">       

      <template v-if="items.messageType == 'audio'"> 
      
           <q-chat-message 
              :name="items.senderID == user_uuid ? label.you : items.sender"
              :avatar="items.photo"
              :stamp="items.time"
              :text-color="items.senderID == user_uuid ? 'white' : 'dark'"
              :bg-color="items.senderID == user_uuid ? 'blue' : 'grey-2'"
              :sent="items.senderID == user_uuid ? true : false"
            >
              <template #avatar>
                <q-avatar class="q-ml-sm">
                  <q-img
                    :src="items.photo"
                    spinner-size="sm"
                    spinner-color="primary"
                    style="height: 48px; max-width: 48px; min-width: 48px"
                    fit="cover"
                    loading="lazy"
                  ></q-img>
                </q-avatar>
              </template>
              <div style="min-width: 300px; max-width: 300px">
                <AudioPlayback
                  :audio_path="items.fileUrl"
                  :show_media="true"
                  :layout="items.senderID == user_uuid ? 1 : 0"
                  :uploading_status="upload_audio_loading"
                ></AudioPlayback>
              </div>
            </q-chat-message>

      </template>
      <template v-else>

      <q-chat-message
        :text-color="items.senderID==user_uuid?'white':'dark'"
        :bg-color="items.senderID==user_uuid?'primary':'mygrey'"
        :sent="items.senderID==user_uuid?true:false"  
        :stamp="items.time"
      >
        <template v-slot:name>
          <template v-if="items.sender">
             <template v-if="items.senderID==user_uuid">
                {{label.you}}
             </template>
             <template v-else>
                {{items.sender}}
             </template>             
          </template>
          <template v-else>
             {{label.uknown}}
          </template>          

        </template>
        <!--- slot-name -->
        
        <template v-slot:avatar>
            <template v-if="items.senderID!=user_uuid">
              <img
              v-if="items.photo"
                class="q-message-avatar q-message-avatar--received"
                :src="items.photo"
              >
              </img>
              <q-avatar v-else color="grey-5" text-color="white"> </q-avatar>
            </template>                
        </template>
        
        <div>
          <template v-if="items.message && items.fileUrl">
              <div>{{items.message}}</div>
              <q-img
              :src="items.fileUrl"
              spinner-size="sm"
              spinner-color="primary"
              style="height: 80px; min-width:80px;max-width:80px"
              >
              </q-img>  
          </template>
          <template v-else-if="items.fileUrl">              
              <q-img
              :src="items.fileUrl"
              spinner-size="sm"
              spinner-color="primary"
              style="height: 180px; min-width:300px;max-width:300px"
              >
              </q-img>           
          </template>
          <template v-else>
             {{items.message}}          
          </template>          
        </div>                
      </q-chat-message>         
      </template>

    </template>
    <!--- END MESSAGES -->

    
    <template v-if="!hasChatDocID && !loading">
       <div class="text-center q-mt-sm">       
          <q-img
          :src="no_chat_image_url"
          spinner-color="white"
          fit="fill"
          style="height: 120px; max-width: 130px"
         >
         </q-img>
         <h6 class="q-ma-none q-pt-md">{{label.no_chat_selected}}</h6>
       </div>
    </template>
    
        
    <!--- TYPING -->    
    <template v-if="getUserTyping">
       <template v-for="(items, userUUID) in getUserTyping" :key="items">
            <template v-if="items && userUUID!=user_uuid">  
                <q-chat-message                    
                :text-color="userUUID==user_uuid?'white':'dark'"
                :bg-color="userUUID==user_uuid?'primary':'mygrey'"
                :sent="userUUID==user_uuid?true:false"  
                >       
                  <template v-slot:name>
                    <template v-if="to_info">
                       {{ to_info.last_name }}
                    </template>
                    <template v-else >
                       {{ label.uknown }}
                    </template>
                     {{label.is_typing}}
                  </template>
                  <template v-slot:avatar>
                     <img
                      v-if="to_info"
                      class="q-message-avatar q-message-avatar--received"
                      :src="to_info.photo"
                     >
                     </img>
                     <q-avatar v-else color="grey-5" text-color="white"> </q-avatar>
                  </template>                  
                  <q-spinner-dots size="2rem" ></q-spinner-dots>
                </q-chat-message>
            </template>
       </template>
    </template>
  ` 
};


const AudioRecorder = {
  components: {   
    'AudioPlayback':AudioPlayback,  
  },
  data() {
    return {
      is_recording: false,
      audioPath: null,
      audioBase64: null,
      has_permission: false,
      timer: 0,
      interval: null,
      duration: null,
      current_time: null,
      is_playback: false,
      is_uploading: false,
      mimeType: null,
      testData: null,
      recordingStatus: null,
      wavesurfer : null,
      record : null,      
      record_progress : null,
      cancel_recording : false,
      maxRecordingDuration : 1 * 60 * 1000 // 1 minute in milliseconds (60,000 ms)
      //maxRecordingDuration : 10 * 1000
    }
  },
  mounted() {    
     this.initWaveform();  
  },
  computed: {
    showMedia() {
      if (!this.is_recording && this.hasAudio && !this.is_uploading) {
        return true;
      }
      return false;
    },
    formattedTime() {
      // const minutes = Math.floor(this.timer / 60);
      // const seconds = this.timer % 60;
      // return `${String(minutes).padStart(2, "0")}:${String(seconds).padStart(
      //   2,
      //   "0"
      // )}`;
      return this.record_progress?  this.record_progress : "00:00";
    },
    hasAudio() {
      if (!empty(this.audioBase64)) {
        return true;
      }
      return false;
    },
    getPlayTime() {
      if (!this.is_playback) {
        return this.duration;
      }
      return this.current_time;
    },
  },
  methods: {
    async initWaveform() {      
      
      if (this.wavesurfer) {
        this.wavesurfer.destroy()
      }
      
      this.is_uploading = false;

      this.wavesurfer = WaveSurfer.create({
        container: this.$refs.ref_mic,
        waveColor: "#4F4A85",
        progressColor: "#383351",
        barWidth: 2,
        barGap: 1,
        barRadius: 1,
        height: 40,
      });
            
      let scrollingWaveform = false
      let continuousWaveform = true

      this.record = this.wavesurfer.registerPlugin(
        RecordPlugin.create({
          renderRecordedAudio: false,
          scrollingWaveform,
          continuousWaveform,
          continuousWaveformDuration: 30, // optional
        }),
      )

      this.record.on('record-end', async  (blob) => {         
          this.mimeType = blob.type;
          this.audioPath = URL.createObjectURL(blob);
          this.audioBase64 = await this.blobToBase64(blob);                         
      });

      this.record.on('record-start', () => {
        console.log('Recording started...',this.maxRecordingDuration);
        setTimeout(() => {
          this.toogleRecording();
          console.log('Recording stopped automatically after 10 seconds.');
        }, this.maxRecordingDuration);
      });

      this.record.on('record-progress', (time) => {        
        const formattedTime = [
          Math.floor((time % 3600000) / 60000), // minutes
          Math.floor((time % 60000) / 1000), // seconds
        ]
          .map((v) => (v < 10 ? '0' + v : v))
          .join(':')
        this.record_progress = formattedTime
      })

      setTimeout(() => {              
        this.toogleRecording();
      }, 1000); 

    },
    blobToBase64(blob){
      return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onloadend = () => {
          const base64String = reader.result.split(',')[1]; // Extract Base64 part
          resolve(base64String);
        };
        reader.onerror = (error) => reject(error);
        reader.readAsDataURL(blob); // Read the Blob as Data URL
      });
    },
    toogleRecording(){          
      
      this.is_uploading = false;

      if (this.record.isRecording() || this.record.isPaused()) {
          console.log("stop recording");
          this.is_recording = false;
          this.record.stopRecording()       
        return
      }

      const deviceId = '';
      this.record.startRecording({ deviceId }).then(() => {
         console.log("start recording");
         this.is_recording = true;
      });
    },
    PauseResumeRecording(){
       console.log("PauseResumeRecording", this.is_recording);
       if(this.record.isRecording() ){
           console.log("pause recording")      
           this.is_recording = false;
           this.record.pauseRecording(); 
       } else if ( this.record.isPaused() ) {
           console.log("resume recording")    
           this.is_recording = true;
           this.record.resumeRecording();      
       }
    },    
    stopRecording(){      
      console.log("stopRecording");
      if (this.record.isRecording() || this.record.isPaused()) {         
         this.is_recording = false;         
         this.record.stopRecording();             
      }      
      this.$emit("cancelRecording");
    },
    async UploadAudio() {
      console.log("UploadAudio");
      if (this.record.isRecording() || this.record.isPaused()) {          
          this.is_recording = false;         
          this.record.stopRecording();             
      }      
      this.is_uploading = true;      

      setTimeout(() => {              
        this.$emit(
          "startUpload",
          this.audioPath,
          this.audioBase64,
          this.mimeType
        );
      }, 500); // 2.5 sec delay      
    },
    //
  },
  template : `    
  
  <AudioPlayback
    :audio_path="audioPath"
    :show_media="showMedia"
    layout="2"
  ></AudioPlayback>

  <div
    class="text-center text-subtitle1 flex flex-center"
    style="height: 50px"
    :class="{ hidden: showMedia }"
  >    
     <div class="row full-width items-center">
       <div class="col-2">
           {{ formattedTime }}
       </div>
       <div class="col">
          <div ref="ref_mic" style="height: 40px;" class="full-width"></div>
       </div>
     </div>
  </div>

  <div class="row items-center text-center">
    <div class="col-2 borderx">
      <q-btn
        icon="delete_outline"
        size="md"
        flat
        round
        @click="stopRecording"
      ></q-btn>
    </div>
    <div class="col borderx">
      <q-btn
        :icon="is_recording ? 'pause' : 'mic'"
        color="red"
        size="md"
        flat
        round
        @click="toogleRecording"
      ></q-btn>
    </div>
    <div class="col-2 borderx">
      <q-btn
        color="green"
        icon="send"
        no-caps
        size="md"
        class="text-weight-bold"
        round
        unelevated
        @click="UploadAudio"
      >
      </q-btn>
    </div>
  </div>
  `
};

const componentsChat = {
  props : ['api', 'api_upload', 'user_uuid', 'conversation_id' ,'user_data' ,'label' ,'max_file_size'],
  components: {   
    'AudioRecorder':AudioRecorder,  
  },
  data() {
    return {
      message : '',
      files : {},
      file_url : '',
      file_type : '',
      upload_loading : false,
      loading : false,
      is_typing : false,
      is_recording : false,
      audioPath: null,
      audioBase64: null,
      mimeType: null,
      upload_response: null,
      upload_audio_loading: false,
    }
  },  
  computed: {
    hasConversation(){
       if(!empty(this.conversation_id)){
          return true;
       }
       return false;
    },
    hasMessage(){
      if(!empty(this.message)){         
        return true;
     }
     if (Object.keys(this.files).length > 0) {
         return true;
     }       
     return false;
    },    
  },
  watch: {
    conversation_id(newval,oldval){      
      this.message = '';
    },
    is_typing(newval,oldval){      
      if(newval){         
         this.UpdateWhoistyping(true);
      } else {        
         this.UpdateWhoistyping(false);
      }
    },
    message(newval,oldval){
      if (!this.is_typing) {        
        setTimeout(() => {              
          this.is_typing = false;
        }, 1000); // 2.5 sec delay
      }
      this.is_typing = true;
    }
  },
  methods: {
    onSubmit(){      
      if (!this.hasMessage) {        
        this.checkPermissionAndStartAudio();
        return;
      }

      if (Object.keys(this.files).length > 0) {                    
          this.$refs.uploader.upload();
      } else {          
          this.saveChatMessage();
      }      
    },
    async checkPermissionAndStartAudio() {      
      this.is_recording = true;
    },
    cancelRecording(){
      this.is_recording = false;
    },
    async saveChatMessage(){
      this.loading = true;
      const messagesRef = collection(firebaseDb, firebaseCollectionEnum.chats, this.conversation_id, 'messages');
      try {
        const fromInfo = JSON.parse(from_info);        
        let data = {
          message: this.message,
          senderID: this.user_uuid,
          timestamp: Timestamp.now(),
          serverTimestamp: serverTimestamp(),
          fileUrl : this.file_url,
          fileType : this.file_type,
          photo : fromInfo.photo,
          sender : fromInfo.first_name,
        };                
        await addDoc(messagesRef, data);                
        this.loading = false;       
        this.documentLastUpdate(this.conversation_id);
        this.resetChat();
        this.$emit('afterAddmessage');
      } catch (error) {
        console.error('Error adding message to the conversation:', error);
        quasarComponents.notify('red-5', error, 'error_outline'); 
      }            
    },
    async documentLastUpdate(doc_id){
      try{
        const chatRef = doc(firebaseDb, firebaseCollectionEnum.chats, doc_id);
        await updateDoc(chatRef, {
          lastUpdated: serverTimestamp()
        });        
      } catch (error) {        
        quasarComponents.notify('red-5', error, 'error_outline'); 
      }
    },
    resetChat(){
      this.message = '';
      this.file_url = '';
      this.file_type = '';
      this.files = {};
      this.$refs.uploader.reset();
    },
    pickFiles(){
      this.$refs.uploader.pickFiles();
    },
    onRejectedFiles(data){      
      quasarComponents.notify('red-5', "Invalid file type", 'error_outline'); 
    },
    afterAddedFiles(data){             
      Object.entries(data).forEach(([key, items]) => {                
        this.files[items.name] = {
          name : items.name
        };
      });
    },
    afterRemoveFiles(data){      
      Object.entries(data).forEach(([key, items]) => {                        
        delete this.files[items.name];
      });
    },
    onUploadingFiles(data){      
      this.upload_loading = true;
    },    
    afterUploaded(data){      
      if(data.xhr.status==200){
        let result = JSON.parse(data.xhr.response);        
        let code = result.code || false;
        let details = result.details || [];
        let message = result.msg || '';        
        if(code==1){
            this.file_url = details.file_url;
            this.file_type = details.file_type;            
            this.saveChatMessage();
        } else {
            quasarComponents.notify('red-5', message, 'error_outline'); 
            this.$refs.uploader.reset();
        }
      } else {
          quasarComponents.notify('red-5', "Error uploading files", 'error_outline'); 
          this.$refs.uploader.reset();
      }
    },      
    afterFinishUpload(){      
      this.upload_loading = false;
    },
    showEmoji(){      
      document.querySelector('emoji-picker').addEventListener('emoji-click', e => {         
         insertText(document.querySelector('textarea'), e.detail.unicode)
      });
    },    
    async UpdateWhoistyping(data){
      //console.log('UpdateWhoistyping=>'+data);      
      try {        
        const docRef = doc(firebaseDb, firebaseCollectionEnum.chats, this.conversation_id);
        await updateDoc(docRef, {
          [`isTyping.${this.user_uuid}`]: data
        });        
        //console.log('Typing status updated successfully.');
      } catch (error) {
        console.error('Error updating typing status:', error);
      }
    },
    async startUpload(audioPath, audioBase64, mimeType) {      
      this.is_recording = false;
      this.audioPath = audioPath;
      this.audioBase64 = audioBase64;
      this.mimeType = mimeType;          
      try {

        if (!this.audioBase64) {
          console.error("No audio file to upload");
          return;
        }

        this.upload_audio_loading = true;

        const data = {
          audioBase64: this.audioBase64,
          fileName: `recording_${Date.now()}.webm`,
        };

        const response = await axios.post(
          `${ajaxurl}/uploadaudio`,
          data,
          {
            headers: {
              "Content-Type": "application/json",
            },
          }
        );

        console.log("response",response);

        this.upload_audio_loading = false;
        this.upload_response = response.data;

        if (response.data.code == 1) {
          this.saveChatAudio();
        } else {
          quasarComponents.notify('red-5', response.data.msg, 'error_outline'); 
        }

      } catch (error) {
        this.upload_response = "Failed to upload audio =>" + error.message;
      }
    },
    async saveChatAudio(){      
      try {
         this.loading = true;
         const messagesRef = collection(firebaseDb, firebaseCollectionEnum.chats, this.conversation_id, 'messages');
         const fromInfo = JSON.parse(from_info);        
         console.log("fromInfo",fromInfo);
         let data = {
           messageType: "audio",
           message: this.message,
           senderID: this.user_uuid,
           timestamp: Timestamp.now(),
           serverTimestamp: serverTimestamp(),
           fileUrl: this.upload_response.details.file_url,
           fileType: this.mimeType,
           photo : fromInfo.photo,
           senderID : fromInfo.client_uuid,
           sender : fromInfo.first_name,
         };                
         await addDoc(messagesRef, data);                
         this.loading = false;       
         this.documentLastUpdate(this.conversation_id);
         this.resetChat();
         this.$emit('afterAddmessage');         
      } catch (error) {
       console.error("Error adding message to the conversation:", error);
     }
    },
    //
  },
  template :`   
  

  <div v-if="hasConversation" class="full-width border-grey q-pa-sm radius10">
    
    <q-inner-loading
      :showing="upload_loading"
      color="primary"
      :label="label.please_wait"
      label-class="text-dark"
      label-style="font-size: 1em"
    >
    </q-inner-loading>

    <template v-if="is_recording">
      <div class="q-pa-sm">        
        <AudioRecorder
          ref="ref_audio"
          @cancel-recording="cancelRecording"
          @start-upload="startUpload"
        ></AudioRecorder>
      </div>
    </template>
    <template v-else>

    <q-uploader            
      :url="api_upload"            
      multiple
      ref="uploader"
      flat                  
      accept=".jpg, image/*"
      :max-total-size="max_file_size"
      field-name="file"            
      @added="afterAddedFiles"        
      @removed="afterRemoveFiles"   
      @rejected="onRejectedFiles"                        
      @uploading="onUploadingFiles"   
      @uploaded="afterUploaded" 
      @finish="afterFinishUpload"
      >
      <template v-slot:header="scope">         
        <q-uploader-add-trigger ></q-uploader-add-trigger>
      </template>
      <template v-slot:list="scope">
          <div class="flex justify-start q-col-gutter-x-md">
            <template v-for="file in scope.files" :key="file.__key">
              <div class="relative-position">
                  <img :src="file.__img.src" style="max-width: 60px; height:60px;" class="radius10"></img>
                  <div class="absolute-right" style="margin-right: -10px;margin-top: -5px;">
                    <q-btn 
                    unelevated 
                    round color="primary" 
                    icon="close" 
                    size="xs"
                    @click="scope.removeFile(file)"
                    ></q-btn>
                  </div>
              </div>            
            </template>
          </div>
      </template>
  </q-uploader>

    <q-input color="primary" 
    v-model="message"
    :label="label.your_message"      
    ref="message"      
    autogrow
    borderless             
    >
      <template v-slot:append>
        <div class="q-gutter-sm">

          <q-btn unelevated round color="mygrey" text-color="grey"  @click="pickFiles"  >
            <q-icon name="attach_file" class="rotate-45"></q-icon>
          </q-btn>

          <q-btn unelevated round color="mygrey" text-color="grey"  >
            <q-icon name="emoji_emotions" ></q-icon>
            <q-popup-proxy @show="showEmoji">
            <emoji-picker ref="emoji"></emoji-picker>
            </q-popup-proxy>
          </q-btn>               
       
          
          <q-btn    
          @click="onSubmit"                        
          :loading="loading"
          color="green" 
          :icon="hasMessage ? 'send' : 'mic'"
          size="md" 
          round
          unelevated
          >
          </q-btn>                  

        </div>
      </template>
    </q-input>        
    </template>
  </div>           
  ` 
};

const componentsSearchChat = {
  props : ['api' ,'label' ,'language' ,'search_type'],
  data() {
    return {
      search :'',
      is_search : false,        
      awaitingSearch : false,      
    }
  },
  computed: {    
    hasSearch(){
      if(!empty(this.search)){
        return true;
      }
      return false;
    },
  },
  watch: {
    awaitingSearch(newval,oldval){      
      this.$emit('onSearchloading',newval);
    },
    is_search(newval,oldval){
      this.$emit('onSearchchat',newval);
    },    
    search(newsearch,oldsearch){
      this.$emit('setSearchtext',newsearch);

      if (!this.awaitingSearch) {
        if(empty(newsearch)){
            return false;
        }
        setTimeout(() => {
            axios({
            method: 'post',
            url: this.api+"/searchChats?language="+this.language,
            data : {                    
                search : this.search,
                search_type : this.search_type
            },
            }).then( result => {	 
                if(result.data.code==1){                    
                   this.$emit('onSearchresults',result.data.details);
                } else {
                   this.$emit('onSearchresults',[]);
                }
            }).catch(error => {	
            //
            }).then(data => {		
              this.awaitingSearch = false;                		
          });          
           
        }, 1000); // 1 sec delay
        this.awaitingSearch = true;
      }
    },
  },
  methods: {
    closeSearch(){
      this.is_search = false;
      this.search = '';
    },
  },
  template :`       
    <q-input color="primary" outlined v-model="search" :label="label.search_chat" class="q-mb-md"
    @click="is_search=true"    
    >            
      <template v-slot:prepend>              
        <q-btn v-if="is_search" @click="closeSearch" flat round color="primary" icon="keyboard_backspace" ></q-btn>
      </template>
      <template v-slot:append>
        <q-icon v-if="!is_search" name="search" size="md" ></q-icon>
        <q-btn v-if="hasSearch" @click="search=''" flat round color="primary" icon="highlight_off" ></q-btn>
      </template>
    </q-input>
  `
};

const deleteMessagesInConversation = async (conversationID) => {
  try {
    const messagesRef = collection(firebaseDb, firebaseCollectionEnum.chats, conversationID, 'messages');
    const querySnapshot = await getDocs(messagesRef);

    querySnapshot.forEach(async (doc) => {
      await deleteDoc(doc.ref);
    });

    console.log('All messages in the conversation deleted successfully.');
  } catch (error) {
    console.error('Error deleting messages in conversation:', error);
  }
};

const app_chat = Vue.createApp({   
    components: {   
        'components-participants':componentsParticipants,
        'components-messages' : componentsMessages,
        'components-chat' : componentsChat,
        'components-search-chat': componentsSearchChat
    },
    data() {
        return { 
            snap_shot : undefined,
            data :[],
            drawer : false,            
            message :'',
            conversation_id : '',
            user_data : [],
            user_typing_data :[],
            chatting_with_uuid : '',
            chatting_to : null,
            file: null, 
            image: null,
            show_uploader : false,         
            search_chat : false ,
            search_loading : false ,
            search_data : [],
            search_text : '',
            to_info : null,
            some_words : null
        };
    },      
    computed: {    
      hasConversation(){
         if(!empty(this.conversation_id)){
            return true;
         }
         return false;
      },
      hasMessage(){
        if(!empty(this.message)){
          return true;
        }
        return false;
      },
      hasSearch(){
        if(!empty(this.search_text)){
          return true;
        }
        return false;
      },
      hasSearchData(){
        if (Object.keys(this.search_data).length > 0) {
            return true;
        }
        return false;
      },
      getSearchData(){
        return this.search_data;
      },
    },
    mounted() {       
       if ((typeof some_words !== "undefined") && (some_words !== null)) {	 
          this.some_words = JSON.parse(some_words);
       }       
    },
    methods: {               
      setUserdata(data){        
        this.user_data = data;
      },
      setWhoistyping(data){
        this.user_typing_data = data;
      },
      afterClickconversation(doc_id){        
        this.conversation_id = doc_id;
      },
      setChattingwith(data){               
        //this.chatting_with_uuid = data;      
        this.chatting_to = data;        
        this.scrollTobottom();
      },            
      afterAddmessage(){        
        this.scrollTobottom();        
        this.notifyUser();
      },
      scrollTobottom(){                   
        setTimeout(()=>{ 	
          if ((typeof this.$refs.scroll_ref !== "undefined") && (this.$refs.scroll_ref !== null)) {	 
            let value = parseInt(this.$refs.scroll_ref.getScroll().verticalSize) + 100;                                  
            this.$refs.scroll_ref.setScrollPosition('vertical',value);
          }
        }, 500);                
      },      
      onSearchchat(data){
        this.search_chat = data;
      },
      onSearchresults(data){        
        this.search_data = data;
      },
      onSearchloading(data){
        this.search_loading = data;
      },
      setSearchtext(data){
        this.search_text = data;
      },
      async chatToUser(user_uuid,to_info){

        try {

          this.to_info = {
            client_uuid : to_info.client_uuid,
            first_name : to_info.first_name,
            last_name : to_info.last_name,
            photo : to_info.photo_url,
            user_type : to_info.user_type_raw,
          };          
          
          const collectionRef = collection(firebaseDb, firebaseCollectionEnum.chats);
          const q = query(collectionRef, 
                where("participants", 'array-contains',  user_uuid),
                orderBy('lastUpdated', 'desc'),
                limit(1)        
          );               
          
          let current_doc_id = '';
          const querySnapshot = await getDocs(q);
          querySnapshot.forEach((doc) => {                                  
              let data = doc.data();                  
              let participants = data.participants || null;              
              if(participants.includes(main_user_uuid)===true){	                     
                 current_doc_id = doc.id;
              }              
          });      

          console.log('main_user_uuid=>'+main_user_uuid);
          console.log('chatToUser=>'+user_uuid);
          console.log("current_doc_id=>"+current_doc_id);

          if(!empty(current_doc_id)){          
            this.loadConversation(current_doc_id,user_uuid);
          } else {          
            this.createConversation(user_uuid);
          }

        } catch (error) {          
          quasarComponents.notify('red-5', error, 'error_outline'); 
        }   

      },
      async createConversation(user_uuid){
        
        try {
          const newConversationRef = await addDoc(collection(firebaseDb, firebaseCollectionEnum.chats), {        
            lastUpdated: serverTimestamp()
          });
          const chatId = newConversationRef.id;   
          const chatDocRef = doc(firebaseDb, firebaseCollectionEnum.chats, chatId);       
          
          let data = {
            lastUpdated: serverTimestamp(),
            dateCreated :serverTimestamp(),
            participants:[ user_uuid, main_user_uuid ],
            isTyping:{
              [`${user_uuid}`]: false,
              [`${main_user_uuid}`]: false,
            },
            from_info: JSON.parse(from_info),
            to_info : this.to_info
          };       
          
          setDoc(chatDocRef, data )
            .then(() => {            
              this.loadConversation(chatId);
            })
            .catch((error) => {            
              quasarComponents.notify('red-5', error, 'error_outline'); 
          });              
        } catch (error) {          
          quasarComponents.notify('red-5', error, 'error_outline'); 
        }   

      },
      loadConversation(doc_id){              
        this.$refs.search_chat.closeSearch();        
        this.conversation_id = doc_id;        
        setTimeout(() => {              
            if ((typeof this.$refs.participants !== "undefined") && (this.$refs.participants !== null)) {	 
               this.$refs.participants.document_id = doc_id;
            }
        }, 600); 
      },
      deleteChatConfirm(){
        this.$q.dialog({
          title: this.some_words.confirm,
          message: this.some_words.are_you_sure,
          cancel: true,
          persistent: true,
          ok: {
            unelevated: false,
            flat : true,
            label: this.some_words.yes,
            'no-caps': true,            
          },
          cancel: {
            unelevated: true,
            flat : true,
            label: this.some_words.cancel,
            'no-caps': true,
            'text-color': 'grey', 
          }
        }).onOk(() => {          
          this.deleteChat();
        }).onOk(() => {
          // console.log('>>>> second OK catcher')
        }).onCancel(() => {
          // console.log('>>>> Cancel')
        }).onDismiss(() => {
          // console.log('I am triggered on both OK and Cancel')
        })
      },
      async deleteChat(){        
        try {
          const conversationRef = doc(firebaseDb, firebaseCollectionEnum.chats, this.conversation_id);
          await deleteDoc(conversationRef);          

          let conversation_id = this.conversation_id;
          this.conversation_id = '';          

          // DELETE MESSAGES          
          deleteMessagesInConversation(conversation_id);
          
        } catch (error) {          
          quasarComponents.notify('red-5', error, 'error_outline'); 
        }    
      },
      notifyUser(){
          const fromInfo = JSON.parse(from_info); 
          axios({
            method: 'post',
            url: chat_api+"chatapi/notifyUser?language="+ chat_language,
            data : {                              
                user_uuid : this.chatting_to.client_uuid,
                from_user_uuid : main_user_uuid,
                first_name : fromInfo?fromInfo.first_name:'',
                last_name : fromInfo?fromInfo.last_name:'',
                avatar : fromInfo?fromInfo.photo:'',
                conversation_id : this.conversation_id
            },
            }).then( result => {	                   
            }).catch(error => {	
            //
            }).then(data => {			                     
          });          
      },
    },
});


app_chat.use(Quasar,{
    config : {
		notify: {},
		loadingBar: { skipHijack: true },
        loading : {}
	}
});
app_chat.mount('#app-chat')