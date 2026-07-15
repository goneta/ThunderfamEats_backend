

<div class="container">
 
<div class="login-container m-auto pt-5 pb-5 p-2">

  <h5 class="text-center mb-4"><?php echo t("Login")?></h5>
  
  <div class="forms-center">
  
       <DIV id="vue-login" v-cloak >     
		
	   <?php if($login_method=="user"):?>
			<form @submit.prevent="login">
							
			<div class="form-label-group">    
				<input class="form-control form-control-text" placeholder="" 
				id="username" type="text" autocomplete="new-username"  v-model="username" maxlength="100" >   
				<label for="username" class="required"><?php echo t("Mobile number or email")?></label> 
			</div>   
			
				<div class="form-label-group change_field_password">    
				<input class="form-control form-control-text" autocomplete="new-password" 
				placeholder="Password"  :type="password_type" id="password" v-model="password" maxlength="100"  >
				<label for="password" class="required"><?php echo t("Password")?> <span class="required">*</span></label>      
				<a href="javascript:;" @click="showPassword" >
					<i v-cloak v-if="show_password==false" class="zmdi zmdi-eye"></i>
					<i v-cloak v-if="show_password==true" class="zmdi zmdi-eye-off"></i>
				</a>
				</div> 
				
				<!--COMPONENTS--> 
			<components-recapcha  
				sitekey="<?php echo $captcha_site_key;?>"
				size="normal" 
				theme="light"
				:tabindex="0"
				is_enabled="<?php echo CJavaScript::quote($capcha)?>"
				captcha_lang="<?php echo CJavaScript::quote($captcha_lang)?>"
				@verify="recaptchaVerified"
				@expire="recaptchaExpired"
				@fail="recaptchaFailed"
				ref="vueRecaptcha">
			</components-recapcha>		
			<!--END COMPONENTS-->
				
			<div class="row m-0">
				<div class="col-6 p-0"> 
					<div class="custom-control custom-checkbox">
					<input type="checkbox" id="rememberme" v-model="rememberme"  class="custom-control-input">
					<label class="custom-control-label" for="rememberme"><?php echo t("Remember me")?></label>
					</div>   
				</div>
				<div class="col-6 mb-4 mb-lg-3 d-flex justify-content-end"> 
				<a href="<?php echo Yii::app()->createUrl("account/forgot_pass")?>" class="a-12"><u><?php echo t("Forgot password?")?></u></a>
				</div>
			</div><!-- row-->
			
			<div  v-cloak v-if="error.length>0" class="alert alert-warning" role="alert">
				<p v-cloak v-for="err in error">{{err}}</p>	    
			</div>
			
			<div  v-cloak v-if="success" class="alert alert-success" role="alert">
				<p class="m-0">{{success}}</p>	    
			</div>
				
			<button class="btn btn-green w-100"  :class="{ loading: loading }" :disabled="!formValid" >
				<span v-if="loading==false"><?php echo t("Sign in")?></span>
				<div v-cloak v-if="loading==true" class="m-auto" data-loader="circle-side"></div>
			</button>
			</form>

	   <?php else :?>	
		   <form @submit.prevent="getOTPLogin">			
		   
		   <template v-if="validation_type==1">			    
				<div class="form-label-group">    				    
					<input class="form-control form-control-text" placeholder="" 
					id="email_address" type="text"  v-model="email_address"  >   
					<label for="email_address" class="required"><?php echo t("Email Address")?></label> 
				</div> 
				
			</template>
			<template v-else>
				<component-phone
					default_country="<?php echo CJavaScript::quote($phone_default_country);?>"    
					:only_countries='<?php echo json_encode($phone_country_list)?>'	
					v-model:mobile_number="mobile_number"
					v-model:mobile_prefix="mobile_prefix"
					>
				</component-phone>
			</template>

			<template v-if="!is_verify">
				<el-radio-group v-model="validation_type">
					<el-radio :value="1"><?php echo t("Email")?></el-radio>
					<el-radio :value="2"><?php echo t("Mobile")?></el-radio>			
				</el-radio-group>
			</template>


			<template v-if="is_verify">				
				<template v-if="counter===0">   
				<div class="mb-2">
					<el-button										
						type="primary"
						link
						@click="getOTPLogin()"
						>
						<?php echo t("Resend OTP")?>
					</el-button>
				</div>				
				</template>
				<template v-else>          
					<p><u><?php echo t("Resend Code in")?> {{counter}}</u></p>
				</template>

				<div class="form-label-group">    
					<input class="form-control form-control-text" placeholder="" 
					id="otp" type="text"  v-model="otp" v-maska="'######'"  >   
					<label for="otp" class="required"><?php echo t("Your OTP code")?></label> 
				</div> 
			</template>

			<div  v-cloak v-if="success" class="alert alert-success" role="alert">
				<p class="m-0">{{success}}</p>	    
			</div>

		   <template v-if="is_verify">
		        <button type="button" class="btn btn-green w-100"  :class="{ loading: loading }" @click="userLoginByOTP"  >
					<span v-if="loading==false"><?php echo t("Login")?></span>
					<div v-cloak v-if="loading==true" class="m-auto" data-loader="circle-side"></div>
				</button>
		   </template>
		   <template v-else>
			    <button class="btn btn-green w-100"  :class="{ loading: loading }" :disabled="checkForm" >
					<span v-if="loading==false"><?php echo t("Get OTP")?></span>
					<div v-cloak v-if="loading==true" class="m-auto" data-loader="circle-side"></div>
				</button>
			</template>

			<div class="mt-2 mb-3 d-flex justify-content-between">
			  <a href="<?php echo Yii::app()->createUrl("account/forgot_pass")?>" class="a-12"><u><?php echo t("Forgot password?")?></u></a>
			  <template v-if="is_verify">
			  <a href="javascript:;" @click="startOver" class="a-12"><u><?php echo t("Start over")?></u></a>
			  </template>
			</div>

		   </form>
	   <?php endif;?>	   
	   	   
	   
	   <div class="mt-3 text-center">
	     <p class="m-0"><?php echo t("Don't have an account?")?></p>
	     <a  href="<?php echo Yii::app()->createUrl("/account/signup",array(
	      'redirect'=>$redirect_to,
	      //'next_url'=>$next_url,
	     ))?>" class="btn btn-white p-0 font14"><?php echo t("Sign Up")?></a>
	   </div>
	   	   
	   
	   <?php if($enabled_guest):?>
	   <div class="mt-4 text-center">
		 <a href="<?php echo $guestcheckout_url;?>" class="btn btn-light w-100">
			<?php echo t("Continue as guest")?>
		 </a>
	   </div>
	   <?php endif;?>
	   
	   <div class="mt-4 text-center">
	     <component-facebook
	     app_id="<?php echo $fb_app_id;?>"	     
	     :show_button="<?php echo $fb_enabled==1?true:false;?>"	     
	     version="v12.0"
	     verification="<?php echo $enabled_verification?>"
	     redirect_to="<?php echo $redirect_to?>"
		 :button_width="250"
	     @social-registration="SocialRegister"
	     :errors="{		    
			 user_cancelled: '<?php echo CJavaScript::quote(t("User cancelled login or did not fully authorize."))?>', 		    
		  }"	    
		 :label="{		    
			 title: '<?php echo CJavaScript::quote(t("Login with Facebook"))?>', 		    
		 }"	    
	     >
	     </component-facebook>
	   </div>
	   	    
	    <div class="mt-4 text-center">
	     <component-google
	     client_id="<?php echo $google_client_id;?>"	     
	     :show_button="<?php echo $google_enabled==1?true:false;?>"	     
	     cookiepolicy="single_host_origin"
	     scope = "profile"
	     verification="<?php echo $enabled_verification?>"
	     redirect_to="<?php echo $redirect_to?>"
		 :button_width="250"
	     @social-registration="SocialRegister"
	     :errors="{		    
			 user_cancelled: '<?php echo CJavaScript::quote(t("User cancelled login or did not fully authorize."))?>', 		    
		  }"	    
		 :label="{		    
			 title: '<?php echo CJavaScript::quote(t("Login with Google"))?>', 		    
		 }"	    
	     >
	     </component-google>
	   </div>
	   
	   </DIV> <!--vue-login-->
	   	  
  
  </div> <!--center-->

</div> <!--login container-->

</div> <!--containter-->

<?php $this->renderPartial("//components/vue-bootbox")?>