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
	notify (color, message, icon, position , timeout, iconColor , actions) {		
		if ( typeof position !== "undefined" && position !== null) {			
		} else {
			position = "bottom";
		}		
		if ( typeof timeout !== "undefined" && timeout !== null) {			
		} else {
			timeout = 3000;
		}			
		if ( typeof iconColor !== "undefined" && iconColor !== null) {			
		} else {
			iconColor = color;
		}			
		if ( typeof actions !== "undefined" && actions !== null) {			
		} else {
			actions = [];
		}			
		const $q = Quasar.Notify;			
		$q.create({
		  message,
		  color,
		  icon,
		  iconColor:iconColor,
		  classes: "primevue_toats",
		  position: position,
		  html: true,
		  timeout: timeout,
		  multiLine: false,
		  actions: actions			
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

const ComponentsMoney = {
	props: ['amount'],
	data(){
	   return {						
		 data : 0,
		 config : JSON.parse(money_config)		 
	   };
    },   
	mounted() {	   	     	     	     	     	    
   	   this.data = window["v-money3"].format(this.amount, this.config);   	   
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

const translationVendor = !quasarComponents.empty(translation_vendor) ? JSON.parse(translation_vendor) : null;

const ComponentsSearchcustomer = {
	props:['default_list'],
	data() {
		return {
			customer_name :'',
			loading : false,
			old_search : '',
			options : []
		}
	},	
	watch: {
		default_list() {			
			this.options = this.default_list;
		},
	},
	methods: {
		setModel(val) {
			this.customer_name = val;
		},
		Clear(){
			console.log("Clear");
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
		Clear(){
			this.customer_name = '';			
			this.$emit('clearSelectcustomer');
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid");
			axios.post( pos_api+"/clearCustomer?language="+language , postData, headerAuthorization)
			.then(response => {												
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     											
			});          
		},
		onSelect(data){
			if(!quasarComponents.empty(data)){
				console.log("onSelect",data.value);
				this.$emit("onselectCustomer",data.value);
				let postData = "client_id="+ data.value + "&cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid");
				axios.post( pos_api+"/cartSetCustomer?language="+language , postData, headerAuthorization)
				.then(response => {								
					if(response.data.code==1){								
						this.$emit("afterSelectcustomer", {
							id: data.value,
							data: data
						});
					} 
				})
				.catch(error => {				
					console.error('Error:', error);
				}).then(data => {			     							
					
				});          
				
			}			
		},
		//
	},
	template: '#xtemplate_search_customer',
};

const ComponentsCustomer = {
	template: '#xtemplate_customer',
	data() {
		return {
			modal : false,
			first_name : '',
			last_name :'',
			email_address : '',
			contact_number :'',
			loading : false
		}
	},
	methods: {
		onSubmit(evt){
			this.loading = true;
			let postData = 'first_name='+ this.first_name;
			postData+='&last_name='+this.last_name;
			postData+='&email_address='+this.email_address;
			postData+='&contact_number='+this.contact_number;

			axios.post( pos_api+"/createCustomer?language="+language , postData, {
				headers: {
					'Authorization': `token ${token}`,					
				}
			})
			.then(response => {								
				if(response.data.code==1){
					this.modal = false;
					this.$emit("afterCreatecustomer", {
						id: response.data.details.client_id,
						data: {
							label: response.data.details.client_name,
							value: response.data.details.client_id,
						}
					});
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.loading = false;
		    });          
		}
		//
	},
};

const ComponentsItemdetails = {
	template: '#xtemplate_item_details',
	props : ['transaction_type','edit_cart','customer_id'],
	components :{
		'money-format': ComponentsMoney,   
	},
	data() {
		return {
			dialog: false,
			size: "",
			with_qty: false,
			qty: 1,
			item_qty: 1,
			morphGroupModel: "add",
			item_data: [],	  
			cat_id: 0,
			item_uuid: "",
			loading: false,
			loading_add: false,
			items: [],
			item_size_id: 0,
			size_data: [],
			size_datas: [],
			cooking_ref: 0,
			cooking_data: [],
			ingredients: [],
			ingredients_data: [],
			addons: {},
			special_instructions: "",
			//transaction_type: "",
			if_sold_out: "",
			sold_out_options: [],
			item_total: 0,
			disabled_cart: true,
			slide_items: 0,
			favorites: [],
			number_config: [],
			meta: [],
			items_not_available: [],
			category_not_available: [],
			deep_link: "",
			show_nav: false,
			item_gallery: [],
			image_featured: "",
			points_data : null
		}
	},
	watch: {
		addons: {
		  handler(newValue, oldValue) {
			this.ItemSummary();
		  },
		  deep: true,
		},
		item_size_id() {
		  this.ItemSummary();
		},
		cooking_ref() {
		  this.ItemSummary();
		},
		ingredients() {
		  this.ItemSummary();
		},
		item_qty() {
		  this.ItemSummary();
		},
	},
	methods: {
		resetData() {
			this.item_qty = 1;
			this.items = [];
			this.item_size_id = 0;
			this.size_data = [];
			this.size_datas = [];
			this.cooking_ref = 0;
			this.cooking_data = [];
			this.ingredients = [];
			this.ingredients_data = [];
			this.addons = {};
			this.special_instructions = "";
			//this.transaction_type = "";
			this.if_sold_out = "";
			this.sold_out_options = [];
			this.item_total = 0;
			this.disabled_cart = true;
			this.slide_items = 0;
		},
		getMenuItem(){			
			this.loading = true;
			this.$emit('itemShow');
			this.resetData();

			let postData = "cat_id="+this.item_data.cat_id;
			postData+="&item_uuid="+this.item_data.item_uuid;	
			postData+="&customer_id="+this.customer_id;		
			axios.post( pos_api+"/getMenuItem?language="+language ,postData , {
				headers: {
					'Authorization': `token ${token}`,					
				}
			})
			.then(response => {		
				let data = response.data;								
				if(response.data.code==1){										
					this.items = data.details.data.items;
					this.meta = data.details.data.meta;
					this.size_datas = data.details.data.items.price;
					const soldOutData = data.details.sold_out_options;

					this.if_sold_out = data.details.default_sold_out_options;

					this.items_not_available = data.details.data.items_not_available;
					this.category_not_available = data.details.data.category_not_available;
					this.points_data = data.details.points;

					const prices = data.details.data.items.price;
					const metaCookingRef = data.details.data.meta ? data.details.data.meta.cooking_ref : {};
					const metaCookingRefDetails = data.details.data.meta ? data.details.data.meta_details.cooking_ref : {};

					const metaIngredients = data.details.data.meta ? data.details.data.meta.ingredients : {};
                    const metaIngredientsDetails = data.details.data.meta ? data.details.data.meta_details.ingredients : {};

					const addons = data.details.data ? data.details.data.addons : {};
                    const addonItems = data.details.data ? data.details.data.addon_items : {};

					if (Object.keys(soldOutData).length > 0) {
						Object.entries(soldOutData).forEach(
						  ([itemSoldKey, itemsSoldData]) => {
							this.sold_out_options.push({
							  label: itemsSoldData,
							  value: itemSoldKey,
							});
						  }
					    );
					}

					if (Object.keys(prices).length > 0) {
						Object.entries(prices).forEach(([key, items]) => {
						  if (items.discount <= 0) {
							this.size_data.push({
							  label: items.size_name + " " + items.pretty_price,
							  value: parseInt(items.item_size_id),
							});
						  } else {
							this.size_data.push({
							  label:
								items.size_name + " " + items.pretty_price_after_discount,
							  value: parseInt(items.item_size_id),
							});
						  }
						});
						this.item_size_id = parseInt(Object.keys(prices)[0]);
					}

					if (
						typeof metaCookingRef !== "undefined" &&
						metaCookingRef !== null
					  ) {
						if (metaCookingRef.length > 0) {
						  Object.entries(metaCookingRef).forEach(([key, value]) => {
							this.cooking_data.push({
							  label: metaCookingRefDetails[value].meta_name,
							  value: metaCookingRefDetails[value].meta_id,
							});
						  });
						}
					}

					if (
						typeof metaIngredients !== "undefined" &&
						metaIngredients !== null
					  ) {
						if (metaIngredients.length > 0) {
						  Object.entries(metaIngredients).forEach(([key, value]) => {
							if (metaIngredientsDetails[value]) {
							  this.ingredients_data.push({
								label: metaIngredientsDetails[value].meta_name,
								value: metaIngredientsDetails[value].meta_id,
							  });
							}
							if (this.items.ingredients_preselected) {
							  if (metaIngredientsDetails[value]) {
								this.ingredients.push(
								  metaIngredientsDetails[value].meta_id
								);
							  }
							}
						  });
						}
					}

					// addons
					 if (Object.keys(this.items.item_addons).length > 0) {
						Object.entries(this.items.item_addons).forEach(
						  ([sizeId, SubcatID]) => {
							const addOnsAdded = [];
							Object.entries(SubcatID).forEach(([key, child]) => {
							  if (!quasarComponents.empty(addons[sizeId])) {
								if (!quasarComponents.empty(addons[sizeId][child])) {
								  const addonDetails = addons[sizeId][child];
			
								  const subItems = [];
								  Object.entries(addonDetails.sub_items).forEach(
									([key2, subItemsID]) => {
									  if (addonItems[subItemsID]) {
										const subItemsAdd = addonItems[subItemsID];
										addonItems[subItemsID].checked = false;
										addonItems[subItemsID].disabled = false;
										addonItems[subItemsID].qty = 1;
										subItems.push(subItemsAdd);
									  }
									}
								  );
			
								  const subdata = {
									subcat_id: addonDetails.subcat_id,
									subcategory_name: addonDetails.subcategory_name,
									subcategory_description:
									  addonDetails.subcategory_description,
									multi_option: addonDetails.multi_option,
									multi_option_min: addonDetails.multi_option_min,
									multi_option_value: addonDetails.multi_option_value,
									require_addon: addonDetails.require_addon,
									pre_selected: addonDetails.pre_selected,
									sub_items_checked: "",
									sub_items: subItems,
								  };
								  addOnsAdded.push(subdata);
								}
							  }
							});
							this.addons[sizeId] = addOnsAdded;
						  }
						);
					  }
					// addons

				} else {
					this.resetData();
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.loading = false;
		    });          
		},
		beforeHide() {			
			this.qty = 1;			
			this.$emit('itemHide');
		},
		ItemSummary() {
			let $itemTotal = 0;
			const $requiredAddon = [];
			const $requiredAddonAdded = [];
			let $min_addon = [];
			let $min_addon_added = [];

			if (!quasarComponents.empty(this.size_datas[this.item_size_id])) {
				const item = this.size_datas[this.item_size_id];
				if (item.discount > 0) {
				  $itemTotal += this.item_qty * parseFloat(item.price_after_discount);
				} else $itemTotal += this.item_qty * parseFloat(item.price);
			}

			if (!quasarComponents.empty(this.addons[this.item_size_id])) {
				this.addons[this.item_size_id].forEach((item, index) => {
				  if (item.require_addon === "1") {
					$requiredAddon.push(item.subcat_id);
				  }
		
				  if (item.multi_option === "custom") {
					let totalCheck = 0;
					const multiOptionValue = item.multi_option_value;
					let multi_option_min = item.multi_option_min;
		
					if (multiOptionValue > 0) {
					  $min_addon.push({
						subcat_id: item.subcat_id,
						min: multi_option_min,
						max: multiOptionValue,
					  });
					}
		
					const itemIndex = [];
					const itemIndex2 = [];
					item.sub_items.forEach((item2, index2) => {
					  if (item2.checked === true) {
						totalCheck++;
						$itemTotal += this.item_qty * parseFloat(item2.price);
						$requiredAddonAdded.push(item.subcat_id);
					  } else itemIndex.push(index2);
		
					  if (item2.disabled === true) {
						itemIndex2.push(index2);
					  }
					});
		
					$min_addon_added[item.subcat_id] = {
					  total: totalCheck,
					};
		
					if (totalCheck >= multiOptionValue) {
					  itemIndex.forEach((item3, index3) => {
						item.sub_items[item3].disabled = true;
					  });
					} else {
					  itemIndex2.forEach((item3, index3) => {
						item.sub_items[item3].disabled = false;
					  });
					}
				  } else if (item.multi_option === "one") {
					item.sub_items.forEach((item2, index2) => {
					  if (item2.sub_item_id === item.sub_items_checked) {
						$itemTotal += this.item_qty * parseFloat(item2.price);
						$requiredAddonAdded.push(item.subcat_id);
					  }
					});
				  } else if (item.multi_option === "multiple") {
					var item_index = [];
					let multi_option_min = item.multi_option_min;
					var multi_option_value = item.multi_option_value;
					var limit = 0;
		
					if (multi_option_value > 0) {
					  $min_addon.push({
						subcat_id: item.subcat_id,
						min: multi_option_min,
						max: multi_option_value,
					  });
					}
		
					item.sub_items.forEach((item2, index2) => {
					  if (item2.checked === true) {
						$itemTotal += item2.qty * parseFloat(item2.price);
						$requiredAddonAdded.push(item.subcat_id);
						limit += item2.qty;
					  }
					  item_index.push(index2);
					});
		
					$min_addon_added[item.subcat_id] = {
					  total: limit,
					};
		
					this.addons[this.item_size_id][index].qty_selected = limit;
					if (
					  this.addons[this.item_size_id][index].qty_selected >=
					  multi_option_value
					) {
					  item_index.forEach((item3, index2) => {
						this.addons[this.item_size_id][index].sub_items[
						  item3
						].disabled = true;
					  });
					} else {
					  item_index.forEach((item3, index2) => {
						this.addons[this.item_size_id][index].sub_items[
						  item3
						].disabled = false;
					  });
					}
				  } /* endif custom */
				});
				// end loop addons
			  }

			if ($itemTotal > 0) {
			    this.item_total = $itemTotal;
			}
	
			let $requiredMeet = true;
				if ($requiredAddon.length > 0) {
				$requiredAddon.forEach((requiedItem, requiredIndex) => {
					if ($requiredAddonAdded.includes(requiedItem) === false) {
					$requiredMeet = false;
					return false;
					}
				});
			}

			 // CHECK COOKING REF
			if (this.items.cooking_ref_required) {
				let $cooking_ref_check = false;
				if (this.cooking_ref > 0) {
				$cooking_ref_check = true;
				}
				if (!$cooking_ref_check) {
				$requiredMeet = false;
				}
			}

			// CHECK ADDON MINIMUM AND MAXIMUM
			if (Object.keys($min_addon).length > 0) {
				let min_value, min_selected;
				Object.entries($min_addon).forEach(
				([key_min_addon, items_min_addon]) => {
					min_value = parseInt(items_min_addon.min);
					if ($min_addon_added[items_min_addon.subcat_id]) {
					min_selected = parseInt(
						$min_addon_added[items_min_addon.subcat_id].total
					);
					}
					if (min_selected > 0) {
					if (min_value > min_selected) {
						$requiredMeet = false;
					}
					}
				}
				);
			}

			if ($requiredMeet) {
				this.disabled_cart = false;
			} else this.disabled_cart = true;
			
			 // CHECK COOKING REF
			 if (this.items.cooking_ref_required) {
				let $cooking_ref_check = false;
				if (this.cooking_ref > 0) {
				  $cooking_ref_check = true;
				}
				if (!$cooking_ref_check) {
				  $requiredMeet = false;
				}
			  }
		
			  // CHECK ADDON MINIMUM AND MAXIMUM
			  if (Object.keys($min_addon).length > 0) {
				let min_value, min_selected;
				Object.entries($min_addon).forEach(
				  ([key_min_addon, items_min_addon]) => {
					min_value = parseInt(items_min_addon.min);
					if ($min_addon_added[items_min_addon.subcat_id]) {
					  min_selected = parseInt(
						$min_addon_added[items_min_addon.subcat_id].total
					  );
					}
					if (min_selected > 0) {
					  if (min_value > min_selected) {
						$requiredMeet = false;
					  }
					}
				  }
				);
			  }
		
			  if ($requiredMeet) {
				this.disabled_cart = false;
			  } else this.disabled_cart = true;


			   // CHECK COOKING REF
			   if (this.items.cooking_ref_required) {
				let $cooking_ref_check = false;
				if (this.cooking_ref > 0) {
				  $cooking_ref_check = true;
				}
				if (!$cooking_ref_check) {
				  $requiredMeet = false;
				}
			  }
		
			  // CHECK ADDON MINIMUM AND MAXIMUM
			if (Object.keys($min_addon).length > 0) {
				let min_value, min_selected;
				Object.entries($min_addon).forEach(
					([key_min_addon, items_min_addon]) => {
					min_value = parseInt(items_min_addon.min);
					if ($min_addon_added[items_min_addon.subcat_id]) {
						min_selected = parseInt(
						$min_addon_added[items_min_addon.subcat_id].total
						);
					}
					if (min_selected > 0) {
						if (min_value > min_selected) {
						$requiredMeet = false;
						}
					}
					}
				);
			}
	
			if ($requiredMeet) {
			   this.disabled_cart = false;
			} else this.disabled_cart = true;

		},
		AddToCart() {			
			const $ingredients = [];
			if (this.ingredients.length > 0) {
			  this.ingredients.forEach((ingredientsId, index) => {
				$ingredients.push({
				  meta_id: ingredientsId,
				  checked: true,
				  meta_name: "",
				});
			  });
		   }

		   const $meta = {
				cooking_ref: [
				{
					meta_id: this.cooking_ref,
					checked: this.cooking_ref,
					meta_name: "",
				},
				],
				ingredients: $ingredients,
		   };

		   const $cartUuid = quasarComponents.getStorage("pos_cart_uuid");

		   const postData = {
				cart_uuid: $cartUuid,
				cat_id: this.item_data.cat_id,
				item_size_id: this.item_size_id,
				item_token: this.item_data.item_uuid,
				item_qty: this.item_qty,
				special_instructions: this.special_instructions,
				if_sold_out: this.if_sold_out.value,
				transaction_type: this.transaction_type,
				meta: $meta,
				item_addons: !quasarComponents.empty(this.addons[this.item_size_id]) ? this.addons[this.item_size_id] : [],
				edit_cart : this.edit_cart?this.edit_cart:false
		   };
		   
		   this.loading_add = true;

		   axios.post( pos_api+"/addCartItems" , postData, {
			headers: {
					'Authorization': `token ${token}`,					
				}
			})
			.then(response => {											
				if(response.data.code==1){	
					if (quasarComponents.empty($cartUuid)) {
						//quasarComponents.setStorage("cart_uuid", response.data.details.cart_uuid);
						quasarComponents.setStorage("pos_cart_uuid", response.data.details.cart_uuid);
					}
					this.dialog = false;
					this.$emit("afterAddtocart");
				} else {			
					quasarComponents.notify("myerror",response.data.msg,'error');
				}				
			})
			.catch(error => {				
				console.error('Error:', error);				
			}).then(data => {			     							
				this.loading_add = false;
		   });          
		  
		}
		//
	},
};

const ComponentsAddressList = {
	template: '#xtemplate_address_list',
	props : ['client_id'],
	data() {
		return {
			dialog : false,
			loading : false,
			data : [],			
		}
	},
	computed: {
		hasData() {
			if (Object.keys(this.data).length > 0) {
				return true;
			}
			return false;
		},
	},
	methods: {
		beforeHide(){
			this.data = [];
		},
		clientAddresses(){			
			if(this.client_id=="walkin"){
				return false;
			}
			this.loading = true;
			axios({
				method: 'GET',
				url: pos_api+"/clientAddresses?language="+language +"&client_id="+this.client_id,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){     
						this.data = result.data.details.addresses;                											
					} else {
						this.data = [];
					}                   					  
				}).catch(error => {	
				//
				}).then(data => {			     							
					this.loading = false;
			  });          			
		},		
		showNewaddress(){			
			this.dialog = false;
			this.$emit('showNewaddress');
		},
		setAddress(data){			
			
			const dialog = quasarComponents.dialog(translationVendor.processing + "<br/>"+ translationVendor.close_window);
			let postData = {
				cart_uuid : quasarComponents.getStorage("pos_cart_uuid"),
				data : data
			};
			axios.post( pos_api+"/saveaddress?language="+language , postData, headerAuthorization )
			.then(response => {																
				quasarComponents.setStorage("pos_address",data);
				quasarComponents.setStorage("pos_local_id",data.place_id);						
				this.dialog = false;
				this.$emit("afterSelectaddress",data);
			})
			.catch(error => {								
			}).then(data => {		
				dialog.hide();
			});         
		}
		//
	},
};

const ComponentsSearchAddress = {
	props: ["placeholder"],
	data() {
		return {
		  address: "",
		  address_data: [],
		  options: [],
		  data: [],
		  loading: false,
		};
	},
	methods: {
		Focus() {
			this.$refs.select_address.focus();
		},
		filterFn(val, update, abort) {
			if (val.length < 2) {
				abort();
				return;
			}		
		
			setTimeout(() => {
				update(() => {
					this.options = [];
					let postData = "q="+val;
					axios.post( pos_api+"/getlocationAutocomplete?language="+language , postData, headerAuthorization )
					.then(response => {												
						this.options = response.data.details.data;
					})
					.catch(error => {				
						console.error('Error:', error);
					}).then(data => {			     											
					});         
			    }); 
		    }, 300);
		},
		selectAddress(val) {
			if (!quasarComponents.empty(val)) {
			  this.address_data = val;
			  this.address = val.description;			  
			  
			  let postData = "place_id="+val.id;
			  axios.post( pos_api+"/getLocationDetails?language="+language , postData, headerAuthorization )
			  .then(response => {												
				   if(response.data.code==1){
					    const results = response.data.details.data;						
						if (!quasarComponents.empty(results.latitude)) {
						    this.$emit("afterSelectaddress", results);
						}
				   }
			  })
			  .catch(error => {				
				console.error('Error:', error);
			  }).then(data => {			     											
		     });          
			  
			}
		},
	},
	template : `
	<q-select
    v-model="address"
    ref="select_address"
	use-input
	hide-selected
	fill-input
	input-debounce="0"
	:options="options"
	@filter="filterFn"
	@update:model-value="selectAddress"
	@input-value="setModel"
	@clear="$emit('onClear')"
	hide-dropdown-icon
	:loading="loading_search"
	outlined		
	clearable
	clear-icon="cancel"
	:placeholder="placeholder"
	stack-label
	color="grey-5"
  >
    <template v-slot:option="scope">
      <q-item v-bind="scope.itemProps">
        <q-item-section avatar top style="min-width: auto">
          <q-icon name="las la-map-marker" />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ scope.opt.description }}</q-item-label>
          <q-item-label caption>{{ scope.opt.addressLine2 }}</q-item-label>
        </q-item-section>
      </q-item>
    </template>    
  </q-select>
	`
};

const ComponentsMaps = {
	props: ["keys", "provider", "zoom", "center", "markers", "size",],
	template : `     	
    <div ref="cmaps" class="map" :class="size">	
	</div>  	
    `,
	data() {
        return {
            cmapsMarker : [],            
            latLng : [], 
			cmaps: undefined,    
			bounds : undefined,       
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
                                disableDefaultUI: true                                
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

							this.FitBounds();
					    }
						break;
                } 
            } catch (err) {
               console.error(err);
            } 
        },		
        addMarker(properties, index, draggable) {            
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
							  dump( lngLat.lat  +"=>"+ lngLat.lng);
							  this.$emit("afterSelectmap", lngLat.lat, lngLat.lng);
							});		
							this.mapBoxResize();					
						  }
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
            console.log("moveAllMarker");
            console.log(this.markers);
			
			if(this.provider=='google.maps'){			
				if (Object.keys(this.markers).length > 0) {
					Object.entries(this.markers).forEach(([key, items]) => {                
						let latlng = new google.maps.LatLng(parseFloat(items.lat), parseFloat(items.lng));
						if(!quasarComponents.empty(this.cmapsMarker[items.id])){
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
						if(!quasarComponents.empty(this.cmapsMarker[items.id])){							
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
                  if (!quasarComponents.empty(this.bounds)) {
                    this.cmaps.fitBounds(this.bounds);
                  }
                  break;
                case "mapbox":				  
                  if (!quasarComponents.empty(this.bounds)) {					
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
		setPlusZoom(){
			dump('setPlusZoom');
			this.cmaps.setZoom(this.cmaps.getZoom() + 2);
		},
		setLessZoom(){
			dump('setLessZoom');
			this.cmaps.setZoom(this.cmaps.getZoom() - 2);
		},		
		//		
	},
};

const ComponentsNewaddress = {
	template: '#xtemplate_new_address',
	props : ['client_id' , 'attributes_data'],
	components : {
		'SearchAddress' : ComponentsSearchAddress,
		'components-maps' : ComponentsMaps,  
	},	
	data() {
		return {
			data: [],
			center: { lat: 34.04703, lng: -118.24686 },
			marker_position: {},
			modal: false,
			loading: false,
			formatted_address: "",
			address1: "",
			location_name: "",
			delivery_options: "Leave it at my door",
			delivery_instructions: "",
			address_label: "Home",			
			custom_field1 :'',			
			custom_field2 :'',
		};
	},
	created() {		
		this.marker_position = [
			{
				id: 0,
				lat: parseFloat(this.center.lat),
				lng: parseFloat(this.center.lng),
				draggable: true,
			}
		];
	},
	computed: {
		hasAddress() {
		  if (Object.keys(this.data).length > 0) {
			return true;
		  }
		  return false;
		},
		getMapsConfig(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.maps_config?this.attributes_data.maps_config:false;
			}
			return false;
		},
	},
	methods: {
		afterSelectaddress(data){
			console.log("afterSelectaddress xx",data)	
			this.data = data;			
		
			this.formatted_address = data.address.formatted_address;
            this.address1 = data.address.address1;

			this.marker_position = [
				{
					id: 0,
					lat: parseFloat(data.latitude),
					lng: parseFloat(data.longitude),
					draggable: true,
				}
			];
		},
		dragMarker(){

		},
		afterSelectmap(lat,lng){			
			this.loading = true;              
			  
			let postData = "&lat="+ lat+ "&lng="+lng;
			axios.post( pos_api+"/reverseGeocoding?language="+language , postData, headerAuthorization ).then(response => {												
				 if(response.data.code==1){																												
					this.data = response.data.details.data;
					let parsed_address = this.data.parsed_address;					
					// if(!quasarComponents.empty(parsed_address)){
					// 	this.formatted_address = !quasarComponents.empty(parsed_address.street_name)?parsed_address.street_name:parsed_address.formatted_address;
                    //     this.address1 = parsed_address.street_number;						
					// } else {										
					// 	this.formatted_address = this.data.address.formatted_address;
                    //     this.address1 = this.data.address.address1;		
					// }					
					
					this.formatted_address = this.data.address.formatted_address;
                    this.address1 = this.data.address.address1;		

					this.data.latitude = this.data.latitude;
					this.data.longitude = this.data.longitude;
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
		onClear(){			
			this.data = [];
			this.formatted_address = '';
			this.address1 = '';
			this.location_name = '';
			this.delivery_instructions = '';
		},
		onSubmit() {
			console.log("onSubmit");
			if (quasarComponents.empty(this.formatted_address)) {
				quasarComponents.notify("myerror",translationVendor.enter_your_location,'error');
			}
			this.loading = true;

			
			let postData = {
				cart_uuid : quasarComponents.getStorage("pos_cart_uuid"),
				client_id: this.client_id,
				place_id: this.data.place_id,
				formatted_address: this.formatted_address,
				address1: this.address1,
				latitude: this.data.latitude,
				longitude: this.data.longitude,
				location_name: this.location_name,
				delivery_instructions: this.delivery_instructions,
                address_label: this.address_label,
				delivery_options : this.delivery_options,
				custom_field1 : this.custom_field1,
				custom_field2 : this.custom_field2,
				data : this.data
			};
			axios.post( pos_api+"/saveCartAddress?language="+language , postData, headerAuthorization )
			.then(response => {												
				 if(response.data.code==1){													
					this.data.attributes = {
						location_name : this.location_name,
						delivery_options : this.delivery_options,
						delivery_instructions : this.delivery_instructions,
						address_label : this.address_label,
					};					
					quasarComponents.setStorage("pos_address",this.data);
					quasarComponents.setStorage("pos_local_id",this.data.place_id);
					this.modal = false;
					this.$emit("afterSaveaddress",this.data);
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
		//
	},
};


const ComponentsDiscount = {
	template: '#xtemplate_discount',
	props : ['label','field_type','icon','filed_name','method_name','transaction_type'],
	data() {
		return {
			loading: false,
			modal : false,
			value: 0,
		}
	},
	methods: {
		beforeShow() {
			if(this.field_type=='number'){
				this.value = 0;
			} else this.value = '';
		},
		OnShow(){
			console.log("OnShow");
			this.$refs.value.focus();			
		},
		onSubmit() {			
			this.loading = true;
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid") +"&"+this.filed_name+"="+this.value;
			postData+="&transaction_type=" + this.transaction_type;
			axios.post( pos_api+"/"+this.method_name+"?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){				
					this.modal = false;
					this.$emit('refreshCart');
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
	},
};

const ComponentsPoints = {
	template: '#xtemplate_points',	
	props : ['use_thresholds','client_id'],
	data() {
		return {
			loading: false,
			modal : false,
			points: 0,
			points_tab: 0,
			data_points: [],
			balance: 0,
			points_id: 0,
			available_points : 0
		}
	},
	methods: {
		beforeShow() {
			this.points = 0;
			this.available_points = 0;
			if(this.use_thresholds){
				this.getPointsthresholds();
			}
			this.getAvailablepoints();
		},
		OnShow(){			
			if(!this.use_thresholds){
			   this.$refs.points.focus();			
			}
		},
		getPointsthresholds(){
			this.loading = true;
			let postData ="customer_id=" + this.client_id;
			axios.post( pos_api+"/getPointsthresholds?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){			
					this.data_points = response.data.details.data;
                    this.balance = response.data.details.balance;						
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
		setPoints(){			
            this.points = this.points_tab.points;
            this.points_id = this.points_tab.id;
		},
		onSubmit() {			
			this.loading = true;
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid") +"&points="+ this.points;
			postData+="&customer_id=" + this.client_id;
			postData+="&points_id=" + this.points_id;
			axios.post( pos_api+"/applyPoints?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){				
					this.modal = false;
					this.$emit('refreshCart');
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
		getAvailablepoints(){			
			this.loading = true;			
			let postData="&customer_id=" + this.client_id;			
			axios.post( pos_api+"/getavailablepoints?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){			
					this.available_points = response.data.details.total;						
				} else {					
					this.available_points = 0;
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.loading = false;
		    });          
		},
	},
};

const ComponentsHoldorder = {
	template: '#xtemplate_holdorder',	
	data() {
		return {
			loading: false,
			modal : false,
			order_reference : ''
		}
	},
	methods: {
		beforeShow() {
			this.order_reference = '';
		},
		OnShow(){
			console.log("OnShow");
			this.$refs.order_reference.focus();			
		},
		onSubmit() {			
			this.loading = true;
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid");			
			postData+="&order_reference="+this.order_reference;
			axios.post( pos_api+"/applyHoldOrder?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){				
					this.modal = false;

					quasarComponents.setStorage("pos_cart_uuid","");
					quasarComponents.setStorage("pos_local_id","");
					quasarComponents.setStorage('pos_address','');

					this.$emit('afterHoldcart',response.data.details);
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
	},
};

const ComponentsPayment = {
	template: '#xtemplate_createpayment',	
	props : ['attributes_data','transaction_list','transaction_type','cart_total'],
	data() {
		return {
			loading: false,
			modal : false,		
			whento_deliver: "now",
			delivery_date: "",
			delivery_time: "",
			order_status: "",
			receive_amount: 0,
			payment_code: "",
			payment_reference: "",
			order_notes: "",
			pay_left: 0,
			change: 0,
			room_id: "",
			table_id: "",
			guest_number: 1,
			total : 0
		}
	},	
	computed: {
		getOpeningDates() {			
			let data = [];
			if (Object.keys(this.attributes_data).length > 0) {
				Object.entries(this.attributes_data.opening_hours.dates).forEach(
					([key, items]) => {
					data.push({
						label: items.name,
						value: items.value,
					});
					}
				);
				return data;
			}
			return data;
		},
		getTimelist(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (!quasarComponents.empty(this.attributes_data.opening_hours.time_ranges[this.delivery_date])) {
					return this.attributes_data.opening_hours.time_ranges[this.delivery_date];
				}
			}
			return [];
		},
		getOrderStatus(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.order_status_list;
			}
			return [];      
		},
		getPaymentMethod(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.data;
			}
			return [];      
		},
		getRoomList(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.room_list;
			}
			return [];      
		},
		getTableList(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (this.attributes_data.table_list[this.room_id]) {
					return this.attributes_data.table_list[this.room_id];
				}
			}
			return [];      
		},		
  },
  watch: {
	cart_total(newval,oldval){		
		this.receive_amount = newval.raw;
		this.total = newval.raw;
	},
	receive_amount(newamount, oldamount) {
		this.pay_left = parseFloat(this.total) - parseFloat(newamount);
		if (this.pay_left <= 0) {
			this.pay_left = 0;
		}
		this.change = parseFloat(newamount) - parseFloat(this.total);
		if (this.change <= 0) {
			this.change = 0;
		}
	},
  },
  methods: {	
	afterSelectRoom() {
		this.table_id = "";
	},
	getCartUUID(){
		let cart_uuid = quasarComponents.getStorage("pos_cart_uuid");		
		return !quasarComponents.empty(cart_uuid)?cart_uuid:'';
	},
	getLocalID(){
		let local_id = quasarComponents.getStorage("pos_local_id");
		return !quasarComponents.empty(local_id)?local_id:'';
	},	
    beforeShow(){				
		if (Object.keys(this.attributes_data).length > 0) {
			this.order_status = this.attributes_data.order_status;
			this.payment_code = this.attributes_data.default_payment;
		}
	},
	onSubmit(){		
		let postData = {
			place_id: this.getLocalID(),
			place_data : quasarComponents.getStorage("pos_address"),
			cart_uuid : this.getCartUUID(),			
			transaction_type : this.transaction_type,
			delivery_date : this.delivery_date,
			delivery_time: this.delivery_time,
			order_status: this.order_status,
			receive_amount: this.receive_amount,
			payment_code: this.payment_code,
			payment_reference: this.payment_reference,
			order_notes: this.order_notes,
			order_change: this.change,
			guest_number: this.guest_number,
			room_id: this.room_id,
            table_id: this.table_id,
		};
		this.loading = true;
		axios.post( pos_api+"/submitPOSOrder?language="+language , postData, headerAuthorization )
		.then(response => {								
			if(response.data.code==1){				
				this.modal = false;
				quasarComponents.notify("mysuccess",response.data.msg,'info');
				//this.$emit('afterPayment',response.data.details.order_uuid);
				this.$emit('afterPayment',response.data.details);
			} else {
				quasarComponents.notify("myerror",response.data.msg,'error');
			}
		})
		.catch(error => {				
			console.error('Error:', error);
		}).then(data => {			     							
			this.loading = false;
		});          		
	}
	//
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
	// mounted() {
	// 	this.orderDetails();
	// },
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

const ComponentsSelectTable = {
	template: '#xtemplate_select_table',
	props : ['label'],
	data() {
		return {
			modal : false,	
			loading : false,
			// room_uuid : '',
			// table_uuid : '',		
			guest_number :0,
			guest_options : [],
			table_details : []
		}
	},
	methods: {		
		beforeShow(){
			console.log("table_details",this.table_details);			

			this.guest_options = [];
			const min = parseInt(this.table_details.min_covers);
			const max = parseInt(this.table_details.max_covers);			
			//this.guest_number = min;
			for (let i = min; i <= max; i++) {
				this.guest_options.push({
					label : i,
					value : i
				});
			}		
			this.guest_number =  this.table_details? (this.table_details.guest_number?this.table_details.guest_number:min) : min; 	
		},
		onSubmit(){
			this.loading = true;
			let cart_uuid = quasarComponents.getStorage("pos_cart_uuid");
			let postData = "room_uuid="+this.table_details.room_uuid + "&table_uuid="+this.table_details.table_uuid;
			postData+="&guest_number="+this.guest_number +"&cart_uuid="+cart_uuid;
			axios.post( pos_api+"/saveguestnumber?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){		
					this.modal = false;
					//this.$emit('afterAddtocart');
					this.$emit('afterAddguestnumber');				
				 } else {				
					quasarComponents.notify("myerror",response.data.msg,'error');	 
				 }
			 })
			 .catch(error => {				
				 console.error('Error:', error);
			 }).then(data => {			     							
				this.loading = false;
			 });          
		}
	},
};

const ComponentsMakepayment = {
	template: '#xtemplate_makepayment',
	props : ['title','attributes_data','transaction_type','cart_total','room_uuid','table_uuid', 'skip_kitchen',
		'whento_deliver','delivery_date','delivery_time'
	],
	components :{
		'money-format': ComponentsMoney,   
	},	
	data() {
		return {
			modal : false,
			status : '',
			payment_code : '',
			receive_amount : 0,
			pay_left : 0,
			change : 0,
			payment_reference : '',
			order_notes : '',		
			change_denomination : 0,
			change_denomination_list : [],
			payment_icons : {
				cod: {
					bg: "bg-green",
					icon: "local_atm",
				  },
				  paypal: {
					bg: "bg-blue",
					icon: "credit_score",
				  },
				  stripe: {
					bg: "bg-blue-6",
					icon: "lab la-stripe",
				  },
				  razorpay: {
					bg: "bg-blue-10",
					icon: "credit_card",
				  },
				  mercadopago: {
					bg: "bg-light-blue-5",
					icon: "credit_card",
				  },
				  bank: {
					bg: "bg-blue-7",
					icon: "account_balance",
				  },
				  ocr: {
					bg: "bg-light-green",
					icon: "credit_card",
				  },
				  paydelivery: {
					bg: "bg-yellow-9",
					icon: "credit_card",
				  },
			}
		}
	},
	computed: {
		getPaymentMethod(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.data;
			}
			return [];      
		},	
		hasPaymentSelected()	
		{
			if(this.payment_code){
				return true;
			}
			return false;
		},
		getOpeningDates() {			
			let data = [];
			if (Object.keys(this.attributes_data).length > 0) {
				Object.entries(this.attributes_data.opening_hours.dates).forEach(
					([key, items]) => {
					data.push({
						label: items.name,
						value: items.value,
					});
					}
				);
				return data;
			}
			return data;
		},
		getTimelist(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (!quasarComponents.empty(this.attributes_data.opening_hours.time_ranges[this.delivery_date])) {
					return this.attributes_data.opening_hours.time_ranges[this.delivery_date];
				}
			}
			return [];
		},
		getOrderStatus(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.order_status_list;
			}
			return [];      
		},
	},
	watch: {
		receive_amount(newamount, oldamount) {
			this.pay_left = parseFloat(this.cart_total.raw) - parseFloat(newamount);
			if (isNaN(this.pay_left )) {
				this.pay_left = 0;
			} else {
				this.pay_left = this.pay_left>0?this.pay_left:0;
			}			
			this.change = parseFloat(newamount) - parseFloat(this.cart_total.raw);
			if (isNaN(this.pay_left )) {
				this.change = 0;
			} else {				
				this.change = this.change>0?this.change:0;
			}			
		},
		change_denomination(newval,oldval){
			this.receive_amount = newval;
		},
	},
	methods: {
		beforeShow(){
			this.receive_amount = 0;
			this.payment_code = '';
			this.generateDenomination();
		},
		getCartUUID(){
			let cart_uuid = quasarComponents.getStorage("pos_cart_uuid");		
			return !quasarComponents.empty(cart_uuid)?cart_uuid:'';
		},
		getLocalID(){
			let local_id = quasarComponents.getStorage("pos_local_id");
			return !quasarComponents.empty(local_id)?local_id:'';
		},	
		setPaymenMethod(data){			
			this.payment_code = data;
		},
		getSelectedPayment(data){
			if(this.payment_code==data){
				return 'table-waiting-for-bill';
			}
			return 'table-occupied';
		},
		onSubmit(){
			const dialog = quasarComponents.dialog(translationVendor.payment_processing + "<br/>"+ translationVendor.close_window);
			let postData = {
				place_id: this.getLocalID(),
				place_data : quasarComponents.getStorage("pos_address"),
				cart_uuid : this.getCartUUID(),			
				whento_deliver : this.whento_deliver,
				 transaction_type : this.transaction_type,
				delivery_date : this.delivery_date,
				delivery_time: this.delivery_time,
				order_status: this.status,
				receive_amount: this.receive_amount,
				payment_code: this.payment_code,
				payment_reference: this.payment_reference,
				order_notes: this.order_notes,
				order_change: this.change,
				// guest_number: this.guest_number,
				room_id: this.room_uuid,
				table_id: this.table_uuid,
				skip_kitchen : this.skip_kitchen==true?1:0
			};
			this.loading = true;
			axios.post( pos_api+"/submitPOSOrder?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){				
					this.modal = false;										
					//this.$emit('afterPayment',response.data.details.order_uuid);					
					this.$emit('afterPayment',response.data.details);					
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
				quasarComponents.notify("myerror",error,'error');
			}).then(data => {			     							
				dialog.hide();
			});          		
		},
		generateDenomination(){			
			const denominations = [];
			const increment = 10;
			const totalOrder = parseFloat(this.cart_total.raw);
			const lowerDenomination = Math.floor(totalOrder / increment) * increment;
			if (lowerDenomination < totalOrder) {
			  denominations.push({
				label :lowerDenomination,
				value : lowerDenomination
			  });
			}
			denominations.push({
				label :totalOrder,
				value : totalOrder
			});

			let higherDenomination = Math.ceil(totalOrder / increment) * increment;
			for (let i = 0; i < 5; i++) { // Generate 5 denominations as an example
			  denominations.push({
				label :higherDenomination,
				value : higherDenomination
			  });
			  higherDenomination += increment;
			}					
			this.change_denomination_list = denominations;
		}
		//
	},
};

const ComponentsElapsetime = {
	props : ['start','timezone'],
	data() {
		return {
			elapse :'',
			interval: undefined,
		}
	},
	mounted() {
		if(!quasarComponents.empty(this.startElapse)){
			this.startElapse();
			this.interval = setInterval(this.startElapse, 1000);		
		}
	},
	beforeUnmount() {
		clearInterval(this.interval);
	},
	methods: {
		startElapse(){			
			
			 if ((typeof this.start !== "undefined") && ( this.start !== null)) {
			 } else {
				return '';
			 }
			 const givenDateStr = this.start;
             const timezone = this.timezone;
			 const DateTime = luxon.DateTime;			
			 const givenDate = DateTime.fromISO(givenDateStr, { zone: timezone });
			 const currentDate = DateTime.now().setZone(timezone);
			 const timeDiff = currentDate.diff(givenDate).milliseconds;
			 const elapsedSeconds = Math.floor(timeDiff / 1000);
			 const seconds = elapsedSeconds % 60;
			 const minutes = Math.floor(elapsedSeconds / 60) % 60;
			 const hours = Math.floor(elapsedSeconds / (60 * 60)) % 24;
			 const days = Math.floor(elapsedSeconds / (60 * 60 * 24));
			 if(minutes>0){
				if(days>0){
					this.elapse = `${days} days, ${hours}:${minutes}:${seconds}`;
				} else if (hours>0) {				
					this.elapse = `${hours}:${minutes}:${seconds}`;
				} else {
					this.elapse = `${hours}:${minutes}:${seconds}`;
				}				
			 } else this.elapse = '';
			//console.log(`${days} days, ${hours} hours, ${minutes} minutes, ${seconds} seconds elapsed since ${givenDateStr}`);
		},
	},
	template : `	
	<template v-if="elapse">
	   {{ elapse }}
	</template>
	`
};

{/* <template v-if="elapse">
	  <q-chip dense color="transparent" text-color="grey-7" icon="schedule"> {{ elapse }}
	</template> */}

const ComponentsOrders = {
	template: '#xtemplate_orders',
	props : ['transaction_list','transaction_type','title','printer_list'],
	components : {
		'components-elapsetime' : ComponentsElapsetime
	},
	data() {
		return {
			proxy : false,
			data : [],
			order_type : [],
			loading : false,
			color_status: {
				delivery: "#388e3c",
				pickup: "#b2ebf2",
				dinein: "#ce93d8",
				takeout: "#90caf9",
			},
			columns :[
				{
					name : 'item_name',
					field : 'item_name',
					label : translationVendor.item,
					align: 'left',
				},
				{
					name : 'qty',
					field : 'qty',
					label : translationVendor.qty
				},
				{
					name : 'status',
					field : 'status',
					label : translationVendor.status
				}
			],			
			cart_uuid : null,
			socket_error : '',
			webSocket : null,
		}
	},	
	mounted() {				
		this.getPOSorders(null);
	},
	computed: {
		hasFilter(){
			if (Object.keys(this.order_type).length > 0) {
				return true;
			}
			return false;
		},
		hasData(){
			if (Object.keys(this.data).length > 0) {
				return true;
			}
			return false;
		},
		hasPrinter(){
			if (Object.keys(this.printer_list).length > 0) {
				return true;
			}
			return false;
		},
	},
	methods: {
		getStatusClass(value){
			return "table-"+ value;
		},
		refreshData(){
			this.order_type = [];
			this.getPOSorders(true);
		},
		getPOSorders(refresh){
			if(refresh){
				console.log("refresh");
				this.$refs.bar.start();
			} else {
				this.loading = true;
			}
			let postData = {
				order_type : this.order_type,
				transaction_type : this.transaction_type
			};
			axios.post( pos_api+"/getPOSorders?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){				
					this.data = response.data.details.data; 
				 } else {
					this.data = [];
				 }
			 })
			 .catch(error => {				
				 console.error('Error:', error);
			 }).then(data => {			
				this.loading = false;     	
				this.$refs?.bar?.stop();										 
			 });          	
		},
		applyFilters(){		
			this.proxy = false;
			this.getPOSorders(true);
		},		
		deleteConfirm(data,index){
			this.$q
			.dialog({
			  title: translationVendor.confirm_deletion,
			  message: translationVendor.delete_records_confirm,
			  transitionShow: "fade",
			  transitionHide: "fade",
			  cancel: true,
			  ok: {
				unelevated: true,
				color: "primary",
				rounded: true,
				"text-color": "white",
				size: "md",
				label: translationVendor.yes,
				"no-caps": true,				
			  },
			  cancel: {
				unelevated: true,
				color: "grey-5",
				"text-color": 'white',
				rounded: true,
				outline: false,
				size: "md",
				label: translationVendor.cancel,
				"no-caps": true,
			  },
			})
			.onOk(() => {
			  this.deleteOrders(data,index);
			});
		},
		deleteOrders(data,index){		
			console.log("index",index)	;
			const dialog = quasarComponents.dialog(translationVendor.deleting_records);
			let postData = "cart_uuid="+data.cart_uuid;
			axios.post( pos_api+"/deleteOrders?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){	
					this.getPOSorders(true);
					console.log('deleteOrders');
					this.$emit('afterDeleteorders');
				 } else {				
					quasarComponents.notify("myerror",error,'error');						
				 }
			 })
			 .catch(error => {				
				 console.error('Error:', error);
			 }).then(data => {																				 
				dialog.hide();
			 });          	
		},
		getItemStatus(status) {
			let $colors = "";
			switch (status) {
			  case "in progress":
				$colors = "orange-5";
				break;
	  
			  case "ready":
				$colors = "light-green-3";
				break;
	  
			  case "delayed":
				$colors = "red-7";
				break;
	  
			  case "cancelled":
				$colors = "red";
				break;
	  
			  case "completed":
				$colors = "green";
				break;
	  
			  default:
				$colors = "primary";
				break;
			}
			return $colors;
		},
		SwitchPrinter(cart_uuid,printerId,printerModel){			
			this.cart_uuid = cart_uuid;
			if(printerModel=="feieyun"){
				this.printUsingFP(printerId);
			} else {
				this.wifiPrint(printerId);
			}
		},
		printUsingFP(printer_id){
			const dialog = quasarComponents.dialog(translationVendor.printing + "<br/>"+ translationVendor.close_window);
			let postData = "printer_id="+printer_id;
			postData+="&cart_uuid="+this.cart_uuid;
			axios.post( pos_api+"/FPPrintOT?language="+language , postData, headerAuthorization )
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
			postData+="&cart_uuid="+this.cart_uuid;
			axios.post( pos_api+"/wifiPrintOT?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){			
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
		//
	},
};

const ComponentsChooseReceipt = {
	template: '#xchoose_receipt',
	props : ['title','enabled_email','enabled_whatsapp','enabled_webprint','enabled_print','printer_list'],
	data() {
		return {
			modal:false,
			email_address :'',
			mobile_number :'',
			order_uuid :'',
			order_id :'',
			success_message :'',
			socket_error : '',
			webSocket : null
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
		getPrinterList(){
			if (Object.keys(this.printer_list).length > 0) {
				return this.printer_list;
			}
			return false;
		}
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
		PrintWeb(){
			//this.modal = false;
			this.$emit('webPrint',this.order_uuid);
		},
		sendReceipt(value){
			//this.modal = false;
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
			postData+="&order_uuid="+this.order_uuid;
			axios.post( pos_api+"/printUsingThermal?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){		
					//this.modal = false;									
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
			postData+="&order_uuid="+this.order_uuid;			
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
		//
	},
};

const ComponentsNotifications = {
	template: '#xnotifications',
	props :['realtime_data','merchant_uuid'],
	data() {
		return {
			loading : false,
			count :0,
			data :[],
			pusher : null,
			channel: null,
		}
	},
	mounted() {
		this.getNotifications();		
		this.initPusher();
	},
	computed: {
		getData(){
			if (Object.keys(this.data).length > 0) {
				return this.data;
			}
			return [];      
		},
		hasData() {
			if (Object.keys(this.data).length > 0) {
				return true;
			}
			return false;
		},		
	},
	methods: {
		initPusher() {			
			if (Object.keys(this.realtime_data).length > 0) {
				console.log("realtime_data",this.realtime_data);
				Pusher.logToConsole = false;
				this.pusher = new Pusher( this.realtime_data.key , {
					cluster: this.realtime_data.cluster
				});
				this.channel = this.pusher.subscribe(this.merchant_uuid);
                this.channel.bind_global(this.handlePusherEvent);
			}
		},
		handlePusherEvent(eventName, data) {
			console.log(`Received Pusher event '${eventName}':`, data);														
			if (eventName != "pusher:subscription_succeeded") {				
				this.count++;
				this.playAlert();
				const newdata = {
					'message':data.message,
					'date':data.date
				};						
				this.data.unshift(newdata);				

				this.$emit("afterReceivenotification",data.meta_data);

				if(data.notification_type=="customer_request"){
					let title = data.message?data.message:'';
					title+="<br/>";
					title+=data.meta_data? (data.meta_data?data.meta_data.title:'') :'';
					quasarComponents.notify("occupied",title,'info','top-right',1000*20,'dark',[
						{
							label : translationVendor.view,
							color :"blue",
							'no-caps':true,
							handler: () =>{											
								this.$emit("onAlertactions",data.meta_data);
							}
						}
					]);										
				} else {					
					quasarComponents.notify("occupied",data.message,'info','top-right',1000*20,'dark',[
						{
							label : "View",
							color :"blue",
							'no-caps':true,
							handler: () =>{											
								this.$emit("onAlertactions",data.meta_data);
							}
						}
					]);	
				}
			}			
		},
		getNotifications(){
			this.loading = true;
			axios({
				method: 'GET',
				url: pos_api+"/getNotifications?language="+language ,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){ 		
						this.count = result.data.details.count;					
						this.data = result.data.details.data;
					} else {								
						this.count = 0;
						this.data = [];
					}                    					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.loading = false;				
			  });          
		},
		playAlert(){    		
    		this.player = new Howl({
			  src: ['../assets/sound/notify.mp3', '../assets/sound/notify.ogg' ],
			  html5: true,			  
			});
			this.player.play();
    	},
		clearNotifications(){
			const dialog = quasarComponents.dialog( translationVendor.clear_notifications );
			axios({
				method: 'GET',
				url: pos_api+"/clearNotifications?language="+language ,
					data : { },
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){ 		
						this.count = 0;
						this.data = [];						
					}                    					  
				}).catch(error => {	
				//
				}).then(data => {			     
					dialog.hide();
			  });          
		},
	},
};

const ComponentsContinuesalert = {
	props :['enabled_interval','interval_seconds'],
	data() {
		return {
			handle : undefined,
			loading : false,
		}
	},
	created() {		
		if(this.enabled_interval){			
			this.getTableorder();			
		}		
	},
	methods: {
		startRequest(){
			if(this.handle){
				clearInterval(this.handle);
			}

			let TimeInterval = 30000;
			if ((typeof this.interval_seconds !== "undefined") && ( this.interval_seconds !== null)) {
				TimeInterval = parseFloat(this.interval_seconds)*1000;
			}
			
			this.handle = setInterval(() => {
				this.getTableorder();
			}, TimeInterval);
		},
		getTableorder(){					
			axios({
				method: 'GET',
				url: pos_api+"/getTableneworder?language="+language,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){  						
						Object.entries(result.data.details.data).forEach(([key, items]) => {
							console.log("items",items);
							if(key==0){
								this.playAlert();
								quasarComponents.notify("occupied",items.title+"<br/>"+items.message,'info','top-right',1000*20,'dark',[
									{
										label : "View",
										color :"blue",
										'no-caps':true,
										handler: () =>{											
											this.$emit("onAlertactions",items.metadata);
										}
									}
								]);		
							} else {
								setTimeout(() => {
									this.playAlert();
									quasarComponents.notify("occupied",items.title+"<br/>"+items.message,'info','top-right',1000*20,'dark',[
										{
											label : "View",
											color :"blue",
											'no-caps':true,
											handler: () =>{												
												this.$emit("onAlertactions",items.metadata);
											}
										}
									]);		
								}, 1000*2);								
							}				
						});
					}                 					  
				}).catch(error => {	
				//
				}).then(data => {
					this.startRequest();
			  });          
		},
		playAlert(){    		
    		this.player = new Howl({
			  src: ['../assets/sound/notify.mp3', '../assets/sound/notify.ogg' ],
			  html5: true,			  
			});
			this.player.play();
    	},
	},
};

const ComponentsRequestitem = {
	template: '#xrequest_item_details',
	data() {
		return {
			modal : false,
			table_uuid : '',
			request :[]
		}
	},
};

const ComponentsRequestList = {
	template: '#xrequest_list',
	components :{
		'components-elapsetime' : ComponentsElapsetime
	},
	data() {
		return {
			loading : false,			
			data : null,
			field_data :{},
			selected_items:[],
			request_selected :[]
		}
	},
	mounted() {
		this.getCustomerRequest();
	},	
	computed: {
		hasData() {
			if(this.data){
				if (Object.keys(this.data).length > 0) {
					return true;
				}
			}			
			return false;
		},
	},
	methods: {		
		hasValue(table_uuid){
			return this.data[table_uuid].items.some(item => item.checked);
		},
		isLoading(table_uuid){
			return this.data[table_uuid]?this.data[table_uuid].loading:false;
		},
		getCustomerRequest(){
			//if(!this.data){
				this.loading = true;
				axios({
					method: 'GET',
					url: pos_api+"/getPendingRequestList?language="+language,
						headers: {
							Authorization: `token ${token}`,
						},
					}).then( response => {	 					
						if(response.data.code==1){       
							this.data = response.data.details.data;
						} else {						
							this.data = [];
						}
					}).catch(error => {	
					//
					}).then(data => {			     
						this.loading = false;				
				});     
			//}     
		},
		setCompleted(table_uuid){
			const checkedRequestIds = [];
			const data = this.data[table_uuid]?this.data[table_uuid]:null;				
			if(data){
				data.items.forEach(item => {				
					if (item.checked) {
						checkedRequestIds.push(item.request_id);
					}
				});
				let postData = {
					request_id : checkedRequestIds
				};
				data.loading = true;
				axios.post( pos_api+"/setRequestcompleted?language="+language , postData, headerAuthorization )
				.then(response => {								
					if(response.data.code==1){		
						 this.data = response.data.details.data;															
					} else {			
						quasarComponents.notify("myerror",response.data.msg,'error');			
					}
				})
				.catch(error => {				
					console.error('Error:', error);
				}).then(data2 => {		
					data.loading = false;	     												
				});          				
			}			
		},
	},
};

let html5QrCode = null;
const ComponentsBarcode = {
	template: '#xbarcode_template',
	props : ['customer_id'],
	data() {
		return {
			modal : false,
			is_scan : false,
			html5QrCode : null,
			loading : false,
			scan_loading : false,
		}
	},
	mounted() {				
	},
	methods: {		
		onBeforeshow(){

		},
		startScan(){
			this.is_scan = !this.is_scan;						
			this.scan_loading = true;
			setTimeout(() => {
				html5QrCode = new Html5Qrcode("reader");								
				html5QrCode.start(
					{ facingMode: "environment" }, // Use back camera
					{ fps: 10, qrbox: 250 },
					(decodedText) => {						
						console.log("decodedText",decodedText);
						this.findItemBarcode(decodedText);
					},
					(error) => { console.warn(error); }
				).then(() => {					
					this.scan_loading = false;
				}).catch(err => {					
					quasarComponents.notify("myerror",err,'error');			
				});
								
			 }, 500); 			
		},
		stopScan(){			
			html5QrCode.stop().then(() => {                
				html5QrCode = null;
				this.is_scan = false;
            }).catch(err => quasarComponents.notify("myerror",err,'error') );
		},
		onBeforehide(){
			if(this.is_scan){
				this.stopScan();
			}
		},
		findItemBarcode(value){					
			this.loading = true;
			this.stopScan();
			let postData = {
				barcode : value,
				customer_id : this.customer_id
			};
			axios.post( pos_api+"/findItemBarcode?language="+language , new URLSearchParams(postData).toString(), headerAuthorization )
			.then(response => {								
				if(response.data.code==1){
					const results = response.data.details.data;					
					this.$emit("afterScan",results);
					this.modal = false;									
				} else {			
					quasarComponents.notify("myerror",response.data.msg,'error');			
				}
			})
			.catch(error => {								
				quasarComponents.notify("myerror", error ,'error');
			}).then(data2 => {		
				this.loading = false;	     												
			});          				
		},
		//
	},
};


const globalPrint = {
  webSocket: null,
  socket_error : null,  

  async ConnectToPrintServer() {
    let printer_server = !quasarComponents.empty(printerServer) ? printerServer : null;

    if (this.webSocket == null && printer_server) {
      console.log("CONNECT ConnectToPrintServer");
      try {
        this.webSocket = await new Promise((resolve, reject) => {
          const ws = new WebSocket(printer_server);

          ws.onopen = () => {
            console.log("WebSocket is open now.");
            resolve(ws);
          };

          ws.onerror = (event) => {
            console.error("WebSocket error", event);
            this.socket_error = translationVendor.websocket_error;
            if (event instanceof ErrorEvent) {
              this.socket_error = event.message;
            }
            reject(event);
          };

          ws.onmessage = (event) => {
            console.log("Received message from server:", event.data);
          };

          ws.onclose = () => {
            console.log("WebSocket is closed now.");
            this.webSocket = null;
          };
        });

        console.log("WebSocket connected and assigned.");
		 return true;
      } catch (error) {
        console.warn("Failed to connect to WebSocket.");
        this.webSocket = null;
		 return false;
      }
    }	
	return this.webSocket !== null;
  },

  async sendServerToPrinter(data) {
	if (this.webSocket && this.webSocket.readyState === WebSocket.OPEN) {
		try {
			this.webSocket.send(JSON.stringify(data));
			return true;
		} catch (err) {
		   console.warn("Send failed:", err);
		   return false;
		}
	} else {
		console.warn("WebSocket is not connected.");
		return false;
	}
  }
  
};
//globalPrint


// START HERE
const app_pos = Vue.createApp({   	
	components : {
		'components-searchcustomer' : ComponentsSearchcustomer,
		'components-customer' : ComponentsCustomer,
		'components-item' : ComponentsItemdetails,
		'components-addresslist' : ComponentsAddressList,
		'components-newaddress' : ComponentsNewaddress,
		'components-promo'	: ComponentsDiscount,
		'components-discount'	: ComponentsDiscount,
		'components-tips'	: ComponentsDiscount,
		'components-points'	: ComponentsPoints,
		'components-addtotal' : ComponentsDiscount,
		'components-holdorder' : ComponentsHoldorder,
		'components-payment' : ComponentsPayment,
		'components-receipt' : ComponentsReceipt,
		'components-selecttable' : ComponentsSelectTable,
		'components-makepayment' : ComponentsMakepayment,
		'components-orders' : ComponentsOrders,
		'components-choose-receipt':ComponentsChooseReceipt,
		'components-notifications':ComponentsNotifications,
		'components-continuesalert': ComponentsContinuesalert,
		'components-requestitem' : ComponentsRequestitem,
		'components-request-list' : ComponentsRequestList,
		'components-elapsetime' : ComponentsElapsetime,
		'components-barcode' : ComponentsBarcode,
	},
    data() {
        return {
            page_ready : false,			
            drawer : false,            
			menu_layout : 'column',
			mobile_tab : 'pos',
			category_id : '',
			category_data : [],
			category_loading : false,
			items_loading : true,
			items : true,
			attributes_loading : false,
			attributes_data : [],
			transaction_list : [],
			transaction_type : '',	
			cart_transaction_type : '',
			modal_visible : true,	
			payload: [
				"items",
				"merchant_info",
				"service_fee",
				"delivery_fee",
				"packaging",
				"tax",
				"tips",
				"checkout",
				"discount",
				"distance_local_new",
				"summary",
				"total",
				"subtotal",
				"items_count",
				"points",
				"points_discount",
			],					
			items_count: 0,
			cart_items : [],
			loading_cart : false,
			refresh_cart : false,
			cart_summary : [],
			cart_total : [],
			cart_error : [],
			cart_subtotal : [],
			customer_data : [],
			order_reference : "",
			pos_address : [],
			q :'',
			awaitingSearch : false,
			item_results : [],
			table_uuid : '',
			room_uuid : '',
			table_data :[],
			send_kds_loading: false,
			table_list : [],
			pos_edit_cart : false,
			total_sendorder : 0,
			total_unsendorder : 0,
			skip_kitchen : false,
			view : 'new_view', //view : 'new_view', order_view
			view_list : [],
			do_search : false,
			table_status_loading : false,
			player : null,
			whento_deliver : "now",
			delivery_date : '',
			delivery_time : '',
			sidebar : false,
			sidebar_menu :[],
			points_data : null,
			customerID : null,
			modal_barcode : false,
			promoEligibility : {}
        }
    },
	created() {
		this.posAttributes();
		this.getTableStatus();
		this.BackofficeMenu();
	},
    mounted() {        
				
        this.page_ready=true;
		let menu_layout = quasarComponents.getStorage('pos_menu_layout');		
		if(!quasarComponents.empty(menu_layout)){
			this.menu_layout = menu_layout;
		}
		this.CategoryList();		
		this.initialCart();

		let pos_address = quasarComponents.getStorage("pos_address");
		this.pos_address = !quasarComponents.empty(pos_address)?pos_address:'';

		let skip_kitchen = quasarComponents.getStorage("skip_kitchen");
		if(skip_kitchen){
			this.skip_kitchen = skip_kitchen;
		}		
		
		if(this.view!="new_view"){			
			setTimeout(() => {
				this.drawer = false;
			 }, 1000); 			
		}
				
    },
	computed: {				
		getOrderStatus(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.order_status_list;
			}
			return [];      
		},
		getDefaultCustomer(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.default_customer;
			}
			return [];      
		},
		whatsAppEnabled(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.whatsapp_enabled;
			}
			return false;     
		},
		printerList(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (Object.keys(this.attributes_data.printer_list).length > 0) {
				    return this.attributes_data.printer_list;
				}
			}
			return false;     
		},
		isTableEmpty(){
			if(quasarComponents.empty(this.table_uuid)){
				return true;
			}
			return false;
		},
		getRoomList(){
			if (Object.keys(this.attributes_data).length > 0) {
				return this.attributes_data.room_list;
			}
			return [];      
		},
		hasRooms(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (Object.keys(this.attributes_data.room_list).length > 0) {
					return true;
				}
			}
			return false;
		},
		getTableList(){
			if (Object.keys(this.table_list).length > 0) {
				if (this.table_list[this.room_uuid]) {
					return this.table_list[this.room_uuid];
				}
			}
			return [];      
		},		
		hasTableList(){
			if (Object.keys(this.table_list).length > 0) {
				if (this.table_list[this.room_uuid]) {
					return true;
				}
			}
			return false;
		},				
		getCategory() {
			return this.category_data;
		},
		getItems() {
			return this.items;
		},
		hasItems() {
			if (Object.keys(this.items).length > 0) {
				return true;
			}
			return false;
		},
		getTransactionList() {
			return this.attributes_data.transaction_list;
		},
		hasCart(){
			if (Object.keys(this.cart_items).length > 0) {
				return true;
			}
			return false;
		},
		getCartItems(){
			return this.cart_items;
		},
		isLoading() {
			if (this.loading_cart) {
			  return true;
			} else {
			  if (this.refresh_cart) {
				return true;
			  }
			}
			return false;
		},
		getSummary() {
			return this.cart_summary;
		},
		customerSelected(){
			if (Object.keys(this.customer_data).length > 0) {
				return true;
			}
			return false;
		},
		hasAddress(){			
			if (Object.keys(this.pos_address).length > 0) {
				return true;
			}
			return false;
		},
		getAddress(){			
			if (Object.keys(this.pos_address).length > 0) {
				return this.pos_address;
			}			
			return false;
		},
		isSearch(){
			if(!quasarComponents.empty(this.q)){
				return true;
			}
			return false;
		},
		searchResults(){
			if (Object.keys(this.item_results).length > 0) {
				return true;
			}
			return false;
		},
		hasCartError(){			
			if (Object.keys(this.cart_error).length > 0) {
				return true;
			}

			if (Object.keys(this.cart_items).length > 0) {
				if (Object.keys(this.customer_data).length <= 0) {
					return true;
				}
				if(this.transaction_type=="delivery"){
					if (Object.keys(this.pos_address).length <= 0) {
						return true;
					}
				} else if (this.transaction_type=="dinein" ){
					if(quasarComponents.empty(this.table_uuid)){
						return true;
					}
				}				
		    }
			
			return false;
		},
		getCartError(){			
			if (Object.keys(this.cart_error).length > 0) {
				return this.cart_error;
			}

			let error = [];
			if (Object.keys(this.cart_items).length > 0) {				
				if (Object.keys(this.customer_data).length <= 0) {			
					error.push(translationVendor.please_select_customer);				
					return error;
				}
				if(this.transaction_type=="delivery"){
					if (Object.keys(this.pos_address).length <= 0) {
						error.push(translationVendor.delivery_address_required);				
						return error;
					}
				} else if (this.transaction_type=="dinein" ){
					if(quasarComponents.empty(this.table_uuid)){
						error.push(translationVendor.table_number_required);				
						return error;
					}
				}
		    }			

			return false;
		},
		hasTabledata(){			
			if (Object.keys(this.table_data).length > 0) {
				return true;
			}
			return false;
		},
		isEdit(){
			return this.pos_edit_cart;
		},		
		isChangeTransaction(){
			if(this.cart_transaction_type){
				if(this.cart_transaction_type!=this.transaction_type){
					return true;
				}
			}			
			return false;
		},
		isNeedtosendorder(){			
			if(this.total_unsendorder>0){
				return true;
			}
			return false;
		},
		getOpeningDates() {			
			let data = [];
			if (Object.keys(this.attributes_data).length > 0) {
				Object.entries(this.attributes_data.opening_hours.dates).forEach(
					([key, items]) => {
					data.push({
						label: items.name,
						value: items.value,
					});
					}
				);
				return data;
			}
			return data;
		},
		getTimelist(){
			if (Object.keys(this.attributes_data).length > 0) {
				if (!quasarComponents.empty(this.attributes_data.opening_hours.time_ranges[this.delivery_date])) {
					return this.attributes_data.opening_hours.time_ranges[this.delivery_date];
				}
			}
			return [];
		},  		
		isBarcodeactive(){
			return this.attributes_data?.enabled_barcode ?? false;
		},		
	}, // end computed
	watch: {
		q(newsearch,oldsearch){
			if (!this.awaitingSearch) {
			   if(quasarComponents.empty(newsearch)){
					return false;
			   }

			   this.view = 'new_view';
			   setTimeout(() => {				   
				   let postData = "q="+this.q;
				   axios.post( pos_api+"/searchfooditems?language="+language , postData, headerAuthorization )
					.then(response => {								
						if(response.data.code==1){											
							this.item_results = response.data.details.data;														
						} else {
							this.item_results = [];
						}
					})
					.catch(error => {				
						console.error('Error:', error);
					}).then(data => {			     							
						this.awaitingSearch = false;
					});          

			   }, 1000);
			}

			this.item_results = [];
	        this.awaitingSearch = true;
		},		
	},
	methods: {
		onselectCustomer(value){
			console.log("onselectCustomer",value);
			this.customerID = value;
		},
		BackofficeMenu(){
			axios({
				method: 'GET',
				url: ajaxurl+"/backofficemenu?language="+language,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){          
						this.sidebar_menu = result.data.details;           											
					} else {						
						this.sidebar_menu = [];
					}                   					  
				}).catch(error => {	
				//
				}).then(data => {					
			  });          
		},
		updateDeliveryDate(){		
			this.delivery_time = '';
		},
		getCartUUID(){
			let cart_uuid = quasarComponents.getStorage("pos_cart_uuid");		
			return !quasarComponents.empty(cart_uuid)?cart_uuid:'';
		},
		getLocalID(){
			let local_id = quasarComponents.getStorage("pos_local_id");
			return !quasarComponents.empty(local_id)?local_id:'';
		},		
		posAttributes(){
			this.attributes_loading = true;
			axios({
				method: 'GET',
				url: pos_api+"/posAttributes?language="+language,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){                     					
						this.attributes_data = result.data.details;

						let transactionType = quasarComponents.getStorage("pos_transaction_type");						
						this.transaction_type = !quasarComponents.empty(transactionType)? transactionType :  this.attributes_data.transaction_type;

						Object.entries(this.attributes_data.transaction_list).forEach(([key, items]) => {							
							this.transaction_list.push({
								label : items.service_name,
								value : items.service_code,
							});
						});		
						
						if (Object.keys(result.data.details.room_list).length > 0) {							
							const [firstKey] = Object.keys(result.data.details.room_list);
                            this.room_uuid = firstKey;							
						}

						this.view_list = result.data.details.view_list;

					} else {
						this.attributes_data = [];
					}                   					  
				}).catch(error => {	
				//
				}).then(data => {			     							
					this.attributes_loading = false;
			  });          
		},
		setMenuLayout(){			
			this.menu_layout = this.menu_layout=='column'?'grid':'column';
			quasarComponents.setStorage("pos_menu_layout",this.menu_layout);
		},
		CategoryList(){			
			this.category_loading = true;
			axios({
				method: 'GET',
				url: pos_api+"/CategoryList?language="+language,
					data : {  					
					},
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){                     					
						this.category_data = result.data.details.data;
						this.category_id =  result.data.details.data[0].cat_id;
						this.categoryItems();
					} else {
						this.items = [];
						this.items_loading = false;
					}                    					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.category_loading = false;				
			  });          
		},
		loadItems(data) {
			this.category_id = data;
			this.categoryItems();
		},
		categoryItems(){			
			this.items_loading = true;
			axios({
				method: 'GET',
				url: pos_api+"/categoryItems?language="+language+"&cat_id="+this.category_id,					
					headers: {
						Authorization: `token ${token}`,
					},
				}).then( result => {	 					
					if(result.data.code==1){                     					
						this.items = result.data.details.data;
					}                     					  
				}).catch(error => {	
				//
				}).then(data => {			     
					this.items_loading = false;				
			  });          
		},		
		afterCreatecustomer(data){						
			this.$refs.customer_search.customer_name = data.data;
			this.customer_data = data;
		},
		afterSelectcustomer(data){
			console.log("afterSelectcustomer",data);
			this.customer_data = data;
			this.refreshCart();
		},
		clearSelectcustomer(){
			this.customer_data = [];
			this.pos_address = [];
			quasarComponents.setStorage('pos_address','');
		},
		clearAddress(){
			console.log("clearAddress");
			this.pos_address = [];
			quasarComponents.setStorage('pos_address','');
		},
		initialCart(){
			let cart_uuid = this.getCartUUID();
			let local_id = this.getLocalID();
			let payload = quasarComponents.getStorage("pos_payload");
			payload = payload?payload:null;
			
			let is_edit = quasarComponents.getStorage('pos_edit_cart');
			this.pos_edit_cart = is_edit;
			
			this.GetCart(cart_uuid,local_id,payload,false);
		},
		refreshCart(){
			let cart_uuid = this.getCartUUID();
			let local_id = this.getLocalID();
			let payload = quasarComponents.getStorage("pos_payload");
			payload = payload?payload:null;

			this.GetCart(cart_uuid,local_id,payload,true);
		},
		GetCart(cart_uuid, local_id,payload,refresh){
			this.loading_cart = refresh ? false : true;
						
			this.$refs.bar.start();
			const postData = {
				cart_uuid: !quasarComponents.empty(cart_uuid)?cart_uuid:'',
				local_id: !quasarComponents.empty(local_id)?local_id:'',
				payload: !quasarComponents.empty(payload) ? payload : this.payload,
			};
			axios.post( pos_api+"/getCart?language="+language , postData, {
				headers: {
					'Authorization': `token ${token}`,					
				}
			})
			.then(response => {		
				let data = response.data;	
				//console.log("getcart data",data);
				if(response.data.code==1){	
					this.items_count = data.details.items_count;
					this.cart_items = data.details.data.items;									
					this.cart_summary = data.details.data.summary;
					this.cart_total = data.details.data.total;
					this.cart_error = data.details.error;
					this.cart_subtotal = data.details.data.subtotal;
					this.customer_data = data.details.customer_data;
					this.order_reference = data.details.order_reference;
					this.points_data = data.details.points_data;


					this.transaction_type = data.details.checkout_data.transaction_type;
					this.cart_transaction_type = data.details.checkout_data.transaction_type;

					if (Object.keys(data.details.customer_data).length > 0) {
						this.$refs.customer_search.customer_name = data.details.customer_data.data[0];						
						this.onselectCustomer(data.details.customer_data.id);
					}										

					this.table_data = data.details.table_data;
					this.total_sendorder = data.details.total_sendorder;
					this.total_unsendorder = data.details.total_unsendorder;

					this.pos_address = data.details.address_component;
					
					let checkout_data = data.details.checkout_data?data.details.checkout_data.data:[];
					console.log("checkout_data",checkout_data);
					this.whento_deliver = checkout_data.whento_deliver;	
					this.delivery_date = checkout_data.delivery_date;
					this.delivery_time = checkout_data.delivery_time;
					
				} else {				
					this.cart_error = [];
					this.items_count = 0;
					this.cart_uuid = "";
					this.cart_items = [];
					this.cart_summary = [];
					this.cart_total = [];
					this.error = [];
					this.cart_subtotal = [];
					this.customer_data = [];
					this.order_reference = "";
					this.points_data = null;					
					if(data.details){					
						this.table_data = data.details.table_data;					
						if(!quasarComponents.empty(data.details.customer_data)){							
							this.onselectCustomer(data.details.customer_data.value);
							this.$refs.customer_search.customer_name = data.details.customer_data;
						}
				    }
				}

				
				//console.log("table_data",this.table_data);
				if(this.table_data.table_uuid){
					this.table_uuid = this.table_data.table_uuid;
				} else {
					this.table_uuid = '';
				}

				let cart_uuid = quasarComponents.getStorage("pos_cart_uuid");	
				if(quasarComponents.empty(cart_uuid)){
					quasarComponents.setStorage("pos_cart_uuid",response.data.details.cart_uuid);
				}		
				
				if(this.$q.screen.lt.sm){
					//this.drawer = false;					
				} else {					
					this.drawer = true;
				}									
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.loading_cart = false;
				this.refresh_cart = false;				
				this.$refs?.bar?.stop();
		    });         
			
			this.promoCheck();
		},
		async promoCheck(){		   
		    try {				
				const params = new URLSearchParams({					
					cart_uuid: this.getCartUUID(),								
				}).toString();
				//const response = await axios.get(pos_api+"/PromoCheck?"+params);								
				const response = await axios.get(
				`${pos_api}/PromoCheck?${params}`,
				headerAuthorization
				);				
				if(response.data.code==1){
					this.promoEligibility  = response.data.details.data;					
				} else {
					this.promoEligibility  = [];
				}
			} catch (error) {
				//console.error('Error fetching data:', error);
			}
	    },
		setTransactionType(val){								
			if (Object.keys(this.cart_items).length > 0) {
				if(this.pos_edit_cart){
					//
				} else {
					this.$refs.bar.start();
					let cart_uuid = this.getCartUUID();	
					axios.post( pos_api+"/setTransactionType?language="+language , "cart_uuid="+cart_uuid + "&transaction_type="+val, {
						headers: {
							'Authorization': `token ${token}`,					
						}
					})
					.then(response => {								
						if(response.data.code==1){	
							this.refreshCart();
						} 
					})
					.catch(error => {				
						console.error('Error:', error);
					}).then(data => {			     												
						this.$refs?.bar?.stop();
					});          											
				}				
			} else {
				quasarComponents.setStorage("pos_transaction_type",val);
				if(val=="dinein"){
					this.getTableStatus();
				}				
			}
		},
		viewItems(val){			
			
			const is_item_eligible = this.isEligible(val);
			if(!is_item_eligible){
				quasarComponents.notify("myerror", this.getPromoMessage(val) ,'error');
				return;
			}
			
			this.$refs.item_details.item_data = {
				'item_uuid' : val.item_uuid,
				'cat_id' : this.category_id
			};
			 this.$refs.item_details.dialog = true;
		},
		afterAddtocart(){			
			console.log("afterAddtocart");
			this.playScan();
			this.refreshCart();
		},
		afterAddguestnumber(){
			this.view = 'new_view';
			if(this.transaction_type!='dinein'){
				this.transaction_type = 'dinein';
			}			
			this.refreshCart();
		},
		itemShow(){
			console.log("itemShow");
			this.modal_visible = false;
		},
		itemHide(){
			console.log("itemHide");
			this.modal_visible = true;
		},
		lessCartQty(itemQty, item) {
			itemQty--;
			if (itemQty > 0) {
			  this.updateCartQty(itemQty, item);
			} else {
			  this.removeCartItem(item.cart_row);
			}
		},
		addCartQty(itemQty, item) {
			itemQty++;
			this.updateCartQty(itemQty, item);
		},
		updateCartQty(itemQty, item) {
			this.refresh_cart = true;
			let postData = "cart_uuid="+this.getCartUUID() + "&row="+item.cart_row + "&item_qty="+itemQty;
			axios.post( pos_api+"/updateCartItems?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){		
					this.playScan();		
					this.refreshCart();
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.refresh_cart = false;
		    });          
		},
		removeCartItem(cart_row) {
			this.refresh_cart = true;
			let postData = "cart_uuid="+this.getCartUUID() + "&row="+cart_row;
			axios.post( pos_api+"/removeCartItem?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){				
					this.refreshCart();
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.refresh_cart = false;
		    });          
		},
		resetConfirm(){
			this.$q
			.dialog({
			  title: translationVendor.clear_all_items,
			  message: translationVendor.are_you_sure,
			  transitionShow: "fade",
			  transitionHide: "fade",
			  cancel: true,
			  ok: {
				unelevated: true,
				color: "primary",
				rounded: true,
				"text-color": "white",
				size: "md",
				label: translationVendor.confirm,
				"no-caps": true,				
			  },
			  cancel: {
				unelevated: true,
				color: "grey-5",
				"text-color": 'white',
				rounded: true,
				outline: false,
				size: "md",
				label: translationVendor.cancel,
				"no-caps": true,
			  },
			})
			.onOk(() => {
			  this.DeleteCart();
			});
		},
		DeleteCart(){			
			this.$refs.bar.start();
			let postData = "cart_uuid="+this.getCartUUID();
			axios.post( pos_api+"/clearCart?language="+language , postData, headerAuthorization )
			.then(response => {								
				if(response.data.code==1){			
					quasarComponents.setStorage('pos_address','');
					this.resetCart();
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {			     							
				this.$refs.bar.stop();
		    });          
		},
		resetCart(){
			this.whento_deliver = 'now';
			this.delivery_time = '';
			this.delivery_date = '';

			this.address_data = [];
			this.pos_address = [];
			this.customer_data = [];
			this.$refs.customer_search.customer_name='';
			quasarComponents.setStorage('pos_local_id','');
			quasarComponents.setStorage('pos_address','');
			quasarComponents.setStorage('pos_cart_uuid','');
			quasarComponents.setStorage('pos_payload','');
			quasarComponents.setStorage('pos_edit_cart',false);

			this.payload = this.payload.filter(key => key !== "all_orders");
			this.total_unsendorder = 0;
			this.total_sendorder = 0;
			this.cart_transaction_type = '';	
			this.view = 'new_view';		

			this.pos_edit_cart = false;
			this.refreshCart();
			this.getTableStatus();
		},
		showNewaddress(){						
			this.$refs.new_address.modal = true;
		},
		afterSaveaddress(data){			
			console.log("afterSaveaddress",data);			
			this.pos_address = data;			
			this.refreshCart();
		},
		removePromocode(){			
			this.$refs.bar.start();
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid");
			axios.post( pos_api+"/removePromocode?language="+language , postData, headerAuthorization)
			.then(response => {		
				if(response.data.code==1){				
					this.refreshCart();
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {		
				this.$refs.bar.stop();  											
			});          
		},
		removeTips(){			
			this.$refs.bar.start();
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid");
			axios.post( pos_api+"/removeTips?language="+language , postData, headerAuthorization)
			.then(response => {		
				if(response.data.code==1){				
					this.refreshCart();
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {		
				this.$refs.bar.stop();  											
			});          
		},
		removePoints(){			
			this.$refs.bar.start();
			let postData ="cart_uuid=" + quasarComponents.getStorage("pos_cart_uuid");
			axios.post( pos_api+"/removePoints?language="+language , postData, headerAuthorization)
			.then(response => {		
				if(response.data.code==1){				
					this.refreshCart();
				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			})
			.catch(error => {				
				console.error('Error:', error);
			}).then(data => {		
				this.$refs.bar.stop();
			});          
		},
		async afterHoldcart(data){			
			this.resetCart();
			
			const wifi_printers = data?.wifi_printers || null;
			const cart_uuid = data?.cart_uuid;			
			
			const connected = await globalPrint.ConnectToPrintServer();
			console.log("connected",connected);
			if (!connected) {			  
			   quasarComponents.notify("myerror",globalPrint.socket_error,'error');		
			   return;		
			}

			if (wifi_printers && wifi_printers.length > 0) {				
				wifi_printers.forEach(printer => {			

					 let postData = "printer_id="+printer.printer_id;
			         postData+="&cart_uuid="+cart_uuid;
					 console.log("postData",postData);

					 const dialog = quasarComponents.dialog(translationVendor.printing + "<br/>"+ translationVendor.close_window);
					 axios.post( pos_api+"/wifiPrintOT?language="+language , postData, headerAuthorization ).then(async response => {						
						if(response.data.code==1){										
							const printResponse = await globalPrint.sendServerToPrinter(response.data.details.data);
							console.log("printResponse",printResponse);
							if(printResponse){
								quasarComponents.notify("mysuccess",response.data.msg,'info');
							} else {
								const printError = translationVendor.websocket_is_not_open + globalPrint.webSocket.readyState;
								quasarComponents.notify("myerror",printError,'error');
							}
						} else {
							quasarComponents.notify("myerror",response.data.msg,'error');					
						}
					}).catch(error => {				
						console.error('Error:', error);
					}).then(data => {			     							
						dialog.hide();
					});          				

				});
			}			
		},		
		afterDeleteorders(){
			console.log('afterDeleteorders');
			this.resetCart();
		},
		afterPayment(data){		
			console.log("afterPayment",data);
			this.resetCart();
			this.$refs.choose_receipt.order_uuid = data.order_uuid;
			this.$refs.choose_receipt.email_address = data.email_address;
			this.$refs.choose_receipt.mobile_number = data.contact_phone;
			this.$refs.choose_receipt.order_id = data.order_id;
			this.$refs.choose_receipt.success_message = data.success_message;
			this.$refs.choose_receipt.modal = true;

			const wifi_printers = data.wifi_printers || null;
			console.log("wifi_printers",wifi_printers);
			if (wifi_printers && wifi_printers.length > 0) {				
				wifi_printers.forEach(printer => {					 
					 this.$refs.choose_receipt.wifiPrint(printer.printer_id);
				});
			}

		},
		showSelectGuestNumber(data){			
			console.log("data",data);
			if(data.cart_uuid){					
				if (Object.keys(this.cart_items).length > 0) {
					quasarComponents.notify("myerror", translationVendor.table_not_available ,'error');
				} else {
					this.payload.push('all_orders');				
					quasarComponents.setStorage("pos_cart_uuid",data.cart_uuid);
					quasarComponents.setStorage("pos_payload",this.payload);
					quasarComponents.setStorage("pos_edit_cart",true);
					this.pos_edit_cart = true;					
					this.GetCart(data.cart_uuid,null,this.payload,null);
				}						
			} else {
				this.$refs.selectable.table_details = data;			
			    this.$refs.selectable.modal = true;
			}			
		},
		editTable(){
			console.log("editTable",this.table_data);						
			this.showSelectGuestNumber(this.table_data);
		},
		SendToKitchen(){			
			
			this.send_kds_loading = true;
			const dialog = this.$q.dialog({
				message: translationVendor.sending_orders,
				progress: true, 
				persistent: true,
				ok: false 
			});

			let postData = "cart_uuid="+ this.getCartUUID();
			postData+="&table_number="+ this.table_uuid;
			postData+="&transaction_type="+ this.transaction_type;
			postData+="&client_id="+ (this.customer_data?this.customer_data.id:'');
			postData+="&whento_deliver="+this.whento_deliver;
			postData+="&delivery_date="+this.delivery_date;
			postData+="&delivery_time="+this.delivery_time;

			axios.post( pos_api+"/SendToKitchen?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){				
					quasarComponents.notify("mysuccess",response.data.msg,'info');		
					this.resetCart();					
				 } else {					 
					quasarComponents.notify("myerror",response.data.msg,'error');
				 }
			 })
			 .catch(error => {				
				 console.error('Error:', error);
			 }).then(data => {			 
				this.send_kds_loading = false;    							
				dialog.hide();
			 });          
		},
		getTableStatus(){	
			this.table_status_loading = true;
			axios.post( pos_api+"/getTableStatus?language="+language , '', headerAuthorization )
			 .then(response => {								 
				 if(response.data.code==1){		
					this.table_list = response.data.details.table_list;											
				 } else {			
					this.table_list = [];
				 }
			 })
			 .catch(error => {				
				 console.error('Error:', error);
			 }).then(data => {		
				this.table_status_loading = false;	 				
			 });          
		},
		getStatusClass(value){
			return "table-"+ value;
		},
		closeOrder(){
			this.resetCart();
		},
		UpdateTransactions(){
			const dialog = this.$q.dialog({
				message: 'Updating orders',
				progress: true, 
				persistent: true,
				ok: false 
			});
			let postData = "cart_uuid="+ this.getCartUUID();			
			postData+="&transaction_type="+ this.transaction_type;
			postData+="&cart_transaction_type="+ this.cart_transaction_type;
			axios.post( pos_api+"/UpdateTransactions?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){		
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
		isNeedtosendorders(){
			// if(this.total_sendorder>0 && this.total_unsendorder>0){
			// 	return true;
			// }
			if(this.total_unsendorder>0){
				return true;
			}
			return false;
		},
		MakePayment(){
			console.log("MakePayment",this.skip_kitchen);
			if(this.isNeedtosendorders() && !this.skip_kitchen){
				this.$q.dialog({
					class: "myowndialog",
					title: "Confirm",
					message:
					"<span class='text-body2'>" + translationVendor.unabled_to_pay +
					".</span>" +
					"<br/><br/><span class='text-body2 text-weight-bold'>" + translationVendor.clear_items +
					"</span>",
					html: true,
					cancel: true,
					persistent: true,
					ok: {
					unelevated: true,
					color: "green",
					rounded: false,
					"text-color": "white",					
					label: translationVendor.continue,
					"no-caps": true,
					class: "btn-width-normal",
					},
					cancel: {
					unelevated: true,
					color: "grey",
					"text-color":'grey-7',
					outline: true,
					rounded: false,					
					label: translationVendor.cancel,
					"no-caps": true,
					class: "btn-width-normal",
					},
				})
				.onOk(() => {
					this.clearNewitems();
				})
				.onCancel(() => {})
				.onDismiss(() => {});
			} else {
				console.log('makepayment');
				this.$refs.makepayment.modal=true
			}
		},
		clearNewitems(){
			const dialog = quasarComponents.dialog(translationVendor.clearing_items);
			let postData = "cart_uuid="+ this.getCartUUID();			
			axios.post( pos_api+"/clearNewitems?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){					
					//this.refreshCart();
					this.initialCart();
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
		onSelectSkipkitchen(data){
			console.log("onSelectSkipkitchen",data);			
			quasarComponents.setStorage("skip_kitchen",data);
		},
		removeTable(){
			console.log("removeTable");
			this.table_uuid = '';
			this.table_data = [];
		},
		updateView(data){
			console.log("updateView",data);
			if(data=='new_view'){
				if(this.$q.screen.lt.sm){
					this.drawer = false;
				} else this.drawer = true;				
			} else this.drawer = false;

			switch (data) {
				case "table_view":
					this.getTableStatus();					
					break;				
			}
		},
		loadOrders(data){
			console.log("loadOrders",data);
			let payload = this.payload;
			payload.push('all_orders');				
			quasarComponents.setStorage("pos_cart_uuid",data.cart_uuid);
			quasarComponents.setStorage("pos_payload",payload);
			quasarComponents.setStorage("pos_edit_cart",true);
			this.pos_edit_cart = true;					
			this.view = "new_view";
			this.GetCart(data.cart_uuid,null,payload,null);
		},
		deleteConfirm(){
			this.$q
			.dialog({
			  title: translationVendor.confirm_deletion,
			  message: translationVendor.delete_records_confirm,
			  transitionShow: "fade",
			  transitionHide: "fade",
			  cancel: true,
			  ok: {
				unelevated: true,
				color: "primary",
				rounded: true,
				"text-color": "white",
				size: "md",
				label: translationVendor.yes,
				"no-caps": true,				
			  },
			  cancel: {
				unelevated: true,
				color: "grey-5",
				"text-color": 'white',
				rounded: true,
				outline: false,
				size: "md",
				label: translationVendor.cancel,
				"no-caps": true,
			  },
			})
			.onOk(() => {
			  this.deleteOrders();
			});
		},
		deleteOrders(){					
			const dialog = quasarComponents.dialog(translationVendor.deleting_records);			
			let postData = "cart_uuid="+this.getCartUUID();
			axios.post( pos_api+"/deleteOrders?language="+language , postData, headerAuthorization )
			 .then(response => {								
				 if(response.data.code==1){	
					this.resetCart();
				 } else {				
					quasarComponents.notify("myerror",error,'error');						
				 }
			 })
			 .catch(error => {				
				 console.error('Error:', error);
			 }).then(data => {																				 
				dialog.hide();
			 });          	
		},
		webPrint(value){
			console.log("webPrint",value);
			this.$refs.receipt.order_uuid = value;
			this.$refs.receipt.modal = true;
		},
		closeSearch(){
			this.do_search = false;
			this.q='';
		},
		beforeShowaddress(){
			console.log("beforeShowaddress",this.customer_data.id);
			if(this.customer_data.id>0){
				this.$refs.address_list.dialog=true;
			} else {				
				this.$refs.new_address.modal=true;
			}
		},
		playAlert(){    		
    		this.player = new Howl({
			  src: ['../assets/sound/notify.mp3', '../assets/sound/notify.ogg' ],
			  html5: true,			  
			});
			this.player.play();
    	},
		onAlertactions(value){			
			console.log("onAlertactions",value);			
			switch (value.notification_type) {
				case "order":		
				case "request_bill":					
					let payload = this.payload;
					payload.push('viewed');
					payload.push('all_orders');				
					quasarComponents.setStorage("pos_cart_uuid",value.cart_uuid);
					quasarComponents.setStorage("pos_payload",payload);
					quasarComponents.setStorage("pos_edit_cart",true);
					this.pos_edit_cart = true;					
					this.view = "new_view";
					this.GetCart(value.cart_uuid,null,payload,null);			
					break;
				case "call_staff":
					console.log("request_id",value.request_id);
				    this.view = 'table_request';
					break;
				case "table_payment":
					window.parent.location.href = value.url;
					break;
			}
		},
		afterReceivenotification(data){
			console.log("afterReceivenotification",data);
			const notification_type = data?data.notification_type:null;
			console.log("notification_type",notification_type);
			console.log("view",this.view);
			if(notification_type=="call_staff" && this.view=="table_request"){					
				this.$refs.request_list.getCustomerRequest();			
			} else if ( notification_type=="order" && this.view=="order_view" ){
				this.$refs.ref_order_list.getPOSorders(null);
			} else if ( notification_type=="table_payment" && this.view=="order_view" ){
				this.$refs.ref_order_list.getPOSorders(null);
			}
		},		
		OpenParentLink(link){
			console.debug("OpenParentLink",link);
			window.parent.location.href = link;
		},
		showScanbarcode(){			
			this.$refs.ref_barcode.modal = true;
		},
		playScan(){    		
    		this.player = new Howl({
			  src: ['../assets/sound/scan.mp3'],
			  html5: true,			  
			});
			this.player.play();
    	},
		afterScan(value){			
			this.playScan();
			this.$refs.item_details.item_data = {
				'item_uuid' : value.item_uuid,
				'cat_id' : value.cat_id
			};
			 this.$refs.item_details.dialog = true;
		},
		testPrint(){
			console.log("testPrint");
			this.$q.dialog({
			  title: translationVendor.confirm_test_print,
			  message: translationVendor.test_print_message + "\n" + translationVendor.test_print_message_ready,
			  transitionShow: "fade",
			  transitionHide: "fade",
			  cancel: true,
			  ok: {
				unelevated: true,
				color: "primary",
				rounded: true,
				"text-color": "white",
				size: "md",
				label: translationVendor.submit,
				"no-caps": true,				
			  },
			  cancel: {
				unelevated: true,
				color: "grey-5",
				"text-color": 'white',
				rounded: true,
				outline: false,
				size: "md",
				label: translationVendor.cancel,
				"no-caps": true,
			  },
			})
			.onOk(() => {
			    this.testPrintWifi();
			});
		},
		async testPrintWifi(){
		   const connected = await globalPrint.ConnectToPrintServer();		   
		   if (!connected) {			  
			   quasarComponents.notify("myerror",globalPrint.socket_error,'error');		
			   return;		
		   }
		   try {
				const response = await axios.get(
				`${pos_api}/testPrintWifi?language=${language}`,
				headerAuthorization
				);				
				if(response.data.code==1){
					const printResponse = await globalPrint.sendServerToPrinter(response.data.details.data);
					console.log("printResponse",printResponse);
					if(printResponse){
						quasarComponents.notify("mysuccess",response.data.msg,'info');
					} else {
						const printError = translationVendor.websocket_is_not_open + globalPrint.webSocket.readyState;
						quasarComponents.notify("myerror",printError,'error');
					}

				} else {
					quasarComponents.notify("myerror",response.data.msg,'error');
				}
			} catch (error) {
				console.error("GET failed:", error.message || error);
			}
		},
		getPromoMessage(item) {			
			return this.promoEligibility[item.item_id]?.message || item?.promo_data?.message;
		},
		isEligible(item) {
		   if(!item?.promo_data?.message){
			  return true;
		   }
           return this.promoEligibility[item.item_id]?.is_eligible || false; 
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