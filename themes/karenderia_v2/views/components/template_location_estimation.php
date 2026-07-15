<script type="text/x-template" id="xtemplate_location_estimation">

<template v-if="loading">

<el-skeleton animated >
    <template #template>
    <el-skeleton-item variant="rect" style="height: 40px;width:100%;"  />                           
    </template>
</el-skeleton>

</template>
<template v-else>
<div class="location-estimation grey-bg p-1 radius10 d-inline-flex align-items-center" >    
    <el-scrollbar>
    <template v-for="items in data">
        <el-button size="large" 
        round
        type="plain"
        :text="transaction_type!=items.service_code"
        @click="setTransactionType(items.service_code)"           
        :class="{ disabled : transaction_type==items.service_code }"            
        >
            <div style="display: block;">
                <div class="mb-1">{{ items.service_name }}</div>
                <div class="font11" v-if="items.estimation">
                    <i class="zmdi zmdi-time"></i> {{items.estimation}}
                </div>
            </div>
        </el-button>
    </template>  	
    </el-scrollbar>
</div>
</template>
</script>