<script type="text/x-template" id="xtemplate_assign_driver">

<div ref="modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
	  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
	      <div class="modal-content">
		     <div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel"><?php echo t("Assign Driver")?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">&times;</span>
				</button>
			  </div>
		      <div class="modal-body grey-bg" v-loading="loading">	
			
			  <div class="row">
			     <div class="col-4">
				 
				 <div class="mb-1">
					<div class="row align-items-start">
					  <div class="col"><h6><?php echo t("Filter by zone")?></h6></div>
					  <div class="col text-right">
					 
					  <template v-if="hasFilter">
					  <button type="button" class="btn btn-link p-0" @click="clearFilter" ><?php echo t("Clear filter")?></button>
					  </template>
					  
					  </div>
					</div>
					<el-radio-group v-model="zone_id" size="large">					
					<template v-for="items in zone_data" :key="items">
						<el-radio-button :label="items.value"  >{{items.label}}</el-radio-button>										
						</template>
					</el-radio-group>
				  </div>

				  <div class="mb-1">
				  <h6><?php echo t("Filter by Groups")?></h6>
				  <el-radio-group v-model="group_selected" size="large">					
					  <template v-for="(items,index) in group_data" :key="items">
					   <el-radio-button :label="index"  >{{items}}</el-radio-button>										
					  </template>
				  </el-radio-group>
				  </div>

				  <template v-if="!loading && !hasData">
						<div class="text-center p-3 pt-4">
						<h5><?php echo t("No results")?></h5>
						<p class="text-grey"><?php echo t("No available drivers")?></p>
						</div>
				  </template>
				  <template v-else >				      
				      <h6><?php echo t("Available Drivers")?></h6>
					  <DIV class="card rounded p-2">
					       <div class="row pt-3">
						      <div v-for="items in data" :key="items" class="col-12 mb-3">
							       <div class="d-flex flex-row chip" style="height:40px;">
								        <div class="align-self-center">
											<el-avatar					   
											:src="items.photo_url"
											>
											</el-avatar>
										</div>
										<div class="pl-2 align-self-center">
											{{items.name}}
											<template v-if="active_task[items.driver_id]">
											   <div class="font11"><b>{{active_task[items.driver_id]}}</b> <span class="text-muted"><?php echo t("active orders")?></span> </div>
											</template>
										</div>				 
										<div class="ml-auto pl-2 align-self-center">
										<button @click="AssignDriver(items.driver_id)" type="button" 
										style="line-height:13px;"
										class="btn btn-green small btn-sm"><?php echo t("Assign")?></button>
								   </div>
							  </div>
						   </div>
					  </DIV>
				  </template>

				 </div>
				 <!-- col -->

				 <div class="col" style="border:1px solid #f6f7f8;">
				    <DIV class="card rounded p-2">																   
					   <template v-if="hasMarkers">
					   <components-map
					   ref="map_components"
					   :markers="markers"
					   :center="map_center"
					   :zoom="zoom"
					   :maps_config='<?php echo json_encode($maps_config)?>'
					   ></components-map>
					   </template>
					</DIV>
				 </div>
				 <!-- col -->
			  </div>
			  <!-- row -->

			  </div> 
			  <!-- moday body -->
		  </div>
	   </div>
    </div>
    
</script>