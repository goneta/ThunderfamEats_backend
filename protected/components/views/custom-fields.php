
<?php
 if(is_array($customFields) && count($customFields)>=1):?>
    <?php foreach ($customFields as $field):?>        
        <?php 
          $field_label = t($field['field_label']);
          switch ($field['field_type']) {
             case "text":
                echo "<div class='form-label-group'>";
                echo "<input type='{$field['field_type']}' class='form-control form-control-text' placeholder=''  " . ($field['is_required'] ? 'required' : '') . "
                v-model='custom_fields[{$field['field_id']}]' id='{$field['field_name']}' >";                
                echo "<label for='{$field['field_name']}' class='required'>{$field_label}</label> ";
                echo "</div>";
                break;

             case "select":                
                echo "<label for='{$field['field_name']}'>{$field_label}</label>";
                echo "<select v-model='custom_fields[{$field['field_id']}]' class='custom-select my-1 mr-sm-2 mb-3'  id='{$field['field_name']}' >";
                foreach($field['options'] as $selections){
                     echo "<option value='{$selections}' >{$selections}</option>";
                }
                echo "</select>";
                break;

            case "number":
                echo "<div class='form-label-group'>";
                echo "<input type='{$field['field_type']}' class='form-control form-control-text' placeholder='' 
                v-model='custom_fields[{$field['field_id']}]' id='{$field['field_name']}' >";                
                echo "<label for='{$field['field_name']}' class='required'>{$field_label}</label> ";
                echo "</div>";
                break;

            case "textarea":
                echo "<div class='form-group'>";
                echo "<label for='{$field['field_name']}' >{$field_label}</label>";
                 echo "<textarea v-model='custom_fields[{$field['field_id']}]'  class='form-control'id='{$field['field_name']}' rows='3'></textarea>";
               echo "</div>";
                break;

            case "date":
                echo "<el-form-item label='{$field_label}' label-position='top' >";
                echo "<el-date-picker style='width:100%;' v-model='custom_fields[{$field['field_id']}]' 
                type='date' placeholder='{$field_label}' size='large' value-format='YYYY-MM-DD' ></el-date-picker>";
                echo "</el-form-item>";
                break;

            case "checkbox":
                echo "<el-form-item label='{$field_label}' label-position='top' >";
                echo "<el-checkbox-group v-model='custom_fields[{$field['field_id']}]' >";
                foreach($field['options'] as $selections){
                   echo "<el-checkbox label='{$selections}' value='{$selections}' ></el-checkbox>";
                }
                echo "</el-checkbox-group>";
                echo "</el-form-item>";
                break;
          }
        ?>        
    <?php endforeach;?>        
<?php endif;?>