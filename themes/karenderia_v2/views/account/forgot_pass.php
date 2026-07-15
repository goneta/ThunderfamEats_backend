

<div class="container">
 
<div class="login-container m-auto pt-5 pb-5">
  
  
  <DIV id="vue-forgot-password" v-cloak >       
     
  <template v-if="steps==1">
  <form @submit.prevent="requestResetPassword"> 	
    <div class="text-center" >
	   <h5 class="mb-4"><?php echo t("Let's Get your account back!")?></h5>     
	   <template v-if="password_reset_options=='both_sms_email'">
	      <p><?php echo t("Don't worry, just enter your registered phone or email address")?></p>
	   </template>
	   <template v-else-if="password_reset_options=='email'">
	       <p>
			<?php echo t("Please specify your email address to receive instructions for resetting it. If an account exists by that email, we will send a password reset")?>
		   </p>    
	   </template>
	   <template v-else-if="password_reset_options=='sms'">
	      <p><?php echo t("Don't worry, just enter your registered phone")?></p>
	   </template>
	</div>

	<div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
		<p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
	</div>        
     
	 <template v-if="validation_type==1">
		<div class="form-label-group">    
			<input class="form-control form-control-text" placeholder="" 
			id="email_address" type="text"  v-model="email_address"  >   
			<label for="email_address" class="required"><?php echo t("Email")?></label> 
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
  
	 
	    <template v-if="password_reset_options=='both_sms_email'">
		<el-radio-group v-model="validation_type">
			<el-radio :value="1">Email</el-radio>
			<el-radio :value="2">Mobile</el-radio>			
		</el-radio-group>
		</template>
	</div>

	<div class="mt-2">	   
	   <button class="btn btn-green w-100"  :class="{ loading: loading }" :disabled="checkForm" >
	      <span v-if="loading==false">
			<template v-if="validation_type==1">
			   <?php echo t("Reset Password")?>
			</template>
			<template v-else>
			  <?php echo t("Send OTP")?>
			</template>
		   </span>
	      <div v-cloak v-if="loading==true" class="m-auto" data-loader="circle-side"></div>
	   </button>
	</div>
     
  </form>
  </template>

  <template v-else-if="steps==2">
     <div class="mb-5">
         
		 <h5 class="text-center mb-4"><?php echo t("Password Reset")?></h5>
		 
		 <div v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			<p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
		 </div>      
   
		 <div  v-cloak v-if="success" class="alert alert-success" role="alert">
			<p class="m-0">{{success}}</p>	    
		 </div>
		 
		 <div v-if="loading">
		   <div class="loading">
			 <div v-cloak class="m-auto" data-loader="circle-side"></div>
		   </div>
		 </div>
	 
		 <template v-if="counter===0">          
		   <div class="mt-1 mb-3">           
			<a href="javascript:;" @click="resendResetEmail" :disabled="is_loading" >
			  <p class="m-0"><u><?php echo t("Resend reset email")?></u></p>
			</a>
		   </div>
		 </template>
		 <template v-else>          
			 <p><u><?php echo t("Resend Code in")?> {{counter}}</u></p>
		 </template>
	 
	 </div>
  </template>

  <template v-else-if="steps==3">
  <div class="text-center">
	      <h5 class="mb-4"><?php echo t("OTP Verification")?></h5>     		  
		  <div  v-cloak v-if="success" class="alert alert-success" role="alert">
			    <p class="m-0">{{success}}</p>	    
		  </div>
		</div>

		<div v-if="loading">
		<div class="loading">
			<div v-cloak class="m-auto" data-loader="circle-side"></div>
		</div>
		</div>

		<div class="form-label-group">    
			<input class="form-control form-control-text" placeholder=""  v-maska="'####'"
			id="otp" type="text"  v-model="otp"  >   
			<label for="otp" class="required"><?php echo t("Enter code")?></label> 
		</div> 

		<button @click="verifyOTP" class="btn btn-green w-100 mb-3"  :class="{ loading: loading }"  >
	      <span v-if="loading==false"><?php echo t("Verify")?></span>
	      <div v-cloak v-if="loading==true" class="m-auto" data-loader="circle-side"></div>
	     </button>

		<template v-if="counter===0">          
			<div class="mt-1 mb-3">           
			<a href="javascript:;" @click="resendResetEmail" :disabled="is_loading" >
				<p class="m-0"><u><?php echo t("Resend OTP")?></u></p>
			</a>
		</div>
		</template>
		<template v-else>          
			<p><u><?php echo t("Resend OTP in")?> {{counter}}</u></p>
		</template>
  </template>

       <template v-if="steps==1">
		<div class="mt-3 text-center">
			<span><?php echo t("Have an account")?>? <a  href="<?php echo Yii::app()->createUrl("/account/login",array(
			'redirect'=>$redirect_to
			))?>" class="btn btn-white p-0 font14"><?php echo t("Sign in")?></a></span>
		</div>
	   </template>
	   <template v-else>
	     <div class="mt-3 text-center">
			<a @click="startOver" href="javascript:;" class="btn btn-white p-0 font14"><?php echo t("Start over")?></a>
		 </div>
	   </template>

  </DIV>     
  
  
  </div> <!--center-->

</div> <!--login container-->

</div> <!--containter-->