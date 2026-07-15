
const someWords = JSON.parse(some_words);
const LocaleLang = {
    el: {
        pagination: {                
            pagesize: '',
            total: someWords.total
        },
    }    
};

const app_featured = Vue.createApp({	
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
            sortField : null,
            sortOrder : null,
            selected_rows :[]
        }
    },
    mounted() {                
        this.getItemList(1);
        this.filter_by_list = JSON.parse(filter_by);
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
            return Object.keys(this.selected_rows).length > 0;
        },
    },
    methods: {
        StatusColor(value){
            if(value=="rejected"){
                return 'warning';
            } else if ( value=="approved"){
                return 'success';
            } else {
                return 'primary';
            }
        },
        handleSortChange({ prop, order }){            
            this.sortField = prop;
            this.sortOrder = order === 'ascending' ? 'asc' : 'desc';
            this.getItemList( this.current_page>0?this.current_page:1 );
        },
        getParams(){
            let params = "&filter_by="+ (this.filter_by?this.filter_by:'');
            params+="&query="+ (this.query?this.query:'');
            params+="&filter_all_featured="+ (this.filter_all_featured?this.filter_all_featured:'');
            params+="&page_size="+this.page_size;
            params+="&sort_field="+(this.sortField?this.sortField:'');
            params+="&sort_order="+(this.sortOrder?this.sortOrder:'');
            return params;
        },
        filteFeatured(){
            this.filter_all_featured = !this.filter_all_featured;
            this.getItemList(1);
        },        
        ApplyFilter(){
            this.getItemList(1);
        },
        ClearSearch(){
            this.getItemList(1);
        },
        getItemList(value){
            this.loading = true;
            const params = this.getParams();
            axios.get(api_url+"/getItemsSuggested?page="+value + params ).then(response => {                                
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
        toggleFeatured(value){
            value.loading = true;
            const formData = new FormData();
            formData.append('item_id', value.item_id);
            formData.append('is_featured', value.is_featured?1:0);
            formData.append('featured_priority', value.featured_priority);            
            axios.post( api_url+"/updateFeatureItems", formData)
			.then(response => {																				
                if(response.data.code==1){                    
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
				value.loading =false;
			});         
        },     
        handleApprove(index,row){                     
            row.loading = true;
            const formData = new FormData();
            formData.append('id', row.id);            
            axios.post( api_url+"/ApprovedSuggestItems", formData)
			.then(response => {																				
                if(response.data.code==1){            
                    this.list_data.items.splice(index, 1);        
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
				row.loading =false;
			});         
        },
        handleReject(index,row){

            ElementPlus.ElMessageBox.prompt(someWords.reason_for_rejection, someWords.reject, {
                confirmButtonText: someWords.ok,
                cancelButtonText: someWords.cancel,                 
              })
                .then(({ value }) => {
                   
                    row.loading = true;
                        const formData = new FormData();
                        formData.append('id', row.id);            
                        formData.append('reason', value); 
                        axios.post( api_url+"/RejectSuggestItems", formData)
                        .then(response => {																				
                            if(response.data.code==1){         
                                this.list_data.items.splice(index, 1);
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
                            row.loading =false;
                        });         

                    //
                })
                .catch(() => {                  
            });            
        },
        handleSelectionChange(selection){            
            this.selected_rows = selection;
        },
        getSelectedIds(){
            const params = [];
            Object.entries(this.selected_rows).forEach(([key, items]) => {
                params.push(items.item_id);
            });
            return params;
        },
        markAllApproved(){
            const params = this.getSelectedIds();            
            this.loading = true;
            axios.post( api_url+"/markAllApproved", {
                item_ids : params
            })
            .then(response => {																				
                if(response.data.code==1){         
                    this.selected_rows = [];
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'success',
                    });          
                    this.getItemList(1);
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
        confirmMarkRejected(){
            ElementPlus.ElMessageBox.confirm(
                someWords.confirm_reject_all,
                someWords.confirm,
                {
                  confirmButtonText: someWords.ok,
                  cancelButtonText: someWords.cancel,
                  type: 'warning',
                }
              ).then(() => {
                   this.markAllRejected();
                }).catch(() => {
                  
            });
        },
        markAllRejected(){
            console.log("markAllRejected",this.selected_rows);
            const params = this.getSelectedIds();            
            this.loading = true;
            axios.post( api_url+"/markAllRejected", {
                item_ids : params
            })
            .then(response => {																				
                if(response.data.code==1){         
                    this.selected_rows = [];
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'success',
                    });          
                    this.getItemList(1);
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
    },
});


app_featured.use(ElementPlus,{
    locale : LocaleLang
});
const vm_featured = app_featured.mount('#vue-feature-items');