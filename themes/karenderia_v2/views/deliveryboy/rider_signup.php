

<div class="container">
 
<div class="login-container m-auto pt-5 pb-5">

  <div class="text-center">
     <h5 class="m-1"><?php echo t("Become our delivery partner")?></h5>
     <p class="m-0"><?php echo t("Get paid to deliver")?></p>  
  </div>
  
  <div class="forms-center mt-4">
  
       <DIV id="vue-rider-registration"> 
       
       <form 
       @submit.prevent="onRegister" 
       method="POST" >
       
       <div class="row p-0">
         <div class="col pr-0">
         
          <div class="form-label-group">    
           <input class="form-control form-control-text" placeholder="" v-model="first_name" id="first_name" type="text"  >   
           <label for="first_name" class="required"><?php echo t("First name")?></label> 
          </div>    
         
         </div> <!--col-->
         <div class="col">
         
          <div class="form-label-group">    
           <input class="form-control form-control-text" placeholder="" v-model="last_name" id="last_name" type="text" >   
           <label for="last_name" class="required"><?php echo t("Last name")?></label> 
         </div>   
         
         </div> <!--col-->
       </div> <!--row-->
       
       <div class="form-label-group">    
         <input class="form-control form-control-text" placeholder="" v-model="email" id="email" type="email" maxlength="50" >   
         <label for="email" class="required"><?php echo t("Email address")?></label> 
       </div>   
       
       <!--COMPONENTS-->                
       <component-phone
	    default_country="<?php echo CJavaScript::quote($phone_default_country);?>"    
	    :only_countries='<?php echo json_encode($phone_country_list)?>'	
	    v-model:mobile_number="mobile_number"
	    v-model:mobile_prefix="mobile_prefix"
	    >
	    </component-phone>
	    <!--END COMPONENTS-->
     
        <div class="form-label-group">    
         <input class="form-control form-control-text" placeholder="" v-model="address" id="address" type="email" maxlength="50" >   
         <label for="address" class="required"><?php echo t("Complete address")?></label> 
       </div>          
              
        <div class="form-label-group change_field_password">    
		   <input class="form-control form-control-text" autocomplete="new-password" 
           placeholder="Password"  :type="password_type" id="password" v-model="password"  >
		   <label for="password" class="required"><?php echo t("Password")?></label>      
		   <a href="javascript:;" @click="showPassword" >
		      <i v-if="show_password==false" class="zmdi zmdi-eye"></i>
		      <i v-cloak v-if="show_password==true" class="zmdi zmdi-eye-off"></i>
		   </a>
		</div> 
		
		<div class="form-label-group">    
		   <input class="form-control form-control-text" autocomplete="new-password" 
           placeholder="Password" :type="password_type" id="cpassword" v-model="cpassword"  >
		   <label for="cpassword" class="required"><?php echo t("Confirm Password")?></label>      		   
		</div> 
      
        <?php if(!empty($terms_condition)):?>
          <p><?php echo $terms_condition?></p>
        <?php else :?>
          <p class="m-0 mt-3 mb-3"><?php echo t("By clicking submit you agree to our terms and conditions");?></p>
        <?php endif;?>        
	          
	    <el-button type="primary" 
        type="submit"
        @click="onRegister" 
        :loading="loading" 
        :disabled="!validForm"
        size="large" 
        class="w-100" 
        style="padding:23px;"
        >
            <?php echo t("Submit")?>
        </el-button>
	   	   
	  </form> 
	   	   
	   </DIV> <!--vue-register-->
	   	  
  
  </div> <!--center-->

</div> <!--login container-->

</div> <!--containter-->