<script type="text/x-template" id="review_driver">
<el-dialog 
v-model="modal" 
:lock-scroll="true" 
width="400" 
id="footer-none-bg"	
@open="whenOpenDialog"
>

   <div class="text-center p-2 ">    
      <el-avatar :size="80" :src="driver_info.photo" ></el-avatar>
      <h6 class="mt-3">Let's rate your driver's delivery service</h6>
      <p class="mb-1">{{driver_info.rate_message}}</p>
      <div class="mt-2 mb-3">
         <el-rate v-model="rating" size="large"  ></el-rate>
      </div>

      <p class="mb-1">What did you like about the delivery?</p>      

      <div style="width: 80%;margin: auto;">
         <div class="flex-content w-100">
            <template v-for="items in data">
               <div class="flex-content-item">
               <el-button round @click="review_text=items" :type="review_text==items?'primary':''" >
                  {{items}}
               </el-button>
               </div>
            </template>                  
         </div>    
      </div>

   </div>  

   <template #footer>    
     <el-button type="success" size="large" @click="submitReview" :disabled="!hasRating" :loading="loading" >
          Submit Review
     </el-button>
   </template>

</el-dialog>
</script>