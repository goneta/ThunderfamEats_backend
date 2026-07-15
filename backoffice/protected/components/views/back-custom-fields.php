
<?php
 if(is_array($customFields) && count($customFields)>=1):?>
    <?php foreach ($customFields as $field):?>        
        <?php           
          $field_label = t($field['field_label']);
          $field_id = $field['field_id'];
          $post_value = isset($data[$field_id])?$data[$field_id]:'';                              
          switch ($field['field_type']) {
             case "text":
                echo "<div class='form-label-group'>";
                echo "<input type='{$field['field_type']}' class='form-control form-control-text' placeholder=''  " . ($field['is_required'] ? 'required' : '') . "
                name='custom_fields[{$field['field_id']}]' id='{$field['field_name']}' value='{$post_value}' >";                
                echo "<label for='{$field['field_name']}' class='required'>{$field_label}</label> ";
                echo "</div>";
                break;

             case "select":                
                echo "<label for='{$field['field_name']}'>{$field_label}</label>";
                echo "<select name='custom_fields[{$field['field_id']}]' class='custom-select my-1 mr-sm-2 mb-3'  id='{$field['field_name']}' >";
                foreach($field['options'] as $selections){
                     echo "<option value='{$selections}' " . ($post_value==$selections ? 'selected' : '') . "  >{$selections}</option>";
                }
                echo "</select>";
                break;

            case "number":
                echo "<div class='form-label-group'>";
                echo "<input type='{$field['field_type']}' class='form-control form-control-text' placeholder='' 
                name='custom_fields[{$field['field_id']}]' id='{$field['field_name']}' value='{$post_value}' >";                
                echo "<label for='{$field['field_name']}' class='required'>{$field_label}</label> ";
                echo "</div>";
                break;

            case "textarea":
                echo "<div class='form-group'>";
                echo "<label for='{$field['field_name']}' >{$field_label}</label>";
                 echo "<textarea name='custom_fields[{$field['field_id']}]'  class='form-control'id='{$field['field_name']}' rows='3'>{$post_value}</textarea>";
               echo "</div>";
                break;

            case "date":                
                echo "<div class='form-label-group'>";
                echo "<input class='form-control form-control-text datepick_all' placeholder='' readonly='readonly'
                name='custom_fields[{$field['field_id']}]' 
                id='custom_fields[{$field['field_id']}]' 
                type='text'
                value='{$post_value}'
                >   
                ";
                echo "<label for='custom_fields[{$field['field_id']}]' class='required'>{$field_label}</label>";
                echo "</div>";
                break;

            case "checkbox":             
                 echo "<div class='mb-2'>{$field_label}</div>";
                 foreach($field['options'] as $selections){
                    echo "<div class='form-check form-check-inline mb-2'>";
                    echo "<input name='custom_fields[{$field['field_id']}][]' 
                    class='form-check-input' type='checkbox' value='{$selections}' id='custom_fields[{$field['field_id']}]' 
                     " . (in_array($selections,(array)$post_value) ? 'checked' : '') . " 
                    >";
                    echo "<label class='form-check-label' for='custom_fields[{$field['field_id']}]'>{$selections}</label>";      
                    echo "</div>";
                }
                echo "<div class='pt-1 pb-1'></div>";
                break;
          }
        ?>        
    <?php endforeach;?>        
<?php endif;?>