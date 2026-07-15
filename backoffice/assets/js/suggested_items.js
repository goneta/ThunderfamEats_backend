
const someWords = JSON.parse(some_words);
const LocaleLang = {
    el: {
        pagination: {                
            pagesize: '',
            total: someWords.total
        },
    }    
};

const app_suggested = Vue.createApp({	
    components : {
        'items-modal': ItemsModal
    },
    data() {
        return {
            loading : false,          
            list_data : null,
            current_page : 1,
            query : null,
            loading_search : false,
            filter_featured : false,
            filter_by_list : null,
            filter_by : null,
            filter_all_featured : null,
            page_size : parseInt(list_limit),
            pageSizes: JSON.parse(paginationSize),
            modal : false,
            selectable : undefined,
            selected_rows : null
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
        },      
        hasSelectedRows(){
            if(!this.selected_rows){
                return false;
            }
            if (Object.keys(this.selected_rows).length > 0) {
                return true;
            }
            return false;
        }
    },
    methods: {       
        handleSelectionChange(selection){            
            this.selected_rows = selection;
        },
        StatusColor(value){
            if(value=="rejected"){
                return 'warning';
            } else if ( value=="approved"){
                return 'success';
            } else {
                return 'primary';
            }
        },
        showModal(){            
            this.$refs.items_modal.showModal();
        },
        getItemList(value){
            this.loading = true;            
            axios.get(apibackend+"/SuggestedItems?page="+value +"&page_size="+this.page_size ).then(response => {                                
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
        afterSelecteditems(value){            
            this.modal = true;
            axios
            .post(
              apibackend + "/SaveSuggestedItems",
              { items: value }, // Send data as an object
              {
                headers: {
                  "Content-Type": "application/json", // Set the Content-Type header
                },
              }
            )
            .then((response) => {
              if (response.data.code == 1) {
                this.getItemList(1);
                ElementPlus.ElNotification({
                    title: "",
                    message: response.data.msg,
                    position: "bottom-right",
                    type: "success",
                  });
              } else {
                ElementPlus.ElNotification({
                  title: "",
                  message: response.data.msg,
                  position: "bottom-right",
                  type: "warning",
                });
              }
            })
            .catch((error) => {
              // Handle error
              console.error(error);
            })
            .then(() => {
                this.modal = false;
            });          
        },
        handleDelete(index,row){            
            this.modal = true;     
            axios.get(apibackend+"/DeleteSuggested?id="+row.id  ).then(response => {                                
                if (response.data.code == 1) {
                    this.getItemList(1);
                    ElementPlus.ElNotification({
                        title: "",
                        message: response.data.msg,
                        position: "bottom-right",
                        type: "success",
                      });
                } else {
                    ElementPlus.ElNotification({
                      title: "",
                      message: response.data.msg,
                      position: "bottom-right",
                      type: "warning",
                    });
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {			
                this.modal = false;
            });                 
        },
        deleteSelectedRows(){
            this.modal = true;
            axios
            .post(apibackend + "/DeleteSuggestedRows",{ items: this.selected_rows }, 
              {
                headers: {
                  "Content-Type": "application/json", 
                },
              }
            ).then((response) => {
              if (response.data.code == 1) {
                this.selected_rows = null;
                this.getItemList(1);
                ElementPlus.ElNotification({
                    title: "",
                    message: response.data.msg,
                    position: "bottom-right",
                    type: "success",
                  });
              } else {
                ElementPlus.ElNotification({
                  title: "",
                  message: response.data.msg,
                  position: "bottom-right",
                  type: "warning",
                });
              }
            }).catch((error) => {
              // Handle error
              console.error(error);
            }).then(() => {
                this.modal = false;
            });                      
        }
    },
});
app_suggested.use(ElementPlus,{
    locale : LocaleLang
});
const vm_suggested = app_suggested.mount('#vue-suggested-items');