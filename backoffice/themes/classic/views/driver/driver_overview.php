


<div id="app-driveroverview" class="card">
<div class="card-body">

 <component-driveroverview
 ref="driver_overview"
 ajax_url="<?php echo isset($ajax_url)?$ajax_url:Yii::app()->createUrl("/api");?>" 
 driver_uuid="<?php echo $driver_uuid;?>"
 :label="{
      no_data : '<?php echo CJavaScript::quote(t("No available data"));?>',
      driver_overview : '<?php echo CJavaScript::quote(t("Driver Overview"));?>',
      total_review : '<?php echo CJavaScript::quote(t("Total reviews"));?>',
      total_delivered : '<?php echo CJavaScript::quote(t("Total Delivered"));?>',
      total_tips : '<?php echo CJavaScript::quote(t("Total Tips"));?>',
      activities : '<?php echo CJavaScript::quote(t("Activities"));?>',
      date : '<?php echo CJavaScript::quote(t("Date"));?>',
      star : '<?php echo CJavaScript::quote(t("Star"));?>',
    }"  
 >
 </component-driveroverview>

</div>
</div>

<script type="text/x-template" id="xtemplate_overview">
<div v-loading="loading" >
	
	<div class="row mb-4">
    <div class="col-3">
        <h5 class="m-0 mb-3">{{label.driver_overview}}</h5>
        <h2 class="font-medium mt-2 mb-0">{{overview_data.total}}</h2>
        <span class="text-muted">{{label.total_review}}</span> 
    </div>
    <div class="col">
         <div v-for="items in overview_data.review_summary" class="rating-list mb-1">  
            <div class="d-flex justify-content-between align-items-center">
            <div class="flex-col font11">{{items.count}} {{label.star}}</div>
            <div class="flex-col font11">{{items.in_percent}}</div>
            </div>
            <el-progress :percentage="items.review" :show-text="false" :stroke-width="10" />
        </div> <!-- rating-list -->
    </div>
  </div>

  <div class="row mb-4">
  <div class="col">
	    <el-card shadow="hover">
		   <div class="row">
		     <!-- <div class="col">
			     <el-progress type="circle" :percentage="overview_data.total_delivered_percent" :width="80" color="#3ecf8e" />
			 </div> -->
			 <div class="col">
			    <p class="text-muted m-0"><?php echo t("Total earnings")?></p>
				<h2 class="font-medium mt-2 mb-0 text-primary">{{overview_data.wallet_balance}}</h2>
			 </div>
		   </div>
		</el-card>
    </div>
    <div class="col">
	    <el-card shadow="hover">
		   <div class="row">
		     <!-- <div class="col">
			     <el-progress type="circle" :percentage="overview_data.total_delivered_percent" :width="80" color="#3ecf8e" />
			 </div> -->
			 <div class="col">
			    <p class="text-muted m-0">{{label.total_delivered}}</p>
				<h2 class="font-medium mt-2 mb-0 text-warning">{{overview_data.total_delivered}}</h2>
			 </div>
		   </div>
		</el-card>
    </div>
	<div class="col">
	    <el-card shadow="hover">
		   <div class="row">
		     <!-- <div class="col">
			     <el-progress type="circle" :percentage="overview_data.total_tip_percent" :width="80" color="#ffd966" />
			 </div> -->
			 <div class="col">
			    <p class="text-muted m-0">{{label.total_tips}}</p>
				<h2 class="font-medium mt-2 mb-0 text-info">{{overview_data.total_tip}}</h2>
			 </div>
		   </div>
		</el-card>
    </div>
  </div>

  <h5 class="m-0 mb-3">{{label.activities}}</h5>
  
  <el-table :data="tableData" v-loading="loading_activity" style="width: 100%" :empty-text="label.no_data" >
    <el-table-column prop="created_at" label="Date" width="180" />    
    <el-table-column prop="remarks" label="" />    
  </el-table>

  </div>
</script>