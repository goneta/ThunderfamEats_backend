var $global_items = [];
var $global_addons = [];
var $global_addon_items = [];
var $global_item_addons = [];
var $new_item_addons

const ComponentsMoney = {
	props: ['ajax_url', 'amount','price_format'],
	data(){
	   return {						
		 data : 0,
		 config : JSON.parse(money_config)		
	   };
    },   	
	mounted() {	   	     	     	     	     	    
   	   this.data = window["v-money3"].format(this.amount, this.config);   	   
    },   
	watch: {
		price_format(newval,oldval){			
			this.config = newval;	
		}
	}, 
    updated(){   	
   	   this.data = window["v-money3"].format(this.amount, this.config);
    },    
    methods :{
    	formatNumber(data){
    		return window["v-money3"].format(data, this.config);
    	},
    },
    template:`		
    {{data}}
    `
};

const ComponentsTextdescription = {
    props: {
        label: {
            type: String,
            required: false,
        },
        description: {
          type: String,
          required: true,
        },
        maxLength: {
          type: Number,
          default: 100,
        },
    },
    data() {
        return {
            isExpanded: false,
        }
    },
    computed: {
        truncatedDescription() {
          return this.description.length > this.maxLength
            ? this.description.slice(0, this.maxLength) + "..."
            : this.description;
        },
        showReadMore() {
          return this.description.length > this.maxLength;
        },
    },
    methods: {
        toggleReadMore() {
          this.isExpanded = !this.isExpanded;
        },
    },
    template:`
     <p class="text-descriptions">
      <span v-html="isExpanded ? description : truncatedDescription" ></span>
      <span v-if="showReadMore" @click="toggleReadMore" class="read-more">
        {{ isExpanded ? label.read_less : label.read_more }}
      </span>
    </p>
    `
};

