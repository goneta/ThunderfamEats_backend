

const api_location = site_url + "/apilocations";
const StoreName = 'store';

const ComponentsState = {
    props: ['country_id','label'],	
	emits: ['update:state_id'],
    data() {
        return {
            loading : false,
            state_id : null,
            state_list : []
        }
    },
    mounted() {
        this.fetchState();
    },
    methods: {
        OnselectState(value){            
            this.$emit('update:state_id', value);
            this.$emit('afterSelectstate',value);
        },
        fetchState(){		
			this.loading = true;		
			axios({
				method: 'POST',
				url: api_location+"/fetchState" ,
				data : "country_id="+this.country_id,				
			  }).then( response => {	 
				   if(response.data.code==1){		
					   this.state_list = response.data.details.data;
				   } else {
					  this.state_list = []
				   }				  					   		    						   								   
			  }).catch(error => {	
				 //
			  }).then(data => {			 
				this.loading = false;    				
			});			
		},		
    },
    template: `                
     <el-select v-model="state_id" :loading="loading" filterable remote reserve-keyword :placeholder="label.state" :no-match-text="label.no_data" remote-show-suffix :loading="loading_state" size="large" class="w-100" :automatic-dropdown="true" @change="OnselectState"><el-option v-for="item in state_list" :key="item.value" :label="item.label" :value="item.value"></el-select>
    `
};

const ComponentsCity = {    
    props : ['location_searchtype','label'],
	emits: ['update:city_id'],
    data() {
        return {
            loading : false,
            city_id : null,
            city_list : [],
        }
    },
    mounted() {        
        if(this.location_searchtype==1){            
            this.fetchCity('');
        }
    },
    methods: {
        fetchCity(state_id){		            
			this.loading = true;		
			axios({
				method: 'POST',
				url: api_location+"/fetchCity" ,
				data : "state_id="+ state_id
			  }).then( response => {	 
				   if(response.data.code==1){		
					   this.city_list = response.data.details.data;
				   } else {
					  this.city_list = []
				   }				  					   		    						   								   
			  }).catch(error => {	
				 //
			  }).then(data => {			 
				this.loading = false;    				
			});			
		},		
        OnselectCity(value){            
            this.$emit('update:city_id', value);
            this.$emit('afterSelectcity',value);
        },
    },
    template: `           
       <el-select v-model="city_id" :loading="loading" filterable remote reserve-keyword :placeholder="label.city" :no-match-text="label.no_data" remote-show-suffix :loading="loading" size="large" class="w-100" :automatic-dropdown="true" @change="OnselectCity"><el-option v-for="item in city_list" :key="item.value" :label="item.label" :value="item.value"></el-select>
    `
};

const ComponentsArea = {
    props : ['label'],
    emits: ['update:area_id'],
    data() {
        return {
            loading : false,
            area_id : null,
            area_list : []
        }
    },
    methods: {
        fetchArea(city_id){		            
			this.loading = true;		
			axios({
				method: 'POST',
				url: api_location+"/fetchArea" ,
				data : "city_id="+ city_id,				
			  }).then( response => {	 
				   if(response.data.code==1){		
					   this.area_list = response.data.details.data;
				   } else {
					  this.area_list = []
				   }				  					   		    						   								   
			  }).catch(error => {	
				 //
			  }).then(data => {			 
				this.loading = false;    				
			});			
		},		
        OnselectArea(value){            
            this.$emit('update:area_id', value);
            this.$emit('afterSelectarea',value);
        },        
    },
     template: `        
       <el-select
        v-model="area_id"        
        :loading="loading"
        filterable
        remote
        reserve-keyword
        :placeholder="label.area"
        :no-match-text="label.no_data"
        remote-show-suffix                
        size="large"
        class="w-100"
        :automatic-dropdown="true"       
        @change="OnselectArea"  
        >
        <el-option
            v-for="item in area_list"
            :key="item.value"
            :label="item.label"
            :value="item.value"
        />
        </el-select>
     `
};

const ComponentsPostalcode = {
    props : ['label'],
    emits: ['update:postal_code'],
    data() {
        return {
            loading : false,
            postal_code : null,
            postal_code_list : []
        }
    },
    mounted() {
        this.fetchPostal();
    },
    methods: {
        fetchPostal(){		            
			this.loading = true;		
			axios({
				method: 'POST',
				url: api_location+"/fetchPostal" ,				
			  }).then( response => {	 
				   if(response.data.code==1){		
					   this.postal_code_list = response.data.details.data;
				   } else {
					  this.postal_code_list = []
				   }				  					   		    						   								   
			  }).catch(error => {	
				 //
			  }).then(data => {			 
				this.loading = false;    				
			});			
		},		
        OnselectPostalcode(value){            
            this.$emit('update:postal_code', value);
        },        
    },
    template: `
     <el-select
        v-model="postal_code"        
        :loading="loading"
        filterable
        remote
        reserve-keyword
        :placeholder="label.postal_code"
        :no-match-text="label.no_data"
        remote-show-suffix                
        size="large"
        class="w-100"
        :automatic-dropdown="true"       
        @change="OnselectPostalcode"  
        >
        <el-option
            v-for="item in postal_code_list"
            :key="item.value"
            :label="item.label"
            :value="item.value"
        />
        </el-select>
    `
};

var getCookie = function(cname, cvalue, exdays){
    let name = cname + "=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
      let c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
};

var setCookie = function(cname, cvalue, exdays){
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
  }

var empty = function(data){	
	if (typeof data === "undefined" || data==null || data=="" || data=="null" || data=="undefined" ) {	
		return true;
	}
	return false;
};

var setStorage = function(key,value)
{
	localStorage.setItem(key,value);
}

var getStorage = function(key)
{
	return localStorage.getItem(key);
}

var removeStorage = function(key)
{
	localStorage.removeItem(key);
}

var changeUrlParameters = function(key,value){
    const url = new URL(window.location.href);
    const params = new URLSearchParams(url.search);
    params.set(key, value);
    window.history.replaceState({}, '', `${url.pathname}?${params.toString()}`);
}

function setInLocalStorageStore(storeKey, key, value) {    
    let store = JSON.parse(localStorage.getItem(storeKey));
    if (!store) {
      store = {};
    }  
    store[key] = value;  
    localStorage.setItem(storeKey, JSON.stringify(store));
}

function getFromLocalStorageStore(storeKey, key) {
    const store = JSON.parse(localStorage.getItem(storeKey));
    return store ? store[key] : undefined;
}  

function removeFromLocalStorageStore(storeKey, key) {    
    let store = JSON.parse(localStorage.getItem(storeKey));
    if (!store) {
      return;
    }
    delete store[key];
    localStorage.setItem(storeKey, JSON.stringify(store));
}
  
var getLocation = function()
{	
    const city_id = getFromLocalStorageStore(StoreName,"selectedCityId");
    const area_id = getFromLocalStorageStore(StoreName,"selectedAreaId");
    const state_id = getFromLocalStorageStore(StoreName,"selectedStateId");
    const postal_id = getFromLocalStorageStore(StoreName,"selectedPostalId");
    return { city_id, area_id , state_id , postal_id};
}

