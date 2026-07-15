<!--promoModal-->
<div class="modal" ref="promo_modal" id="promoModal" tabindex="-1" role="dialog" aria-labelledby="promoModal" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content 
    <?php echo Yii::app()->params['isMobile']==TRUE?"modal-mobile":"" ?>
    ">      

      <div class="modal-header border-bottom-0" style="padding-bottom:0px !important;">
        <h5 class="modal-title" id="exampleModalLongTitle">
           <?php echo t("Promotions")?>
       </h5>
       <div class="close">
         <a href="javascript:;" @click="close" class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a>   
       </div>
      </div>

      <div class="modal-body">          
            
          
      <div  v-cloak v-if="error" class="alert alert-warning m-0" role="alert">
	    <p v-cloak class="m-0">{{error}}</p>	    
	   </div>             
      
      <ul class="list-unstyled list-selection m-0 p-0">
      
        <li class="d-flex align-items-center" v-cloak v-for="promo in data" >
         <div class="flexcol  mr-3">
         
          <div class="custom-control custom-radio">
		      <input type="radio" v-model="promo_id" class="custom-control-input"
              :id="promo.promo_id+promo.promo_type" :value="[promo.promo_type, promo.promo_id]"
		      >
		      <label class="custom-control-label font14 bold" :for="promo.promo_id+promo.promo_type">
		         {{ promo.title }}
		      </label>
          
		      <p class="m-0 text-grey" v-if="promo.sub_title" >{{ promo.sub_title }}</p>
          <p class="m-0 text-grey" v-if="promo.max_spend" >{{ promo.max_spend }}</p>
          <p class="m-0 text-grey" v-if="promo.max_cap" >{{ promo.max_cap }}</p>
		      <p class="m-0 text-grey" v-if="promo.valid_to">{{ promo.valid_to }}</p>
		                
		      <a v-if="promo.promo_id+promo.promo_type==promo_selected[1]+promo_selected[0]" @click="removePromo(promo.promo_type,promo.promo_id)"
		       href="javascript:;" class="btn btn-black mt-1" :class="{ loading: remove_loading }"  >
		        <span class="label"><?php echo t("Remove")?></span>
		        <div class="m-auto circle-loader" data-loader="circle-side"></div>
		      </a>		      
		    </div>   	           
         
         </div> <!--flexcol-->         
       </li>       
       
      </ul>  

      </div> <!--modal body-->
          

      <div class="modal-footer">
        <button type="button" class="btn btn-light" @click="showPromoCode">
          <?php echo t("Add promo")?>
        </button>        
        <button type="button" class="btn btn btn-green pl-4 pr-4" @click="save" :class="{ loading: loading }" :disabled="!hasSelectedPromo"  >
          <span class="label"><?php echo t("Save")?></span><div class="m-auto circle-loader" data-loader="circle-side"></div>
        </button>
      </div>
      
      </div> <!--footer-->
    </div> <!--content-->
  </div> <!--dialog-->
</div> <!--modal-->              

