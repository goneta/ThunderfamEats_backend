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
		  iconColor:color,
		  classes: "primevue_toats",
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
	dialog(message){
		const $q = Quasar.Dialog;
		const dialog = $q.create({
			message: message,
			progress: true, 
			persistent: true,
			ok: false ,
			html:true
		});
		return dialog;
	},
};


const headerAuthorization = {
	headers: {
		'Authorization': `token ${token}`,					
	}
};

const translationVendor = !quasarComponents.empty(translation_vendor) ? JSON.parse(translation_vendor) : null;

const ComponentsSearchcustomer = {   
    emits: ['update:filter_customer'],     
    data() {
        return {
            customer_name :'',
			loading : false,
			old_search : '',
			options : [],
			translationVendor : []
        }
    },
    mounted() {
        this.getInitialCustomer();        
		this.translationVendor = translationVendor;
    },
    methods: {
        getInitialCustomer(){
            axios({
                method: 'GET',
                url: pos_api+"/getInitialCustomer?language="+language ,
                    data : {  					
                    },
                    headers: {
                        Authorization: `token ${token}`,
                    },
                }).then( result => {	 					
                    if(result.data.code==1){ 	
                        this.options = result.data.details.data;					
                    } else {		
                        this.options = [];
                    }                    					  
                }).catch(error => {	
                //
                }).then(data => {			                         
              });          
        },
        searchCustomer(val, update, abort){
            if (val.length < 2) {				
				if (!quasarComponents.empty(this.customer_name)) {
					update(() => {});
					return;
				}
				this.old_search = "";
				this.loading = false;
				//abort();
				update(() => {});
				return;
			}
	
			if (this.loading == val) {				
			    update(() => {});
			}

			this.loading = true;			
			setTimeout(() => {
			  update(() => {
				this.options = [];

				axios({
					method: 'GET',
					url: pos_api+"/searchCustomer?language="+language + "&q="+ val,
						data : {  					
						},
						headers: {
							Authorization: `token ${token}`,
						},
					}).then( result => {	 					
						if(result.data.code==1){ 	
							this.options = result.data.details.data;					
						} else {		
							this.options = [];
						}                    					  
					}).catch(error => {	
					//
					}).then(data => {			     
						this.loading = false;				
				  });          
				
			  });
			}, 300);
        },
        onSelect(data){
            if ((typeof  data !== "undefined") && ( data !== null)) {						
                console.log("onSelect",data.label);     
                this.$emit('update:filter_customer', data.label);
            }
        },        
    },
    template :`		
    <q-select 
    v-model="customer_name" 
    :options="options"
    :label="translationVendor.customer" 
    emit-valuex
    map-options
    @filter="searchCustomer"	
    @update:model-value="onSelect"    
    use-input
    clearable
    >
    </q-select>
    `
};

const ComponentsSendReceipt = {
    template: '#xchoose_receipt',
    props : ['title','enabled_email','enabled_whatsapp','enabled_webprint','enabled_print'],
    data() {
		return {
			modal:false,
			email_address :'',
			mobile_number :'',
			order_uuid :''
		}
	},
    computed: {
		ValidEmail() {		
			const email = this.email_address;
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return emailRegex.test(email);
		},
		ValidMobileNumber() {		
			const mobile = this.mobile_number;
			const mobileRegex = /^\+?[0-9]{10,}$/;
			return mobileRegex.test(mobile);
		},
	},
	methods: {
        isValidEmail(email) {		
			const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
			return emailRegex.test(email);
		},
		isValidMobileNumber(mobile) {		
			const mobileRegex = /^\+?[0-9]{10,}$/;
			return mobileRegex.test(mobile);
		},		
		sendReceipt(value){		
			const dialog = quasarComponents.dialog(translationVendor.sending_receipt + "<br/>"+ translationVendor.close_window);
			let postData = "sending_type="+value + "&email_address="+this.email_address + "&mobile_number="+this.mobile_number;
			postData+="&order_uuid="+this.order_uuid;
			axios.post( pos_api+"/sendReceipt?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){		
					this.modal = false;									
					quasarComponents.notify("mysuccess",response.data.msg,'info');
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');					
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				dialog.hide();
			});          								
		},		
    },
};


