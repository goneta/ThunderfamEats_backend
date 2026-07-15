<div id="vue-address-needed" v-cloak>

<div class="container-fluid" v-if="address_needed" >
    <div class="container">
       <div class="d-flex justify-content-center">
              
       <div class="w-100 text-center mt-3 mb-3">   
	      <h4><?php echo t("Enter your address")?></h4>	      
	      <p class="m-0"><?php echo t("We'll confirm that you can have this restaurant delivered.")?></p>
	      <a href="javascript:;" @click="show" class="font-weight-bold"><?php echo t("Add Address")?></a>
	   </div>
       
       </div>
    </div>
</div>

<div class="container-fluid" v-if="out_of_range" >
    <div class="container">
       <div class="d-flex justify-content-center">
              
       <div class="w-100 text-center mt-3 mb-3">   
	      <h4><?php echo t("You're out of range")?></h4>
	      <p class="m-0"><?php echo t("This restaurant cannot deliver to")?> 
		  <span v-if="address_component.address1">{{address_component.address1}} {{address_component.formatted_address}}</span>
		  </p>
		  <div class="p-1"></div>
		  <a href="javascript:;" @click="show" class="font-weight-bold"><?php echo t("Change address")?></a>
	   </div>
       
       </div>
    </div>
</div>



<!--CHANGE ADDRRESS--> 
<component-change-address
ref="address"
@set-location="afterChangeAddress"
@after-close="afterCloseAddress"	
@set-placeid="afterSetAddress"	
@set-edit="editAddress"
@after-delete="afterDeleteAddress"
:label="{
	title:'<?php echo CJavaScript::quote(t("Delivery Address"))?>', 
	enter_address: '<?php echo CJavaScript::quote(t("Enter your address"))?>',	    	    
}"
:addresses="addresses"
:location_data=""
saveplace="0"
>
</component-change-address>

<components-select-address
	ref="address_modal"
	:data="deliveryAddress"
	keys="<?php echo $maps_config['key']?>"
	provider="<?php echo $maps_config['provider']?>"
	zoom="<?php echo $maps_config['zoom']?>"
	:center="{
	lat: '<?php echo CJavaScript::quote($maps_config['default_lat'])?>',  
	lng: '<?php echo CJavaScript::quote($maps_config['default_lng'])?>',  
	}"        
	:label="{
		exact_location : '<?php echo CJavaScript::quote(t("What's your exact location?"))?>', 
        enter_address : '<?php echo CJavaScript::quote(t("Enter your street and house number"))?>', 
		submit : '<?php echo CJavaScript::quote(t("Submit"))?>', 
    }"
	@after-changeaddress="afterPointaddress"
	>
</components-select-address>

<components-address-form
ref="address_form"
:location_data="location_data"
@on-savelocation="onSavelocation"
>	
</components-address-form>

</div>
<!--vue-address-needed-->