const componentsMaps = {
    props : ['keys','provider','zoom','center','markers'],
    data() {
        return {
            cmapsMarker : [],            
            latLng : [], 
			cmaps: undefined,    
			bounds : undefined,       
			loading : false,
        }
    },
    mounted() {        
        this.renderMap();
    },
    watch: {
        markers(newval, oldval) {          
          this.renderMap();
        },        
    },
    methods: {
        renderMap(){
            try {						
                switch (this.provider) {						
                    case "google.maps":
                        this.bounds = new window.google.maps.LatLngBounds();                        
                        if (typeof this.cmaps !== "undefined" && this.cmaps !== null && Object.keys(this.cmapsMarker).length>0 ) {                                                    
                            this.moveAllMarker();
                        } else {
                            this.cmaps = new window.google.maps.Map(this.$refs.cmaps, {
                                center: {
                                  lat: parseFloat(this.center.lat),
                                  lng: parseFloat(this.center.lng),
                                },
                                zoom: parseInt(this.zoom),
                                disableDefaultUI: true ,								                
                            });

                            Object.entries(this.markers).forEach(([key, items]) => {
                                this.addMarker(
                                    {
                                      position: {
                                        lat: parseFloat(items.lat),
                                        lng: parseFloat(items.lng),
                                      },
                                      map: this.cmaps,                                  
                                      draggable: items.draggable==1?true:false,
                                      label: items.label,
                                    },
                                    items.id,
                                    items.draggable
                                );    
                            });

                        }                        
                        break;

					case "mapbox":						
					   if (typeof this.cmaps !== "undefined" && this.cmaps !== null && Object.keys(this.cmapsMarker).length>0 ) {                            
						   this.moveAllMarker();
					   } else {
							mapboxgl.accessToken = this.keys;
							this.bounds = new mapboxgl.LngLatBounds();
							this.cmaps = new mapboxgl.Map({
								container: this.$refs.cmaps,
								style: 'mapbox://styles/mapbox/streets-v12',
								center: [ parseFloat(this.center.lng), parseFloat(this.center.lat)],
								zoom: 14
							});			

							this.cmaps.on('error', (response) => {
								dump(response.error.message)
							});
							                            
							Object.entries(this.markers).forEach(([key, items]) => {                                
								this.addMarker(
									{
									position: {
										lat: parseFloat(items.lat),
										lng: parseFloat(items.lng),
									},
									map: this.cmaps,                                  
									draggable: items.draggable==1?true:false,								  								  
									},
									items.id,
									items.draggable
								);    
							});
														
							if (Object.keys(this.markers).length > 1) {								
								this.FitBounds();
							} else {			
								if(this.markers[0]){
									this.setCenter(this.markers[0].lat, this.markers[0].lng);								
								}								
							}
					    }
						break;					
                } 
            } catch (err) {
               console.error(err);
            } 
        },
        async addMarker(properties, index, draggable) {              
            try {

                switch (this.provider) {
                    case "google.maps":
                        this.cmapsMarker[index] = new window.google.maps.Marker(properties);

                        this.cmaps.panTo(
                            new window.google.maps.LatLng(
                              properties.position.lat,
                              properties.position.lng
                            )
                        );
                        this.bounds.extend(this.cmapsMarker[index].position);

                        if (draggable === true) {
							google.maps.event.addListener(this.cmaps, 'drag',(data)=>{								
								this.$emit("dragMarker", true);													
								let new_position = new google.maps.LatLng(this.cmaps.getCenter().lat(),this.cmaps.getCenter().lng());
								this.cmapsMarker[index].setPosition(new_position);
							});

							google.maps.event.addListener(this.cmaps, 'dragend',(data)=>{																
								let wrapped = new google.maps.LatLng(this.cmaps.getCenter().lat(), this.cmaps.getCenter().lng());								
								this.$emit("dragMarker", false);
                                this.$emit("afterSelectmap",wrapped.lat() ,  wrapped.lng());
							});
							
                            window.google.maps.event.addListener(
                                this.cmapsMarker[index],
                              "drag",
                              (marker) => {
                                this.$emit("dragMarker", true);
                              }
                            );
              
                            window.google.maps.event.addListener(
                                this.cmapsMarker[index],
                              "dragend",
                              (marker) => {
                                const latLng = marker.latLng;
                                this.latLng = {
                                    lat :latLng.lat(),
                                    lng : latLng.lng()
                                }
                                this.$emit("dragMarker", false);
                                this.$emit("afterSelectmap", latLng.lat(), latLng.lng());
                              }
                            );
                        }

                        break;

					case "mapbox":		                          
						  this.cmapsMarker[index] = new mapboxgl.Marker(properties)
						  .setLngLat([properties.position.lng, properties.position.lat])
					      .addTo(this.cmaps);	
						  												  
						  this.bounds.extend(new mapboxgl.LngLat(properties.position.lng, properties.position.lat));

						  if (draggable === true) {
							this.cmapsMarker[index].on("dragend", (event) => {
							  const lngLat = this.cmapsMarker[index].getLngLat();							  							  
							  this.$emit("afterSelectmap", lngLat.lat, lngLat.lng);
							});		

							this.cmaps.on("drag", () => {
								this.cmapsMarker[index].setLngLat([
								  this.cmaps.getCenter().lng.toFixed(4),
								  this.cmaps.getCenter().lat.toFixed(4),
								]);
							});

							this.cmaps.on("dragend", () => {
								this.cmapsMarker[index].setLngLat([
								  this.cmaps.getCenter().lng.toFixed(4),
								  this.cmaps.getCenter().lat.toFixed(4),
								]);
								this.$emit(
								  "afterSelectmap",
								  this.cmaps.getCenter().lat.toFixed(4),
								  this.cmaps.getCenter().lng.toFixed(4)
								);                                
							});

						  }

						this.mapBoxResize();					
						break;					
                }

            } catch (err) {
                console.error(err);
            }
        },
        mapBoxResize() {
			if (this.provider == "mapbox") {
			  setTimeout(() => {
				this.cmaps.resize();
			  }, 500);
			}
		},
        moveAllMarker(){ 

			if(this.provider=='google.maps'){			
				if (Object.keys(this.markers).length > 0) {
					Object.entries(this.markers).forEach(([key, items]) => {                
						let latlng = new google.maps.LatLng(parseFloat(items.lat), parseFloat(items.lng));
						if(!empty(this.cmapsMarker[items.id])){
							this.cmapsMarker[items.id].setPosition(latlng);
						}                    
					});
					
					if (Object.keys(this.markers).length > 1) {
						this.FitBounds();
					} else {                                
						this.cmaps.panTo(
							new window.google.maps.LatLng(
								this.markers[0].lat,
								this.markers[0].lng
							)
						);
					}
				}
		    } else {								
				if (Object.keys(this.markers).length > 0) {
					Object.entries(this.markers).forEach(([key, items]) => { 						
						if(!empty(this.cmapsMarker[items.id])){							
							this.cmapsMarker[items.id].setLngLat([items.lng, items.lat])
							.addTo(this.cmaps);
						}
					});

					if (Object.keys(this.markers).length > 1) {
						this.FitBounds();
					} else { 						
						this.cmaps.flyTo({							
							center : [ this.markers[0].lng , this.markers[0].lat ],
							essential: true,
							zoom: 14,
						});
					}
				}
			}
        },
        centerMap() {
            this.FitBounds();
        },
        FitBounds() {
            try {
              switch (this.provider) {
                case "google.maps":
                  if (!empty(this.bounds)) {
                    this.cmaps.fitBounds(this.bounds);
                  }
                  break;
                case "mapbox":				  
                  if (!empty(this.bounds)) {					
                      this.cmaps.fitBounds(this.bounds, { duration: 0, padding: 50 });
                  }
                  break;
              }
            } catch (err) {
              //console.error(err);
            }
        },
        setCenter(lat, lng) {
            try {
              switch (this.provider) {
                case "google.maps":
                  this.cmaps.setCenter(new window.google.maps.LatLng(lat, lng));
                  break;
                case "mapbox":
                  this.cmaps.flyTo({
                    center: [lng, lat],
                    essential: true,
                  });
                  break;
              }
            } catch (err) {
              console.error(err);
            }
        },
        //
    },
    template : `            
    <div ref="cmaps" class="map border" class="map-medium"></div> 
    `
};

