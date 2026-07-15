<script type="text/x-template" id="xtemplate_item_list">

<div v-for="items in data" class="col-lg-3 col-sm-12 mb-3" >            
    <el-card shadow="hover" :body-style="{ padding: '0px' }" >
        <el-image  
        style="min-height: 130px;max-height: 130px; height:130px !important;"
        class="w-100 h-100"
        :src="items.url_image"
        fit="cover"
        lazy
        >
        </el-image>

        <div class="p-2">
            <div class="d-flex align-items-center mb-1">
                <h6 class="m-0 text-truncate">{{items.item_name}}</h6>
            </div>
            <p class="d-inline-block text-truncate" style="max-width: 200px;" v-html="items.item_description"></p>

            <div class="d-flex justify-content-between align-items-center w-100">
                <template v-if="items.price[0]">                       
                    <template v-if="items.price[0].discount>0">
                        <h5 class="m-0 text-grey"><del>{{ items.price[0].pretty_price }}</del></h5>
                        <h5 class="m-0">{{ items.price[0].pretty_price_after_discount }}</h5>
                    </template>
                    <template v-else>
                        <h5 class="m-0">{{ items.price[0].pretty_price }}</h5>
                    </template>
                </template>    
                                                
                <template v-if="itemAvailable(items.item_id,items.cat_id)">
                    <el-button color="#3ecf8e" class="white-color" @click="viewItem(items)" >
                    <?php echo t("Add")?>
                    </el-button>
                </template>
                <template v-else>
                    <el-button disabled color="#b2b2b2" class="white-color">
                        <?php echo t("Not available")?>
                    </el-button>
                </template>
            </div>                        
        </div>
        <!-- p-2 -->
    </el-card>           
</div>
</script>