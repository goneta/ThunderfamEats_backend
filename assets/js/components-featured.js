

window.componentsFeaturedList = {
    template: '#xtemplate_restaurant_list',
    props : ['title','is_filters'],
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
            this.page++;        
            let params = "language="+language + "&query="+featured_id+"&page="+this.page;                       
            
             axios.get(ajaxurl+"/GetFeaturedLocation?" + params ).then(response => {                                
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
    }
};