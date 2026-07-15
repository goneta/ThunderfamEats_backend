let mp;const componentsMercadopago={props:["title","label","payment_code","merchant_id","merchant_type","public_key","amount","currency_code","ajax_url","cart_uuid","on_error"],data(){return{is_loading:false,error:[],mp_identification_type_list:[],mp_credit_card_number:"5031755734530604",mp_expiry_date:"11/2022",mp_cvv:"123",mp_card_name:"basti",mp_email_address:"test1@yahoo.com",mp_identification_type:"DNI",mp_identification_number:"12334566",form_ready:false,customer_id:"",verify_cvv:"",card_number:"",card_id:"",payment_uuid:"",order_uuid:"",jwt_data:""}},computed:{checkForm(){if(empty(this.mp_credit_card_number)){return true}if(empty(this.mp_expiry_date)){return true}if(empty(this.mp_cvv)){return true}if(empty(this.mp_card_name)){return true}if(empty(this.mp_identification_number)){return true}return false},checkFormCVV(){if(empty(this.verify_cvv)){return true}return false}},methods:{showPaymentForm(){this.error=[];this.createCustomer();$(this.$refs.modal_mercadopago).modal("show")},close(){$(this.$refs.modal_mercadopago).modal("hide")},closeCvv(){$(this.$refs.modal_cvv).modal("hide");this.error=[];this.$emit("afterCancelPayment")},createCustomer(){var e={YII_CSRF_TOKEN:$("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_code:this.payment_code,merchant_id:this.merchant_id,merchant_type:this.merchant_type};var t=1;e=JSON.stringify(e);ajax_request_cmp[t]=$.ajax({url:this.ajax_url+"/createCustomer",method:"PUT",dataType:"json",data:e,contentType:$content_type.json,timeout:$timeout_cmp,crossDomain:true,beforeSend:e=>{this.error=[];this.is_loading=true;if(ajax_request_cmp[t]!=null){ajax_request_cmp[t].abort()}}}).done(e=>{if(e.code==1){this.customer_id=e.details.customer_id;this.form_ready=true;this.includeScript()}else{this.error=e.msg}}).always(e=>{this.is_loading=false})},includeScript(){if(window.MercadoPago==null){new Promise(e=>{const t=window.document;const a="mercadopago-script";const i=t.createElement("script");i.id=a;i.setAttribute("src","https://sdk.mercadopago.com/js/v2");t.head.appendChild(i);i.onload=()=>{e()}}).then(()=>{this.initPayment()})}else{this.initPayment()}},initPayment(){this.is_loading=true;mp=new MercadoPago(this.public_key);mp.getIdentificationTypes().then(e=>{dump(e);this.mp_identification_type_list=e;this.is_loading=false})["catch"](e=>{this.is_loading=false;this.close();this.$emit("Alert",this.on_error.error+" "+e.message)})},submitForms(){var e=this.mp_expiry_date;var t=e.split("/");var a=t[0];var i=t[1];var r=this.mp_credit_card_number;var s=r.replace(/ /g,"");this.error=[];this.is_loading=true;mp.createCardToken({cardNumber:s,cardholderName:this.mp_card_name,cardExpirationMonth:a,cardExpirationYear:i,securityCode:"",identificationType:this.mp_identification_type,identificationNumber:this.mp_identification_number}).then(e=>{this.AddCard(e)})["catch"](e=>{this.is_loading=false;if(typeof e.message!=="undefined"&&e.message!==null){this.error[0]=e.message}else{e.forEach((e,t)=>{this.error[t]=e.message})}location.href="#error_message"})},AddCard(e){var t={YII_CSRF_TOKEN:$("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_code:this.payment_code,merchant_id:this.merchant_id,merchant_type:this.merchant_type,id:e.id,card_name:this.mp_card_name,customer_id:this.customer_id};var a=1;t=JSON.stringify(t);ajax_request_cmp[a]=$.ajax({url:this.ajax_url+"/AddCard",method:"PUT",dataType:"json",data:t,contentType:$content_type.json,timeout:$timeout_cmp,crossDomain:true,beforeSend:e=>{this.error=[];if(ajax_request_cmp[a]!=null){ajax_request_cmp[a].abort()}}}).done(e=>{if(e.code==1){this.error=[];this.close();this.$emit("setPaymentlist")}else{this.error=e.msg}}).always(e=>{this.is_loading=false})},PaymentRender(e){this.payment_uuid=e.payment_uuid;this.order_uuid=e.order_uuid;var t={YII_CSRF_TOKEN:$("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_uuid:this.payment_uuid};var a=1;t=JSON.stringify(t);ajax_request_cmp[a]=$.ajax({url:this.ajax_url+"/getCardID",method:"PUT",dataType:"json",data:t,contentType:$content_type.json,timeout:$timeout_cmp,crossDomain:true,beforeSend:e=>{this.$emit("showLoader",this.label.getting_payment);if(ajax_request_cmp[a]!=null){ajax_request_cmp[a].abort()}}}).done(e=>{if(e.code==1){this.includeScript();this.card_number=e.details.card_number;this.card_id=e.details.card_id;this.error=[];$(this.$refs.modal_cvv).modal("show");setTimeout(()=>{this.$refs.cvv_input.focus()},100)}else{this.$emit("Alert",this.on_error.error+" "+e.msg);this.$emit("afterCancelPayment")}}).always(e=>{this.$emit("closeLoader")})},SubmitPayment(){this.is_loading=true;mp.createCardToken({cardId:this.card_id,securityCode:this.verify_cvv}).then(e=>{if(!empty(this.jwt_data)){this.processPayment(e.id)}else{this.capturePayment(e.id)}})["catch"](e=>{this.is_loading=false;if(typeof e.message!=="undefined"&&e.message!==null){this.error[0]=e.message}else{e.forEach((e,t)=>{this.error[t]=e.message})}location.href="#error_message"})},capturePayment(e){var t={YII_CSRF_TOKEN:$("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_code:this.payment_code,merchant_id:this.merchant_id,merchant_type:this.merchant_type,order_uuid:this.order_uuid,cart_uuid:this.cart_uuid,payment_uuid:this.payment_uuid,card_token:e};var a=1;t=JSON.stringify(t);ajax_request_cmp[a]=$.ajax({url:this.ajax_url+"/capturePayment",method:"PUT",dataType:"json",data:t,contentType:$content_type.json,timeout:$timeout_cmp,crossDomain:true,beforeSend:e=>{this.is_loading=true;this.error=[];if(ajax_request_cmp[a]!=null){ajax_request_cmp[a].abort()}}}).done(e=>{if(e.code==1){this.closeCvv();this.$emit("showLoader",e.msg);setTimeout(()=>{window.location.href=e.details.redirect},500)}else{this.error=e.msg}}).always(e=>{this.is_loading=false})},validEmail(e){var t=/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;return t.test(e)},Dopayment(e,t){this.jwt_data=e;var a={YII_CSRF_TOKEN:$("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_uuid:t.payment_uuid};axios({method:"PUT",url:this.ajax_url+"/getCardID",data:a,timeout:$timeout_cmp}).then(e=>{if(e.data.code==1){this.includeScript();this.card_number=e.data.details.card_number;this.card_id=e.data.details.card_id;this.error=[];this.$emit("closeTopup");$(this.$refs.modal_cvv).modal("show");setTimeout(()=>{this.$refs.cvv_input.focus()},100)}else{this.$emit("Alert",this.on_error.error+" "+e.data.msg);this.$emit("afterCancelPayment")}})["catch"](e=>{}).then(e=>{this.$emit("closeLoader")})},processPayment(e){this.is_loading=true;axios({method:"PUT",url:this.ajax_url+"/processpayment",data:{YII_CSRF_TOKEN:$("meta[name=YII_CSRF_TOKEN]").attr("content"),card_token:e,data:this.jwt_data},timeout:$timeout_cmp}).then(e=>{if(e.data.code==1){this.$emit("afterSuccessfulpayment",e.data.details)}else{this.$emit("afterCancelPayment",e.data.msg)}})["catch"](e=>{}).then(e=>{this.is_loading=false})}},template:`		        	 
	 <div class="modal" ref="modal_mercadopago" tabindex="-1" role="dialog" aria-labelledby="mercadopagopayForm" aria-hidden="true">
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">
	       <div class="modal-body position-relative">
	       
	         <a href="javascript:;" @click="close" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{title}}</h4>  	
	         	         
	         <p>{{label.notes}}</p>	 	 
	         
	         <form v-if="form_ready" class="forms mt-2 mb-2" @submit.prevent="submitForms" >         

	          <div class="form-label-group">    
              <input v-model="mp_credit_card_number" v-maska="'#### #### #### ####'"  class="mask_card form-control form-control-text" placeholder=""
               id="mp_credit_card_number" type="text"  >   
              <label for="mp_credit_card_number" class="required">{{label.credit_card_number}}</label> 
             </div>        
             
             <div class="row">
              <div class="col">
              
              <div class="form-label-group"> 
              <input v-model="mp_expiry_date" v-maska="'##/####'" class="form-control form-control-text" placeholder=""
               id="mp_expiry_date" type="text" >   
              <label for="mp_expiry_date" class="required">{{label.expiry_date}}</label>   
              </div>
              
              </div>
              <div class="col">
              
              <div class="form-label-group"> 
              <input v-model="mp_cvv" v-maska="'###'" class="form-control form-control-text" placeholder=""
               id="mp_cvv" type="text" maxlength="3" >   
              <label for="mp_cvv" class="required">{{label.cvv}}</label>   
              </div>
              
              </div>
             </div> <!--row-->         
             
              <div class="row">
              <div class="col">
              
	              <div class="form-label-group"> 
	              <input v-model="mp_card_name" class="form-control form-control-text" placeholder=""
	               id="mp_card_name" type="text" maxlength="255" >   
	              <label for="mp_card_name" class="required">{{label.card_name}}</label>   
	              </div>
              
              </div>              
             </div> <!--row-->
             
              <div class="row">
              <div class="col">
              
                  
	              <select class="form-control custom-select form-control-text" 
	              v-model="mp_identification_type">		 
			        <option v-for="(items, key) in mp_identification_type_list" :value="items.id" >
			        {{items.name}}
			        </option>
				  </select>
				  
              
              </div>
              <div class="col">
              
	              <div class="form-label-group"> 
	              <input v-model="mp_identification_number" class="form-control form-control-text" placeholder=""
	               id="mp_identification_number" type="text" maxlength="255" >   
	              <label for="mp_identification_number" class="required">{{label.identification_number}}</label>   
	              </div>
              
              </div>
             </div> <!--row-->
	         
	         </form> <!--forms-->
	         
	         <div id="error_message" v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			  <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			 </div>    
	         
	       </div> <!--modal body-->	  
	       
	       <div class="modal-footer justify-content-start">	        	       
		       <button class="btn btn-green w-100" @click="submitForms" :disabled="checkForm" 
		       :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->    

	
	
	 <div class="modal" ref="modal_cvv" tabindex="-1" role="dialog" aria-labelledby="modal_cvv" aria-hidden="true" data-backdrop="static" data-keyboard="false"  >
	   <div class="modal-dialog" role="document">
	     <div class="modal-content">
	       <div class="modal-body position-relative">
	       
	         <a href="javascript:;" @click="closeCvv" 
	          class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a> 
	        
	         <h4 class="m-0 mb-3 mt-3">{{label.cvv_verification}}</h4>  	
	         	         
	         <p>{{label.enter_cvv}} {{card_number}}</p>	 	 
	         
	         <form class="forms mt-2 mb-2" @submit.prevent="SubmitPayment" >         

	         <div class="form-label-group"> 
              <input ref="cvv_input" v-model="verify_cvv" v-maska="'####'" class="form-control form-control-text" placeholder=""
               id="verify_cvv" type="text" maxlength="3" >   
              <label for="verify_cvv" class="required">{{label.cvv}}</label>   
              </div>
	         
	          
	         </form> <!--forms-->
	         
	         <div id="error_message" v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
			  <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
			 </div>    
	         
	       </div> <!--modal body-->	  
	       
	       <div class="modal-footer justify-content-start">	        	       
		       <button class="btn btn-green w-100" @click="SubmitPayment" :disabled="checkFormCVV"
		       :class="{ loading: is_loading }"   >
		          <span class="label">{{label.submit}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </button>		      
		   </div> <!--footer-->
	            
	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->      	 	
	`};