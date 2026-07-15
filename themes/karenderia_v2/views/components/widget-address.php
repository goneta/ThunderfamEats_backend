

<!--ADDRESS AND DELIVERY DETAILS-->
<component-transaction-info
ref="transaction_info" 
@after-click="setTransactionInfo"
:label="{
	select_address:'<?php echo CJavaScript::quote(t("Select your address"))?>', 	
}"
>
</component-transaction-info>

<!--POPUP DELIVERY DETAILS-->
<component-delivery-details
ref="transaction"      
@show-address="showAddress"
@show-trans-options="ShowTransOptions"
:label="{
	title:'<?php echo CJavaScript::quote(t("Delivery details"))?>', 
	done: '<?php echo CJavaScript::quote(t("Done"))?>',	    
	select_address:'<?php echo CJavaScript::quote(t("Select your address"))?>',
}"
>
</component-delivery-details>


<!--CHANGE DELIVERY OPTIONS SCHEDULE OR NOW-->
<component-trans-options
ref="transaction_options" 
:label="{
title:'<?php echo CJavaScript::quote(t("Pick a time"))?>', 
save: '<?php echo CJavaScript::quote(t("Save"))?>',	    	    
}"
@after-save="afterSaveTransOptions"
@after-close="afterCloseAddress"
>
</component-trans-options>

<!--CHANGE ADDRRESS-->      
<component-change-address
ref="address"
@set-location="afterChangeAddress"
@after-close="afterCloseAddress"	
@set-placeid="afterSetAddress"	
@set-edit="editAddress"
@after-delete="afterDeleteAddress"
:label="{
    title:'<?php echo CJavaScript::quote(t("Change address"))?>', 
    enter_address: '<?php echo CJavaScript::quote(t("Enter delivery address"))?>',	    	    
}"
:addresses="addresses"
:location_data=""
saveplace="0"
>
</component-change-address>

<?php $maps_config = CMaps::config();?>  
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
	  

<!--END COMPONENTS	-->	 