
<script type="text/x-template" id="xtemplate_delivery_address">
<el-dialog
		v-model="dialog_address"
		title="<?php echo t("Delivery address")?>"
		width="55%"		        
	>

    <component-auto-complete
        ref="auto_complete"
        :label="{
            enter_address : '<?php echo t("Enter your street and house number")?>'
        }"        
        @after-choose="afterChoose"
    >
    </component-auto-complete>

    <components-maps
        ref="cmaps"
        :keys="keys"
        :provider="provider"
        :zoom="zoom"
        size="map-smalls mt-1"
        :center="center"
        :markers="markers"                        
        @after-selectmap="afterSelectmap"
        @drag-marker="dragMarker"
        >
    </components-maps>

    <!-- <pre>{{keys}}</pre>
    <pre>{{provider}}</pre>
    <pre>{{zoom}}</pre>
    <pre>{{center}}</pre> -->

    <!-- <pre>{{markers}}</pre> -->
    <!-- <pre>{{data}}</pre>
    <pre>{{address_data}}</pre> -->
	
    <div class="row pt-2 align-items-center">
        <div class="col-10" v-if="hasDeliveryAddress">            
        <h5 class="m-0 p-0">{{address1}}</h5>
        <p class="m-0 p-0">{{formatted_address}}</p>
        </div>        
    </div>
    <!-- row -->
                
    <div class="pt-2"></div>      

    <div class="form-label-group">
        <input v-model="formatted_address" class="form-control form-control-text" placeholder="" id="formatted_address" type="text">
        <label for="formatted_address">
        <?php echo t("Street name")?>
        </label>
    </div>

    <div class="form-label-group">
        <input v-model="address1" class="form-control form-control-text" placeholder="" id="address1" type="text">
        <label for="address1">
        <?php echo t("Street number")?>
        </label>
    </div>
    
    <div class="form-label-group">
        <input v-model="location_name" class="form-control form-control-text" placeholder="" id="location_name" type="text">
        <label for="location_name">
        <?php echo t("Aparment, suite or floor")?>
        </label>
    </div>
        
    <h5 class="m-0 mt-2 mb-2"><?php echo t("Delivery options")?></h5>
    <select v-model="delivery_options" class="form-control custom-select">          
        <option v-for="(items, key) in data.delivery_option" :value="key" >{{items}}</option>      
    </select>        

    <h5 class="m-0 mt-3 mb-2"><?php echo t("Add delivery instructions")?></h5>
    <textarea v-model="delivery_instructions" id="delivery_instructions" class="form-control form-control-text font13" 
    placeholder="<?php echo t("eg. ring the bell after dropoff, leave next to the porch, call upon arrival, etc")?>" style="max-height: 150px;">
    </textarea>
    
    <h5 class="m-0 mt-3 mb-2"><?php echo t("Address label")?></h5>
    <div class="btn-group btn-group-toggle input-group-small mb-4" >
        <label class="btn" v-for="(items, key) in data.address_label" :class="{ active: address_label==key }" >
            <input v-model="address_label" type="radio" :value="key" > 
            {{ items }}
        </label>           
    </div>

    <el-button :loading="loading" @click="saveAddress" type="success" size="large" class="w-100 p-4">Save address</el-button>

	</el-dialog>
</script>