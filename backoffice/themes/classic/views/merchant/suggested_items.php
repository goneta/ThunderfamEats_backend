<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
</nav>


<div id="vue-suggested-items" >
    <div v-loading="loading" v-cloak>
    <el-card shadow="never" >


    <p>
      <?php echo CommonUtility::safeTranslate("Suggest your best food items to be featured on the homepage for increased visibility and sales, admin will review and approve based on platform guidelines.")?>
    </p>

    <div class="d-flex align-items-center justify-content-between mb-3">
    <div>    
       <template v-if="hasSelectedRows">
        <el-button type="danger" size="large" @click="deleteSelectedRows">
          <?php echo CommonUtility::safeTranslate("Delete Items")?>
        </el-button>
       </template>
       <template v-else>
        <el-button type="primary" size="large" @click="showModal">
          <?php echo CommonUtility::safeTranslate("Add Suggested Items")?>
        </el-button>
       </template>       
    </div>
    <div v-if="hasItems">
      <h4 class="primary-text">{{getTotalDisplay}}</h4>      
    </div>
</div>    


<template v-if="!hasItems && !loading">
  <div class="d-flex align-items-center justify-content-center " style="min-height:20rem;">
    <div><h6 class="disabled-text"><?php echo CommonUtility::safeTranslate("No items found")?></h6></div>
  </div>
</template>

<template v-if="loading_search">
  <div v-loading="loading_search" style="height: 20rem;"></div>
</template>
<template v-else-if="hasItems">


    <el-table :data="getItems" 
    @selection-change="handleSelectionChange"
    style="width: 100%" empty-text="<?php echo CommonUtility::safeTranslate("No available data")?>"
    >
        <el-table-column type="selection" width="55"></el-table-column>
        <el-table-column prop="item_id" label="<?php echo CommonUtility::safeTranslate('ID')?>" width="80" ></el-table-column>
        <el-table-column prop="item_name" label="<?php echo CommonUtility::safeTranslate('Name')?>" ></el-table-column>
        <el-table-column prop="item_price" label="<?php echo CommonUtility::safeTranslate('Price')?>"  ></el-table-column>        
        <el-table-column prop="status" label="<?php echo CommonUtility::safeTranslate('Status')?>" >
           <template #default="scope">
               <el-tag :type="StatusColor(scope.row.status_raw)" effect="plain">{{scope.row.status}}</el-tag>
           </template>
        </el-table-column>
        <el-table-column prop="reason" label="<?php echo CommonUtility::safeTranslate('Reason')?>" ></el-table-column>
        <el-table-column prop="created_at" label="<?php echo CommonUtility::safeTranslate('Date Submitted')?>" ></el-table-column>        
        <el-table-column prop="id" label="<?php echo CommonUtility::safeTranslate('Actions')?>" >
           <template #default="scope">
             <el-button
              size="small"
              type="danger"
              @click="handleDelete(scope.$index, scope.row)"
              round              
            >
            <?php echo CommonUtility::safeTranslate('Delete')?>
            </el-button>
           </template>
        </el-table-column>        
    </el-table>
</template>

    <div class="mt-3 mb-3" style="float:right;">   
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

    </el-card>


    <items-modal ref="items_modal"
    @after-selecteditems="afterSelecteditems"
    limit="6"
    ></items-modal>

    <el-dialog v-model="modal"    
    width="150"		
    id="footer-none-bg"
    :close-on-click-modal="false"
    :close-on-press-escape="false"   
    :show-close="false"
    align-center     	        
    >        
    <div class="text-center">
      <div v-loading="true" style="min-height: 2rem;">
         <h5><?php echo CommonUtility::safeTranslate("Loading")?>...</h5>
      </div>      
    </div>
    </el-dialog>
 

    </div>      
</div>

<?php
$this->renderPartial("//components/items_modal");