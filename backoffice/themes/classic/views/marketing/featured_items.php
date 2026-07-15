<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
</nav>


<div id="vue-feature-items" >
    <div v-loading="loading" v-cloak>
    <el-card shadow="never" >

    <div class="d-none d-lg-block">
          <div class="d-flex align-items-center justify-content-between mb-3">
          <div>

            <el-select 
            v-model="filter_by" 
            placeholder="<?php echo CommonUtility::safeTranslate("Filter by")?>" 
            class="mr-1" 
            style="width: 130px"
            size="large"
            no-data-text="<?php echo CommonUtility::safeTranslate("No available data")?>"	
            no-match-text="<?php echo CommonUtility::safeTranslate("No matching data")?>"
            >
            <el-option
              v-for="(item,index) in filter_by_list"
              :key="index"
              :label="item"
              :value="index"
            ></el-option>
            </el-select>      

            <el-input
              v-model="query"
              style="width: 150px"
              placeholder="<?php echo CommonUtility::safeTranslate("Type a keyword")?>"              
              @clear="ClearSearch"
              clearable
              class="mr-1"
              size="large"        
            >
              <template #prefix>
                <i class="zmdi zmdi-search" style="font-size: 25px;"></i>
              </template>
            </el-input>  
            
            <el-button type="primary" size="large" @click="ApplyFilter" :loading="loading" :disabled="query?false:true"> 
              <?php echo CommonUtility::safeTranslate("Search")?>
            </el-button>
                  
            <el-button size="large" :type="filter_all_featured?'primary':'plain'" :class="{'disabled-text':!filter_all_featured}" @click="filteFeatured"            
            class="truncate-text"
            >
              <i class="zmdi zmdi-filter-list font14 mr-1"></i>
              <?php echo CommonUtility::safeTranslate("View Featured")?>
            </el-button>
          </div>
          <div v-if="hasItems">
            <h4 class="primary-text">{{getTotalDisplay}}</h4>      
          </div>
      </div>    
    </div>

    <div class="d-block d-lg-none">
       <div class="d-flex align-items-center justify-content-between mb-3 ">
          <div>

            <el-input
              v-model="query"
              style="width: 150px"
              placeholder="<?php echo CommonUtility::safeTranslate("Type a keyword")?>"              
              @clear="ClearSearch"
              clearable
              class="mr-1"
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
          <div v-if="hasItems">
            <h5 class="primary-text">{{getTotalDisplay}}</h5>      
          </div>
       </div>
    </div>
    <!-- FILTERS -->



<template v-if="!hasItems && !loading">
  <div class="d-flex align-items-center justify-content-center" style="min-height:300px;">
    <div><h6 class="disabled-text"><?php echo CommonUtility::safeTranslate('No items found')?></h6></div>
  </div>
</template>

<template v-if="loading_search">
  <div v-loading="loading_search" style="height: 300px;"></div>
</template>
<template v-else-if="hasItems">

    <el-table :data="getItems" 
    style="width: 100%" 
    empty-text="<?php echo CommonUtility::safeTranslate("No available data")?>"
    @sort-change="handleSortChange"  
    >       
        <el-table-column prop="photo" label="<?php echo CommonUtility::safeTranslate('Photo')?>" width="80"  >
            <template #default="scope">               
               <el-avatar :size="50" shape="square" :src="scope.row.photo" fit="cover"  ></el-avatar>
            </template>
        </el-table-column>
        <el-table-column prop="item_id" label="<?php echo CommonUtility::safeTranslate('ID')?>" sortable width="80"  ></el-table-column>
        <el-table-column prop="item_name" label="<?php echo CommonUtility::safeTranslate('Name')?>" sortable  ></el-table-column>
        <el-table-column prop="merchant" label="<?php echo CommonUtility::safeTranslate('Merchant')?>" sortable ></el-table-column>
        <el-table-column prop="category" label="<?php echo CommonUtility::safeTranslate('Category')?>" sortable  ></el-table-column>
        <el-table-column prop="price" label="<?php echo CommonUtility::safeTranslate('Price')?>" sortable ></el-table-column>
        <el-table-column prop="is_featured" label="<?php echo CommonUtility::safeTranslate('Featured')?>" >          
           <template #default="scope">              

              <el-switch v-model="scope.row.is_featured" 
              @change="toggleFeatured(scope.row)"  
              :loading="scope.row.loading?scope.row.loading:false"              
              >
              </el-switch>
           </template>
        </el-table-column>
        <el-table-column prop="featured_priority" label="<?php echo CommonUtility::safeTranslate('Priority')?>" >
           <template #default="scope">
             <el-input 
             v-model="scope.row.featured_priority" 
             :disabled="!scope.row.is_featured" 
             @change="toggleFeatured(scope.row)"
             ></el-input>
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
    </div>
    
</div>