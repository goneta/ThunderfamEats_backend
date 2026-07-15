

const someWords = JSON.parse(some_words);
const LocaleLang = {
    el: {
        pagination: {                
            pagesize: '',
            total: someWords.total
        },
    }    
};

const ComponentsPauseItems = {
    props : ['label','pause_time_list','default_time'],
    data() {
        return {
            modal : false,            
            unavailable_until : this.default_time,
            formData : {
                days :'',
                hours :''
            },
            daysList: [],
            hoursList: [],
            message : '',
            itemName : null,
            item_token : null,
            loading_submit : false,
            dialogWidth: "50%",
        }
    },
    mounted() {
        this.generateDaysList(30);
        this.generateHoursList(23);
        this.message = this.label.pause_message;
        this.updateDialogWidth();
        window.addEventListener("resize", this.updateDialogWidth);
    },
    beforeDestroy() {
        window.removeEventListener("resize", this.updateDialogWidth);
    },
    computed: {
        formattedMessage() {
            return this.message.replace("{item_name}", this.itemName);
        },
    },
    methods: {
        updateDialogWidth() {
            const screenWidth = window.innerWidth;
            this.dialogWidth = screenWidth < 768 ? "90%" : "50%";
        },
        setData(value){
            console.log("setData",value);    
            this.itemName = value.item_name;        
            this.item_token = value.item_token;
            this.modal = true;
        },
        generateDaysList(maxDays) {
            this.daysList = Array.from({ length: maxDays + 1 }, (_, index) => ({
              value: index,
              label: `${index} ${index === 1 ? this.label.day : this.label.days}`,
            }));
        },
        generateHoursList(maxHours) {
            this.hoursList = Array.from({ length: maxHours + 1 }, (_, index) => ({
              value: index,
              label: `${index} ${index === 1 ? this.label.hour : this.label.hours}`,
            }));
        },
        onSubmit(){
             this.loading_submit = true;
             let data = "";
             data+="item_token="+this.item_token;
             data+="&unavailable_until="+this.unavailable_until;
             data+="&days="+this.formData.days;
             data+="&hours="+this.formData.hours;
             axios.post( apibackend+"/PauseItem" , data).then(response => {																				
                if(response.data.code==1){		 
                    this.modal = false;
                    this.$emit("afterSave",response.data.details);
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
				this.loading_submit = false;
			});         
        },
        //
    },
    template: '#xtemplate_pause_items',
};

const app_items_availability = Vue.createApp({	
    components : {
        'pause-items': ComponentsPauseItems,	   
    },
	data() {
		return {
			loading : false,          
            list_data : null,
            current_page : 1,
            query : null,
            searchTimeout : null,
            filter_pause : false,
            loading_search : false,
            page_size : parseInt(list_limit),
            pageSizes: JSON.parse(paginationSize),            
		}
	},
    mounted() {
        this.getItemList(1);
    },
    computed: {
        getItems(){
            if(this.list_data){
                return this.list_data.items?this.list_data.items:null;
            }
            return null;
        },
        hasItems(){            
            return this.list_data?.items && Object.keys(this.list_data.items).length > 0;
        },
        getTotalItems(){
            if(this.list_data){
                return this.list_data.total_items?this.list_data.total_items:null;
            }
            return 0;
        },
        getTotalDisplay(){
            if(this.list_data){
                return this.list_data.total_display_items?this.list_data.total_display_items:null;
            }
            return '';
        }
    },
    methods: {        
        getParams(){
            let params = "&filter_pause="+ (this.filter_pause?this.filter_pause:'');
            params+="&query="+ (this.query?this.query:'');            
            params+="&page_size="+this.page_size;
            return params;
        },
        ApplyFilter(){
            this.getItemList(1);
        },
        ClearSearch(){
            this.getItemList(1);
        },
        filterPauseitems(){            
            this.filter_pause = !this.filter_pause;
            this.loading_search = true;
            axios.get(apibackend+"/getItemList?"+ this.getParams()  ).then(response => {                                
                if(response.data.code==1){      
                    this.list_data =  response.data.details;    
                    this.current_page = response.data.details.current_page;                                                 							
                } else {          
                    this.list_data = null;          
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {			
                this.loading_search = false;			
            });         
        },
        getItemList(value){
            this.loading = true;
            const params = this.getParams();
            axios.get(apibackend+"/getItemList?page="+value + params ).then(response => {                                
                if(response.data.code==1){      
                    this.list_data =  response.data.details;    
                    this.current_page = response.data.details.current_page;                                                 							
                } else {          
                    this.list_data = null;          
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {			
                this.loading = false;			
            });         
        },
        paginationChange(value){            
            this.getItemList(value);
        },
        OptionsPause(value){
            console.log("OptionsPause",value);
            if(!value.unavailable_until){
                this.$refs.ref_pause.setData(value);
            } else {
                this.ResumeItems(value);
            }
        },
        ResumeItems(value){
            //this.loading = true;
            value.loading = true;
            let data = "";
            data+="item_token="+value.item_token;            
            axios.post( apibackend+"/ResumeItem" , data).then(response => {																				
               if(response.data.code==1){		 
                   this.updateItems(response.data.details);
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
               //this.loading = false;
           });         
        },
        // Assuming `items` is your array of data in Vue.js
        updateItems(updatedItems) {            
            updatedItems.forEach(updatedItem => {                
                const index = this.list_data.items.findIndex(item => item.item_id === updatedItem.item_id);                
                if (index !== -1) {                  
                  this.list_data.items[index]=updatedItem;
                }
            });
        },  
        afterSave(value){
            this.updateItems(value);
        }
    },
});
app_items_availability.use(ElementPlus,{
    locale : LocaleLang
});
const vm_items_availability = app_items_availability.mount('#vue-items-availability');