window.componentsItemDialog = {
    props : ['title','label'],
    components : {
        'money-format': ComponentsMoney,
        'text-description' : ComponentsTextdescription
    },
    data() {
        return {
            merchant_id : 0,   		
   			menu_loading : true,
   			menu_data: [],
   			items : [],   	
   			item_addons : [],
   			item_addons_load : false,
   			size_id : 0,
   			disabled_cart : true,
   			item_qty : 1,
   			item_total : 0,
   			add_to_cart : false,   
   			meta : [],   			
   			special_instructions : '',
   			sold_out_options : [],
   			if_sold_out : 'substitute',  
   			view_data : [] ,
		    item_loading : false,	
			item_in_cart : 0,
			merchant_data : [],
			items_not_available : [],
			category_not_available : [],
			menu_layout :'list',
			q: '',
			search_menu_data : [],
			allergen : [],
			allergen_data : [],
			item_gallery : [],
			image_featured : '',
			money_config : '',
			dish : [],
            cart_data : null
        }
    },
    updated () {    	    			
    	if(this.item_addons_load==true){
    	    this.ItemSummary();      	        	    
    	}    	    	
    }, //      
    methods: {
        viewItem(data){   
            this.special_instructions = '';
            $('#itemModal').modal('show'); 

            var $item_uuid = data.item_uuid;
    		var $cat_id = data.cat_id;
            let merchant_id = data.merchant_id;
            
            var $params;     	
			let currency_code = getCookie('currency_code');
			currency_code = !empty(currency_code)?currency_code:'';

            $params="merchant_id=" + merchant_id;
	 		$params+="&item_uuid="+ $item_uuid;
	 		$params+="&cat_id="+ $cat_id;
			$params+="&currency_code="+ currency_code;
            $params+="&cart_uuid="+getCookie('cart_uuid');

            $params+=addValidationRequest();	 		
			this.item_loading = true; 

            axios.post( ajaxurl+"/getMenuItem?language="+language ,$params)
			.then(response => {
                if(response.data.code==1){                                               
                    const details = response.data.details;
                    $global_items = details.data.items;                    
                    $global_addons = details.data.addons;				                    
                    $global_addon_items = details.data.addon_items;                    
                    $global_item_addons = details.data.items.item_addons;	

                    this.cart_data = details.cart_data;                    
                    
                    var $meta = details.data.meta;                    
                    this.money_config = details.money_config;				
                    this.item_gallery = details.data.meta.item_gallery;
                    this.image_featured = '';
                    this.renderGallery();                    

                    var $meta_details = details.data.meta_details;
                    var $new_meta = {
                        'cooking_ref' : [],
                        'ingredients' : [],
                        'dish' : [],					
                    };	 	    

                    let $ingredients_preselected = details.data.items.ingredients_preselected;				

                    if( !empty($meta)){
                        $.each($meta,function(key, item) {						
                            $.each(item,function(key2, item2) {	 	    					 	    		
                               if(!empty($meta_details[key])){
                                   if(!empty($meta_details[key][item2])){
                                       let $defaul_check = false;
                                       if(key=="ingredients" && $ingredients_preselected){
                                           $defaul_check = true;
                                       }
                                       $new_meta[key].push({
                                       'meta_id': $meta_details[key][item2].meta_id,
                                       'meta_name': $meta_details[key][item2].meta_name,
                                       'checked': $defaul_check
                                       });	 	    			
                                   }
                               }
                            });
                        });
                    }	 	                        

                    var $price = $global_items.price;	 	    	
	 	    	    var $size_id = Object.keys($price)[0];	 
                     
                    this.item_qty = 1;
                    this.items = $global_items;                    
                    this.size_id = $size_id;	                     	   
                    this.meta =  $new_meta;                               
                    this.getSizeData($size_id);	 
                    this.sold_out_options = details.sold_out_options;                    
                } else {                  
                    this.cart_data = null;
                    $('#itemModal').modal('hide');                       
                }
			})
			.catch(error => {								
			}).then(data => {		
				this.item_loading = false;
			});         
        },
        setItemSize(event){    	 	
            var $size_id = event.currentTarget.firstElementChild.value;    	 	
            this.size_id = $size_id;
            this.getSizeData($size_id);       	 	            
        },
        getSizeData($size_id){            

            $new_item_addons = []; var $sub_items=[];

            if( !empty($global_item_addons[$size_id]) ){
                $.each($global_item_addons[$size_id],function(key, item) { 
                    if(!empty($global_addons[$size_id])){
                        if(!empty($global_addons[$size_id][item])){                            
                            $global_addons[$size_id][item].subcat_id;                            
                            $.each($global_addons[$size_id][item].sub_items,function(key2, item2) {                                
                               if ((typeof $global_addon_items[item2] !== "undefined") && ( $global_addon_items[item2] !== null)) { 	                                      
                                   $sub_items.push({
                                   'sub_item_id':$global_addon_items[item2].sub_item_id,
                                   'sub_item_name':$global_addon_items[item2].sub_item_name,
                                   'item_description':$global_addon_items[item2].item_description,
                                   'price':$global_addon_items[item2].price,
                                   'pretty_price':$global_addon_items[item2].pretty_price,
                                   'checked':false,
                                   'disabled':false,
                                   'qty':1
                                   }); 	   
                               }
                            }); 	    			                            
                                                         
                            $new_item_addons.push({
                                'subcat_id' : $global_addons[$size_id][item].subcat_id,
                                'subcategory_name' : $global_addons[$size_id][item].subcategory_name,
                                'subcategory_description' : $global_addons[$size_id][item].subcategory_description,
                                'multi_option' : $global_addons[$size_id][item].multi_option,
                               'multi_option_min' : $global_addons[$size_id][item].multi_option_min,
                                'multi_option_value' : $global_addons[$size_id][item].multi_option_value,
                                'require_addon' : $global_addons[$size_id][item].require_addon,
                                'pre_selected' : $global_addons[$size_id][item].pre_selected,
                                'sub_items_checked':'',
                                'sub_items':$sub_items
                            });	 	
                            $sub_items = [] ;
                        }
                    } 	    		
                });
            }

                    
            this.item_addons = $new_item_addons;	
            this.item_addons_load = true; 	    	
        },
        renderGallery(){
			setTimeout(() => {			
				let $is_rtl = false;
				if ((typeof is_rtl !== "undefined") && ( is_rtl !== null)) {
					$is_rtl = is_rtl=='1'?true:false;
				}   													 
				$(this.$refs.owl_item_gallery).owlCarousel({
					rtl : $is_rtl,
					loop:false,
					margin:10,
					nav:false,
					dots:false,
					responsive:{
						0:{
							items:1
						},
						600:{
							items:3
						},
						1000:{
							items:5
						}
					}					
				});
			}, 10);
		},
		setImage(data){
			this.image_featured = data;
		},
        ItemSummary(data){    	      	        	
    		 
            $item_total = 0;
            var $required_addon = [];	
            var $required_addon_added = [];
            let $min_addon = [];
            let $min_addon_added = [];
                                      
            if(!empty(this.items.price)){
                if(!empty(this.items.price[this.size_id])){
                    var item = this.items.price[this.size_id];
                    if( item.discount>0){
                        $item_total+=( this.item_qty * parseFloat(item.price_after_discount) );
                    } else $item_total+=( this.item_qty * parseFloat(item.price) );
                }    		 
            }    	
            
            this.item_addons.forEach((item,index) => {		
                       
              //dump("=>" + item.multi_option);
              if ( item.require_addon==1){
                     $required_addon.push(item.subcat_id);
              }
                           
              if(item.multi_option=="custom"){				  
                    var total_check = 0;    	  	 
                 let multi_option_min = item.multi_option_min;
                    var multi_option_value = item.multi_option_value; 

                 if(multi_option_value>0){
                   $min_addon.push({
                       'subcat_id': item.subcat_id,
                       'min':multi_option_min,
                       'max':multi_option_value 
                   });
                 }				  

                    var item_index=[]; var item_index2=[];   	    	  	 	  
                    item.sub_items.forEach((item2,index2) => {		       	  	 
                         if(item2.checked==true){
                          total_check++;    	     	  	 			
                          $item_total+=( this.item_qty * parseFloat(item2.price) );
                          $required_addon_added.push( item.subcat_id );						
                       } else item_index.push(index2);
                       
                       if(item2.disabled==true){
                          item_index2.push(index2);
                       }
                       
                    });
                 
                 
                 $min_addon_added[item.subcat_id] = {
                   'total' : total_check
                 };
                 
                    if(total_check>=multi_option_value){		       	  	 
                         item_index.forEach((item3,index3) => {			       	  	 
                             this.item_addons[index].sub_items[item3].disabled = true;
                         });
                    } else {		       	  	 
                         item_index2.forEach((item3,index3) => {			       	  	 
                             this.item_addons[index].sub_items[item3].disabled = false;
                         });
                    }		
                    
              } else if ( item.multi_option=="one" ){				       	   		       	   
                     item.sub_items.forEach((item2,index2) => {
                           if( item2.sub_item_id == item.sub_items_checked ) {		       	   	     
                              $item_total+=( this.item_qty * parseFloat(item2.price) );
                              $required_addon_added.push( item.subcat_id );
                           }		       	   
                     });
              } else if ( item.multi_option=="multiple" ){	   
                var item_index=[]; 
                let multi_option_min = item.multi_option_min;
                var multi_option_value = item.multi_option_value;  
                var limit = 0;

                if(multi_option_value>0){
                   $min_addon.push({
                       'subcat_id': item.subcat_id,
                       'min':multi_option_min,
                       'max':multi_option_value 
                   });
                }	

                item.sub_items.forEach((item2,index2) => {
                   if(item2.checked==true){	
                       $item_total+=( item2.qty * parseFloat(item2.price) );
                       $required_addon_added.push( item.subcat_id );						
                       limit += item2.qty;														
                   }
                   item_index.push(index2);
                }); 

                $min_addon_added[item.subcat_id] = {
                   'total' : limit
                 };

                this.item_addons[index].qty_selected =limit;
                if ( this.item_addons[index].qty_selected>= multi_option_value){
                   item_index.forEach((item3,index2) => {
                       this.item_addons[index].sub_items[item3].disabled = true;
                   }); 
               } else {
                   item_index.forEach((item3,index2) => {
                       this.item_addons[index].sub_items[item3].disabled = false;
                   }); 
               }

              } /*endif custom*/
              
           });/* end loop*/
                       
           
           this.item_total = $item_total;		    
           
           var $required_meet=true;			
           if($required_addon.length>0){
               $.each($required_addon, function(i, val){					
                   if($required_addon_added.includes(val)===false){						
                      $required_meet = false;
                      return false;
                   }
               });
           }

           // CHECK COOKING REF			
           if(this.items.cooking_ref_required){
               let $cooking_ref_check = false;
               if (Object.keys(this.meta.cooking_ref).length > 0) {
                   Object.entries(this.meta.cooking_ref).forEach(([cooking_key, cooking_items]) => {
                       if(cooking_items.checked){
                           $cooking_ref_check = true;
                       }
                   });
               }				
               if(!$cooking_ref_check){
                   $required_meet = false;
               }
           }

           if (Object.keys($min_addon).length > 0) {				
               let min_value,min_selected;
               Object.entries($min_addon).forEach(([key_min_addon, items_min_addon]) => {					
                   min_value = parseInt(items_min_addon.min);
                   if($min_addon_added[items_min_addon.subcat_id]){
                       min_selected = parseInt($min_addon_added[items_min_addon.subcat_id].total);
                   }					
                   if(min_selected>0){
                       if(min_value>min_selected){
                           $required_meet  = false;							
                       }
                   }					
               });
           }
                           
           //dump("required_meet=>"+$required_meet);
           if($required_meet){
               this.disabled_cart = false;
           } else this.disabled_cart = true;
                                                   
       }, // end ItemSummary   
       CheckaddCartItems(){                     
          if(this.cart_data){
             if(this.items.merchant_id!=this.cart_data.merchant_id){
                this.label.your_order = this.label.your_order.replace("{restaurant_name}", this.cart_data.restaurant_name);
                ElementPlus.ElMessageBox.confirm(
                    this.label.your_order,
                    this.label.new_order+"?",
                    {
                      confirmButtonText: this.label.yes,
                      cancelButtonText: this.label.cancel,
                      type: 'warning',
                    }
                  ).then(() => {                        
                        this.clearCart(getCookie('cart_uuid')).then((result) => {
                            this.addCartItems();
                        });
                   }).catch(() => {                  
                });
             } else {
                this.addCartItems();
             }
          } else {
              this.addCartItems();
          }
       },   	
       addCartItems(){                       
            this.add_to_cart = true;
            let transactionType = getCookie('cart_transaction_type');              
                                                
            var $params = {
                'YII_CSRF_TOKEN':$('meta[name=YII_CSRF_TOKEN]').attr('content'),			 	
                'merchant_id' : this.items.merchant_id,
                'cat_id' : this.items.cat_id,
                'item_token': this.items.item_token,
                'item_size_id': this.size_id,
                'item_qty': this.item_qty,
                'item_addons': this.item_addons,
                'special_instructions': this.special_instructions,
                'meta' : this.meta,
                'cart_uuid':getCookie('cart_uuid'),
                'transaction_type': transactionType,
                'if_sold_out' : this.if_sold_out,
            };		                                                
            $params = JSON.stringify($params);                        
            
            this.add_to_cart = true;
            axios({
                url: `${ajaxurl}/addCartItems`,
                method: "PUT",
                data: $params,
                headers: {
                    "Content-Type": "application/json"
                },                                
            })
            .then(response => {                
                if(response.data.code==1){         

                    ElementPlus.ElNotification({			
                        title: "",			
                        message: response.data.msg,
                        position: 'bottom-right',
                        type: 'success',
                    });					

                    this.$emit('afterAddtocart');           
                    setCookie('cart_uuid',response.data.details.cart_uuid,30);                    
                    $('#itemModal').modal('hide');                     
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
				this.add_to_cart = false;
			});                                
       } ,
       clearCart(cart_uuid){
        return new Promise((resolve, reject) => {
            let params="cart_uuid=" + cart_uuid; 		    
 		    params+=addValidationRequest();
            axios.post( `${ajaxurl}/clearCart` , params)
            .then(response => {																				
                if(response.data.code==1){
                    resolve(true);                                              
                } else {                                        
                    reject(response.data.msg);
                }
            })
            .catch(error => {				
                reject(error);				
            }).then(data => {                
            });         
        });
       },
       //
    },
    template: '#xtemplate_item_details',
};