const ComponentsReceipt = {	
	template: '#xtemplate_receipt',	
	data() {
		return {
			modal : false,
			loading : false,
			order_uuid : '',
			payload: [
				"merchant_info",
				"items",
				"summary",
				"order_info",				
				"status_allowed_cancelled",								
				"order_delivery_status",											
			],
			merchant :[],
			order_info : [],
			order_items : [],
			order_summary : [],
			services : [],
			order_table_data : [],
			is_printing : false
		}
	},		
	computed: {
		hasBooking(){
			if (Object.keys(this.order_table_data).length > 0) {		
				return true;
			}
			return false;
		}	
	},
	methods: {
		beforeShow(){
			this.orderDetails();
		},
		orderDetails(){
			this.loading = true;
			let postData = {
				order_uuid : this.order_uuid,
				payload : this.payload
			};
			axios.post( pos_api+"/orderDetails?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){			
					let data = response.data.details.data;
					this.merchant = data.merchant;
					this.order_info = data.order.order_info;
					this.order_items = data.items;
					this.order_summary = data.summary;
					this.services = data.order.services;
					this.order_table_data = data.order_table_data;
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.loading = false;
			});          					
		},
		printReceipt(){
			this.is_printing = true;
			$(".printhis").printThis(); 
			setTimeout(() => {
				this.is_printing = false;
			 }, 1000); 
		}
	},
};      

