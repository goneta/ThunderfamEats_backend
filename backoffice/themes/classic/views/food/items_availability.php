<nav class="navbar navbar-light justify-content-between">
  <a class="navbar-brand">
  <h5><?php echo CHtml::encode($this->pageTitle)?></h5>
  </a>     
</nav>


<div id="vue-items-availability" class="card">
<div class="card-body" v-cloak  v-loading="loading">


<div class="d-none d-lg-block">
  <div class="d-flex align-items-center justify-content-between mb-3">
      <div>    
        <el-input
          v-model="query"
          style="width: 180px"
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

        <el-button size="large" :type="filter_pause?'primary':'plain'" :class="{'disabled-text':!filter_pause}" @click="filterPauseitems"      
        >
          <i class="zmdi zmdi-filter-list font14 mr-1"></i>
          <?php echo CommonUtility::safeTranslate("Show all pause items")?>
        </el-button>
      </div>
      <div v-if="hasItems">
        <h4 class="primary-text">{{getTotalDisplay}}</h4>      
      </div>
  </div>
</div>

<div class="d-block d-lg-none">   
    <div class="mb-3">
        <el-button size="large" :type="filter_pause?'primary':'plain'" :class="{'disabled-text':!filter_pause}" @click="filterPauseitems"
        :disabled="!hasItems" class="w-100"
        >
          <i class="zmdi zmdi-filter-list font14 mr-1"></i>
          <?php echo CommonUtility::safeTranslate("Show all pause items")?>
        </el-button>
    </div>
    <div class="mb-3">
        <el-input
          v-model="query"          
          placeholder="<?php echo CommonUtility::safeTranslate("Type a keyword")?>"              
          @clear="ClearSearch"
          clearable
          class="mr-2 w-50"
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
<!-- FILTERS -->


<template v-if="!hasItems && !loading">
  <div class="d-flex align-items-center justify-content-center" style="min-height:300px;">
    <div><h6 class="disabled-text"><?php echo CommonUtility::safeTranslate('No items found')?></h6></div>
  </div>
</template>

<template v-if="loading_search">
  <div v-loading="loading_search" style="height: 300px;"></div>
</template>
<template v-else>
    <template v-for="items in getItems" :keys="items">
    <el-card  shadow="never" class="mb-2" >  
      <div class="d-flex align-items-center justify-content-between">
          <div class="borderx">
              <h5 class="m-0 regular-text">
                {{items.item_name}}
              </h5>
              <p class="m-0 font14 disabled-text">
                {{ items.item_price }}
              </p>
          </div>
          <div class="borderx">
            <!-- =>{{items.status}} -->
              <div class="d-flex align-items-center">
                  <div class="mr-3 disabled-text" v-if="items.unavailable_until">{{ items.unavailable_until }}</div>
                  <div>
                      <el-button :type="!items.unavailable_until?'primary':'warning'" plain :icon="Delete" @click="OptionsPause(items)"
                      :loading="items.loading"
                      >            
                        <div class="d-flex">
                            <template v-if="!items.unavailable_until">
                                <div class="mr-1"><i class="zmdi zmdi-pause-circle-outline font14"></i></div>
                                <div><?php echo CommonUtility::safeTranslate("Pause availability")?></div>
                            </template>
                            <template v-else>
                                <div class="mr-1"><i class="zmdi zmdi-play-circle-outline"></i></div>
                                <div><?php echo CommonUtility::safeTranslate("Resume availability")?></div>
                            </template>
                        </div>
                    </el-button>
                  </div>
              </div>                 
          </div>
      </div>
    </el-card>
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

</template>


<pause-items
ref="ref_pause"
:label="<?php echo CommonUtility::safeJsonEncode([
  'title'=>t("Pause Item"),
  'cancel'=>t("Cancel"),
  'pause_message'=>t("Select a time frame where {item_name} will be paused. After this time, it'll be automatically resumed."),
  'day'=>t("day"),
  'days'=>t("days"),
  'hour'=>t("hour"),
  'hours'=>t("hours"),
])?>"
:pause_time_list="<?php echo CommonUtility::safeJsonEncode($pause_time_list)?>"
default_time='30_minutes'
@after-save="afterSave"
></pause-items>

</div>
</div>

<script type="text/x-template" id="xtemplate_pause_items">
<el-dialog v-model="modal"
    :title="label.title"
    :width="dialogWidth"		
    id="footer-none-bg"
    :close-on-click-modal="false"
    :close-on-press-escape="false"        				
>

<p class="font14 regular-text">{{ formattedMessage }}</p>

<el-scrollbar height="300px"> 


<template v-if="unavailable_until=='custom'">
   <div class="d-flex align-items-center justify-content-center position-relative " style="height:250px;">
   
   <div style="position:absolute;top:0;left:0;">
   <el-button                        
      circle 
      @click="unavailable_until=null"
    >
      <i class="zmdi zmdi-chevron-left" style="font-size:18px;"></i> 
    </el-button>
   </div>

   <el-form
    :label-position="top"    
    :model="formData"    
    :inline="true" 
   >   
   <el-form-item label="<?php echo CommonUtility::safeTranslate('Days')?>" >
        <el-select
          v-model="formData.days"
          placeholder="<?php echo CommonUtility::safeTranslate('Select')?>"
          size="large"          
          style="width: 100px"
          label-position="top"
        >         
            <el-option
              v-for="item in daysList"
              :key="item.value"
              :label="item.label"
              :value="item.value"              
            ></el-option>
        </el-select>
    </el-form-item>
    <el-form-item label="<?php echo CommonUtility::safeTranslate('Hours')?>" >
        <el-select
          v-model="formData.hours"
          placeholder="<?php echo CommonUtility::safeTranslate('Select')?>"
          size="large"          
          style="width: 130px"
          label-position="top"
        >         
             <el-option
              v-for="item in hoursList"
              :key="item.value"
              :label="item.label"
              :value="item.value"              
            ></el-option>
        </el-select>
    </el-form-item>
   </el-form>  

   </div>
</template>
<template v-else>
  <div class="block-el-radio">
  <el-radio-group v-model="unavailable_until">
      <template v-for="(items,key) in pause_time_list" :keys="items">
        <el-radio :value="key">{{items}}</el-radio>
      </template>    
  </el-radio-group>
  </div>
</template>

</el-scrollbar>

<template #footer>
    <div class="dialog-footer">
      <el-button size="large" @click="modal = false">
        {{ label.cancel}}
      </el-button>
      <el-button 
      @click="onSubmit" 
      type="primary" 
      size="large"
      :loading="loading_submit"        
      >
          {{label.title}}
      </el-button>
    </div>
</template>

</el-dialog>
</script>
