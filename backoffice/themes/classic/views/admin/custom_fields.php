

<DIV id="app-custom-fields" v-cloak>

<div class="text-right mb-3">
  <el-button type="primary" @click="modal=true" >
    <?php echo t("Add New")?>
  </el-button>
</div>

<el-table :data="data" stripe v-loading="refresh" >
   <el-table-column label="<?php echo CommonUtility::safeTranslate("ID")?>" width="50">
      <template #default="scope">
        {{scope.row.field_id}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Field Name")?>" >
      <template #default="scope">
        {{scope.row.field_name}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Field Label")?>" >
      <template #default="scope">
        {{scope.row.field_label}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Field Type")?>" >
      <template #default="scope">
        {{scope.row.field_type}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Required")?>" >
      <template #default="scope">
        {{scope.row.is_required}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Entity")?>" >
      <template #default="scope">
        {{scope.row.entity}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Sequence")?>" >
      <template #default="scope">
        {{scope.row.sequence}}
      </template>
   </el-table-column>
   <el-table-column label="<?php echo CommonUtility::safeTranslate("Actions")?>" width="150" >
      <template #default="scope">
      <el-button size="small" @click="handleEdit(scope.$index, scope.row)">          
          <?php echo CommonUtility::safeTranslate("Edit")?>
        </el-button>
        <el-button
          size="small"
          type="danger"
          @click="handleDelete(scope.$index, scope.row)"
        >          
          <?php echo CommonUtility::safeTranslate("Delete")?>
        </el-button>
      </template>
   </el-table-column>
</el-table>

<el-dialog
    v-model="modal"
    title="<?php echo t("Custom Fields")?>"
    width="500"       
    @close="clearForm"
>

<el-form
    label-position="top"
    label-width="auto"    
    style="max-width: 600px"
>  


  <el-row :gutter="10">
     <el-col :span="12">
        <el-form-item label="<?php echo CommonUtility::safeTranslate("Field Name (Internal use)")?>">
           <el-input v-model="field_name" size="large"  @input="sanitizeInput" ></el-input>
        </el-form-item>
     </el-col>
     <el-col :span="12">
        <el-form-item label="<?php echo CommonUtility::safeTranslate("Field Label (Displayed)")?>">
           <el-input v-model="field_label" size="large"></el-input>
        </el-form-item>
     </el-col>
  </el-row>  
  
  <el-row :gutter="10">    
    <el-col :span="12" >
    <el-form-item label="<?php echo CommonUtility::safeTranslate("Field Type")?>">
        <el-select
        v-model="field_type"      
        size="large"        
        >    
            <el-option
            v-for="(item,index) in fieldtype_list"
            :key="index"
            :label="item"
            :value="index"
            ></el-option>
        </el-select>
    </el-form-item>
    </el-col>

    <el-col :span="12" >
    <el-form-item label="<?php echo CommonUtility::safeTranslate("Entity")?>">

       <el-select
        v-model="entity"      
        size="large"        
        >    
            <el-option
            v-for="(item,index) in entity_list"
            :key="index"
            :label="item"
            :value="index"
            ></el-option>
        </el-select>
        
    </el-form-item>
    </el-col>

  </el-row>  

  <el-row :gutter="10">    
    <el-col :span="12" >
    <el-form-item label="<?php echo CommonUtility::safeTranslate("Is Required")?>">
        <el-checkbox v-model="is_required" label="<?php echo CommonUtility::safeTranslate("Yes")?>" size="large" />
    </el-form-item>
    </el-col>

    <el-col :span="12" v-if="field_id" >
      <el-form-item label="<?php echo CommonUtility::safeTranslate("Sequence")?>">
         <el-input-number v-model="sequence" :min="1" :max="9999" ></el-input-number>
    </el-form-item>
    </el-col>
  </el-row>  


  <el-row :gutter="10" v-if="field_type=='select' || field_type=='checkbox'">    
    <el-col :span="24" >
    <el-form-item label="<?php echo CommonUtility::safeTranslate("Options (for Select/Checkbox types, comma-separated):")?>">
        <el-input
        v-model="options"            
        :rows="3"
        type="textarea"            
        >
        </el-input>
    </el-form-item>
    </el-col>
  </el-row>  


</el-form>

<template #footer>
      <div class="dialog-footer">
        <el-button 
        @click="onSubmit" 
        type="primary" 
        size="large"
        :loading="loading_create"
        >        
        <?php echo CommonUtility::safeTranslate("Submit")?>
        </el-button>
      </div>
</template>

</el-dialog>


</DIV>