const app_pos = Vue.createApp({
    components : {
        'components-searchcustomer' : ComponentsSearchcustomer,
        'components-send-receipt' : ComponentsSendReceipt,
        'components-receipt' : ComponentsReceipt,
    },
    data() {
        return {
            order_type :null,
            drawer : false,  
            q: '',
            loading : false,
            rows : [],
			rowPerPageLabel : translationVendor.record_per_page,
			paginationLabel : (start, end, total) => `${ start }-${ end } ${translationVendor.oflabel} ${ total }`,
            columns :[
				{
					name : 'order_id',
					field : 'order_id',
					label : translationVendor.table_order_id,
					align: 'left',
                    sortable: true,
				},
				{
					name : 'order_type',
					field : 'order_type',
					label : translationVendor.table_order_type,
                    sortable: true,
                    align: 'left',
				},
				{
					name : 'customer_name',
					field : 'customer_name',
					label : translationVendor.table_customer,
                    sortable: false,
                    align: 'left',
				},
                {
					name : 'total',
					field : 'total',
					label : translationVendor.table_amount,
                    sortable: true,
				},
                {
					name : 'date_created',
					field : 'date_created',
					label : translationVendor.table_date,
                    sortable: true,
                    align: 'center',
				},
                {
					name : 'status',
					field : 'status',
					label : translationVendor.table_status,
                    sortable: true,
				}
			],			
            rows : [],           
            pagination :{
                sortBy: 'order_id',
                descending: true,
                page: 1,
                rowsPerPage: 10,
                rowsNumber: 50
            },
            filters :[],
            attributes_data :[],
            awaitingSearch : false,
            order_uuid : null,
            payload : [
                //"merchant_info",
                "items",
                "summary",
                "order_info",
                // "progress",
                // "refund_transaction",
                // "status_allowed_cancelled",
                // "pdf_link",
                // "delivery_timeline",
                // "order_delivery_status",
                // "allowed_to_review",
                // "credit_card",
                // "driver",
            ],
            order_details : [],
            order_details_loading : false,
            filterDate : '',
            // filter_date : {
            //     from :'2020/07/08',
            //     to :'2020/07/17'
            // },
            filter_date :{},
            calendarProxy : false,
            filter_order_status :'',
            filter_customer : '',
            filter_proxy : false,
			socket_error : '',
			webSocket : null,
			calendarLocale : {
				days: translationVendor.calendar_days,
				daysShort: translationVendor.calendar_short_days,
				months: translationVendor.calendar_months,
				monthsShort: translationVendor.calendar_short_months,
				firstDayOfWeek: 1, 
				format24h: true,
				pluralDay: translationVendor.days
			}
        }
    },
    mounted() {        
        this.getAttributes();
        //this.$refs.tableRef.requestServerInteraction();	  
        //this.initFilterDate();
    },
    computed: {        
        printerList(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (Object.keys(this.attributes_data.printer_list).length > 0) {
				    return this.attributes_data.printer_list;
				}
			}
			return false;     
		},
        getTabList(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.tab_list;
			}
			return [];      
		},
        getStatusList(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.order_status_list;
			}
			return [];      
		},
        getStatusListValue(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.order_status_list_value;
			}
			return [];      
		},
        hasData(){
            if (Object.keys(this.rows).length > 0) {
                return true;
            }
            return false;
        },
        isSearch(){
			if(!quasarComponents.empty(this.q)){
				return true;
			}
			return false;
		},
        hasOrderDetails(){
            if (Object.keys(this.order_details).length > 0) {
				return true;
			}
			return false;
        },
        getOrderInfo(){
            if (Object.keys(this.order_details).length > 0) {
				return this.order_details.order.order_info;
			}
			return false;
        },
        getOrderItems(){
            if (Object.keys(this.order_details).length > 0) {
				return this.order_details.items;
			}
			return false;
        },
        getOrderSummary(){
            if (Object.keys(this.order_details).length > 0) {
				return this.order_details.summary;
			}
			return false;
        },
        getOrderCustomer(){
            if (Object.keys(this.order_details).length > 0) {
				return this.order_details.customer;
			}
			return false;
        },
        hasFilters(){
            if (Object.keys(this.filters).length > 0) {
				return true;
			}
			return false;
        }
    },
    watch: {
		q(newsearch,oldsearch){
			if (!this.awaitingSearch) {
			   if(quasarComponents.empty(newsearch)){
					return false;
			   }
			   setTimeout(() => {                  
                  this.filters ={
                      order_type: this.order_type,
                      q: this.q,
                  };   
			   }, 1000);
			}			
	        this.awaitingSearch = true;
		},      
	},
    methods: {
        resetFilters(){
            this.filters = [];
            this.filterDate = '';
            this.filter_order_status = '';
        },
        initFilterDate(){
            const today = luxon.DateTime.local();
            const startDate = today;
            const endDate = today.minus({ days: 7 });
            const formattedStartDate = startDate.toFormat("yyyy/MM/dd");
            const formattedEndDate = endDate.toFormat("yyyy/MM/dd");
            console.log("Start Date:", formattedStartDate);
            console.log("End Date:", formattedEndDate);
            this.filter_date = {
                from :formattedEndDate,
                to :formattedStartDate
            };
            this.filterDate = this.filter_date.from + " - " + this.filter_date.to;
        },
        rangeEnded(){            
            this.calendarProxy = false;
        },
        updateRangevalue(value){            
            if ((typeof  value !== "undefined") && ( value !== null)) {						
               this.filterDate = value.from + " - " + value.to;
            } else {
                this.filterDate ='';
            }
        },
        clearSearch(){
            this.q = '';
            this.filters ={
                order_type: this.order_type,
                q: this.q,
            };   
        },
        getAttributes(){
            axios({
				method: 'GET',
				url: pos_api+"/Orderhistoryattributes?language="+language,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){        
                        this.order_type = result.data.details.first_tab;                        
                        this.attributes_data = result.data.details;             											
					} else {						
                        this.attributes_data = [];
					}           
                    this.$refs.tableRef.requestServerInteraction();	           				                    
				}).catch(error => {	
				//
				}).then(data => {			     					
			  });          
        },
        updateOrderList(value){            
            this.filters ={
                'order_type': value
            };            
        },
        onRowClick(row){            
            this.getOrderDetails(row.order_uuid);
        },
        onRequest(props){              
            const { page, rowsPerPage, sortBy, descending } = props.pagination
            let filter = props.filter      

            const fetchCount = rowsPerPage === 0 ? pagination.rowsNumber : rowsPerPage;            
            const startRow = (page - 1) * rowsPerPage            
            
            this.loading = true;
			if(filter.length<=0){				
				filter = {
                   order_type: this.order_type || 'all'
                };
			}
            let postData = {
                startRow : startRow,
                fetchCount :fetchCount,
                filter : filter,
                sortBy : sortBy,
                descending :descending
            };						
            axios.post( pos_api+"/PosOrders?language="+language , postData, headerAuthorization )
            .then(response => {								
                if(response.data.code==1){	
                    this.order_uuid = response.data.details.order_uuid;
                    this.rows = response.data.details.data;
                    this.pagination.rowsNumber = response.data.details.total;
                    this.getOrderDetails(this.order_uuid);
                } else {                    
                    this.rows = [];
                }
                this.pagination.page = page
                this.pagination.rowsPerPage = rowsPerPage
                this.pagination.sortBy = sortBy
                this.pagination.descending = descending    	
            })
            .catch(error => {				
                console.error('Error:', error);
            }).then(data => {			     							
                this.loading = false;
                this.awaitingSearch = false;
            });                                      
        },        
        getOrderDetails(value){      
            this.$refs.bar.start();      
            this.order_details_loading = true;
            let postData = {
                order_uuid :value,
                payload : this.payload
            };
            axios.post( pos_api+"/orderDetails?language="+language , postData, headerAuthorization )
             .then(response => {								
                 if(response.data.code==1){				
                     this.order_details = response.data.details.data;
                 } else {
                     this.order_details = [];
                 }
             })
             .catch(error => {				
                 console.error('Error:', error);
             }).then(data => {			     							
                this.order_details_loading = false;
                this.$refs.bar.stop();
             });          
        },
        applyFilters(){
            console.log('applyFilters');
            this.filters ={
                filter_order_status: this.filter_order_status,
                filter_customer: this.filter_customer,
                filter_date : this.filter_date
            };   
            this.filter_proxy = false;
        },
        getLabel(value) {
            if (Object.keys(this.attributes_data).length > 0) {
                const option = this.attributes_data.tab_list.find(option => option.value === value);
                return option ? option.label : '';
            }
        },
        viewOrder(){
            console.log("getOrderInfo.view_order_link",this.getOrderInfo.view_order_link);
            window.parent.location.href = this.getOrderInfo.view_order_link;
        },
        chooseReceipt(){
            console.log("chooseReceipt",this.getOrderInfo);
            this.$refs.choose_receipt.order_uuid = this.getOrderInfo.order_uuid;
			this.$refs.choose_receipt.email_address = this.getOrderInfo.contact_email;
			this.$refs.choose_receipt.mobile_number = this.getOrderInfo.contact_number;
			this.$refs.choose_receipt.modal = true;
        },
        webPrint(){            
            this.$refs.receipt.order_uuid = this.getOrderInfo.order_uuid;
			this.$refs.receipt.modal = true;
        },
		SwitchPrinter(printerId,printerModel){			
			if(printerModel=="feieyun"){
				this.printUsingThermal(printerId);
			} else {
				this.wifiPrint(printerId);
			}
		},
        printUsingThermal(printer_id){			
			const dialog = quasarComponents.dialog(translationVendor.printing + "<br/>"+ translationVendor.close_window);
			let postData = "printer_id="+printer_id;
			postData+="&order_uuid="+this.getOrderInfo.order_uuid;
			axios.post( pos_api+"/printUsingThermal?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){		
					this.modal = false;									
					quasarComponents.notify("mysuccess",response.data.msg,'info');
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');					
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				dialog.hide();
			});          					
		},
		wifiPrint(printer_id){
			const dialog = quasarComponents.dialog(translationVendor.printing + "<br/>"+ translationVendor.close_window);
			let postData = "printer_id="+printer_id;
			postData+="&order_uuid="+this.getOrderInfo.order_uuid;			
			axios.post( pos_api+"/wifiPrint?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){		
					//this.modal = false;						
					this.ConnectToPrintServer();
					   setTimeout(() => {
						this.sendServerToPrinter(response.data.details.data,response.data.msg);
					}, 500); 				
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');					
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				dialog.hide();
			});          					
		},
		ConnectToPrintServer(){						
			let printer_server = !quasarComponents.empty(printerServer)?printerServer:null;
			if(this.webSocket==null){
				console.log("CONNECT ConnectToPrintServer");
				this.webSocket = new WebSocket(printer_server);
			
				this.webSocket.onopen = function(event) {
					console.log('WebSocket is open now.');
				};
		
				// Define the onmessage event handler
				this.webSocket.onmessage = function(event) {
					console.log('Received message from server:', event.data);
				};
		
				// Define the onclose event handler
				this.webSocket.onclose = function(event) {
					console.log('WebSocket is closed now.');
					this.webSocket = null;
				};
		
				// Define the onerror event handler
				this.webSocket.onerror = (event)=> {		
					this.webSocket = null;		
					this.socket_error = translationVendor.websocket_error;
					if (event instanceof ErrorEvent) {
						this.socket_error = event.message;
					}							
				};
			}			
		},
		sendServerToPrinter(data,messageOk){						
			if (this.webSocket.readyState === WebSocket.OPEN) {
				this.webSocket.send(JSON.stringify(data));								
				quasarComponents.notify("mysuccess",messageOk,'info');
			} else {
				if(quasarComponents.empty(this.socket_error)){
					this.socket_error = translationVendor.websocket_is_not_open + this.webSocket.readyState;
				}				
				quasarComponents.notify("myerror",this.socket_error,'error');				
			}
		},
        // end methods
    },
});

app_pos.use(Quasar,{
    config : {
        screen :{},
		notify: {},
		loadingBar: { skipHijack: true },
        loading : {},
		ripple: {}
	}
});
app_pos.mount('#app-pos');