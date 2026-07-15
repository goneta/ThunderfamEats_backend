<script type="text/x-template" id="xtemplate_location_current_address">
<div class="w-50">
   <template v-if="loading && !hasLocation">
      <el-skeleton animated >
         <template #template>
            <el-skeleton-item variant="rect" style="height: 25px;width:100%;"  />                           
         </template>
      </el-skeleton>
   </template>
   <template v-else>
    <div class="mt-1">
      <el-button link class="warm-pink" @click="showSelectLocation">
         <el-icon class="el-icon--left"><i class="fas fa-map-marker-alt"></i></el-icon> 
         <span class="truncate" style="max-width: 300px;">
               <template v-if="hasLocation">
                  {{data.complete_address}}
               </template>
               <template v-else>
                  Choose an address
               </template>            
         </span>  
         <el-icon class="el-icon--right"><i class="fas fa-caret-down"></i></el-icon> 
      </el-button>
    </div>
  </template>
</div>
</script>