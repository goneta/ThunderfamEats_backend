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

const app_pos = Vue.createApp({   
    data() {
        return {
            page_ready : false,
			drawer_left :true,
            drawer : true,
            category : 'All Tables',
            transaction_type : 'Dine-in',
            modal : false,
			miniState : true,
			tab : 'ongoing'
        }
    },
    mounted() {        
        this.page_ready=true;
    },
});

app_pos.use(Quasar,{
    config : {
        screen :{},
		notify: {},
		loadingBar: { skipHijack: true },
        loading : {}
	}
});
app_pos.mount('#app-tableside')