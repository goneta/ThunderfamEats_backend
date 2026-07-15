

const ComponentsTax = {
    props : ['label'],
    template: '#xtemplate_tax_table',
    data() {
        return {
            modal : false,
            tax_name : null,
            tax_rate : 0,
            loading : false,
            active : true,
            data : null, 
            loading_fetch : false,  
            tax_uuid : null     
        }
    },
    mounted() {
        this.fecthTaxList();
    },
    computed: {
        getItems(){
            return this.data;
        },
    },
    methods: {
        onDialogClose(){
            console.log("onDialogClose");
            this.tax_name = '';
            this.tax_rate = 0;
            this.tax_uuid = null;
        },
        showTaxform(){
            console.log("showTaxform");
            this.modal = true;
            this.onDialogClose();
        },
        async handleEdit(value){
            console.log("handleEdit",value);
            try {
                this.loading_fetch = true;
                const requestData = new URLSearchParams({
                    tax_uuid : value
                }).toString();
                const response = await axios.post(apibackend+"/getTax", requestData);
                const response_data = response.data.details;
                console.log("response",response.data.code);
                console.log("response",response_data);
                if(response.data.code!=1){
                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'warning',
                    });
                    return;
                }

                this.tax_uuid = response_data.tax_uuid;
                this.tax_name = response_data.tax_name;
                this.tax_rate = response_data.tax_rate;
                this.active = response_data.active==1?true:false;
                this.modal = true;
                
            } catch (error) {
                
            } finally {
                this.loading_fetch = false;
            }
        },
        async handleDelete(value){
            ElementPlus.ElMessageBox.confirm(
                this.label.are_you_sure,
                this.label.confirm,
                {
                  confirmButtonText: this.label.ok,
                  cancelButtonText: this.label.cancel,
                  type: 'warning',
                }
              ).then(() => {                   
                   this.deleleTax(value);
                }).catch(() => {                  
            });
        },
        async deleleTax(value){
            try {
                this.loading_fetch = true;
                const requestData = new URLSearchParams({
                    tax_uuid : value
                }).toString();
                const response = await axios.post(apibackend+"/taxDelete", requestData);
                console.log("response",response);
                this.fecthTaxList();
            } catch (error) {
                
            } finally {
                this.loading_fetch = false;
            }
        },
        fecthTaxList(){
            this.loading_fetch = true;            
            axios.get(apibackend+"/fecthTaxList").then(response => {   
                console.log("response",response);
                if(response.data.code==1){        
                    this.data = response.data.details.data;                  
                } else {
                    this.data = null;
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {			
                this.loading_fetch = false;			
            });         
        },
        async submitForm(){            
            try {
                this.loading = true;                
                const requestData = new URLSearchParams({
                    tax_name : this.tax_name,
                    tax_rate : this.tax_rate,
                    active : this.active ? 1 : 0,
                    tax_uuid : this.tax_uuid ?? ''
                }).toString();
                const response = await axios.post(apibackend+"/saveTaxrate", requestData);
                console.log("response",response);

                if(response.data.code==1){		     
                    this.modal = false;                
                    this.fecthTaxList();
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

            } catch (error) {                
                ElementPlus.ElNotification({			
                    title: "",			
                    message: error,
                    position: 'bottom-right',
                    type: 'warning',
                });
            } finally {
                this.loading = false;
            }
        }
    },
};

const app_tax = Vue.createApp({
    components : {
        'tax-table': ComponentsTax,	   
    },
    data() {
        return {
            tabs : 'standard'
        }
    },
    mounted() {        
        this.tabs = tax_type ?? 'standard';
    },
});

app_tax.use(ElementPlus,{
    //locale : LocaleLang
});
const vm_tax = app_tax.mount('#vue-tax-new');