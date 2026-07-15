<script type="text/x-template" id="xtemplate_items_modal">

<el-dialog v-model="modal"
    title="<?php echo CommonUtility::safeTranslate("Add Suggested Items")?>"
    width="600"		
    id="footer-none-bg"
    :close-on-click-modal="false"
    :close-on-press-escape="false"   
    align-center     	  
    @open="OnOpen"      
>    

<el-scrollbar height="400px">

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
    <el-input
    v-model="query"
    style="width: 200px"
    placeholder="<?php echo CommonUtility::safeTranslate("Type a keyword")?>"              
    @clear="ClearSearch"
    clearable
    class="mr-2"
    size="large"        
    >
    <template #prefix>
        <i class="zmdi zmdi-search" style="font-size: 25px;"></i>
    </template>
    </el-input>            

    <el-button type="primary" size="large" @click="ApplyFilter" :loading="loading" :disabled="query?false:true"> 
    <?php echo CommonUtility::safeTranslate("Search")?>
    </el-button>
    </div>
</div>

<el-table  v-loading="loading"  :data="getItems" style="width: 100%" empty-text="<?php echo CommonUtility::safeTranslate("No available data")?>">
   <el-table-column prop="item_id" label="<?php echo CommonUtility::safeTranslate('ID')?>" width="80" ></el-table-column>
   <el-table-column prop="item_name" label="<?php echo CommonUtility::safeTranslate('Name')?>" ></el-table-column>
   <el-table-column prop="item_price" label="<?php echo CommonUtility::safeTranslate('Price')?>"  ></el-table-column>
   <el-table-column prop="item_token" label="<?php echo CommonUtility::safeTranslate('Select')?>" >  
       <template #default="scope">
          <el-checkbox v-model="items_selected" :value="scope.row.item_id" size="small"> </el-checkbox>
       </template>
   </el-table-column>
</el-table>
</el-scrollbar>

<div class="mt-3 mb-3" >    
      <!-- <el-pagination background layout="prev, pager, next" 
      :total="getTotalItems"
      :page-size="page_size"
      @current-change="paginationChange"
      @change="paginationChange"
      @prev-click="paginationPrev"
      @next-click="paginationNext"
      ></el-pagination>           -->

      <el-pagination background 
      v-model:page-size="page_size"
      :page-sizes="pageSizes"
      layout="total, sizes, prev, pager, next"      
      :total="getTotalItems"
      :page-size="page_size"
      @current-change="paginationChange"
      @change="paginationChange"
      @prev-click="paginationPrev"
      @next-click="paginationNext"
      >            
      </el-pagination>    
      <div style="clear:both;"></div>
</div>

<template #footer>
    <div class="dialog-footer" >	        
        <el-button @click="SubmitItems" size="large" color="#626aef" @click="modal_events=false" 
        :disabled="loading || !hasSelected || loading_submit" >
        <?php echo CommonUtility::safeTranslate("Submit")?>
        </el-button>                
    </div>
</template>	

</el-dialog>

</<script>