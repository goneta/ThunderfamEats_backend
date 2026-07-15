<DIV id="vue-my-bookings" v-cloak> 
<div class="row mb-4">
  <div class="col-lg-6 col-md-3 col-3 d-flex justify-content-start align-items-center"></div> <!--col-->
   
  <div class="col-lg-6 col d-flex justify-content-end align-items-center order-search-wrap">     
   <div class="position-relative search-geocomplete w-75"> 	
	  <div v-if="!awaitingSearch" class="img-20 m-auto search_placeholder icon"></div>
	  <div v-if="awaitingSearch" class="icon" data-loader="circle"></div>    
	  <input class="form-control form-control-text form-control-text-white" 
      placeholder="<?php echo t("Search Booking")?>" v-model="q" :disabled="loading"  >		     
	  <div v-if="q" @click="resetData" class="icon-remove"><i class="zmdi zmdi-close"></i></div>
   </div>
  
  </div> <!--col-->
</div> <!--row-->

<div class="card p-3 mb-3 d-none d-lg-block"  v-if="!is_loading" >
 <div class="rounded p-3 grey-bg" >
  <div class="row no-gutters align-items-center">
    <div class="col-md-2">
       <div class="header_icon _icons points d-flex align-items-center justify-content-center">         
       </div>
    </div>
    
    <div class="col-md-6">             
        <h5><?php echo isset($summary['total_reservation'])?$summary['total_reservation']:0?></h5>
        <p class="m-0"><?php echo t("Total Bookings")?></p>
    </div>      
    
  </div>
 </div>
</div> <!--card -->

<component-booking-list
ref="booking_list"
api_url="<?php echo Yii::app()->createUrl("/Apibooking")?>" 
:status_list='<?php echo json_encode($status_list)?>'
:q="q"
@set-search="setSearch"
@reset-data="resetData"
@clear-search="clearSearch"
>
</component-booking-list>

<div><el-backtop /></div>
</DIV>

<!-- BOOKING LIST -->
<script type="text/x-template" id="xtemplate_booking_list">  
  <el-tabs v-model="tab" v-loading="loading" @tab-change="tabChange" >

  <div v-if="!hasData && !loading" class="d-flex justify-content-center mt-4 mb-5">
    <p class="m-0 text-muted"><?php echo t("No data available")?></p>
  </div>    
  
  
    <template v-for="(status,key) in status_list">
      <el-tab-pane :label="status" :name="key" 	>
      
         <template v-for="datas in data" >
            <div v-for="row_data in datas" class="kmrs-row row m-0 rounded p-2 mb-2" >
            
            <div class="col p-0">
              <div class="d-flex justify-content-start">
                <div class="pr-2">
                  <img class="img-60 rounded-pill" v-if="merchant[row_data.merchant_id]" :src="merchant[row_data.merchant_id].url_logo"/>
                </div>
                <div class="flex-fill">
                    <div class="d-flex align-items-center">
                      <div class="align-self-center mr-2">
                          <h6 v-if="merchant[row_data.merchant_id]" class="m-0" >{{ merchant[row_data.merchant_id].restaurant_name }}</h6>
                      </div>                                       
                    </div>
                    <p class="m-0" v-if="merchant[row_data.merchant_id]" >
                    {{ merchant[row_data.merchant_id].merchant_address }}
                    </p>        
                </div> 
              </div> <!--flex-->
            </div> <!--col-->

            <div class="col pl-5">
               <h6 class="font13">{{row_data.booking_id}}</h6>
               <p class="badge badge-light m-0"><?php echo t("Guest")?> : {{row_data.guest_number}}</p>
               <p class="m-0" v-if="table_list[row_data.table_id]">
                {{ table_list[row_data.table_id] }}
               </p>
            </div> <!--col-->

            <div class="col">
               <div class="d-flex">                
                  <div v-if="status_list[row_data.status]">                                  
                     <span class="badge" :style="{background:`${row_data.status_color.background}`,color:`${row_data.status_color.color}`}">
                     {{ status_list[row_data.status] }}
                     </span>
                     <p class="text-grey m-0">{{row_data.reservation_date}}</p>
                  </div>
                  <div class="flex-grow-1 text-right">
                  
                  <div class="dropdown dropleft">	      
                    <a class="btn btn-sm dropdown-toggle no-arrow text-truncate shadow-none" href="#" 
                      role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="zmdi zmdi-more"></i> 
                   </a>
                   <div class="dropdown-menu dropdown-actions" aria-labelledby="dropdownMenuLink">	    
                      <a class="dropdown-item ssm-toggle-nav" :href="row_data.view" >
                        <i class="zmdi zmdi-eye mr-2"></i> <?php echo t("View")?>
                      </a>		                      
                      <a class="dropdown-item ssm-toggle-nav" :href="row_data.cancel"  >
                        <i class="zmdi zmdi-close mr-2"></i> <?php echo t("Cancel")?>
                      </a>		                      
                  </div>

                  </div>
                  <!-- dropdown -->

                  </div>
               </div>
            </div> <!--col-->
            
            </div> 
            <!-- row -->
         </template         
      </el-tab-pane>    
    </template>

    
    <template v-if="!loading">
      <div class="d-flex justify-content-center mt-4 mb-5" v-if="show_next" >
      <el-button type="primary" round
      @click="loadMore(page)"
      :loading="load_more"
      size="large"   
        ><?php echo t("Load more")?></el-button> 
      </div>
      <div v-else class="d-flex justify-content-center mt-4 mb-5">
        <p v-if="hasData" class="m-0 text-muted"><?php echo t("end of results");?></p>
      </div>
    </template>

</el-tabs>
</script>