const componentsLocationAddress = {
    props : ['delivery_option_list','address_label_list' ,'delivery_option_first_value','address_label_first_value','enabled_map_selection','cmaps_config'],
    components : {
        'components-state':ComponentsState,
        'components-city':ComponentsCity,
        'components-area':ComponentsArea,
        'components-maps' : componentsMaps
    },
    data() {
        return {
            loading : false,
            modal : false,
            address_uuid : '',
            state_id : null,
            city_id : null,
            area_id : null,          
            zip_code : null,  
            formatted_address : '',
            address1 :'',
            custom_field1 :'',            
            location_name :'',
            delivery_options :'',
            delivery_instructions :'',
            address_label :'',
            latitude : '',
            longitude :'',
            house_number : ''
        }
    },
    mounted() {
        this.delivery_options = this.delivery_option_first_value;
        this.address_label = this.address_label_first_value;
    },    
    methods: {
        afterSelectstate(value){            
            this.city_id = null;  
            if ((typeof  this.$refs.ref_city !== "undefined") && ( this.$refs.ref_city !== null)) {
                this.$refs.ref_city.city_id = null;
                this.$refs.ref_city.city_list = [];
                this.$refs.ref_area.area_id = null;
                this.$refs.ref_area.area_list = [];
                this.$refs.ref_city.fetchCity(value);
            }
        },
        afterSelectcity(value){            
            this.area_id = null;  
            if ((typeof  this.$refs.ref_area !== "undefined") && ( this.$refs.ref_area !== null)) {
                this.$refs.ref_area.area_id = null;
                this.$refs.ref_area.area_list = [];
                this.$refs.ref_area.fetchArea(value);
            }
        },
        afterSelectarea(value){            
            axios.get(api_location+"/fetchZip?" + "area_id="+ value ).then(response => {                                
                if(response.data.code==1){                                                
                    this.zip_code = response.data.details.data.postal_code;
                } else {                    
                    this.zip_code = '';
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {						
			});         
        },
        onSubmit(){
            this.loading = true;		
            let data = "";
            data+="address_uuid="+this.address_uuid;
            data+="&formatted_address="+this.formatted_address;
            data+="&address1="+this.address1;
            data+="&location_name="+this.location_name;
            data+="&state_id="+this.state_id;
            data+="&city_id="+this.city_id;
            data+="&area_id="+this.area_id;            
            data+="&zip_code="+this.zip_code;              
            data+="&delivery_options="+this.delivery_options;
            data+="&delivery_instructions="+this.delivery_instructions;
            data+="&address_label="+this.address_label;
            data+="&latitude="+this.latitude;
            data+="&longitude="+this.longitude;
            data+="&house_number="+this.house_number;

			axios.post( api_location+"/saveAddress?language="+language , data, {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){		 
                    this.modal = false;	 	     
                    this.$emit("afterSaveaddress",{
                        state_id : this.state_id,
                        city_id : this.city_id,
                        area_id : this.area_id,
                    });
                    this.clearForm();                    
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'success',
                    });						                    
                } else {			 	 	
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                    });
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        clearForm(){
            this.address_uuid = '';
            this.formatted_address = '';
            this.address1 = '';
            this.location_name = '';
            this.house_number = '';
            this.state_id = null;
            this.city_id = null;
            this.area_id = null;            
            this.delivery_instructions = '';
            this.delivery_options = this.delivery_option_first_value;
            this.address_label = this.address_label_first_value;
        },
        setData(data){                     
            this.address_uuid = data.address_uuid;
            this.formatted_address  = data.formatted_address;
            this.address1  = data.address1;
            this.location_name  = data.location_name;                        
            this.delivery_options  = data.delivery_options;
            this.delivery_instructions  = data.delivery_instructions;
            this.address_label  = data.address_label;
            this.house_number = data.house_number;
            
            this.state_id = data.postal_code;  
            this.city_id = data.address2;  
            this.area_id = data.custom_field1;
            this.zip_code = data.zip_code;

            this.latitude = data.latitude; 
            this.longitude = data.longitude; 

            this.modal = true;

            setTimeout(() => {
                this.$refs.ref_state.state_id = this.state_id;
                this.$refs.ref_city.city_id = this.city_id;
                this.$refs.ref_area.area_id = this.area_id;

                this.$refs.ref_city.fetchCity(this.state_id);
                this.$refs.ref_area.fetchArea(this.city_id);
            }, 500); 	 	 
        },
        afterSelectmap(lat,lng){            
            this.latitude = lat;
            this.longitude = lng;
        }
    },
    template : "#xtemplate_location_address"
};

const app_location_address = Vue.createApp({		
    components : {
        'components-location-address' : componentsLocationAddress
    },
    data() {
        return {
            loading : false,
            data :[]
        }
    },
    mounted() {
        this.fetchLocationAddress();
    },
    computed: {
        getTotal(){
            return this.data.length;
        }
    },
    methods: {
        afterSaveaddress(){
            this.fetchLocationAddress();
        },
        fetchLocationAddress(){
            this.loading = true;		
			axios.post( api_location+"/fetchLocationAddress?language="+language , '', {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){
                    this.data = response.data.details.data;
                    this.total = response.data.details.count;
                } else {
                    this.data = [];
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        showAddAddress(){            
            this.$refs.ref_location_address.modal = true;
        },
        ConfirmDelete(value){            
            ElementPlus.ElMessageBox.confirm(
                'Are you sure you want to continue?',
                'Confirm',
                {
                  confirmButtonText: 'Yes',
                  cancelButtonText: 'Cancel',
                  type: 'warning',
                }
              )
                .then(() => {
                   this.deleteAddress(value);
                })
                .catch(() => {                  
            });
        },
        getAddress(value){            
            this.loading = true;		
			axios.post( api_location+"/getAddress?language="+language , "address_uuid="+value, {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){                          
                    this.$refs.ref_location_address.setData(response.data.details.data);
                } else {
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                    });
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        deleteAddress(value){
            this.loading = true;		
			axios.post( api_location+"/deleteAddress?language="+language , "address_uuid="+value, {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){                    
                    const index = this.data.findIndex(item => item.address_uuid === value);
                    if (index !== -1) {
                        this.data.splice(index, 1);
                    } else {
                        this.fetchLocationAddress();
                    }
                } else {
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                    });
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        //
    },
});
app_location_address.use(ElementPlus);
const vm_location_address = app_location_address.mount('#location-addresses');

const filterIcons =  {
    ratings_plus4 : 'fas fa-star',
    popular : 'fas fa-fire',
    best_seller : 'fas fa-fire',
    promo : 'fas fa-percent',
    new : 'fas fa-glass-cheers'
};

const componentsLocationFilters = {
    template: '#xtemplate_location_filters',
    data() {
        return {
            loading : false,
            more_cuisine : false,
            price_range_list : null,
            sort_by : null,
            services_list : null,
            cuisine_list : null,
            cuisine : [],
            filterby : null,
            price_range : null,            
            search_cuisine : '',
            filteredCuisine : null,
            filter_transaction : '',
            quick_filters : [],            
            quick_filters_list : null,
            offers_filters : [],
            offers_filters_list : null,
            filter_icons : filterIcons
        }
    },
    mounted() {
        this.getLocationFilters();
    },
    computed: {
        hasFilter(){
            if (Object.keys(this.cuisine).length > 0) {
                return true;
            }            
            if(this.price_range){
                return true;
            }                        
            if (Object.keys(this.quick_filters).length > 0) {
                return true;
            }
            if (Object.keys(this.offers_filters).length > 0) {
                return true;
            }
            return false;
        },
        filterCuisineFound(){         
            if(this.filteredCuisine){
                if (Object.keys(this.filteredCuisine).length > 0) {
                    return true;
                }
            }            
            return false;
        },
    },    
    methods: {
        onChangeTransaction(value){            
            this.$emit("onChangetransactions",{
                cuisine : this.cuisine,                
                price_range : this.price_range,           
                transaction_type : this.filter_transaction ,
                quick_filters : this.quick_filters,
                offers_filters : this.offers_filters
            });                        
            setInLocalStorageStore(StoreName,'SelectedtransactionType',this.filter_transaction);
            this.clearFilters();
        },
        ifFilterIncludes(value){
            if (this.quick_filters.includes(value)) {
                return true;
            }
            return false;
        },
        getLocationFilters(){
            let currency_code = getCookie('currency_code');
			currency_code = !empty(currency_code)?currency_code:'';
            this.loading = true;
            axios.get(api_location+"/getLocationFilters?language="+language + "&currency_code="+currency_code ).then(response => {                                
                if(response.data.code==1){                    
                    this.price_range_list = response.data.details.price_range;
                    this.sort_by = response.data.details.sort_by;
                    this.services_list = response.data.details.services_list;
                    this.cuisine_list = response.data.details.cuisine;
                    this.filteredCuisine = response.data.details.cuisine;
                    this.filter_transaction = response.data.details.transaction_type;
                    this.quick_filters_list = response.data.details.quick_filters;
                    this.offers_filters_list = response.data.details.offers_filters;     
                                        
                    let transactionType = getFromLocalStorageStore(StoreName,'SelectedtransactionType');
                    if(!transactionType){                                                
                        setInLocalStorageStore(StoreName,'SelectedtransactionType',this.filter_transaction);
                    } else {
                        this.filter_transaction = transactionType;
                    }
                }                             
                this.$emit('afterGetfilters',response.data.details);
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		
				this.loading = false;
			});         
        },
        onChangeFilters(){            
            this.$emit("onChangefilter",{
                cuisine : this.cuisine,                
                price_range : this.price_range,           
                transaction_type : this.filter_transaction ,
                quick_filters : this.quick_filters,
                offers_filters : this.offers_filters
            })
        },
        clearFilters(){
            this.cuisine = [];
            this.filterby = null;
            this.price_range = null;            
            this.search_cuisine = '';
            this.filteredCuisine = this.cuisine_list;          
            this.quick_filters = [];
            this.offers_filters = [];
            this.$emit("onChangefilter",[])       
        },
        searchInput(value){                    
            this.filteredCuisine = this.cuisine_list.filter(cuisine => cuisine.cuisine_name.toLowerCase().includes(value.toLowerCase()));
        },    
        setQuickFilters(value){
            if (this.quick_filters.includes(value)) {
                this.quick_filters = this.quick_filters.filter(item => item !== value);
            } else {
                this.quick_filters.push(value);
            }            
            this.onChangeFilters();
        }
        //
    },
};

const componentsBanner = {
    template: '#xtemplate_banner',
    data() {
        return {
            loading : false,
            swiperBanner : null,
            data : null
        }
    },
    mounted() {        
        this.getBanner();        
    },
    computed: {
        hasBanner(){
            if (!this.loading && Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
    },
    methods: {
        getBanner(){
            this.loading = true;
            axios.get(api_location+"/getBanner" ).then(response => {                                
                if(response.data.code==1){       
                    this.data = response.data.details.data;                         
                    setTimeout(() => {
                        this.initSwiper();  
                    }, 1); 
                    this.$emit("afterGetbanner",true);
                }  else {
                    this.data = null;                    
                    this.$emit("afterGetbanner",false);
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		
				this.loading = false;
			});         
        },
        initSwiper(){
            this.swiperBanner =  new Swiper(this.$refs.refSwiper, {
                lazy: true,
                slidesPerView: 3,
                spaceBetween: 15,         
                autoHeight: true,        
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },              
                pagination: {
                    el: ".swiper-pagination",
                    clickable : true,
                    dynamicBullets: true,
                },         
                breakpoints: {
                    310: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    480: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    640: {
                       slidesPerView: 2,
                       spaceBetween: 10,
                    },
                    768: {
                       slidesPerView: 2,
                       spaceBetween: 10,
                    },
                    1024: {
                       slidesPerView: 3,
                       spaceBetween: 15,
                    },
                  },        
            });
        },
        onBannerClick(value){            
            window.location.href = value.url;
        },
        //
    },
};

const componentSwiperList = {
    template: '#xtemplate_swiper_list',
    props : ['title','query','filters_transactions','city_id','area_id','state_id','postal_id'],
    data() {
        return {
            swiperList : null,
            loading : true,
            data : [],
            available_vouchers : null,
            available_promos : null
        }
    },
    mounted() {        
        this.getData();
    },
    computed: {
        hasData(){
            if (!this.loading && Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
    },    
    methods: {
        getData(){
            this.loading = true;            
            let params = "language="+language;
            params+="&featured="+ JSON.stringify(this.query);            
            let transactionType = getFromLocalStorageStore(StoreName,'SelectedtransactionType');
            if(transactionType){
                params+="&transaction_type="+ transactionType;
            }
            params+="&city_id="+ this.city_id;
            params+="&area_id="+ this.area_id;     
            params+="&state_id="+ this.state_id;  
            params+="&postal_id="+ this.postal_id;  
            axios.get(api_location+"/getfeedv1?" + params ).then(response => {                                
                if(response.data.code==1){        
                    this.data = response.data.details.data;      
                    this.available_vouchers = response.data.details.available_vouchers; 
                    this.available_promos = response.data.details.available_promos; 
                    setTimeout(() => {
                       this.initSwiper();
                    }, 1); 
                    this.$emit("afterGetfeatured",true);
                } else {
                    this.data = [];
                    this.$emit("afterGetfeatured",false);
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		
				this.loading = false;
			});         
        },
        initSwiper(){
            this.swiperList = new Swiper( this.$refs.refSwiperList, {
                lazy: true,
                slidesPerView: 3.5,
                spaceBetween: 15,         
                autoHeight: true,                           
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    310: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 0
                      },
                    480: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    640: {
                       slidesPerView: 2,
                       spaceBetween: 10,
                    },
                    768: {
                       slidesPerView: 3,
                       spaceBetween: 10,
                    },
                    1024: {
                       slidesPerView: 3.5,
                       spaceBetween: 15,
                    },
                },        
            });
        },
        setUrl(value){            
            window.location.href = value;
        },
        //
    },
};

const componentsCuisineList = {
    template: '#xtemplate_cuisine_list',
    props : ['title'],
    data() {
        return {
            swiperCuisine : null,
            data : [],
            loading : false
        }
    },
    mounted() {        
        this.getCuisine();
    },
    computed: {
        hasData(){
            if (!this.loading && Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
    },
    methods: {
        getCuisine(){
            this.loading = true;
            let params = "language="+language;            
            axios.get(api_location+"/cuisineList?" + params ).then(response => {                                
                if(response.data.code==1){        
                    this.data = response.data.details;             
                    setTimeout(() => {
                       this.initSwiper();
                    }, 1); 
                    this.$emit("afterGetcuisine",true);
                } else {
                    this.data = [];
                    this.$emit("afterGetcuisine",false);
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		
				this.loading = false;
			});         
        },
        initSwiper(){
            this.swiperCuisine = new Swiper(this.$refs.swiperCuisineList, {
                lazy: true,
                slidesPerView: 7.5,
                spaceBetween: 15,         
                autoHeight: true,               
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    310: {
                        slidesPerView: 3,
                        spaceBetween: 0
                    },
                    320: {
                        slidesPerView: 3,
                        spaceBetween: 0
                      },
                    480: {
                        slidesPerView: 3.5,
                        spaceBetween: 0
                    },
                    640: {
                       slidesPerView: 4.5,
                       spaceBetween: 5,
                    },
                    768: {
                       slidesPerView: 7,
                       spaceBetween: 10,
                    },
                    1024: {
                       slidesPerView: 7.5,
                       spaceBetween: 15,
                    },
                },        
            });
        },
        setUrl(value){            
            window.location.href = value;
        },
        //
    },
};

const componentsRestaurantList = {
    template: '#xtemplate_restaurant_list',
    props : ['title','city_id','area_id','state_id','postal_id','filters','is_filters','query'],
    data() {
        return {
            loading : true,
            data : [],
            available_vouchers : null,
            available_promos : null,
            total_pretty : '',
            page: 0,
            limit: 20,
            total: 0,     
            response_code : null ,
            identifier : false  
        }
    },
    created() {        
        if ((typeof  list_limit !== "undefined") && ( list_limit !== null)) {
            this.limit = list_limit;
         }
    },
    computed: {
        hasData(){
            if (!this.loading && Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },        
        totalPages() {
            return Math.ceil(this.total / this.limit);
        },
    },    
    methods: {        
        async getData($state){
            this.loading = true;
            let params = "language="+language;                   
            this.page++;

            if(!empty(this.filters)){                
                params+="&filters="+ JSON.stringify(this.filters);
            }                                         
            let transactionType = getFromLocalStorageStore(StoreName,'SelectedtransactionType');
            if(transactionType){
                params+="&transaction_type="+ transactionType;
            }
            params+="&city_id="+ this.city_id;
            params+="&area_id="+ this.area_id;        
            params+="&state_id="+ this.state_id;   
            params+="&postal_id="+ this.postal_id;   

            params+="&query="+ this.query;       
            params+="&page="+ this.page; 
            params+="&limit="+ this.limit;            

            axios.get(api_location+"/getFeedV1?" + params ).then(response => {        
                this.response_code = response.data.code;                        
                if(response.data.code==1){                                               
                    this.data = [...this.data, ...response.data.details.data];
                    this.total_pretty = response.data.details.total_pretty;                      
                    this.total = response.data.details.total;       
                    if (this.page < this.totalPages) {
                        $state.loaded();
                    } else {
                        $state.complete();
                    }                                     
                } else {                                     
                    $state.complete();                                 
                }                
            })
            .catch(error => {                
                console.error('Error:', error);
                $state.error();
            }).then(data => {		
				this.loading = false;
			});                     
        },
        onBannerClick(value){            
            window.location.href = value.url;
        },
        prevPage() {
            if (this.page > 1) {
                this.page--;
                this.getData();
            }
        },
        nextPage() {
            if (this.page < this.totalPages) {
                this.page++;
                this.getData();
            }
        },   
        resetInfiniteloading(){          
            this.page = 0;
            this.data = [] ;
            this.identifier = !this.identifier;
        }
        //
    },
};

const componentsSearch = {
    template: '#xtemplate_search',
    props : ['cuisine_list'],
    data() {
        return {
            search_filters : '',
            search_modal : false,
            timeoutId: null,      
            is_typing: false,
            searchHistory : [],
            data : [],
            loading : false,
            query :''
        }
    },    
    mounted() {
        this.loadSearchHistory();
        const urlParams = new URLSearchParams(window.location.search);
        this.query = urlParams.get('query');   
        if(!empty(this.query)) {
            this.search_filters = this.query;
        }
    },
    computed: {
        hasInput(){
            if(!empty(this.search_filters)){
                return true;
            }
            return false;
        },
        hasSearchHistory(){
            if (Object.keys(this.searchHistory).length > 0) {
                return true;
            }
            return false;
        },
    },
    methods: {
        searchInput(value){
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
            }
            this.is_typing = true;
            this.timeoutId = setTimeout(() => {
                this.search(value);
            }, 300);
        },
        search(query) {            
            if(!empty(query)){
                this.loading = true;
                let params = "q="+query;
                axios.get(api_location+"/SearchSuggestion?" + params ).then(response => {                                
                    if(response.data.code==1){                               
                        this.data = response.data.details;                                            
                    } else {
                        this.data = [];                                            
                    }
                })
                .catch(error => {                
                    console.error('Error:', error);
                }).then(data => {		
                    this.loading = false;
                });                     
            }            
        },
        saveSearchHistory(searchTerm) {
            if(empty(searchTerm)){
                return ;
            }
            let searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || [];
            searchHistory.push({
                searchTerm: searchTerm,
                searchedAt: new Date().toISOString(),
            });
            if (searchHistory.length > 4) {
                searchHistory.shift(); // Remove the oldest entry
            }
            localStorage.setItem('searchHistory', JSON.stringify(searchHistory));
            this.searchHistory = searchHistory;
        },
        loadSearchHistory() {           
            this.searchHistory = JSON.parse(localStorage.getItem('searchHistory')) || [];
        },
        deleteSearch(index){            
            this.searchHistory.splice(index, 1);
            localStorage.setItem('searchHistory', JSON.stringify(this.searchHistory));
        },
        setSearchFilters(value){
            this.saveSearchHistory(value);
            this.search_filters = value;
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            params.set('query', value);
            window.history.replaceState({}, '', `${url.pathname}?${params.toString()}`);
            this.search_modal = false;
            this.$emit('afterSetsearch',value);
        },
        searchInputClear(){            
            const url = new URL(window.location.href);
            const params = new URLSearchParams(url.search);
            params.delete('query');
            if (params.toString()) {
                window.history.replaceState({}, '', `${url.pathname}?${params.toString()}`);
            } else {
                // If there are no more parameters, just keep the pathname
                window.history.replaceState({}, '', url.pathname);
            }
            this.$emit('afterClearsearch');
        },
        //
    },
};

const componentsCurrentAddress = {
    template: '#xtemplate_current_address',
    props : ['search_type','is_guest'],
    components :{
        'components-city':ComponentsCity,
        'components-area':ComponentsArea,
        'components-state': ComponentsState,
        'components-postalcode':ComponentsPostalcode,
    },
    data() {
        return {
            modal : false,        
            state_id : null,
            city_id : null,
            area_id : null,            
            postal_id : null,
            loading : false,   
            data : [] ,
            address_uuid : '',    
            is_guest : true,        
        }
    },    
    mounted() {
        let is_guest = 1;
        if ((typeof isGuest !== "undefined") && ( isGuest !== null)) {
            is_guest = isGuest;
        }		     
        this.is_guest = is_guest==1?true:false;        
    },
    watch: {
        state_id(newval,oldval){                    
            this.city_id = null;    
            if ((typeof  this.$refs.ref_city !== "undefined") && ( this.$refs.ref_city !== null)) {
                this.$refs.ref_city.city_id = null;
               this.$refs.ref_city.fetchCity(newval);
            }
        },
        city_id(newval,oldval){                    
            this.area_id = null;    
            if ((typeof  this.$refs.ref_area !== "undefined") && ( this.$refs.ref_area !== null)) {
                this.$refs.ref_area.area_id = null;
                this.$refs.ref_area.fetchArea(newval);
            }
        },
    },
    computed: {
        canSearch(){
            if(this.search_type==1){
                if(!empty(this.city_id) && !empty(this.area_id)){
                    return true;
                }
            } else if(this.search_type==2){
                if(!empty(this.city_id) && !empty(this.state_id)){
                    return true;
                }
            } else if(this.search_type==3){
                if(this.postal_id){
                    return true;
                }
            }
            return false;
        },
        hasAddress(){
            if (!this.loading && Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
    },
    methods: {
        initgetAddress(){            
            if(!this.is_guest && !this.hasAddress){
                this.getAddress();
            }            
        },
        getAddress(){
            this.loading = true;		
			axios.post( api_location+"/fetchLocationAddress?language="+language , '', {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {
                if(response.data.code==1){                           
                    this.data = response.data.details.data;
                } else {                    
                    this.data = [];
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        onSearch(){            
            setInLocalStorageStore(StoreName,"selectedCityId",this.city_id);
            setInLocalStorageStore(StoreName,"selectedAreaId",this.area_id);
            setInLocalStorageStore(StoreName,"selectedStateId",this.state_id);
            setInLocalStorageStore(StoreName,"selectedPostalId",this.postal_id);

            this.$emit("afterChangelocation");
            this.modal = false;
        },
        setSelectAddress(){            
            const foundAddress = this.data.find(address => address.address_uuid === this.address_uuid);                        
            if ((typeof  foundAddress !== "undefined") && ( foundAddress !== null)) {                                    
                setInLocalStorageStore(StoreName,"selectedStateId",foundAddress.state_id);
                setInLocalStorageStore(StoreName,"selectedCityId",foundAddress.city_id);
                setInLocalStorageStore(StoreName,"selectedAreaId",foundAddress.area_id);      
                setInLocalStorageStore(StoreName,"selectedPostalId",this.postal_code);          
                this.$emit("afterChangelocation");
                this.modal = false;                    
            }
        },
    },
};

const componentsCheckLocation = {
    template: '#xtemplate_check_locations',
    props : ['search_type','city_id','area_id','state_id','postal_id'],
    data() {
        return {
            merchant_id : null,
            out_of_range : false
        }
    },
    mounted() {
        this.checkLocations()
    },
    created() {
        if ((typeof  merchant_id !== "undefined") && ( merchant_id !== null)) { 
           this.merchant_id = merchant_id;
        }
    },
    methods: {
        checkLocations(){

            if(this.search_type==1){
                if(!this.city_id || !this.area_id){
                    return;
                }                
            }

            let params = "search_type="+this.search_type;
            params+= "&city_id="+this.city_id;
            params+= "&area_id="+this.area_id;
            params+= "&state_id="+this.state_id;
            params+= "&postal_id="+this.postal_id;

            params+= "&merchant_id="+this.merchant_id;            
            let transactionType = getFromLocalStorageStore(StoreName,'SelectedtransactionType');
            if(transactionType){
                params+="&transaction_type="+ transactionType;                
            }          
            axios.get(api_location+"/checkLocations?" + params ).then(response => {                                
                if(response.data.code==1){       
                    this.out_of_range = false;                                                                                    
                } else {                    
                    this.out_of_range = true;
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		                
            });            
        },
        changeLocation(){            
            this.$emit("showCurrentaddress");
        },
        //
    },
};

const componentJoin = {
    template: '#xtemplate_join',
    props : ['title'],
    data() {
        return {
            swiperList : null,
            loading : true,
            data : [],            
        }
    },
    mounted() {        
        this.getData();
    },
    computed: {
        hasData(){
            if (!this.loading && Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
    },    
    methods: {
        getData(){
            this.loading = true;            
            let params = "language="+language;            
            axios.get(ajaxurl+"/getJoin?" + params ).then(response => {                                
                if(response.data.code==1){        
                    this.data = response.data.details.data;                          
                    setTimeout(() => {
                       this.initSwiper();
                    }, 1);                     
                } else {
                    this.data = [];                    
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		
				this.loading = false;
			});         
        },
        initSwiper(){
            this.swiperList = new Swiper( this.$refs.refSwiperList, {
                lazy: true,
                slidesPerView: 3,
                spaceBetween: 15,         
                autoHeight: true,                           
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    310: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 0
                      },
                    480: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    640: {
                       slidesPerView: 2,
                       spaceBetween: 10,
                    },
                    768: {
                       slidesPerView: 3,
                       spaceBetween: 10,
                    },
                    1024: {
                       slidesPerView: 3,
                       spaceBetween: 15,
                    },
                },        
            });
        },
        //
    },
};


const componentsRecentAddress = {
    template: '#xtemplate_recent_address',
    data() {
        return {
            is_guest : true,   
            modal : false,
            location_searchtype : null,
            loading : false,
            data : [],
            address_uuid : null,
        }
    },
    mounted() {        
        if ((typeof location_searchtype !== "undefined") && ( location_searchtype !== null)) {
            this.location_searchtype = location_searchtype;
        }
        this.initRecentAddress();
    },
    computed: {
        hasAddress(){
            if (Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
    },
    methods: {
        initRecentAddress(){
            let is_guest = 1;
            if ((typeof isGuest !== "undefined") && ( isGuest !== null)) {
                is_guest = isGuest;
            }		     
            this.is_guest = is_guest==1?true:false;
            if(this.is_guest){
                return ;
            }        
            
            const location = getLocation();             
            if(this.location_searchtype==1){                
                if (!location.city_id && !location.area_id) {
                    this.getAddress();
                }
            } else if (this.location_searchtype==2){
                if (!location.state_id && !location.city_id) {
                    this.getAddress();
                }
            } else if (this.location_searchtype==3){
                if(!location.postal_id){
                    this.getAddress();
                }
            }
        },
        getAddress(){
            this.loading = true;		
			axios.post( api_location+"/fetchLocationAddress?language="+language , '', {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {
                if(response.data.code==1){                           
                    this.data = response.data.details.data;
                    this.modal = true;
                } else {                    
                    this.data = [];
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        setSelectAddress(){            
            const foundAddress = this.data.find(address => address.address_uuid === this.address_uuid);                                    
            if (Object.keys(foundAddress).length > 0) {

                setInLocalStorageStore(StoreName,'selectedCityId',foundAddress.city_id);
                setInLocalStorageStore(StoreName,'selectedStateId',foundAddress.state_id);
                setInLocalStorageStore(StoreName,'selectedAreaId',foundAddress.area_id);            

                let search_url = '';
                if(this.location_searchtype==1){
                    search_url = next_url+`?city_id=${encodeURIComponent(foundAddress.city_id)}&area_id=${foundAddress.area_id}`;                
                    window.location.href = search_url;
                } else if (this.location_searchtype==2){
                    search_url = next_url+`?state_id=${encodeURIComponent(foundAddress.state_id)}&city_id=${foundAddress.city_id}`;                
                    window.location.href = search_url;
                } else if (this.location_searchtype==3){
                    search_url = next_url+`?postal_id=${encodeURIComponent(foundAddress.postal_code)}`;                
                    window.location.href = search_url;
                }
            }
        },
        //
    },
};

// HOME VUE
const app_locations = Vue.createApp({		
    components :{
        'components-state':ComponentsState,
        'components-city':ComponentsCity,
        'components-area':ComponentsArea,
        'components-postalcode':ComponentsPostalcode,
        'components-cuisine-list' : componentsCuisineList,
        'components-swiper-list' :componentSwiperList,
        'components-join' :componentJoin,
        'location-recent-address' : componentsRecentAddress
    },
	data() {
		return {
            state_id : null,
            city_id : null,
            area_id : null,
            postal_code : null,
            location_searchtype : null,            
		}
	},
    created() {
        if (this.city_id === null) {
            const location = getLocation();            
        }
    },
    mounted() {
        if ((typeof  location_searchtype !== "undefined") && ( location_searchtype !== null)) {
           this.location_searchtype = location_searchtype;
        }                
    },
    watch: {
        state_id(newval,oldval){                    
            this.city_id = null;    
            if ((typeof  this.$refs.ref_city !== "undefined") && ( this.$refs.ref_city !== null)) {
                this.$refs.ref_city.city_id = null;
               this.$refs.ref_city.fetchCity(newval);
            }
        },
        city_id(newval,oldval){                    
            this.area_id = null;    
            if ((typeof  this.$refs.ref_area !== "undefined") && ( this.$refs.ref_area !== null)) {
                this.$refs.ref_area.area_id = null;
                this.$refs.ref_area.fetchArea(newval);
            }
        },
    },    
    computed: {
        canSearch(){
            if(this.location_searchtype==1){
                if(!empty(this.city_id) && !empty(this.area_id)){
                    return true;
                }
            } else if(this.location_searchtype==2){
                if(!empty(this.city_id) && !empty(this.state_id)){
                    return true;
                }
            } else if(this.location_searchtype==3){
                if(this.postal_code){
                    return true;
                }
            }
            return false;
        },
    },
    methods: {
        onSearch(){
            let search_url = '';            
            if(this.location_searchtype==1){
                search_url = next_url+`?city_id=${encodeURIComponent(this.city_id)}&area_id=${this.area_id}`;                
                window.location.href = search_url;
            } else if (this.location_searchtype==2){
                search_url = next_url+`?state_id=${encodeURIComponent(this.state_id)}&city_id=${this.city_id}`;                
                window.location.href = search_url;
            } else if (this.location_searchtype==3){
                search_url = next_url+`?postal_id=${encodeURIComponent(this.postal_code)}`;                
                window.location.href = search_url;
            }
        },     
        //
    },
});
app_locations.use(ElementPlus);
const vm_locations = app_locations.mount('#vm_home_search_location');


const componentsMobileFilters = {
    template: '#xtemplate_mobile_location_filters',
    props : ['data'],
    data() {
        return {
            filter_icons : filterIcons,
            modal : false,      
            filter_transaction : '',
            quick_filters : [],   
            offers_filters : [],       
            cuisine : [],  
            price_range : null,   
        }
    },    
    computed: {
        hasData(){
            if(this.data){
                return true;
            }
            return false;
        },
        getServices(){
            if(this.data){
                return this.data.services_list;
            }
            return false;
        },
        getQuickFilters(){
            if(this.data){
                return this.data.quick_filters;
            }
            return false;
        },        
        getOfferFilters(){
            if(this.data){
                return this.data.offers_filters;
            }
            return false;
        },
        getCuisine(){
            if(this.data){
                return this.data.cuisine;
            }
            return false;
        },
        getPrices(){
            if(this.data){
                return this.data.price_range;
            }
            return false;
        },        
        hasFilters(){
            if (Object.keys(this.quick_filters).length > 0) { 
                return true;
            }
            if (Object.keys(this.offers_filters).length > 0) { 
                return true;
            }
            if (Object.keys(this.cuisine).length > 0) { 
                return true;
            }
            if(this.price_range){
                return true;
            }
            return false;
        },
    },    
    methods: {        
        onOpened(){            
            this.filter_transaction = this.data.transaction_type;

            let transactionType = getFromLocalStorageStore(StoreName,'SelectedtransactionType');
            if(!transactionType){                                                
                setInLocalStorageStore(StoreName,'SelectedtransactionType',this.filter_transaction);
            } else {
                this.filter_transaction = transactionType;
            }
        },
        ifFilterIncludes(value){
            if (this.quick_filters.includes(value)) {
                return true;
            }
            return false;
        },
        setQuickFilters(value,apply_filter){
            if (this.quick_filters.includes(value)) {
                this.quick_filters = this.quick_filters.filter(item => item !== value);
            } else {
                this.quick_filters.push(value);
            }                                   
            if(apply_filter){                
                if (Object.keys(this.quick_filters).length > 0) {                    
                    this.onChangeFilters();
                } else {
                    this.clearFilters();
                }                
            }
        },
        setOffers(value){            
            if (this.offers_filters.includes(value)) {
                this.offers_filters = this.offers_filters.filter(item => item !== value);
            } else {
                this.offers_filters.push(value);
            }                        
        },
        offersIncludes(value){
            if (this.offers_filters.includes(value)) {
                return true;
            }
            return false;
        },
        cuisineIncludes(value){
            if (this.cuisine.includes(value)) {
                return true;
            }
            return false;
        },
        setCuisine(value){
            if (this.cuisine.includes(value)) {
                this.cuisine = this.cuisine.filter(item => item !== value);
            } else {
                this.cuisine.push(value);
            }                        
        },
        closeDrawer(){
            this.modal = false;
            this.clearFilters();
        },
        clearFilters(){            
            this.cuisine = [];        
            this.price_range = null;                                    
            this.quick_filters = [];
            this.offers_filters = [];     
            this.$emit("onChangefilter",[])          
        },
        setTransactiontype(value){
            this.filter_transaction = value;
            this.modal = false;
            this.onChangeTransaction();
        },
        onChangeTransaction(){            
            this.$emit("onChangetransactions",{
                cuisine : this.cuisine,                
                price_range : this.price_range,           
                transaction_type : this.filter_transaction ,
                quick_filters : this.quick_filters,
                offers_filters : this.offers_filters
            });                        
            setInLocalStorageStore(StoreName,'SelectedtransactionType',this.filter_transaction);
            this.clearFilters();
        },
        onChangeFilters(){            
            this.$emit("onChangefilter",{
                cuisine : this.cuisine,                
                price_range : this.price_range,           
                transaction_type : this.filter_transaction ,
                quick_filters : this.quick_filters,
                offers_filters : this.offers_filters
            });
            this.modal = false;
        },
    },
};

// FEED LOCATIONS
const app_feed = Vue.createApp({	
    components : {
        'location-filters' : componentsLocationFilters,
        'components-banner' : componentsBanner,
        'components-swiper-list' :componentSwiperList,
        'components-cuisine-list' : componentsCuisineList,
        'components-restaurant-list' : componentsRestaurantList,
        'components-search' : componentsSearch,
        'components-current-address' : componentsCurrentAddress,
        'components-check-location' : componentsCheckLocation,
        'location-mobile-filters' : componentsMobileFilters

    },
    data() {
        return {
            filters :[],
            filters_transactions :[],
            search_filters : '',
            count :0,
            search_modal : false,            
            city_id : null,
            area_id : null,
            state_id : null,
            postal_id : null,
            has_banner : true,
            transaction_type :'',  
            query : '',  
            cuisineList : [] ,    
            search_type : null,
            isGuest : true,    
            cart_transaction_type : null,  
            location_required : false ,
            filters_data : null
        }
    },
    created() { 

        if ((typeof  isGuest !== "undefined") && ( isGuest !== null)) { 
            this.isGuest = isGuest==1?true:false;
        }

        const urlParams = new URLSearchParams(window.location.search);
        this.query = urlParams.get('query');   
        this.city_id = urlParams.get('city_id');
        this.area_id = urlParams.get('area_id');
        this.state_id = urlParams.get('state_id');        
        this.postal_id = urlParams.get('postal_id');   
        
        if ((typeof  this.city_id !== "undefined") && ( this.city_id !== null)) {                        
            setInLocalStorageStore(StoreName,"selectedCityId",this.city_id);
        }
        if ((typeof  this.area_id !== "undefined") && ( this.area_id !== null)) {            
            setInLocalStorageStore(StoreName,"selectedAreaId",this.area_id);
        } 
        if ((typeof  this.state_id !== "undefined") && ( this.state_id !== null)) {                  
            setInLocalStorageStore(StoreName,"selectedStateId",this.state_id);
        } 

        if ((typeof  this.postal_id !== "undefined") && ( this.postal_id !== null)) {                  
            setInLocalStorageStore(StoreName,"selectedPostalId",this.postal_id);
        } 
        
        if ((typeof  location_searchtype !== "undefined") && ( location_searchtype !== null)) { 
            this.search_type = location_searchtype;
        }        
        
        const location = getLocation();        
                
        if(!this.city_id && this.search_type==1){                                   
            if (location.city_id && location.area_id) {
                this.city_id = location.city_id;
                this.area_id = location.area_id;
            }
        } else if ( !this.state_id && this.search_type==2 ) {            
            if (location.state_id && location.city_id) {
                this.city_id = location.city_id;                
                this.state_id = location.state_id;
            }
        } else if ( !this.postal_id && this.search_type==3 ) {            
            if(location.postal_id){
                this.postal_id = location.postal_id;
            }            
        } 

    },    
    computed: {
        hasFilters(){
            if (Object.keys(this.filters).length > 0) {
                return true;
            }
            if(!empty(this.query)){
                return true;
            }
            return false;
        },        
        hasQuery(){
            if(!empty(this.query)){
                return true;
            }
            return false;
        },
        isAddressNeeded(){
            return this.location_required;
        },
    },    
    methods: {        
        afterChangelocation(){            
            const location = getLocation();            
            this.location_required = false;

            if(location){
                this.city_id = location.city_id;
                this.area_id = location.area_id;
                this.state_id = location.state_id;
                this.postal_id = location.postal_id;
            }

            if(this.search_type==1){
                changeUrlParameters('city_id',this.city_id);
                changeUrlParameters('area_id',this.area_id);
            } else if (this.search_type==2){
                changeUrlParameters('city_id',this.city_id);
                changeUrlParameters('state_id',this.state_id);
            } else if (this.search_type==3){
                changeUrlParameters('postal_id',this.postal_id);
            }
            
            setTimeout(() => {                            
                if(this.$refs.ref_swiperlist){
                    this.$refs.ref_swiperlist.getData();
                }              
                if(this.$refs.ref_restaurantlist){
                    this.$refs.ref_restaurantlist.resetInfiniteloading();
                }                
                
                if(this.$refs.ref_check_location){
                    this.$refs.ref_check_location.checkLocations();
                }
                                
                if ((typeof  vm_widget_nav !== "undefined") && ( vm_widget_nav !== null)) {                    
                    vm_widget_nav.$refs.ref_location_current_address.getLocationCurrentAddress();
                }

                if ((typeof  VmWidgetNavMobile !== "undefined") && ( VmWidgetNavMobile !== null)) {                    
                    VmWidgetNavMobile.$refs.ref_location_current_address.getLocationCurrentAddress();
                }

                if ((typeof  vm_merchant_details !== "undefined") && ( vm_merchant_details !== null)) { 
                    vm_merchant_details.$refs.ref_location_services.getLocationEstimation();
                }       

                if ((typeof  vue_cart !== "undefined") && ( vue_cart !== null)) {                     
                    vue_cart.loadcart();
                }

            }, 100 ); 	 	 
        },      
        onChangefilter(value){                                     
            this.filters = value;
            setTimeout(() => {                            
                this.$refs.ref_restaurantlist.resetInfiniteloading();
            }, 200 ); 	 	 
        },        
        afterGetbanner(value){            
            this.has_banner = value;
        },
        onChangetransactions(value){             
            this.filters_transactions = value;
            setTimeout(() => {
              if(this.$refs.ref_swiperlist){
                 this.$refs.ref_swiperlist.getData();
              }              
              if(this.$refs.ref_restaurantlist){                 
                 this.$refs.ref_restaurantlist.resetInfiniteloading();
              }
            }, 100 ); 	 	 
        },      
        afterClearsearch(){            
            this.query = '';
            setTimeout(() => {                      
                if(this.$refs.ref_restaurantlist){                   
                   this.$refs.ref_restaurantlist.resetInfiniteloading();
                }
            }, 100 ); 	 	 
        },
        afterSetsearch(value){
            this.query = value;
            setTimeout(() => {                      
                if(this.$refs.ref_restaurantlist){                   
                }
            }, 100 ); 	 	 
        },        
        afterGetfilters(value){         
            this.filters_data = value;           
            this.cuisineList = value.cuisine;
        },        
        showCurrentaddress(){            
            this.$refs.ref_current_address.modal = true;
        }
        //
    },
});
app_feed.use(ElementPlus);
app_feed.component("infinite-loading", V3InfiniteLoading.default);
window.vm_location_feed = app_feed.mount('#feed-locations');


const app_checkout_address = Vue.createApp({
    components : {
        'components-location-address' : componentsLocationAddress,        
    },
    data() {
        return {
            data :[],
            address_needed : false,
            default_address : [],
            address_uuid : null,
            add_address : false,            
            visible : false,
            save_address_uuid : null,
            modal_address_list : false,
            loading : false,
            loading_get : false,
        }
    },
    mounted() {
        this.handleAddressRequirement();
    },
    computed: {
        hasAddress(){
            if (Object.keys(this.data).length > 0) {
                return true;
            }
            return false;
        },
        hasDefaultAddress(){
            if (Object.keys(this.default_address).length > 0) {
                return true;
            }
            return false;
        },
        isAddressNeeded(){
            return this.address_needed;
        },
    },
    methods: {
        handleAddressRequirement(){
            this.loading = true;		
            let params = "language="+language + "&merchant_id="+merchant_id + "&cart_uuid="+ getCookie('cart_uuid')
            const location = getLocation();                   
            params+= "&city_id="+ location.city_id;                
            params+= "&area_id="+ location.area_id;
            params+= "&state_id="+ location.state_id;
			axios.post( api_location+"/handleAddressRequirement?" + params , '', {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){
                    this.data = response.data.details.data;
                    this.address_needed = response.data.details.address_needed;
                    this.default_address = response.data.details.default_address;
                    this.address_uuid = this.default_address.address_uuid;
                } else {
                    this.data = [];
                    this.address_needed = false;
                    this.default_address = [];
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading = false;
			});         
        },
        afterSaveaddress(value){              
            setInLocalStorageStore(StoreName,'selectedCityId',value.city_id);
            setInLocalStorageStore(StoreName,'selectedStateId',value.state_id);
            setInLocalStorageStore(StoreName,'selectedAreaId',value.area_id);            
            this.add_address = false;
            this.handleAddressRequirement();
            if ((typeof  vue_cart !== "undefined") && ( vue_cart !== null)) {                     
                vue_cart.loadcart();
            }
        },
        setSelectAddress(){                        
            const foundAddress = this.data.find(address => address.address_uuid === this.save_address_uuid);                        
            console.debug("foundAddress",foundAddress);

            setInLocalStorageStore(StoreName,'selectedCityId',foundAddress.city_id);
            setInLocalStorageStore(StoreName,'selectedStateId',foundAddress.state_id);
            setInLocalStorageStore(StoreName,'selectedAreaId',foundAddress.area_id);            

            this.address_uuid = foundAddress.address_uuid;
            this.default_address = foundAddress;
            this.visible = false;             
            this.modal_address_list = false;

            if ((typeof  vue_cart !== "undefined") && ( vue_cart !== null)) {                     
                vue_cart.loadcart();
            }
        },
        getAddress(){            
            this.visible = false;
            this.loading_get = true;		
			axios.post( api_location+"/getAddress?language="+language , "address_uuid="+this.address_uuid, {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){                          
                    this.add_address = true;
                    setTimeout(() => {                                                
                        this.$refs.ref_location_address.setData(response.data.details.data);
                   }, 100); 	 	 
                } else {
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                    });
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading_get = false;
			});         
        },        
        deleteAddress(){            
            this.loading_get = true;		
			axios.post( api_location+"/deleteAddress?language="+language , "address_uuid="+this.address_uuid, {
                headers: {
                    'Authorization': `token ${client_token}`,					
                }
            })
			.then(response => {																				
                if(response.data.code==1){                          
                    this.handleAddressRequirement();
                } else {
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                    });
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.loading_get = false;
			});         
        },
        //
    },
});
app_checkout_address.use(ElementPlus);
window.vm_checkout_address = app_checkout_address.mount('#location-checkout-address');

const app_location_restaurant_list = Vue.createApp({
    components : {
        'components-restaurant-list' : componentsRestaurantList,
    },
    data() {
        return {
            featured_id : null,
            city_id : null,
            area_id : null,
            state_id : null,
            postal_id : null,
            quick_filters : null,
            query :''
        }
    },
    mounted() {
        if ((typeof  featured_id !== "undefined") && ( featured_id !== null)) {            
            this.quick_filters = {
                quick_filters : [ featured_id ]
            };        
        } else if((typeof  cuisine_filter !== "undefined") && ( cuisine_filter !== null)){
            this.quick_filters = {
                cuisine : JSON.parse(cuisine_filter)
            };        
        }
        
        const location = getLocation();         
        this.city_id = location.city_id;
        this.area_id = location.area_id;
        this.state_id = location.state_id;
        this.postal_id = location.postal_id;        
    },
});
app_location_restaurant_list.use(ElementPlus);
app_location_restaurant_list.component("infinite-loading", V3InfiniteLoading.default);
window.vm_location_restaurant_list = app_location_restaurant_list.mount('#location-restaurant-list');