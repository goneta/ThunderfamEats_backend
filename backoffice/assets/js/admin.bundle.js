(function(c){"use strict";var m;var h;const s=2e4;const a={form:"application/x-www-form-urlencoded; charset=UTF-8",json:"application/json"};var d=function(t){console.debug(t)};var $=function(t){alert(JSON.stringify(t))};jQuery.fn.exists=function(){return this.length>0};var u=function(t){if(typeof t==="undefined"||t==null||t==""||t=="null"||t=="undefined"){return true}return false};jQuery(document).ready(function(){if(c(".new-passowrd").exists()){c(".new-passowrd").val("")}if(c(".sidebar-nav").exists()){c("ul.sidebar-nav ul > li.active").parent().addClass("open");c("ul li a").click(function(){c(this).parent().find(".sidebar-nav-sub-menu").slideToggle("fast");if(c(".nice-scroll").exists()){setTimeout(function(){c(".nice-scroll").getNiceScroll().resize()},100)}})}if(c(".select_two").exists()){c(".select_two").select2({allowClear:false,templateResult:D,theme:"classic",language:"en_us"})}if(c(".select_two_ajax").exists()){var t=c(".select_two_ajax").attr("action");c(".select_two_ajax").select2({theme:"classic",language:"en_us",ajax:{delay:250,url:ajaxurl+"/"+t,type:"POST",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return e}}});c("#merchant_id_booking").on("change",function(t){window.location.href=create_reservation_link+"&merchant_id="+c("#merchant_id_booking").val()});c("#driver_id_ca").on("change",function(t){x("getWalletBalance","id="+c(this).val())})}if(c(".select_two_ajax2").exists()){var t=c(".select_two_ajax2").attr("action");c(".select_two_ajax2").select2({theme:"classic",language:{searching:function(){return"Searching..."},noResults:function(t){return"No results"}},ajax:{delay:250,url:ajaxurl+"/"+t,type:"POST",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return e}}})}let e=["Su","Mo","Tu","We","Th","Fr","Sa"];let a=["January","February","March","April","May","June","July","August","September","October","November","December"];if(typeof daysofweek!=="undefined"&&daysofweek!==null){e=JSON.parse(daysofweek)}if(typeof monthsname!=="undefined"&&monthsname!==null){a=JSON.parse(monthsname)}if(c(".datepick").exists()){var i=c(".datepick");c(".datepick").daterangepicker({singleDatePicker:true,autoUpdateInput:false,locale:{format:"YYYY-MM-DD",daysOfWeek:e,monthNames:a},autoApply:true},function(t,e,a){i.val(t.format("YYYY-MM-DD"))})}if(c(".datepick2").exists()){var s=c(".datepick2");c(".datepick2").daterangepicker({singleDatePicker:true,autoUpdateInput:false,locale:{format:"YYYY-MM-DD",daysOfWeek:e,monthNames:a},autoApply:true},function(t,e,a){s.val(t.format("YYYY-MM-DD"))})}if(c(".datepick_withtime").exists()){c(".datepick_withtime").daterangepicker({timePicker:true,timePicker24Hour:true,autoUpdateInput:false,singleDatePicker:true,locale:{format:"YYYY-MM-DD",daysOfWeek:e,monthNames:a},autoApply:true},function(t,e,a){c(this)[0].element.val(t.format("YYYY-MM-DD HH:mm:ss"))})}if(c(".date_range_picker").exists()){var i=c(".date_range_picker");var l=i.data("separator");c(".date_range_picker").daterangepicker({autoUpdateInput:false,showWeekNumbers:true,alwaysShowCalendars:true,autoApply:true,locale:{format:"YYYY-MM-DD",daysOfWeek:e,monthNames:a},ranges:{Today:[moment(),moment()],Yesterday:[moment().subtract(1,"days"),moment().subtract(1,"days")],"Last 7 Days":[moment().subtract(6,"days"),moment()],"Last 30 Days":[moment().subtract(29,"days"),moment()],"This Month":[moment().startOf("month"),moment().endOf("month")],"Last Month":[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]}},function(t,e,a){i.val(t.format("YYYY-MM-DD")+" "+l+" "+e.format("YYYY-MM-DD"))})}if(c(".timepick").exists()){c(".timepick").datetimepicker({format:"hh:mm A"})}if(c(".tool_tips").exists()){c(".tool_tips").tooltip()}if(c(".colorpicker").exists()){c(".colorpicker").spectrum({type:"component",showAlpha:false})}if(c(".autosize").exists()){autosize(c(".autosize"))}c("#frm_search").submit(function(t){m.search(c(".search").val()).draw();c(".search_close").show()});c(document).on("click",".search_close",function(){c("#frm_search").find(".search").val("");m.search("").draw();c(".search_close").hide()});c("#frm_search_app").submit(function(t){S();w();c(".search_close_app").show()});c(document).on("click",".search_close_app",function(){c("#frm_search_app").find(".search").val("");c(".search_close_app").hide();S(true);w()});c(".frm_search_filter").submit(function(t){c(".search_close_filter").show();m.destroy();m.clear();_(c(".table_datatables"),c(".frm_datatables"))});c(document).on("click",".search_close_filter",function(){c(".frm_search_filter").find(".search,.date_range_picker").val("");c(".search_close_filter").hide();m.destroy();m.clear();_(c(".table_datatables"),c(".frm_datatables"))});c(".image_file").on("change",function(){var t=c(this).val().split("\\").pop();c(this).siblings(".image_label").addClass("selected").html(t)});c(".image2_file").on("change",function(){var t=c(this).val().split("\\").pop();c(this).siblings(".image2_label").addClass("selected").html(t)});if(c(".mask_time").exists()){c(".mask_time").mask("00:00")}if(c(".mask_phone").exists()){c(".mask_phone").mask("(00) 0000-0000")}if(c(".mask_mobile").exists()){let t="+000000000000";if(typeof backend_phone_mask!=="undefined"&&backend_phone_mask!==null){t=backend_phone_mask}c(".mask_mobile").mask(t)}if(c(".mask_date").exists()){c(".mask_date").mask("0000/00/00")}if(c(".summernote").exists()){c(".summernote").summernote({height:200,codeviewFilter:false,toolbar:[["font",["bold","underline","italic","clear"]],["para",["ul","ol","paragraph"]],["style",["style"]],["color",["color"]],["table",["table"]],["insert",["link","picture","video"]],["view",["fullscreen","undo","redo","codeview"]]]})}if(c(".copy_text_to").exists()){c(".copy_text_to").keyup(function(t){var e=c(this).val();var a=c(this).data("id");e=K(e);c(a).val(e)})}if(typeof is_mobile!=="undefined"&&is_mobile!==null){h=is_mobile}if(!h){if(c(".nice-scroll").exists()){c(".nice-scroll").niceScroll({autohidemode:true})}}c(".sidebar-panel").slideAndSwipe();c(document).on("click",".hamburger",function(){c(this).toggleClass("is-active")});c(document).on("click",function(t){if(c(t.target).closest(".hamburger").length===0){if(c(".hamburger").hasClass("is-active")){c(".hamburger").removeClass("is-active")}}});if(c(".headroom").exists()){var r=document.querySelector(".headroom");var d=new Headroom(r);d.init()}if(c(".headroom2").exists()){var r=document.querySelector(".headroom2");var d=new Headroom(r);d.init()}if(c("#sort-items").exists()){var o=document.getElementById("sort-items");var n=Sortable.create(o,{swapThreshold:1,animation:150,direction:"horizontal"})}if(c("#printer-form").exists()){let t=c(".printer_type_list").val();if(!u(t)){c(".element-"+t).removeClass("d-none");if(t=="wifi"){c(".element-feieyun").addClass("d-none")}else{c(".element-wifi").addClass("d-none")}}c(".printer_type_list").on("change",function(){let t=c(this).find(":selected").val();c(".element-"+t).removeClass("d-none");if(t=="wifi"){c(".element-feieyun").addClass("d-none")}else{c(".element-wifi").addClass("d-none")}})}});function K(t){return t.toString().normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase().replace(/\s+/g,"-").replace(/&/g,"-and-").replace(/[^\w\-]+/g,"").replace(/\-\-+/g,"-").replace(/^-+/,"").replace(/-+$/,"")}function D(t){if(t&&!t.selected){return c("<span>"+t.text+"</span>")}}var r=function(t){return t};const i=new Notyf({duration:1e3*4});var o=function(t,e){switch(e){case"error":i.error(t);break;default:i.success(t);break}};var _=function(t,e){c.fn.dataTable.ext.errMode="none";var a=e.serializeArray();var i={};c.each(a,function(){if(i[this.name]){if(!i[this.name].push){i[this.name]=[i[this.name]]}i[this.name].push(this.value||"")}else{i[this.name]=this.value||""}});var s="";if(typeof action_name!=="undefined"&&action_name!==null){s=action_name}let l=false;if(typeof datatable_export!=="undefined"&&datatable_export!==null){l=datatable_export}let r={aaSorting:[[0,"DESC"]],processing:true,serverSide:true,bFilter:true,dom:'<"top">rt<"row"<"col-md-6"i><"col-md-6"p>><"clear">',pageLength:10,ajax:{url:ajaxurl+"/"+s,type:"POST",data:i},language:{url:ajaxurl+"/DatableLocalize"},buttons:["excelHtml5","csvHtml5","pdfHtml5"]};if(l==1){r.dom="lBrtip"}else{r.dom="lrtip"}m=t.on("preXhr.dt",function(t,e,a){d("loading")}).on("xhr.dt",function(t,e,a,i){d("done");setTimeout(function(){c(".tool_tips").tooltip()},100)}).on("error.dt",function(t,e,a,i){d("error")}).DataTable(r)};var e="";var n={};var p={};var l;jQuery(document).ready(function(){if(c(".table_datatables").exists()){_(c(".table_datatables"),c(".frm_datatables"))}c(document).on("click",".checkbox_select_all",function(){c(this).toggleClass("checked");if(!c(this).hasClass("checked")){c(".checkbox_child").prop("checked",false)}else{c(".checkbox_child").prop("checked",true)}});c(document).on("click",".datatables_delete",function(){e=c(this).data("id");c(".delete_confirm_modal").modal("show")});c(document).on("click",".datatables_clone_merchant",function(){e=c(this).data("id");c(".clone_merchant_modal").modal("show")});c(".clone_merchant_modal").on("show.bs.modal",function(){c(".hidden").hide();c(".duplicate_merchant").prop("disabled",false);c('input[type="checkbox"]').prop("checked",true)});c(document).on("click",".duplicate_merchant",function(){c(this).prop("disabled",true);var t=[];c('input[type="checkbox"]:checked').each(function(){t.push(c(this).val())});c(".hidden").show();x("duplicate_merchant",{merchant_id:e,payload:t},99,null,"POST")});c(".delete_confirm_modal").on("shown.bs.modal",function(){c(".item_delete").attr("href",delete_link+"?id="+e)});c(document).on("click",".delete_image",function(){e=c(this).data("id");c(".delete_image_confirm_modal").modal("show")});c(".delete_image_confirm_modal").on("shown.bs.modal",function(){c(".item_delete").attr("href",e)});if(c(".merchant_type_selection").exists()){var t=parseInt(c(".merchant_type_selection").val());g(t)}c(".merchant_type_selection").change(function(){g(parseInt(c(this).val()))});c(document).on("click",".process_csv",function(){if(!c(this).hasClass("disabled")){c(this).addClass("disabled");c(this).html('<span class="spinner-border spinner-border-sm"></span>');b(c(this).data("id"))}});c(document).on("click",".order_history",function(){e=c(this).data("id");c(".order_history_modal").modal("show")});c(".order_history_modal").on("show.bs.modal",function(){x("order_history","id="+e)});c(document).on("change",".set_default_currency",function(){c("input:checkbox").prop("checked",false);c(this).prop("checked",true);setTimeout(function(){x("update_currency_default","id="+c(".set_default_currency:checked").val())},100)});c(document).on("change",".set_email_default_provider",function(){c("input:checkbox").prop("checked",false);c(this).prop("checked",true);setTimeout(function(){x("update_email_provider_default","id="+c(".set_email_default_provider:checked").val())},100)});c(document).on("change",".set_default_smsprovider",function(){c("input:checkbox").prop("checked",false);c(this).prop("checked",true);setTimeout(function(){x("set_default_smsprovider","id="+c(".set_default_smsprovider:checked").val())},100)});c(document).on("change",".set_payment_provider",function(){var t=c(this).val();var e=c(this).prop("checked");e=e==true?"active":"inactive";setTimeout(function(){x("set_payment_provider","id="+t+"&status="+e)},100)});c(document).on("change",".set_banner_status",function(){var t=c(this).val();var e=c(this).prop("checked");e=e==true?1:0;setTimeout(function(){x("setdefaultbanner","id="+t+"&status="+e)},100)});c(document).on("change",".set_template_email,.set_template_sms,.set_template_push",function(t){var e="";if(c(this).hasClass("set_template_email")){e="email"}if(c(this).hasClass("set_template_sms")){e="sms"}if(c(this).hasClass("set_template_push")){e="push"}var a=c(t.target).val();var i=c(this).is(":checked");i=i==true?1:0;setTimeout(function(){x("set_template","id="+a+"&checked="+i+"&method="+e)},100)});c(".coupon_options").change(function(){v(c(this).val())});if(c(".coupon_options").exists()){v(c(".coupon_options").val())}c(document).on("click",".template_restore",function(){c(".modal_restore").modal("show")});c(".locale").on("change",function(){c(".locale_title").val(c(".locale option:selected").text())});if(h){if(c("#lazy-start").exists()){w()}}if(c(".banner_type").exists()){let t=c(".banner_type").val();f(t)}c(".banner_type").on("change",function(){f(c(this).val())})});var f=function(t){c(".section-banner").hide();c(".section-"+t).show()};var v=function(t){c(".coupon_customer").hide();c(".coupon_max_number_use").hide();if(t==6){c(".coupon_customer").show()}else if(t==5){c(".coupon_max_number_use").show()}else{}};var g=function(t){switch(t){case 1:c(".membership_type_1").show();c(".membership_type_3").hide();c(".invoice_section").hide();c(".invoice_section_membership").show();break;case 2:c(".membership_type_1").hide();c(".membership_type_2").show();c(".membership_type_3").hide();c(".invoice_section").show();c(".invoice_section_membership").hide();break;case 3:c(".membership_type_1").hide();c(".membership_type_2").show();c(".membership_type_3").show();break}};var b=function(t){x("process_csv","id="+t)};var y=function(){var t=new Date;var e=t.getTime();return e};var M=function(){var t="";var e=c("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&YII_CSRF_TOKEN="+e;return t};var x=function(t,e,a,i,s){l=y();if(!u(a)){var l=a}if(u(s)){s="POST";e+=M()}n[l]=c.ajax({url:ajaxurl+"/"+t,method:s,data:e,dataType:"json",timeout:2e4,crossDomain:true,beforeSend:function(t){if(typeof i!=="undefined"&&i!==null){}else{}if(n[l]!=null){d("request aborted");n[l].abort();clearTimeout(p[l])}else{p[l]=setTimeout(function(){n[l].abort();o(r("Request taking lot of time. Please try again"))},2e4)}}});n[l].done(function(t){d("done");var e="";if(typeof t.details.next_action!=="undefined"&&t.details.next_action!==null){e=t.details.next_action}console.log("+>"+e);if(t.code==1){switch(e){case"csv_continue":c(".csv_progress_"+t.details.id).html(t.msg);setTimeout(function(){b(t.details.id)},1*1e3);break;case"csv_done":c(".csv_progress_"+t.details.id).html(t.msg);c('a.view_delete_process[data-id="'+t.details.id+'"]').html('<i class="zmdi zmdi-mail-send"></i>');break;case"order_history":z(t.details.data,".order_history_modal table tbody");break;case"collected_balance":c(".ca_balance_wrap").show();c(".ca_balance").html(t.details.balance);break;case"duplicate_close_modal":o(t.msg,"success");c(".clone_merchant_modal").modal("hide");break;default:o(t.msg,"success");break}}else{switch(e){case"clear_order_history":c(".order_history_modal table tbody").html("");break;case"collected_balance":c(".ca_balance_wrap").hide();break;default:o(t.msg,"danger");break}}});n[l].always(function(){d("ajax always");n[l]=null;clearTimeout(p[l])});n[l].fail(function(t,e){clearTimeout(p[l]);o(r("Failed")+": "+e,"danger")})};var z=function(t,e){var a="";c.each(t,function(t,e){a+="<tr>";a+="<td>"+e.date_created+"</td>";a+="<td>"+e.status+"</td>";a+="<td>"+e.remarks+"</td>";a+="</tr>"});c(e).html(a)};var w=function(){l=c("#lazy-start").infiniteScroll({path:function(){var t=c(".frm_search").serializeArray();var e={};var a="";c.each(t,function(){if(e[this.name]){if(!e[this.name].push){e[this.name]=[e[this.name]]}e[this.name].push(this.value||"")}else{e[this.name]=this.value||""}});c.each(e,function(t,e){a+="&"+t+"="+e});a+="&page="+this.pageIndex;return ajaxurl+"/"+action_name+"/?"+a},responseBody:"json",history:false,status:".lazy-load-status"});l.on("load.infiniteScroll",function(t,e){if(e.code==1){c(".page-no-results").hide();if(e.details.is_search){d("search==");l.html("")}U(e.details.data)}else{var a=parseInt(e.details.page);if(a<=0){l.html("");c(".page-no-results").show()}else{l.infiniteScroll("option",{loadOnScroll:false})}}});l.infiniteScroll("loadNextPage")};var U=function(t){var a="";c.each(t,function(t,e){a+='<div class="kmrs-row">';a+='<div class="d-flex bd-highlight">';a+='<div class="p-2 bd-highlight">';a+=e[0];a+="</div>";a+='<div class="p-2 bd-highlight flex-grow-1">';a+=e[1];a+="</div>";a+="</div>";a+='<div class="d-flex justify-content-end">';if(c.isArray(e[2])){c.each(e[2],function(t){a+='<div class="p-2" >';a+=e[2][t];a+="</div>"})}a+="</div>";a+="</div>"});l.append(a)};var S=function(t){try{if(t){l.html("")}l.infiniteScroll("destroy");l.removeData("infiniteScroll");l.off("load.infiniteScroll")}catch(e){d(e.message)}};const t={props:["label","size"],methods:{confirm(){bootbox.confirm({size:this.size,title:this.label.confirm,message:this.label.are_you_sure,centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.yes,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{this.$emit("callback",t)}})},alert(t,e){bootbox.alert({size:!u(e.size)?e.size:"",closeButton:false,message:t,animate:false,centerVertical:true,buttons:{ok:{label:this.label.ok,className:"btn btn-green small pl-4 pr-4"}}})}}};const I={props:["message","donnot_close"],data(){return{new_message:""}},methods:{show(){c(this.$refs.modal).modal("show")},close(){c(this.$refs.modal).modal("hide")},setMessage(t){this.new_message=t}},template:`
	<div class="modal" ref="modal"  tabindex="-1" role="dialog"  aria-hidden="true"
	data-backdrop="static" data-keyboard="false" 	 
	 >
	   <div class="modal-dialog modal-dialog-centered modal-sm modal-loadingbox" role="document">
	     <div class="modal-content">
	         <div class="modal-body">
	            <div class="loading mt-2">
	              <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	            </div>
	            <p class="text-center mt-2">
	              <div v-if="!new_message">{{message}}</div>
	              <div v-if="new_message">{{new_message}}</div>
	              <div>{{donnot_close}}</div>
	            </p>	            
	         </div>
	       </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->      
	`};const A={props:["ajax_url","label","order_uuid"],data(){return{data:[],reason:"",resolvePromise:undefined,rejectPromise:undefined}},computed:{hasData(){if(!u(this.reason)){return true}return false}},mounted(){this.orderRejectionList();autosize(this.$refs.reason)},methods:{confirm(){c(this.$refs.rejection_modal).modal("show");return new Promise((t,e)=>{this.resolvePromise=t;this.rejectPromise=e})},close(){c(this.$refs.rejection_modal).modal("hide")},orderRejectionList(){axios({method:"POST",url:this.ajax_url+"/orderRejectionList",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{})},submit(){this.close();this.resolvePromise(this.reason)}},template:`	
	<div ref="rejection_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            
      <form @submit.prevent="submit" >
        <div class="form-label-group mt-2"> 
        <textarea ref="reason" v-model="reason" id="reason" class="form-control form-control-text" :placeholder="label.reason">
        </textarea>
        </div>
                        
        <div class="list-group list-group-flush">
         <a v-for="item in data" @click="reason=item" 
         :class="{active:reason==item}"
         class="text-center list-group-item list-group-item-action">
         {{item}}
         </a>
        </div>
        
      </form>
      
      </div>      
      <div class="modal-footer">            
        <button type="button" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
         :disabled="!hasData"
         >
          <span>{{label.reject_order}}</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
    </div>
  </div>
</div>            	`};const L={props:["ajax_url","group_name","refund_label","remove_item","out_stock_label","manual_status","modify_order","update_order_label","filter_buttons","enabled_delay_order"],components:{"components-rejection-forms":A,"components-loading-box":I},data(){return{is_loading:false,loading:true,order_uuid:"",merchant:[],order_info:[],items:[],order_summary:[],summary_changes:[],summary_transaction:[],summary_total:0,merchant_direction:"",delivery_direction:"",order_status:[],services:[],payment_status:[],response_code:0,customer:[],buttons:[],status_data:[],stats_id:"",sold_out_options:[],out_stock_options:"",item_row:[],additional_charge:0,additional_charge_name:"",customer_name:"",contact_number:"",delivery_address:"",latitude:"",longitude:"",error:[],link_pdf:[],payment_history:[],credit_card_details:[],driver_data:[],zone_list:[],merchant_zone:[],delivery_status:[],order_table_data:[],kitchen_addon:false,found_in_kitchen:false,print_data:[],socket_error:"",webSocket:null}},computed:{hasData(){if(this.stats_id>0){return true}return false},outStockOptions(){if(this.out_stock_options>0){return true}return false},hasValidCharge(){if(this.additional_charge>0){return true}return false},refundAvailable(){if(this.order_info.payment_status=="paid"){return true}return false},hasRefund(){if(this.summary_changes){if(this.summary_changes.method==="total_decrease"){if(this.summary_changes.refund_due>0){return true}}}return false},hasAmountToCollect(){if(this.summary_changes){if(this.summary_changes.method==="total_increase"){if(this.summary_changes.refund_due>0){return true}}}return false},hasTotalDecrease(){if(this.summary_changes){if(this.summary_changes.method==="total_decrease"){return true}}return false},hasTotalIncrease(){if(this.summary_changes){if(this.summary_changes.method==="total_increase"){return true}}return false},summaryTransaction(){if(this.summary_transaction){if(typeof this.summary_transaction.summary_list!=="undefined"&&this.summary_transaction.summary_list!==null){if(this.summary_transaction.summary_list.length>0){return true}}}return false},hasInvoiceUnpaid(){if(this.summary_changes.unpaid_invoice){return true}if(this.summary_changes.paid_invoice){return true}return false},hasBooking(){if(Object.keys(this.order_table_data).length>0){return true}return false}},mounted(){this.getOrderStatusList()},methods:{orderDetails(t,e){this.order_uuid=t;this.is_loading=true;this.loading=true;var a=["payment_history","print_settings","buttons"];axios({method:"POST",url:this.ajax_url+"/orderDetails",data:{order_uuid:this.order_uuid,group_name:this.group_name,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),payload:a,modify_order:this.modify_order,filter_buttons:this.filter_buttons},timeout:s}).then(t=>{this.response_code=t.data.code;if(t.data.code==1){this.merchant=t.data.details.data.merchant;this.order_info=t.data.details.data.order.order_info;this.driver_data=t.data.details.data.driver_data;this.zone_list=t.data.details.data.zone_list;this.merchant_zone=t.data.details.data.merchant_zone;this.order_table_data=t.data.details.data.order_table_data;this.customer_name=this.order_info.customer_name;this.contact_number=this.order_info.contact_number;this.delivery_address=this.order_info.delivery_address;this.latitude=this.order_info.latitude;this.longitude=this.order_info.longitude;this.customer=t.data.details.data.customer;if(typeof T!=="undefined"&&T!==null){T.client_id=this.customer.client_id}this.order_status=t.data.details.data.order.status;this.services=t.data.details.data.order.services;this.payment_status=t.data.details.data.order.payment_status;this.delivery_status=t.data.details.data.order.delivery_status;this.items=t.data.details.data.items;this.order_summary=t.data.details.data.summary;this.summary_total=t.data.details.data.summary_total;this.summary_changes=t.data.details.data.summary_changes;this.summary_transaction=t.data.details.data.summary_transaction;this.merchant_direction="https://www.google.com/maps/dir/?api=1&destination=";this.merchant_direction+=this.merchant.latitude+",";this.merchant_direction+=this.merchant.longitude;this.delivery_direction=this.order_info.delivery_direction;this.buttons=t.data.details.data.buttons;this.sold_out_options=t.data.details.data.sold_out_options;this.link_pdf=t.data.details.data.link_pdf;this.payment_history=t.data.details.data.payment_history;this.credit_card_details=t.data.details.data.credit_card_details;this.kitchen_addon=t.data.details.data.kitchen_addon;this.found_in_kitchen=t.data.details.data.found_in_kitchen}else{this.merchant_direction="";this.delivery_direction="";this.merchant=[];this.order_info=[];this.items=[];this.order_summary=[];this.buttons=[];this.sold_out_options=[];this.link_pdf=[];this.payment_history=[];this.credit_card_details=[];this.driver_data=[];this.kitchen_addon=false;this.found_in_kitchen=false}})["catch"](t=>{}).then(t=>{this.is_loading=false;this.loading=false})},doUpdateOrderStatus(e,a,t){d("$do_actions=>"+t);console.log("summary_changes=>"+this.summary_changes.method);if(t=="reject_form"){this.$refs.rejection.confirm().then(t=>{if(t){d("rejection reason =>"+t);this.updateOrderStatus(e,a,t)}})}else{if(this.summary_changes.method=="total_decrease"){var i=this.update_order_label.content;i=i.replace("{{amount}}",this.summary_changes.refund_due_pretty);bootbox.confirm({size:"small",title:"",message:"<h5>"+this.update_order_label.title+"</h5>"+"<p>"+i+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.update_order_label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.createRefund(e,a)}}})}else if(this.summary_changes.method=="total_increase"){var i=this.update_order_label.content_collect;i=i.replace("{{amount}}",this.summary_changes.refund_due_pretty);if(this.summary_changes.unpaid_invoice){var s=bootbox.dialog({title:"",message:"<h5>"+this.update_order_label.title_increase+"</h5>"+"<p>"+this.update_order_label.content_payment+"</p>",size:"medium",centerVertical:true,buttons:{cancel:{label:this.update_order_label.close,className:"btn btn-black small pl-4 pr-4",callback:()=>{}}}})}else if(this.summary_changes.paid_invoice){this.updateOrderStatus(e,a)}else{var s=bootbox.dialog({title:"",message:"<h5>"+this.update_order_label.title_increase+"</h5>"+"<p>"+i+"</p>",size:"medium",centerVertical:true,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4",callback:()=>{}},account:{label:this.update_order_label.less_acccount,className:"btn btn-yellow small",callback:()=>{this.AcceptOrder(e,a,"lessOnAccount")}},ok:{label:this.update_order_label.send_invoice,className:"btn btn-green small pl-4 pr-4",callback:()=>{this.AcceptOrder(e,a,"createInvoice")}}}})}}else{this.updateOrderStatus(e,a)}}},updateOrderStatus(t,e,a){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/updateOrderStatus",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t,order_uuid:e,reason:a},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},createRefund(t,e){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/createRefund",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t,order_uuid:e},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},AcceptOrder(t,e,a){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/"+a,data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t,order_uuid:e},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getOrderStatusList(){axios({method:"POST",url:this.ajax_url+"/getOrderStatusList",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.status_data=t.data.details}else{this.status_data=false}})["catch"](t=>{}).then(t=>{})},manualStatusList(t){this.stats_id="";c(this.$refs.manual_status_modal).modal("show")},confirm(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/updateOrderStatusManual",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,stats_id:this.stats_id},timeout:s}).then(t=>{if(t.data.code==1){c(this.$refs.manual_status_modal).modal("hide");this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},cancelOrder(){this.$refs.rejection.confirm().then(t=>{if(t){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/cancelOrder",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,reason:t},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate");o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}})},delayOrder(){this.$emit("delayOrderform",this.order_uuid)},contactCustomer(){k.alert(this.order_info.contact_number,{size:"small"})},orderHistory(){this.$emit("order-history",this.order_uuid)},markItemOutStock(t){this.item_row=t;c(this.$refs.out_stock_modal).modal("show")},setOutOfStocks(){c(this.$refs.out_stock_modal).modal("hide");bootbox.confirm({size:"medium",title:"",message:"<h5>"+this.out_stock_label.title+"</h5>"+"<p>"+this.refund_label.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.refund_label.go_back,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.refund_label.complete,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.itemChanges("out_stock")}else{c(this.$refs.out_stock_modal).modal("show")}}})},itemChanges(e){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/itemChanges",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,item_row:this.item_row.item_row,item_changes:e,out_stock_options:this.out_stock_options},timeout:s}).then(t=>{if(t.data.code==1){switch(e){default:c(this.$refs.out_stock_modal).modal("hide");this.orderDetails(this.order_uuid,true);break}}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},adjustOrder(t){this.item_row=t;c(this.$refs.adjust_order_modal).modal("show")},refundItem(){c(this.$refs.adjust_order_modal).modal("hide");bootbox.confirm({size:"medium",title:"",message:"<h5>"+this.refund_label.title+"</h5>"+"<p>"+this.refund_label.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.refund_label.go_back,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.refund_label.complete,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.doItemRefund()}else{c(this.$refs.adjust_order_modal).modal("show")}}})},doItemRefund(){this.itemChanges("refund")},cancelEntireOrder(){c(this.$refs.adjust_order_modal).modal("hide");this.cancelOrder()},removeItem(){c(this.$refs.adjust_order_modal).modal("hide");bootbox.confirm({size:"medium",title:"",message:"<h5>"+this.remove_item.title+"</h5>"+"<p>"+this.remove_item.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.remove_item.go_back,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.remove_item.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.doRemoveItem()}else{c(this.$refs.adjust_order_modal).modal("show")}}})},doRemoveItem(){this.itemChanges("remove")},additionalCharge(t){this.item_row=t;c(this.$refs.additional_charge_modal).modal("show")},doAdditionalCharge(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/additionalCharge",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,item_row:this.item_row.item_row,additional_charge:this.additional_charge,additional_charge_name:this.additional_charge_name},timeout:s}).then(t=>{if(t.data.code==1){c(this.$refs.additional_charge_modal).modal("hide");this.orderDetails(this.order_uuid,true)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},updateOrderSummary(){axios({method:"put",url:this.ajax_url+"/updateOrderSummary",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid},timeout:s}).then(t=>{if(t.data.code==1){}else{}})["catch"](t=>{}).then(t=>{})},replaceItem(){c(this.$refs.adjust_order_modal).modal("hide");this.$emit("showMenu",this.item_row)},editOrderInformation(){c(this.$refs.update_info_modal).modal("show")},updateOrderDeliveryInformation(){this.is_loading=true;this.error=[];axios({method:"put",url:this.ajax_url+"/updateOrderDeliveryInformation",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,customer_name:this.customer_name,contact_number:this.contact_number,delivery_address:this.delivery_address,latitude:this.latitude,longitude:this.longitude},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);c(this.$refs.update_info_modal).modal("hide");this.$emit("refreshOrder",this.order_uuid)}else{this.error=t.data.details}})["catch"](t=>{}).then(t=>{this.is_loading=false})},showCustomer(){this.$emit("viewCustomer")},printOrder(){this.$emit("to-print",this.order_uuid)},refundFull(){var t={refund_type:"full",order_uuid:this.order_info.order_uuid,total:this.order_info.total,pretty_total:this.order_info.pretty_total};this.$refs.refund.confirm(t).then(t=>{d(t)})},refundPartial(){d("refundPartial")},updateOrder(){d(this.summary_changes.method);if(this.summary_changes.method=="total_decrease"){var t=this.update_order_label.content;t=t.replace("{{amount}}",this.summary_changes.refund_due_pretty);bootbox.confirm({size:"small",title:"",message:"<h5>"+this.update_order_label.title+"</h5>"+"<p>"+t+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.update_order_label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){d(t)}}})}},SwitchPrinter(t,e){if(e=="feieyun"){this.FPprint(t)}else{this.wifiPrint(t)}},FPprint(t){this.$refs.loading_box.show();axios({method:"put",url:this.ajax_url+"/FPprint",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,printer_id:t},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})},wifiPrint(t){this.$refs.loading_box.show();axios({method:"POST",url:this.ajax_url+"/wifiPrint",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&printerId="+t+"&order_uuid="+this.order_uuid}).then(t=>{if(t.data.code==1){this.ConnectToPrintServer();setTimeout(()=>{this.sendServerToPrinter(t.data.details.data,t.data.msg)},500)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})},ConnectToPrintServer(){let t=!u(printerServer)?printerServer:null;if(this.webSocket==null){console.log("CONNECT ConnectToPrintServer");this.webSocket=new WebSocket(t);this.webSocket.onopen=function(t){console.log("WebSocket is open now.")};this.webSocket.onmessage=function(t){console.log("Received message from server:",t.data)};this.webSocket.onclose=function(t){console.log("WebSocket is closed now.");this.webSocket=null};this.webSocket.onerror=t=>{this.webSocket=null;this.socket_error="WebSocket error occurred, but no detailed ErrorEvent is available.";if(t instanceof ErrorEvent){this.socket_error=t.message}}}},sendServerToPrinter(t,e){if(this.webSocket.readyState===WebSocket.OPEN){this.webSocket.send(JSON.stringify(t));ElementPlus.ElNotification({title:"",message:e,position:"bottom-right",type:"success"})}else{if(u(this.socket_error)){this.socket_error="WebSocket is not open. Ready state is:"+this.webSocket.readyState}ElementPlus.ElNotification({title:"",message:this.socket_error,position:"bottom-right",type:"warning"})}},SendToKitchen(){this.$refs.loading_box.show();axios({method:"POST",url:this.ajax_url+"/SendToKitchen",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,order_info:{customer_name:this.order_info.customer_name,order_id:this.order_info.order_id,merchant_id:this.merchant.merchant_id,merchant_uuid:this.merchant.merchant_uuid,order_type:this.order_info.order_type,transaction_type:this.order_info.transaction_type,delivery_date:this.order_info.delivery_date,whento_deliver:this.order_info.whento_deliver,delivery_time:this.order_info.delivery_time,timezone:this.order_info.timezone},order_table_data:this.order_table_data,items:this.items}}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});this.found_in_kitchen=true}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})}},template:"#xtemplate_order_details"};const B={props:["label","ajax_url","client_id","image_placeholder","page_limit","merchant_id"],data(){return{is_loading:false,customer:[],addresses:[],block_from_ordering:false,datatables:undefined,count_up:undefined}},mounted(){this.observer=lozad(".lozad",{loaded:function(t){t.classList.add("loaded");d("image loaded")}})},computed:{hasData(){if(Object.keys(this.customer).length>0){return true}return false}},updated(){this.observer.observe()},methods:{show(){this.getCustomerDetails();this.getCustomerOrders();this.getCustomerSummary();c(this.$refs.customer_modal).modal("show")},close(){c(this.$refs.customer_modal).modal("hide")},getCustomerDetails(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getCustomerDetails",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),client_id:this.client_id,merchant_id:this.merchant_id},timeout:s}).then(t=>{if(t.data.code==1){this.customer=t.data.details.customer;this.addresses=t.data.details.addresses;this.block_from_ordering=t.data.details.block_from_ordering}else{this.customer=[];this.addresses=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getCustomerOrders(){var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),client_id:this.client_id};this.datatables=c(this.$refs.order_table).DataTable({ajax:{url:this.ajax_url+"/getCustomerOrders",contentType:"application/json",type:"POST",data:t=>{t.YII_CSRF_TOKEN=c("meta[name=YII_CSRF_TOKEN]").attr("content");t.client_id=this.client_id;t.merchant_id=this.merchant_id;return JSON.stringify(t)}},language:{url:ajaxurl+"/DatableLocalize"},serverSide:true,processing:true,pageLength:parseInt(this.page_limit),destroy:true,lengthChange:false,order:[[0,"desc"]],columns:[{data:"order_id"},{data:"restaurant_name"},{data:"total"},{data:"status"},{data:"order_uuid"}]})},blockCustomerConfirmation(){if(!this.block_from_ordering){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.block_customer+"</h5>"+"<p>"+this.label.block_content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.blockOrUnlockCustomer(1)}}})}else{this.blockOrUnlockCustomer(0)}},blockOrUnlockCustomer(t){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/blockCustomer",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),client_id:this.client_id,merchant_id:this.merchant_id,block:t},timeout:s}).then(t=>{if(t.data.code==1){if(t.data.details==1){this.block_from_ordering=true}else this.block_from_ordering=false}else{this.block_from_ordering=false}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getCustomerSummary(){axios({method:"POST",url:this.ajax_url+"/getCustomerSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&client_id="+this.client_id+"&merchant_id="+this.merchant_id,timeout:s}).then(t=>{if(t.data.code==1){var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_orders,t.data.details.orders,e);a.start();var i=new countUp.CountUp(this.$refs.summary_cancel,t.data.details.order_cancel,e);i.start();var e=t.data.details.price_format;var s=this.count_up=new countUp.CountUp(this.$refs.summary_total,t.data.details.total,e);s.start();var l=this.count_up=new countUp.CountUp(this.$refs.summary_refund,t.data.details.total_refund,e);l.start()}else{}})["catch"](t=>{}).then(t=>{})}},template:"#xtemplate_customer"};const V={props:["ajax_url","amount"],data(){return{data:0,config:{prefix:"$",suffix:"",thousands:",",decimal:".",precision:2}}},mounted(){this.data=window["v-money3"].format(this.amount,this.config)},updated(){this.data=window["v-money3"].format(this.amount,this.config)},template:`	
    {{data}}
    `};const J={components:{"money-format":V},props:["label","ajax_url","order_uuid","mode","line"],template:"#xtemplate_print_order",data(){return{is_loading:false,merchant:[],order_info:[],order_status:[],services:[],payment_status:[],items:[],order_summary:[],print_settings:[],payment_list:[],order_table_data:[]}},computed:{hasData(){if(this.order_summary.length>0){return true}return false},hasBooking(){if(Object.keys(this.order_table_data).length>0){return true}return false}},methods:{show(){this.orderDetails();c(this.$refs.print_modal).modal("show")},close(){c(this.$refs.print_modal).modal("hide")},orderDetails(){this.order_summary=[];this.is_loading=true;var t=["print_settings"];axios({method:"POST",url:this.ajax_url+"/orderDetails",data:{order_uuid:this.order_uuid,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),payload:t},timeout:s}).then(t=>{this.response_code=t.data.code;if(t.data.code==1){this.merchant=t.data.details.data.merchant;this.order_info=t.data.details.data.order.order_info;this.payment_list=t.data.details.data.order.payment_list;this.order_table_data=t.data.details.data.order_table_data;this.order_status=t.data.details.data.order.status;this.services=t.data.details.data.order.services;this.payment_status=t.data.details.data.order.payment_status;this.items=t.data.details.data.items;this.order_summary=t.data.details.data.summary;this.print_settings=t.data.details.data.print_settings}else{o(t.data.msg,"error");this.merchant_direction="";this.delivery_direction="";this.merchant=[];this.order_info=[];this.items=[];this.order_summary=[];this.print_settings=[];this.print_settings=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false;this.loading=false})},print(){c(".printhis").printThis();this.$refs.print_button.disabled=true;setTimeout(()=>{this.$refs.print_button.disabled=false},1e3)}}};const W={props:["ajax_url","label","order_uuid"],data(){return{is_loading:false,data:[],order_status:[],error:[]}},methods:{show(){this.data=[];this.order_status=[];c(this.$refs.history_modal).modal("show");this.getHistory()},close(){c(this.$refs.history_modal).modal("hide")},getHistory(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/getOrderHistory",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.order_status=t.data.details.order_status;this.error=[]}else{this.error=t.data.msg;this.data=[];this.order_status=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
	<div ref="history_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body position-relative">
      
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	  </div>
      
	  <ul class="timeline m-0 p-0 pl-5">
        <li  v-for="item in data" >
          <div class="time">{{item.created_at}}</div>
           <p v-if="order_status[item.status]" class="m-0">{{order_status[item.status]}}</p>
	       <p v-else class="m-0">{{item.status}}</p>
	       <p class="m-0 text-muted">{{item.remarks}}</p>
	       <p v-if="item.change_by" class="m-0 text-muted">{{item.change_by}}</p>
        </li>        
      </ul>
	  
	  <div id="error_message" v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
        <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
      </div>  
      
      </div>      
      <div class="modal-footer">            
        <button type="button" class="btn btn-green pl-4 pr-4"  data-dismiss="modal">
          <span>{{label.close}}</span>          
        </button>
      </div>
      
    </div>
  </div>
</div>        
	`};const k=Vue.createApp({components:{"component-bootbox":t},data(){return{resolvePromise:undefined,rejectPromise:undefined}},methods:{confirm(){return new Promise((t,e)=>{this.resolvePromise=t;this.rejectPromise=e;this.$refs.bootbox.confirm()})},Callback(t){this.resolvePromise(t)},alert(t,e){this.$refs.bootbox.alert(t,e)}}}).mount("#vue-bootbox");const q={props:["label","max_file","select_type","field","field_path","selected_file","selected_multiple_file","max_file_size","inline","upload_path","save_path"],components:{"component-bootbox":t},data(){return{data:[],q:"",page_count:0,current_page:0,preview:false,dropzone:undefined,tab:1,is_loading:false,page:1,item_selected:[],added_files:[],awaitingSearch:false,data_message:""}},mounted(){this.getMedia();this.getMediaSeleted();this.getMediaMultipleSeleted();this.initDropzone()},updated(){d("inline=>"+this.inline)},watch:{q(t,e){if(!this.awaitingSearch){if(u(t)){this.getMedia();return false}setTimeout(()=>{var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),page:this.page,q:this.q};var e=y();t=JSON.stringify(t);n[e]=c.ajax({url:upload_ajaxurl+"/getMedia",method:"PUT",dataType:"json",data:t,contentType:a.json,timeout:s,crossDomain:true,beforeSend:t=>{if(n[e]!=null){n[e].abort()}}}).done(t=>{this.data_message=t.msg;if(t.code==1){this.data=t.details.data;this.page_count=t.details.page_count;this.current_page=t.details.current_page}else{this.data=[];this.page_count=0;this.current_page=0}}).always(t=>{this.awaitingSearch=false})},1e3);this.data=[];this.awaitingSearch=true}}},computed:{hasData(){if(this.data.length>0){return true}return false},hasSelected(){if(this.item_selected.length>0){return true}return false},totalSelected(){return this.item_selected.length},hasAddedFiles(){if(this.added_files.length>0){return true}return false},noFiles(){if(this.data.length>0){return false}if(this.awaitingSearch){return false}return true},hasSearch(){if(!u(this.q)){return true}return false}},methods:{show(){c(this.$refs.modal_uplader).modal("show")},close(){c(this.$refs.modal_uplader).modal("hide")},previewTemplate(){var t=`
  	  	  <div class="col-lg-3 col-md-12 mb-4 mb-lg-3">
  	  	     <div class="card">
	  	  	     <div class="image"><img data-dz-thumbnail /></div>
	  	  	     
	  	  	     <div class="p-2 pt-0">
	  	  	     <p class="m-0 name" data-dz-name></p>
	  	  	     <p class="m-0 size" data-dz-size></p>  	
	  	  	     
	  	  	     <div class="progress">
					  <div class="progress-bar" role="progressbar" aria-valuenow="0" 
					  style="width:0%;" data-dz-uploadprogress
					  aria-valuemin="0" aria-valuemax="100"></div>
				 </div>  	     
				 </div>
  	  	     </div> 
  	  	  </div> <!-- col -->
  	  	 `;return t},initDropzone(){var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),upload_path:this.upload_path};this.dropzone=new Dropzone(this.$refs.dropzone,{paramName:"file",maxFilesize:parseInt(this.max_file_size),url:upload_ajaxurl+"/file",maxFiles:this.max_file,params:t,clickable:this.$refs.fileinput,previewsContainer:this.$refs.ref_preview,previewTemplate:this.previewTemplate(),acceptedFiles:"image/*"});this.dropzone.on("addedfile",t=>{this.preview=true;d("added file=>"+t.type);switch(t.type){case"image/jpeg":case"image/png":case"image/svg+xml":case"image/webp":case"image/apng":break;default:this.dropzone.removeFile(t);break}});this.dropzone.on("queuecomplete",t=>{d("All files have uploaded ");this.getMedia()});this.dropzone.on("success",(t,e)=>{d("success");e=JSON.parse(e);d(e);if(e.code==2){o(e.msg);this.dropzone.removeFile(t)}})},getMedia(){var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),page:this.page,selected_file:this.selected_multiple_file,item_selected:this.item_selected};var e=y();t=JSON.stringify(t);n[e]=c.ajax({url:upload_ajaxurl+"/getMedia",method:"POST",dataType:"json",data:t,contentType:a.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(n[e]!=null){n[e].abort()}}}).done(t=>{this.data_message=t.msg;if(t.code==1){this.data=t.details.data;this.page_count=t.details.page_count;this.current_page=t.details.current_page}else{this.data=[];this.page_count=0;this.current_page=0}}).always(t=>{this.is_loading=false})},addMore(){this.preview=false},pageNum(t){this.page=t;this.getMedia()},pageNext(){this.page=parseInt(this.page)+1;if(this.page>=this.page_count){this.page=this.page_count}this.getMedia()},pagePrev(){this.page=parseInt(this.page)-1;d(this.page+"=>"+this.page_count);if(this.page<=1){this.page=1}this.getMedia()},itemSelect(a,t){a.is_selected=!a.is_selected;if(this.select_type=="single"){this.removeAllSelected(a.id);if(a.is_selected){this.item_selected[0]={filename:a.filename,image_url:a.image_url,path:a.path}}else{this.item_selected.splice(0,1)}}else{if(a.is_selected){this.item_selected.push({filename:a.filename,image_url:a.image_url,path:a.path})}else{this.item_selected.forEach((t,e)=>{if(t.filename==a.filename){this.item_selected.splice(e,1)}})}}},removeAllSelected(a){this.data.forEach((t,e)=>{if(t.id!=a){t.is_selected=false}})},addFiles(){var a=[];this.item_selected.forEach((t,e)=>{a[e]={filename:t.filename,image_url:t.image_url,path:t.path}});this.added_files=a;this.close()},removeAddedFiles(t){this.added_files.splice(t,1)},getMediaSeleted(){if(u(this.selected_file)){return}var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),selected_file:this.selected_file,save_path:this.save_path};var e=y();t=JSON.stringify(t);n[e]=c.ajax({url:upload_ajaxurl+"/getMediaSeleted",method:"POST",dataType:"json",data:t,contentType:a.json,timeout:s,crossDomain:true,beforeSend:t=>{if(n[e]!=null){n[e].abort()}}}).done(t=>{if(t.code==1){this.added_files=t.details}else{}})},getMediaMultipleSeleted(){if(u(this.selected_multiple_file)){return}var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),selected_file:this.selected_multiple_file,save_path:this.save_path};var e=y();t=JSON.stringify(t);n[e]=c.ajax({url:upload_ajaxurl+"/getMediaMultipleSeleted",method:"PUT",dataType:"json",data:t,contentType:a.json,timeout:s,crossDomain:true,beforeSend:t=>{if(n[e]!=null){n[e].abort()}}}).done(t=>{if(t.code==1){this.added_files=t.details;var a=[];this.added_files.forEach((t,e)=>{a[e]={filename:t.filename,image_url:t.image_url}});this.item_selected=a}else{this.added_files=[];this.item_selected=[]}})},clearData(){this.q="";this.getMedia()},beforeDeleteFiles(){k.confirm().then(t=>{if(t){this.deleteFiles()}})},deleteFiles(){var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),item_selected:this.item_selected};var e=y();t=JSON.stringify(t);n[e]=c.ajax({url:upload_ajaxurl+"/deleteFiles",method:"POST",dataType:"json",data:t,contentType:a.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(n[e]!=null){n[e].abort()}}}).done(t=>{if(t.code==1){this.item_selected=[];this.getMedia()}else{k.alert(t.msg,{})}}).always(t=>{this.is_loading=false})},clearSelected(t){if(this.item_selected.length>0){this.item_selected=[];t.forEach((t,e)=>{t.is_selected=false})}else{if(this.select_type=="multiple"){var a=[];t.forEach((t,e)=>{t.is_selected=true;a[e]={filename:t.filename,image_url:t.image_url}});this.item_selected=a}}}},template:`   
   
   <!--
   <div v-if="inline=='false'" class="input-group">  
	  <div class="custom-file">         
	     <label @click="show" class="custom-file-label image_label" for="upload_image">
	     {{label.upload_button}} 
	     </label>  
	  </div>      
   </div>
   -->

   <div v-if="inline=='false'" class="mb-2">
   <div class="border bg-white  rounded">	 
	  <div class="row justify-content-between align-items-center">
		<div class="col-4 ml-3">{{label.upload_button}}</div>
		<div class="col-4 text-right">
		   <button @click="show"  type="button" class="btn btn-info" style="padding:.375rem .75rem;">
			 <template v-if="label.browse">{{label.browse}}</template>
			 <template v-else>Browse</template>
		   </button>
		</div>
	  </div>
   </div>
  </div>
         
   <template v-for="item in added_files" >
      <template v-if="select_type=='single'">
        <input :name="field" type="hidden" :value="item.filename" />
        <input :name="field_path" type="hidden" :value="item.path" />
      </template>
      <template v-else >
        <input :name="field+'[]'" type="hidden" :value="item.filename" />
        <input :name="field_path" type="hidden" :value="item.path" />
      </template>
   </template>
            
   <div v-if="hasAddedFiles" class="file_added_container pr-2">   
	   <div class="row pt-3">	   
	   <div v-for="(item, index) in added_files" class="col-md-2 mb-3 position-relative">
	     <a @click="removeAddedFiles(index)" class="btn-remove btn btn-black btn-circle" href="javascript:;" >
		  <i class="zmdi zmdi-close"></i>
		 </a>  	  		 
          <img class="rounded" :src="item.image_url" />
	   </div>
	   </div>
   </div>
   <!--  file_added_container  -->
          
    <div ref="modal_uplader" :class="{'modal fade':this.inline=='false'}" id="modalUploader" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="modalUploader" aria-hidden="true">
    
	   <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
	     
	       <div class="modal-header pb-1 bg-light">	        
	        <ul class="nav nav-pills">
	          <li class="nav-item">
			    <a @click="tab=1" href="javascript:;" class="nav-link" :class="{ 'active': tab==1 }" >
			    {{label.select_file}}
			    </a>
			  </li>
			  <li class="nav-item">
			    <a @click="tab=2" href="javascript:;" class="nav-link" :class="{ 'active': tab==2 }"  >
			    {{label.upload_new}}
			    </a>
			  </li>
	        </ul>
	       
	        <button v-if="inline=='false'" type="button" class="close"  aria-label="Close" @click="close" >
	          <span aria-hidden="true" style="font-size:1.5rem;">&times;</span>
	        </button>
	      </div> 
	     
	       <div class="modal-body">	 

	       <!-- file list wrapper  -->
	       <template v-if="tab=='1'" >
	       
	       <div class="row">
			  <div class="col">
				 <button type="button" class="send btn-upload-count" 
				 @click="clearSelected(data)"
				 :class="{selected : item_selected.length>0}"
				 :data-counter="totalSelected">&#10004;</button>
			  </div>
			  <div class="col">
				<div class="form-group has-search">
				  <span v-if="!awaitingSearch" class="fa fa-search form-control-feedback"></span>
				  <span v-if="awaitingSearch" class="img-15 form-control-feedback" data-loader="circle"></span>
				  <div  v-if="hasSearch"  @click="clearData" class="img-15 clear_data">
				    <i class="zmdi zmdi-close"></i>
				  </div>
				  <input v-model="q" type="text" class="form-control" :placeholder="label.search" >
				</div>
			  </div>
			</div>
						
			
			<DIV class="file_wrapper">	
			
             <div v-if="is_loading" class="cover-loader d-flex align-items-center justify-content-center">
	            <div>
	              <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	            </div>
	         </div>
	         	         
	         <div v-if="noFiles" class="d-flex justify-content-center align-items-center file_upload_inner">
	           <div class="text-center">
	             <h5>{{data_message}}</h5>	             
	           </div>
	         </div> 	         
             	 	
			 <ul class="list-unstyled">			  
			  <li v-for="item in data"
			   :class="{ selected: item.is_selected }"	     
			   @click="itemSelect(item,index)"
			   >
			    <img :src="item.image_url" />
			    <p class="m-0"><strong>{{item.title}}</strong></p>
			    <p class="m-0"><small>{{item.size}}</small></p>
			  </li>			  
			</ul>
			
			</DIV>
			<!-- file_wrapper -->
	       
	       </template>	       
	       <!-- end file list wrapper  -->
	       
	       <!-- file_preview_container -->
	       
	       <div :class="{'d-block': tab=='2' }" class="file_upload_container rounded position-relative">
	         <div ref="dropzone" class="d-flex justify-content-center align-items-center file_upload_inner">
	           <div class="text-center">
	             <h5>{{label.drop_files}}<br/>{{label.or}}</h5>
	             <a ref="fileinput" class="btn btn-green fileinput-button" href="javascript:;">{{label.select_files}}</a>
	           </div>
	         </div> 	         
	         
	         <!-- file_preview_container -->
	         <div :class="{ 'd-block': preview }" class="file_preview_container">	 
		          <nav class="navbar bg-light d-flex justify-content-end">
		             <button @click="addMore" type="button" class="btn">+ 
					 
					 <template v-if="label.add_more">
					    {{label.add_more}}
					 </template>
					 <template v-else>
					    Add more
					 </template>
					 
					 </button>
		          </nav>
		          
		          <div ref="ref_preview" class="row p-2">		          		            
		          </div> <!-- row -->
		          
	         </div>
	         <!-- file_preview_container -->
	         
	       </div>
	       
	       <!-- end file_upload_container -->
	       
	       </div> <!--modal body-->
           
	      <div class="modal-footer justify-content-start">
	        <div class="row no-gutters w-100">
	          <div class="col">
	          	          
	           <!-- current page {{current_page}} page {{page}}  page_count {{page_count}} -->
	           <nav aria-label="Page navigation" v-if="hasData" >
				  <ul class="pagination">
				  
				    <li class="page-item" :class="{disabled: current_page=='1'}" >
				      <a @click="pagePrev()" class="page-link" href="javascript:;">{{label.previous}}</a>
				    </li>				    
				    
				    <!--
				    <li v-for="n in page_count" class="page-item" :class="{ active: current_page==n }" >
				      <a @click="pageNum(n)" class="page-link" href="javascript:;">{{n}}</a>
				    </li>
				    -->
				    
				    <li class="page-item" :class="{disabled: page_count==current_page}">
				       <a @click="pageNext()" class="page-link" href="javascript:;">{{label.next}}</a>
				    </li>
				  </ul>
				</nav>
	          
	          </div> <!-- col -->
	          <div class="col text-right">
	           
	           <template v-if="inline=='false'" >
		           <button @click="addFiles" type="button" class="btn btn-green" :disabled="!hasSelected" >  
	                <span class="label">{{label.add_file}}</span>                 
	               </button>
               </template>
               <template v-else>
                  <button @click="beforeDeleteFiles" type="button" class="btn btn-green" :disabled="!hasSelected" >  
	                <span class="label">{{label.delete_file}}</span>                 
	               </button>
               </template>
	          
	          </div>
	        </div> <!-- row -->
          </div> <!--footer-->
	       
	    </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->     	       
   `};const H=Vue.createApp({components:{"component-uploader":q},data(){return{data:[]}},mounted(){},methods:{}}).mount("#vue-uploader");const G={name:"Select2",data(){return{select2:null}},emits:["update:modelValue"],props:{modelValue:[String,Array],id:{type:String,"default":""},name:{type:String,"default":""},placeholder:{type:String,"default":""},options:{type:Array,"default":()=>[]},disabled:{type:Boolean,"default":false},required:{type:Boolean,"default":false},settings:{type:Object,"default":()=>{}}},watch:{options:{handler(t){this.setOption(t)},deep:true},modelValue:{handler(t){this.setValue(t)},deep:true}},methods:{setOption(t=[]){this.select2.empty();this.select2.select2({placeholder:this.placeholder,...this.settings,data:t});this.setValue(this.modelValue)},setValue(t){if(t instanceof Array){this.select2.val([...t])}else{this.select2.val([t])}this.select2.trigger("change")}},mounted(){this.select2=c(this.$el).find("select").select2({theme:"classic",placeholder:this.placeholder,...this.settings,data:this.options}).on("select2:select select2:unselect",t=>{this.$emit("update:modelValue",this.select2.val());this.$emit("select",t["params"]["data"])});this.setValue(this.modelValue)},beforeUnmount(){this.select2.select2("destroy")},template:` 
  <div>
    <select class="form-control" multiple="multiple" :id="id" :name="name" :disabled="disabled" :required="required"></select>
  </div>
  `};const Z={props:["label","ajax_url","group_name","status_list"],components:{Select2:G},data(){return{error:[],status:[],is_loading:""}},computed:{DataValid(){if(this.is_loading){return false}return true}},mounted(){this.getSettings()},methods:{getSettings(){axios({method:"put",url:this.ajax_url+"/getOrderTab",data:{group_name:this.group_name,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{this.response_code=t.data.code;if(t.data.code==1){this.status=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{})},show(){c(this.$refs.modal).modal("show")},close(){c(this.$refs.modal).modal("hide")},submit(){this.is_loading=true;this.success="";this.error=[];axios({method:"put",url:this.ajax_url+"/saveOrderTab",data:{group_name:this.group_name,status:this.status,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{this.response_code=t.data.code;if(t.data.code==1){k.alert(t.data.msg,{size:"small"});this.close()}else{this.error=t.data.msg;this["this"].success=""}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`  
	 <h5 class="card-title">{{label.title}}</h5>     
	 <div class="d-flex justify-content-between mb-2">
	  <div><p class="card-text">{{label.text}}</p></div>
	 </div>  
	 	
	 <form @submit.prevent="submit" >
	  
	  <div class="form-label-group">    
	  <Select2 v-model="status" :options="status_list" :settings="{ settingOption: value, settingOption: value }" >
	  </Select2>
	 </div>
	 
	   <div  v-cloak v-if="error.length>0" class="alert alert-warning" role="alert">
	    <p v-cloak v-for="err in error">{{err}}</p>	    
	   </div>
	      
	  <button type="button" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }" 
	:disabled="!DataValid" 
	 >
	  <span>{{label.save}}</span>
	  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
	</button> 
	  
	 </form> 
	`};const Q={props:["ajax_url","settings"],template:"#xtemplate_payout_filter",mounted(){this.initeSelect2();if(this.settings.load_filter){this.getFilterData()}},data(){return{status_list:[],order_type_list:[],merchant_id:"",client_id:"",order_status:"",order_type:""}},methods:{initeSelect2(){c(".select2-single").select2({width:"resolve"});c(".select2-merchant").select2({width:"resolve",language:{searching:()=>{return this.settings.searching},noResults:()=>{return this.settings.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchMerchant",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}});if(!u(this.$refs.client_id)){c(".select2-customer").select2({width:"resolve",language:{searching:()=>{return this.settings.searching},noResults:()=>{return this.settings.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchCustomer",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}})}if(!u(this.$refs.driver_id)){c(".select2-driver").select2({width:"resolve",language:{searching:()=>{return this.settings.searching},noResults:()=>{return this.settings.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchDriver",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}})}},getFilterData(){axios({method:"put",url:this.ajax_url+"/getFilterData",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.status_list=t.data.details.status_list;this.order_type_list=t.data.details.order_type_list}else{this.status_list=[];this.order_type_list=[]}})["catch"](t=>{}).then(t=>{})},clearFilter(){c(this.$refs.merchant_id).val(null).trigger("change");c(this.$refs.client_id).val(null).trigger("change");c(this.$refs.order_status).val(null).trigger("change");c(this.$refs.order_type).val(null).trigger("change");c(this.$refs.driver_id).val(null).trigger("change");c(this.$refs.order_id).val("")},submitFilter(){this.merchant_id=c(this.$refs.merchant_id).find(":selected").val();this.client_id=c(this.$refs.client_id).find(":selected").val();this.order_status=c(this.$refs.order_status).find(":selected").val();this.order_type=c(this.$refs.order_type).find(":selected").val();this.driver_id=c(this.$refs.driver_id).find(":selected").val();this.order_id=c(this.$refs.order_id).val();this.$emit("afterFilter",{merchant_id:this.merchant_id,client_id:this.client_id,order_status:this.order_status,order_type:this.order_type,driver_id:this.driver_id,order_id:this.order_id})},closePanel(){this.$emit("closePanel")}}};const X={props:["label","ajax_url","group_name","status_list","do_action_list","order_type_list"],data(){return{is_loading:"",error:[],status:"",button_name:"",data:[],uuid:"",do_actions:"",class_name:"",order_type:""}},mounted(){this.getOrderButtonList()},computed:{DataValid(){if(this.is_loading){return false}return true}},methods:{show(){this.button_name="";this.uuid="";this.status="";this.do_actions="";this.class_name="";c(this.$refs.modal).modal("show")},close(){c(this.$refs.modal).modal("hide")},getOrderButtonList(){this.is_loading=true;this.error=[];axios({method:"put",url:this.ajax_url+"/getOrderButtonList",data:{group_name:this.group_name,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},submit(){this.is_loading=true;this.error=[];axios({method:"put",url:this.ajax_url+"/saveOrderButtons",data:{group_name:this.group_name,button_name:this.button_name,status:this.status,order_type:this.order_type,uuid:this.uuid,class_name:this.class_name,do_actions:this.do_actions,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.close();this.getOrderButtonList()}else{this.error=t.data.msg}})["catch"](t=>{}).then(t=>{this.is_loading=false})},deleteButtons(e){k.confirm().then(t=>{if(t){axios({method:"put",url:this.ajax_url+"/deleteButtons",data:{uuid:e,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.getOrderButtonList()}else{k.alert(t.data.msg,{size:"small"})}})["catch"](t=>{}).then(t=>{})}})},getButtons(t){this.show();this.is_loading=true;this.uuid="";axios({method:"put",url:this.ajax_url+"/getButtons",data:{uuid:t,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.status=t.data.details.stats_id;this.order_type=t.data.details.order_type;this.button_name=t.data.details.button_name;this.uuid=t.data.details.uuid;this.do_actions=t.data.details.do_actions;this.class_name=t.data.details.class_name}else{k.alert(t.data.msg,{size:"small"});this.status="";this.button_name="";this.uuid="";this.do_actions="";this.class_name=""}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<h5 class="card-title">{{label.title}}</h5>
	
	<div class="d-flex justify-content-between mb-2">
      <div><p class="card-text">{{label.text}}</p></div>
      <div><button class="btn btn-green" @click="show"><i class="zmdi zmdi-plus mr-1"></i>{{label.add}}</button></div>
    </div>         
    
     <table class="table table-bordered">
       <thead>
       <tr>
        <td width="33%"><b>{{label.button_name}}</b></td>
        <td><b>{{label.button_status}}</b></td>
        <td  class="text-center"><b>{{label.actions}}</b></td>
       </tr>
       </thead>
       <tr v-for="item in data">
        <td>{{item.button_name}}</td>
        <td>
          <p class="m-0">{{item.status}}</p>
          <p v-if="item.order_type" class="m-0">(<b>{{item.order_type}})</b></p>
        </td>
        <td class="text-center">
        <div class="btn-group btn-group-actions" role="group">
          <a class="btn btn-light" @click="getButtons(item.uuid)">
           <i class="zmdi zmdi-border-color"/>
          </a>
          <a class="btn btn-light" @click="deleteButtons(item.uuid)">
           <i class="zmdi zmdi-delete"/>
          </a>
        </div>
        </td>
       </tr>
    </table>
    
    <div ref="modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      
      <form @submit.prevent="submit" >
       
        <div class="form-group">  
          <label for="button_name">{{label.button_name}}</label> 
          <input v-model="button_name" type="text" id="button_name" class="form-control form-control-text">
        </div>
        
        <div class="form-group">  
          <label for="class_name">{{label.class_name}}</label> 
          <input v-model="class_name" type="text" id="class_name" class="form-control form-control-text">
        </div>
        
        <div class="form-group">  
         <label for="status">{{label.button_status}}</label> 
         <select id="status" v-model="status" class="form-control custom-select form-control-select">
           <option v-for="(item, key) in status_list" :value="item.id">{{item.text}}</option>
         </select>
        </div>
        
        <div v-if="order_type_list" class="form-group">  
         <label>{{label.order_type}}</label> 
         <select id="order_type" v-model="order_type" class="form-control custom-select form-control-select">
           <option v-for="(item, key) in order_type_list" :value="key">{{item}}</option>
         </select>
        </div>
        
        <div class="form-group">  
         <label for="status">{{label.actions}}</label> 
         <select id="status" v-model="do_actions" class="form-control custom-select form-control-select">
           <option v-for="(item, key) in do_action_list" :value="key">{{item}}</option>
         </select>
        </div>
        
         <div  v-cloak v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
		    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
		 </div>   
        
      </form>       
       
      
      </div>      
      <div class="modal-footer">            
        <button type="button" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }" 
        :disabled="!DataValid" 
         >
          <span>{{label.save}}</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
    </div>
  </div>
</div>      
	`};const T=Vue.createApp({components:{"components-order-tabs":Z,"components-order-buttons":X},mounted(){},methods:{}}).mount("#vue-order-settings-tabs");const O={components:{"components-order-filter":Q},props:["settings","actions","ajax_url","table_col","columns","page_limit","transaction_type_list","filter","date_filter","merchant_uuid","ref_id"],mounted(){if(this.settings.auto_load){this.getTableData()}this.initDateRange();this.selectPicker();this.initSiderbar()},data(){return{datatables:undefined,date_range:"",date_start:"",date_end:"",transaction_type:[],sidebarjs:undefined,filter_by:[]}},methods:{initDateRange(){var t=JSON.parse(translation_vendor);var e={};e[t.today]=[moment(),moment()];e[t.Yesterday]=[moment().subtract(1,"days"),moment().subtract(1,"days")];e[t.last_7_days]=[moment().subtract(6,"days"),moment()];e[t.last_30_days]=[moment().subtract(29,"days"),moment()];e[t.this_month]=[moment().startOf("month"),moment().endOf("month")];e[t.last_month]=[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")];c(this.$refs.date_range).daterangepicker({autoUpdateInput:false,showWeekNumbers:true,alwaysShowCalendars:true,autoApply:true,locale:{format:"YYYY-MM-DD",daysOfWeek:[t.su,t.mo,t.tu,t.we,t.th,t.fr,t.sa],monthNames:[t.january,t.february,t.march,t.may,t.may,t.july,t.july,t.august,t.september,t.october,t.november,t.december],customRangeLabel:t.custom_range},ranges:e},(t,e,a)=>{this.date_range=t.format("YYYY-MM-DD")+" "+this.settings.separator+" "+e.format("YYYY-MM-DD");this.date_start=t.format("YYYY-MM-DD");this.date_end=e.format("YYYY-MM-DD");this.$emit("afterSelectdate",this.date_start,this.date_end);this.getTableData()})},selectPicker(){c(this.$refs.transaction_type).on("changed.bs.select",(t,e,a,i)=>{this.transaction_type=c(this.$refs.transaction_type).selectpicker("val");this.getTableData()})},initSiderbar(){if(this.filter==1){this.sidebarjs=new SidebarJS.SidebarElement({position:"right"})}},getTableData(){var a;var i=this;this.datatables=c(this.$refs.vue_table).DataTable({ajax:{url:this.ajax_url+"/"+this.actions,contentType:"application/json",type:"PUT",data:t=>{t.YII_CSRF_TOKEN=c("meta[name=YII_CSRF_TOKEN]").attr("content");t.date_start=this.date_start;t.date_end=this.date_end;t.transaction_type=this.transaction_type;t.filter=this.filter_by;t.merchant_uuid=this.merchant_uuid;t.ref_id=this.ref_id;return JSON.stringify(t)}},language:{url:ajaxurl+"/DatableLocalize"},serverSide:true,processing:true,pageLength:parseInt(this.page_limit),destroy:true,bFilter:this.settings.filter,ordering:this.settings.ordering,order:[[this.settings.order_col,this.settings.sortby]],columns:this.columns,dom:"<'row'<'col-sm-6'l><'col-sm-6'f>>"+"<'row'<'col-sm-12 text-right'B>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",buttons:["excelHtml5","csvHtml5","pdfHtml5"]});a=this.datatables;c(".vue_table tbody").on("click",".ref_view_transaction",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){i.viewTransaction(t.merchant_uuid)}});c(".vue_table tbody").on("click",".ref_payout",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){i.viewTransaction(t.transaction_uuid)}});c(".vue_table tbody").on("click",".ref_view_order",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){window.location.href=t.view_order}});c(".vue_table tbody").on("click",".ref_pdf_order",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){window.open(t.view_pdf,"_blank")}});c(".vue_table tbody").on("click",".ref_edit",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){window.location.href=t.update_url}});c(".vue_table tbody").on("click",".ref_view_url",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){window.location.href=t.view_url}});c(".vue_table tbody").on("click",".ref_view",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){i.$emit("view",t)}});c(".vue_table tbody").on("click",".set_status",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){var e=c(this).prop("checked");e=e==true?"active":"inactive";axios({method:"post",url:i.ajax_url+"/"+t.actions,data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&id="+t.id+"&status="+e,timeout:s}).then(t=>{if(t.data.code==1){}else{}})["catch"](t=>{}).then(t=>{})}});c(".vue_table tbody").on("click",".ref_delete",function(){var e=a.row(c(this).parents("tr")).data();if(!u(e)){bootbox.confirm({size:"small",title:"",message:"<h5>"+i.settings.delete_confirmation+"</h5>"+"<p>"+i.settings.delete_warning+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:i.settings.cancel,className:"btn"},confirm:{label:i.settings["delete"],className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){window.location.href=e.delete_url}}})}});c(".vue_table tbody").on("click",".ref_edit_popup",function(){var t=a.row(c(this).parents("tr")).data();if(!u(t)){if(t.rate_id){i.$emit("editRecords",t.rate_id)}else{i.$emit("editRecords",t.id)}}})},openFilter(){this.sidebarjs.toggle()},afterFilter(t){this.sidebarjs.toggle();this.filter_by=t;this.getTableData()},closePanel(){this.sidebarjs.toggle()},viewTransaction(t){this.$emit("viewTransaction",t)}},template:`			
	
	 <div class="row mb-3">
	  <div class="col">
	      
	      <div class="d-flex">
	      
		  <div v-if="date_filter" class="input-group fixed-width-field mr-2">
		    <input ref="date_range" v-model="date_range" class="form-control py-2 border-right-0 border" type="search"         
	        :placeholder="settings.placeholder" :data-separator="settings.separator"
	        >
		    <span class="input-group-append">
		        <div class="input-group-text bg-transparent"><i class="zmdi zmdi-calendar-alt"></i></div>
		    </span>
		  </div>
		  
		  
		  <select v-if="transaction_type_list" ref="transaction_type" data-style="selectpick" class="selectpicker" multiple="multiple" :title="settings.all_transaction" >		    
		    <option v-for="(item, key) in transaction_type_list" :value="key">{{item}}</option>		    
		  </select>
		  
		  <button v-if="filter==1" class="ml-2 btn btn-yellow normal" @click="openFilter" >		   		   
		   <div class="d-flex">
		     <div class="mr-2"><i class="zmdi zmdi-filter-list"></i></div>
		     <div>{{settings.filters}}</div>
		   </div>
		  </button>
		  
		  </div> <!-- flex -->
		  
	  </div>	  
	  <div class="col"></div>
	</div> <!--row-->
			
	<div class="table-responsive-md">
	<table ref="vue_table" class="table vue_table">
	<thead>
	<tr>
	 <th v-for="(col, key) in table_col" :width="col.width">{{col.label}}</th>
	</tr>
	</thead>
	<tbody>
	</tbody>
	</table>
	</div>
	
	<components-order-filter
	ref="filter"
	:ajax_url="ajax_url"
	:settings="settings"
	@after-filter="afterFilter"
	@close-panel="closePanel"
	>
	</components-order-filter>
    `};const tt={props:["ajax_url","action_name","ref_id"],data(){return{is_loading:false}},mounted(){this.getBalance()},methods:{getBalance(){this.is_loading=true;axios({method:"post",url:this.ajax_url+"/"+this.action_name,data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&ref_id="+this.ref_id,timeout:s}).then(t=>{var e;if(t.data.code==1){var a={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){a.suffix=t.data.details.price_format.symbol}else a.prefix=t.data.details.price_format.symbol;e=new countUp.CountUp(this.$refs.balance,t.data.details.balance,a);e.start();this.$emit("afterBalance",t.data.details.balance)}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<span ref="balance"></span>
	`};const et={props:["ajax_url"],data(){return{is_loading:false}},mounted(){this.getBalance()},methods:{getBalance(){this.is_loading=true;axios({method:"post",url:this.ajax_url+"/merchantTotalBalance",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{var e;if(t.data.code==1){var a={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){a.suffix=t.data.details.price_format.symbol}else a.prefix=t.data.details.price_format.symbol;e=new countUp.CountUp(this.$refs.balance,t.data.details.balance,a);e.start();this.$emit("afterBalance",t.data.details.balance)}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<span ref="balance"></span>
	`};const C={props:["ajax_url","label","transaction_type_list","action_name","ref_id"],data(){return{is_loading:false,transaction_description:"",transaction_type:"credit",transaction_amount:0}},computed:{hasData(){if(!u(this.transaction_description)&&!u(this.transaction_type)&&!u(this.transaction_amount)){return true}return false}},mounted(){},methods:{show(){c(this.$refs.modal_adjustment).modal("show")},close(){c(this.$refs.modal_adjustment).modal("hide")},clear(){this.transaction_description="";this.transaction_type="credit";this.transaction_amount=0},submit(){var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_provider:this.payment_provider,transaction_description:this.transaction_description,transaction_type:this.transaction_type,transaction_amount:this.transaction_amount,ref_id:this.ref_id};this.error=[];this.is_loading=true;axios({method:"put",url:this.ajax_url+"/"+this.action_name,data:t,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.clear();this.close();this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<div ref="modal_adjustment" class="modal"
    id="modal_adjustment" data-backdrop="static" 
    tabindex="-1" role="dialog" aria-labelledby="modal_adjustment" aria-hidden="true">
    
	   <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
	     
	     <form @submit.prevent="submit" >
	     
	      <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  
		  <div class="modal-body">
		  
		   <div class="form-label-group">    
		       <input type="text" v-model="transaction_description" id="transaction_description"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="transaction_description">{{label.transaction_description}}</labe>
		   </div>

		   <select ref="transaction_type" v-model="transaction_type"
		     class="form-control custom-select form-control-select mb-3"  >		    
		     <option v-for="(name, key) in transaction_type_list" :value="key">{{name}}</option>		    
		   </select>	   
		   
		   <div class="form-label-group">    
		       <input type="number" v-model="transaction_amount" id="transaction_amount"		       
		       placeholder=""
			   step="0.01"
		       class="form-control form-control-text" >
		       <label for="transaction_amount">{{label.transaction_amount}}</labe>
		     </div>
		  
		   </div> <!-- body -->
		  
		   <div class="modal-footer">     

		   <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.close}}</span>
           </button>
		       
	        <button type="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"
	        :disabled="!hasData"
	        >
	          <span>{{label.submit}}</span>
	          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
	        </button>
	      </div>
		 
	      </form>
		  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->     	   
	`};const at={props:["ajax_url","image_placeholder","label"],components:{"components-datatable":O},data(){return{is_loading:false,merchant_uuid:"",merchant:[],observer:undefined,plan_history:[],merchant_active:false}},mounted(){this.observer=lozad(".lozad",{loaded:function(t){t.classList.add("loaded")}})},updated(){this.observer.observe()},methods:{show(){c(this.$refs.modal_merchant_transaction).modal("show");this.$refs.datatable.getTableData();this.getOrderSummary()},close(){c(this.$refs.modal_merchant_transaction).modal("hide")},getOrderSummary(){this.is_loading=true;axios({method:"post",url:this.ajax_url+"/getordersummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&merchant_uuid="+this.merchant_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.merchant=t.data.details.merchant;this.merchant_active=t.data.details.merchant.merchant_active;var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_orders,t.data.details.orders,e);a.start();var i=new countUp.CountUp(this.$refs.summary_cancel,t.data.details.order_cancel,e);i.start();var e={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){e.suffix=t.data.details.price_format.symbol}else e.prefix=t.data.details.price_format.symbol;var s=new countUp.CountUp(this.$refs.summary_total,t.data.details.total,e);s.start();var l=new countUp.CountUp(this.$refs.total_refund,t.data.details.total_refund,e);l.start()}else{this.merchant=[];o(t.data.msg,"error");this.$refs.summary_orders=0;this.$refs.summary_cancel=0;this.$refs.summary_total=0;this.$refs.total_refund=0}})["catch"](t=>{}).then(t=>{this.is_loading=false})},merchantActiveConfirmation(){if(this.merchant_active){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.block+"</h5>"+"<p>"+this.label.block_content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.changeMerchantStatus(0)}}})}else{this.changeMerchantStatus(1)}},changeMerchantStatus(t){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/changeMerchantStatus",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_uuid:this.merchant_uuid,status:t},timeout:s}).then(t=>{if(t.data.code==1){this.merchant_active=t.data.details.merchant_active}else{this.merchant_active=false}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:"#xtemplate_merchant_transaction"};const it={props:["ajax_url","label","transaction_type_list"],data(){return{is_loading:false,transaction_description:"",transaction_type:"credit",transaction_amount:0,merchant_id:""}},computed:{hasData(){if(!u(this.transaction_description)&&!u(this.transaction_type)&&!u(this.transaction_amount)){return true}return false}},mounted(){},methods:{show(){c(this.$refs.modal_merchant_adjustment).modal("show")},close(){c(this.$refs.modal_merchant_adjustment).modal("hide")},clear(){this.transaction_description="";this.transaction_type="credit";this.transaction_amount=0},submit(){this.merchant_id=c(this.$refs.adjustment_merchant_id).find(":selected").val();var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_provider:this.payment_provider,transaction_description:this.transaction_description,transaction_type:this.transaction_type,transaction_amount:this.transaction_amount,merchant_id:this.merchant_id};this.error=[];this.is_loading=true;axios({method:"put",url:this.ajax_url+"/merchantEarningAdjustment",data:t,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.clear();this.close();this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<div ref="modal_merchant_adjustment" class="modal"
    id="modal_merchant_adjustment" data-backdrop="static" 
   role="dialog" aria-labelledby="modal_merchant_adjustment" aria-hidden="true">
    
	   <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
	     
	     <form @submit.prevent="submit" >
	     
	      <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  
		  <div class="modal-body">
		  
		   <p class="mb-2"><b>{{label.merchant}}</b></p>
			 <div class="form-label-group mb-3">  
			 <select ref="adjustment_merchant_id" class="form-control select2-merchant"  style="width:100%">	  	  
			 </select>
		   </div>
	 
		   <div class="form-label-group">    
		       <input type="text" v-model="transaction_description" id="transaction_description"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="transaction_description">{{label.transaction_description}}</labe>
		   </div>

		   <select ref="transaction_type" v-model="transaction_type"
		     class="form-control custom-select form-control-select mb-3"  >		    
		     <option v-for="(name, key) in transaction_type_list" :value="key">{{name}}</option>		    
		   </select>	   
		   
		   <div class="form-label-group">    
		       <input type="text" v-model="transaction_amount" id="transaction_amount"
		       v-maska="'#################'" 
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="transaction_amount">{{label.transaction_amount}}</labe>
		     </div>
		  
		   </div> <!-- body -->
		  
		   <div class="modal-footer">     

		   <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.close}}</span>
           </button>
		       
	        <button type="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"
	        :disabled="!hasData"
	        >
	          <span>{{label.submit}}</span>
	          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
	        </button>
	      </div>
		 
	      </form>
		  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->     	   
	`};const st=Vue.createApp({components:{"components-datatable":O,"components-commission-balance":tt,"components-total-balance":et,"components-create-adjustment":C,"components-merchant-transaction":at,"components-merchant-earning-adjustment":it},data(){return{is_loading:false,balance:0}},methods:{afterBalance(t){this.balance=t},createTransaction(){this.$refs.create_adjustment.show()},afterSave(){this.$refs.datatable.getTableData();this.$refs.balance.getBalance()},viewMerchantTransaction(t){this.$refs.merchant_transaction.merchant_uuid=t;this.$refs.merchant_transaction.show()},createMerchantAdjustment(){this.$refs.merchant_adjustment.show()}}});st.use(Maska);const lt=st.mount("#vue-commission-statement");const E={props:["ajax_url","label","transaction_type"],data(){return{is_loading:false,transaction_uuid:"",data:[],merchant:[],provider:[]}},computed:{isUnpaid(){if(this.data.status=="unpaid"){return false}return true}},methods:{show(){c(this.$refs.modal_payout).modal("show");this.getPayoutDetails()},close(){c(this.$refs.modal_payout).modal("hide")},getPayoutDetails(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getPayoutDetails",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&transaction_uuid="+this.transaction_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.merchant=t.data.details.merchant;this.provider=t.data.details.provider}else{o(t.data.msg,"error");this.data=[];this.merchant=[];this.provider=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},changePayoutStatus(t){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/"+t,data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&transaction_uuid="+this.transaction_uuid+"&transaction_type="+this.transaction_type,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg,"success");this.close();this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<div ref="modal_payout" class="modal"
    id="modal_payout" data-backdrop="static" 
    tabindex="-1" role="dialog" aria-labelledby="modal_payout" aria-hidden="true">
    
	   <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
	     
	     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
		    <div>
		      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
		    </div>
		 </div>
		 
	     
	      <div class="modal-header">		  
			<h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  		  
		  <div class="modal-body grey-bg">
		  
		  <div class="card p-3">

		  <div class="row">
		    <div class="col">		    
		     <div class="form-group">
			     <label class="m-0 mb-1">{{label.amount}}</label>
			     <h6 class="m-0">{{data.transaction_amount}}</h6>
			  </div> 		    
			  <div class="form-group">
			     <label class="m-0 mb-1">{{label.payment_method}}</label>
			     <h6 class="m-0">{{provider.payment_name}}</h6>
			     <p v-if="provider.is_online==='1'" class="m-0 text-muted font11">({{label.online_payment}})</p>
			     <p v-else class="m-0 text-muted font11">({{label.offline_payment}})</p>
			  </div> 		    
			  
			  <div class="form-group">
			     <label class="m-0 mb-1">{{label.status}}</label>
			     <h6 class="m-0">{{data.status}}</h6>
			  </div> 		    
			  
		    </div>
		    <!-- col -->
		    
		    <div class="col">		    
		     <div class="form-group">
			     <label class="m-0 mb-1">{{label.merchant}}</label>
			     <h6 class="m-0">{{merchant.restaurant_name}}</h6>
			  </div> 		    
			  <div class="form-group">
			     <label class="m-0 mb-1">{{label.date_requested}}</label>
			     <h6 class="m-0">{{data.transaction_date}}</h6>
			  </div> 		    

			  <div v-if="data.merchant_base_currency!=data.admin_base_currency" class="form-group">
			   <label class="m-0 mb-1"><b>Exchange rate info</b></label>
			   <p class="m-0">Total amount : {{data.transaction_amount_original}}</p>
			   <p class="m-0">Exchange rate : {{data.exchange_rate_merchant_to_admin}}</p>			   
			  </div>

		    </div>
		    <!-- col -->
		    
		  </div> 
		  <!-- row -->
		  		  
		  <h5>{{label.payment_to_account}}</h5>	
		  
		  
		  <template v-if="data.provider==='paypal'">	  
		  <p>{{data.email_address}}</p>
		  </template>
		  
		  <template v-else-if="data.provider==='stripe'">	  		  
		  <table class="table table-bordered">
		   <tr>
		    <td width="40%">{{label.account_number}}</td>
		    <td>{{data.account_number}}</td>
		   </tr>
		   <tr>
		    <td >{{label.account_name}}</td>
		    <td>{{data.account_holder_name}}</td>
		   </tr>
		   <tr>
		    <td >{{label.account_type}}</td>
		    <td>{{data.account_holder_type}}</td>
		   </tr>
		   <tr>
		    <td >{{label.account_currency}}</td>
		    <td>{{data.currency}}</td>
		   </tr>
		   <tr>
		    <td>{{label.routing_number}}</td>
		    <td>{{data.routing_number}}</td>
		   </tr>
		   <tr>
		    <td>{{label.country}}</td>
		    <td>{{data.country}}</td>
		   </tr>
		  </table>		  
		  </template>
		  
		  <template v-else-if="data.provider==='bank'">	  		  
		   <table class="table table-bordered">
		   <tr>
		    <td width="40%">{{label.account_holders_name}}</td>
		    <td>{{data.account_name}}</td>
		   </tr>
		   <tr>
		    <td width="40%">{{label.iban}}</td>
		    <td>{{data.account_number_iban}}</td>
		   </tr>
		   <tr>
		    <td width="40%">{{label.switf_code}}</td>
		    <td>{{data.swift_code}}</td>
		   </tr>
		   <tr>
		    <td width="40%">{{label.bank_name}}</td>
		    <td>{{data.bank_name}}</td>
		   </tr>
		   <tr>
		    <td width="40%">{{label.bank_branch}}</td>
		    <td>{{data.bank_branch}}</td>
		   </tr>
		  </table>
		  </template>
		  		 
		  </div>
		  </div> <!-- body -->
		  
		   <div class="modal-footer">     
          
		   <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.close}}</span>
           </button>
		       	        
	       <button type="button" class="btn btn-yellow" :class="{ loading: is_loading }" 
	       :disabled="isUnpaid"       
	       @click="changePayoutStatus('cancelPayout')"  
	       >
            <span class="pl-3 pr-3">{{label.cancel_payout}}</span>
           </button>
           
           <template v-if="provider.is_online==='1'">
	           <button type="button" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"	
	           :disabled="isUnpaid"        	           
	           @click="changePayoutStatus('approvedPayout')"  
		        >
		          <span>{{label.approved}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
		        </button>
	       </template>
	       
	       <template v-else>
	       
	         <button type="button" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"	
	           :disabled="isUnpaid"      	           
	           @click="changePayoutStatus('payoutPaid')"  
		        >
		          <span>{{label.set_paid}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
		      </button>  
	       
	       </template>
	        
            
	      </div>
		 
	      
		  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->   
	`};const rt=Vue.createApp({components:{"components-datatable":O,"components-payout-details":E},data(){return{is_loading:false,summary:[],count_up:undefined}},mounted(){this.payoutSummary()},methods:{payoutSummary(){this.is_loading=true;axios({method:"POST",url:api_url+"/payoutSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.summary=t.data.details.summary;var e={decimalPlaces:0,separator:",",decimal:"."};var a={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){a.suffix=t.data.details.price_format.symbol}else a.prefix=t.data.details.price_format.symbol;this.count_up=new countUp.CountUp(this.$refs.ref_unpaid,this.summary.unpaid,e);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.ref_paid,this.summary.paid,e);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total_unpaid,this.summary.total_unpaid,a);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.ref_total_paid,this.summary.total_paid,a);this.count_up.start()}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewPayoutDetails(t){this.$refs.payout.transaction_uuid=t;this.$refs.payout.show()},afterSave(){d("afterSave");this.$refs.datatable.getTableData();this.payoutSummary()}}});const dt=rt.mount("#vue-payout");const ot={props:["ajax_url","message"],data(){return{settings:[],beams:undefined}},mounted(){this.getWebpushSettings()},methods:{getWebpushSettings(){axios({method:"POST",url:this.ajax_url+"/getWebpushSettings",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.settings=t.data.details;if(this.settings.enabled==1){this.webPushInit()}}else{this.settings=[]}})["catch"](t=>{}).then(t=>{})},webPushInit(){d(this.settings);if(this.settings.provider=="pusher"&&this.settings.user_settings.enabled==1){this.beams=new PusherPushNotifications.Client({instanceId:this.settings.pusher_instance_id});this.beams.start().then(()=>{this.beams.setDeviceInterests(this.settings.user_settings.interest).then(()=>{console.log("Device interests have been set")}).then(()=>this.beams.getDeviceInterests()).then(t=>console.log("Current interests:",t))["catch"](t=>{var e={notification_type:"push",message:"Beams "+t,date:"",image_type:"icon",image:"zmdi zmdi-info-outline"};pt.$refs.notification.addData(e)})})["catch"](t=>{var e={notification_type:"push",message:"Beams "+t,date:"",image_type:"icon",image:"zmdi zmdi-info-outline"};pt.$refs.notification.addData(e)})}else if(this.settings.provider=="onesignal"){}}},template:`	   
	`};const nt={template:"#xtemplate_webpushsettings",props:["ajax_url","settings","iterest_list","message"],data(){return{beams:undefined,is_loading:false,is_submitted:false,webpush_enabled:"",interest:[],device_id:""}},mounted(){this.initBeam()},methods:{initBeam(){this.beams=new PusherPushNotifications.Client({instanceId:this.settings.instance_id});this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getwebnotifications",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.webpush_enabled=t.data.details.enabled==1?true:false;this.device_id=t.data.details.device_token;this.interest=t.data.details.interest}else{this.webpush_enabled=false;this.device_id="";this.interest=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},enabledWebPush(){this.is_loading=true;if(this.webpush_enabled){this.beams.start().then(()=>{d("start");this.beams.getDeviceId().then(t=>{this.device_id=t})})["catch"](t=>{o(this.message.notification_start,"error")}).then(t=>{this.is_loading=false})}else{this.beams.stop().then(()=>{d("stop");this.device_id=""})["catch"](t=>{o(this.message.notification_stop,"error")}).then(t=>{this.is_loading=false})}},saveWebNotifications(){this.is_submitted=true;axios({method:"PUT",url:this.ajax_url+"/savewebnotifications",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),webpush_enabled:this.webpush_enabled,interest:this.interest,device_id:this.device_id},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_submitted=false})}}};const ct=Vue.createApp({components:{"components-web-pusher":nt}});const mt=ct.mount("#vue-webpush-settings");const ht={components:{"components-webpush":ot},props:["ajax_url","label","realtime","view_url"],data(){return{data:[],count:0,new_message:false,player:undefined,ably:undefined,channel:undefined,piesocket:undefined}},mounted(){this.getAllNotification();if(this.realtime.enabled){this.initRealTime()}},computed:{hasData(){if(this.data.length>0){return true}return false},ReceiveMessage(){if(this.new_message){return true}return false}},methods:{initRealTime(){if(this.realtime.provider=="pusher"){Pusher.logToConsole=false;var t=new Pusher(this.realtime.key,{cluster:this.realtime.cluster});var e=t.subscribe(this.realtime.channel);e.bind(this.realtime.event,e=>{d("receive pusher");d(e);if(e.notification_type=="silent"){}else if(e.notification_type=="order_update"){this.playAlert();this.addData(e);if(typeof R!=="undefined"&&R!==null){let t=e.meta_data;R.refreshOrderInformation(t.order_uuid)}}else{this.playAlert();this.addData(e)}})}else if(this.realtime.provider=="ably"){this.ably=new Ably.Realtime(this.realtime.ably_apikey);this.ably.connection.on("connected",()=>{this.channel=this.ably.channels.get(this.realtime.channel);this.channel.subscribe(this.realtime.event,t=>{d("receive ably");d(t.data);this.playAlert();this.addData(t.data)})})}else if(this.realtime.provider=="piesocket"){this.piesocket=new PieSocket({clusterId:this.realtime.piesocket_clusterid,apiKey:this.realtime.piesocket_api_key});this.channel=this.piesocket.subscribe(this.realtime.channel);this.channel.listen(this.realtime.event,t=>{d("receive piesocket");d(t);this.playAlert();this.addData(t)})}},playAlert(){this.player=new Howl({src:["../assets/sound/notify.mp3","../assets/sound/notify.ogg"],html5:true});this.player.play()},getAllNotification(){axios({method:"POST",url:this.ajax_url+"/getNotifications",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.count=t.data.details.count}else{this.data=[];this.count=0}})["catch"](t=>{}).then(t=>{})},addData(t){this.data.unshift(t);this.count++;this.new_message=true;setTimeout(()=>{this.new_message=false},1e3);if(typeof Y!=="undefined"&&Y!==null){Y.refreshLastOrder()}},clearAll(){axios({method:"POST",url:this.ajax_url+"/clearNotifications",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=[];this.count=0}else{o(t.data.msg,"error")}this.new_message=false})["catch"](t=>{}).then(t=>{})}},template:`
	
	<components-webpush
	 :ajax_url="ajax_url"
	 :message='label'
	/>
	</components-webpush>
	
	<div class="btn-group pull-right notification-dropdown">
	      <button type="button" class="btn p-0 btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	        <i class="zmdi zmdi-notifications-none"></i>
	        <span v-if="count>0" :class="{'shake-constant shake-chunk' : ReceiveMessage }" class="badge rounded-circle badge-danger count">{{count}}</span>
	      </button>
          <div class="dropdown-menu dropdown-menu-right">
            <div class="dropdown-header d-flex justify-content-between">
               <div class="flex-col">
                 <div class="d-flex align-items-center">
                  <h5 class="m-0 mr-2">{{label.title}}</h5>
                  <span class="badge rounded-circle badge-green badge-25">{{count}}</span>
                 </div>
               </div>
               <div class="flex-col" v-if="hasData">
                <a @click="clearAll">{{label.clear}}</a>
               </div>
            </div>
            <!--header-->
            
            <!--content-->            
            <ul v-if="hasData"  class="list-unstyled m-0">
             <li v-for="(item,index) in data">
              <a :class="{ active: index<=0 }" >
                <div class="d-flex">
                   <div v-if="item.image!=''" class="flex-col mr-3">  
                      <template v-if="item.image_type=='icon'">
                         <div class="notify-icon rounded-circle bg-soft-primary">
	                        <i :class="item.image"></i>
	                      </div>
                      </template>
                      <template v-else>
                       <div class="notify-icon">
                          <img class="img-40 rounded-circle" :src="item.image" />
                       </div>
                      </template>
                   </div>
                   <div class="flex-col">
                      <div class="text-heading" v-html="item.message"></div>
	                  <div class="dropdown-text-light">{{item.date}}</div>
                   </div>
                </div>
              </a>
             </li>		            		             
            </ul>
            <!--content-->
            
            <div v-if="!hasData" class="none-notification text-center">
              <div class="image-notification m-auto"></div>
              <h5 class="m-0 mb-1 mt-2">{{label.no_notification}}</h5>
              <p class="m-0 font11 text-muted">{{label.no_notification_content}}</p>
            </div>
            
            <div v-if="hasData" class="footer-dropdown text-center">
            <a :href="view_url" targe="_blank" class="text-primary">{{label.view}}</a>
            </div>
            
          </div> <!--dropdown-menu-->
      </div>
      <!--btn-group-->
	`};const ut={props:["ajax_url","enabled_interval","interval_seconds"],data(){return{handle:undefined}},created(){if(this.enabled_interval){this.requestNewOrder()}},methods:{startRequest(){if(this.handle){clearInterval(this.handle)}let t=3e4;if(typeof continues_alert_interval!=="undefined"&&continues_alert_interval!==null){t=parseFloat(continues_alert_interval)*1e3}this.handle=setInterval(()=>{this.requestNewOrder()},t)},requestNewOrder(){axios({method:"POST",url:this.ajax_url+"/requestNewOrder",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){if(Object.keys(t.data.details).length>0){Object.entries(t.data.details).forEach(([t,e])=>{if(t==0){this.playAlert();ElementPlus.ElNotification({title:e.title,message:e.message,duration:4500})}else{setTimeout(()=>{this.playAlert();ElementPlus.ElNotification({title:e.title,message:e.message,duration:4500})},500)}})}}})["catch"](t=>{}).then(t=>{this.startRequest()})},playAlert(){this.player=new Howl({src:["../assets/sound/notify.mp3","../assets/sound/notify.ogg"],html5:true});this.player.play()}}};const _t=Vue.createApp({components:{"components-notification":ht,"components-continuesalert":ut}});_t.use(ElementPlus);const pt=_t.mount("#vue-notifications");let N;let j=16.6;const ft=[{featureType:"administrative",elementType:"labels.text.fill",stylers:[{color:"#686868"}]},{featureType:"landscape",elementType:"all",stylers:[{color:"#f2f2f2"}]},{featureType:"poi",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"road",elementType:"all",stylers:[{saturation:-100},{lightness:45}]},{featureType:"road.highway",elementType:"all",stylers:[{visibility:"simplified"}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{lightness:"-22"}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{saturation:"11"},{lightness:"-51"}]},{featureType:"road.highway",elementType:"labels.text",stylers:[{saturation:"3"},{lightness:"-56"},{weight:"2.20"}]},{featureType:"road.highway",elementType:"labels.text.fill",stylers:[{lightness:"-52"}]},{featureType:"road.highway",elementType:"labels.text.stroke",stylers:[{weight:"6.13"}]},{featureType:"road.highway",elementType:"labels.icon",stylers:[{lightness:"-10"},{gamma:"0.94"},{weight:"1.24"},{saturation:"-100"},{visibility:"off"}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{lightness:"-16"}]},{featureType:"road.arterial",elementType:"labels.text.fill",stylers:[{saturation:"-41"},{lightness:"-41"}]},{featureType:"road.arterial",elementType:"labels.text.stroke",stylers:[{weight:"5.46"}]},{featureType:"road.arterial",elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"road.local",elementType:"geometry.fill",stylers:[{weight:"0.72"},{lightness:"-16"}]},{featureType:"road.local",elementType:"labels.text.fill",stylers:[{lightness:"-37"}]},{featureType:"transit",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"water",elementType:"all",stylers:[{color:"#b7e4f4"},{visibility:"on"}]}];const vt={props:["markers","center","zoom","maps_config"],data(){return{bounds:[],cmaps:undefined,cmapsMarker:[],allMarkers:[]}},mounted(){},watch:{markers(t,e){this.clearMarkers()}},methods:{renderMap(){if(this.maps_config.provider=="google.maps"){this.bounds=new window.google.maps.LatLngBounds;this.cmaps=new window.google.maps.Map(this.$refs.maploc,{center:{lat:parseFloat(this.center.lat),lng:parseFloat(this.center.lng)},zoom:parseInt(this.zoom),disableDefaultUI:false,styles:ft})}else if(this.maps_config.provider=="mapbox"){this.bounds=new mapboxgl.LngLatBounds;mapboxgl.accessToken=this.maps_config.key;this.cmaps=new mapboxgl.Map({container:this.$refs.maploc,style:"mapbox://styles/mapbox/streets-v12",center:[parseFloat(this.center.lng),parseFloat(this.center.lat)],zoom:14});this.cmaps.on("error",t=>{alert(t.error.message)});this.mapBoxResize()}else if(this.maps_config.provider=="yandex"){this.initYandex()}this.instantiateDriverMarker()},async initYandex(){await ymaps3.ready;const{YMap:t,YMapDefaultSchemeLayer:e,YMapMarker:l,YMapDefaultFeaturesLayer:a,YMapListener:i,YMapControls:r}=ymaps3;const{YMapDefaultMarker:s}=await ymaps3["import"]("@yandex/ymaps3-markers@0.0.1");const{YMapZoomControl:d,YMapGeolocationControl:o}=await ymaps3["import"]("@yandex/ymaps3-controls@0.0.1");const n={center:[parseFloat(this.center.lng),parseFloat(this.center.lat)],zoom:j};if(N){N.destroy();N=null}if(!N){N=new t(this.$refs.maploc,{location:n,showScaleInCopyrights:false,behaviors:["drag","scrollZoom"]},[new e({}),new a({})]);N.addChild(new r({position:"right"}).addChild(new d({})));let s=[];Object.entries(this.markers).forEach(([t,e])=>{let a=[parseFloat(e.lng),parseFloat(e.lat)];s.push(a);const i=document.createElement("div");i.className=this.getIconMapbox(e.type);this.cmapsMarker[t]=N.addChild(new l({coordinates:a},i))});if(Object.keys(s).length>1){const c={bounds:s,zoom:j};N.update({location:c})}else if(Object.keys(s).length>0){const m={center:[s[0][0],s[0][1]],zoom:j};N.update({location:m})}}},clearMarkers(){if(this.maps_config.provider=="google.maps"){for(var t=0;t<this.allMarkers.length;t++){this.allMarkers[t].setVisible(false);this.allMarkers[t].setMap(null)}}this.allMarkers=[];this.instantiateDriverMarker()},instantiateDriverMarker(){Object.entries(this.markers).forEach(([t,e])=>{let a="";if(this.maps_config.provider=="google.maps"){a={path:google.maps.SymbolPath.CIRCLE,scale:8,strokeColor:"#f44336"}}else if(this.maps_config.provider=="mapbox"){a=this.getIconMapbox(e.type)}let i={lat:parseFloat(e.lat),lng:parseFloat(e.lng)};let s=e.name;this.addMarker({position:i,map:this.cmaps,icon:a,label:this.getIcon(e.type),info:s},e.index)});this.FitBounds()},getIconMapbox(t){let e=[];e["driver"]="marker_icon_driver";e["merchant"]="marker_icon_merchant";e["customer"]="marker_icon_destination";return e[t]},getIcon(t){let e=[];e["driver"]={text:"",fontFamily:"Material Icons",color:"#ffffff",fontSize:"17px"};e["merchant"]={text:"",fontFamily:"Material Icons",color:"#ffffff",fontSize:"17px"};e["customer"]={text:"",fontFamily:"Material Icons",color:"#ffffff",fontSize:"17px"};return e[t]},addMarker(a,i){if(this.maps_config.provider=="google.maps"){this.cmapsMarker[i]=new window.google.maps.Marker(a);this.cmaps.panTo(new window.google.maps.LatLng(a.position.lat,a.position.lng));this.bounds.extend(this.cmapsMarker[i].position);this.allMarkers.push(this.cmapsMarker[i]);const s=new google.maps.InfoWindow({content:a.info});let t=this.cmaps;let e=this.cmapsMarker[i];e.addListener("click",()=>{s.open({anchor:e,cmaps:t,shouldFocus:false})})}else if(this.maps_config.provider=="mapbox"){const t=new mapboxgl.Popup({offset:25}).setHTML(a.info);const e=document.createElement("div");e.className=a.icon;this.cmapsMarker[i]=new mapboxgl.Marker(e).setLngLat([a.position.lng,a.position.lat]).setPopup(t).addTo(this.cmaps);this.bounds.extend(new mapboxgl.LngLat(a.position.lng,a.position.lat))}},FitBounds(){if(this.maps_config.provider=="google.maps"){try{this.cmaps.fitBounds(this.bounds)}catch(t){console.error(t)}}else if(this.maps_config.provider=="mapbox"){this.cmaps.fitBounds(this.bounds,{padding:30})}},mapBoxResize(){if(this.maps_config.provider=="mapbox"){setTimeout(()=>{this.cmaps.resize()},500)}}},template:`			
	<div ref="maploc" class="map-small" style="border:1px solid #fff;"></div>
	`};const gt={props:["order_uuid","ajax_url","map_center","zoom"],components:{"components-map":vt},data(){return{loading:false,data:[],zone_id:0,group_selected:0,group_data:[],zone_data:[],markers:[],active_task:[]}},created(){this.getAvailableDriver();this.getGroupList();this.getZoneList()},watch:{zone_id(t,e){this.getAvailableDriver()},group_selected(t,e){this.getAvailableDriver()}},computed:{hasData(){if(Object.keys(this.data).length>0){return true}return false},hasMarkers(){if(Object.keys(this.markers).length>0){return true}return false},hasFilter(){let t=false;if(!u(this.zone_id)){t=true}if(this.group_selected>0){t=true}return t}},methods:{show(){c(this.$refs.modal).modal("show");this.$refs.map_components.renderMap()},hide(){c(this.$refs.modal).modal("hide")},getGroupList(){axios({method:"post",url:this.ajax_url+"/getGroupList",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{this.group_data=t.data.details})["catch"](t=>{this.group_data=[]}).then(t=>{})},getZoneList(){axios({method:"post",url:this.ajax_url+"/getZoneList",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{this.zone_data=t.data.details})["catch"](t=>{this.zone_data=[]}).then(t=>{})},clearFilter(){this.zone_id=0;this.group_selected=0;this.getAvailableDriver()},getAvailableDriver(){this.loading=true;axios({method:"post",url:this.ajax_url+"/getAvailableDriver",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid+"&zone_id="+this.zone_id+"&group_selected="+this.group_selected,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.merchant_data=t.data.details.merchant_data;this.active_task=t.data.details.active_task}else{this.data=[];this.merchant_data=t.data.details.merchant_data;this.active_task=[]}this.SetMarker()})["catch"](t=>{this.data=[];this.merchant_data=[];this.active_task=[]}).then(t=>{this.loading=false})},SetMarker(){this.markers=[];if(Object.keys(this.data).length>0){Object.entries(this.data).forEach(([t,e])=>{this.markers.push({type:"driver",name:e.name,lat:e.latitude,lng:e.longitude})})}if(Object.keys(this.merchant_data).length>0){this.markers.push({type:"merchant",name:this.merchant_data.restaurant_name,lat:this.merchant_data.latitude,lng:this.merchant_data.longitude})}},AssignDriver(t){this.loading=true;let e="YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content");e+="&driver_id="+t;e+="&order_uuid="+this.order_uuid;axios({method:"post",url:this.ajax_url+"/AssignDriver",data:e,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder",this.order_uuid);ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});this.hide()}else{ElementPlus.ElNotification({title:"",message:t.data.msg,type:"warning"})}})["catch"](t=>{ElementPlus.ElNotification({title:"",message:t,type:"warning"})}).then(t=>{this.loading=false})}},template:"#xtemplate_assign_driver"};const F=Vue.createApp({components:{"components-orderinfo":L,"components-rejection-forms":A,"components-order-history":W,"components-order-print":J,"components-customer-details":B,"components-merchant-transaction":at,"components-assign-driver":gt},data(){return{order_uuid:"",client_id:"",merchant_id:"",merchant_uuid:"",is_loading:false,group_name:"",manual_status:false,modify_order:false,filter_buttons:false}},mounted(){this.order_uuid=_order_uuid;this.getGroupname()},methods:{showAssigndriver(){this.$refs.assign_driver.show()},afterUpdateStatus(){this.getGroupname();if(typeof vm_siderbar_menu!=="undefined"&&vm_siderbar_menu!==null){vm_siderbar_menu.getOrdersCount()}},refreshOrderInformation(t){this.$refs.orderinfo.orderDetails(this.order_uuid)},loadOrderDetails(){this.$refs.orderinfo.orderDetails(this.order_uuid)},delayOrder(t){this.$refs.delay.show()},orderReject(t){this.$refs.rejection.confirm()},orderHistory(t){this.$refs.history.show()},toPrint(){this.$refs.print.show()},showMerchantMenu(t){this.$refs.menu.replace_item=t;this.$refs.menu.show()},showItemDetails(t){this.$refs.item.show(t)},viewCustomer(){this.$refs.customer.show()},getGroupname(){this.is_loading=true;axios({method:"POST",url:_ajax_url+"/getGroupname",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.group_name=t.data.details.group_name;this.manual_status=t.data.details.manual_status;this.modify_order=t.data.details.modify_order;this.merchant_id=t.data.details.merchant_id;this.merchant_uuid=t.data.details.merchant_uuid;this.client_id=t.data.details.client_id;this.filter_buttons=t.data.details.filter_buttons}else{this.merchant_id="";this.merchant_uuid="";this.client_id="";this.group_name="";this.manual_status=false;this.modify_order=false;this.filter_buttons=false}setTimeout(()=>{this.loadOrderDetails()},1)})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewMerchantTransaction(){this.$refs.merchant_transaction.merchant_uuid=this.merchant_uuid;this.$refs.merchant_transaction.show()}}});F.use(Maska);F.use(ElementPlus);const R=F.mount("#vue-order-view");const bt=Vue.createApp({components:{"components-datatable":O},data(){return{is_loading:false}},mounted(){this.getOrderSummary()},methods:{getOrderSummary(){this.is_loading=true;axios({method:"POST",url:api_url+"/getAllOrderSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_orders,t.data.details.orders,e);a.start();var i=new countUp.CountUp(this.$refs.summary_cancel,t.data.details.order_cancel,e);i.start();var e=t.data.details.price_format;var s=this.count_up=new countUp.CountUp(this.$refs.summary_total,t.data.details.total,e);s.start();var l=this.count_up=new countUp.CountUp(this.$refs.total_refund,t.data.details.total_refund,e);l.start()}else{}})["catch"](t=>{}).then(t=>{this.is_loading=false})}}});const yt=bt.mount("#vue-order-list");const xt={props:["ajax_url","label"],data(){return{data:[],is_loading:false}},mounted(){this.ReportsMerchantSummary()},methods:{ReportsMerchantSummary(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/ReportsMerchantSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.total_registered,t.data.details.total_registered,e);a.start();a=new countUp.CountUp(this.$refs.commission_total,t.data.details.commission_total,e);a.start();a=new countUp.CountUp(this.$refs.membership_total,t.data.details.membership_total,e);a.start();a=new countUp.CountUp(this.$refs.total_active,t.data.details.total_active,e);a.start();a=new countUp.CountUp(this.$refs.total_inactive,t.data.details.total_inactive,e);a.start()}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    <div class="row">
	   <div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.total_registered}}</p><h5 ref="total_registered" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.commission_total}}</p><h5 ref="commission_total" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.membership_total}}</p><h5 ref="membership_total" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.total_active}}</p><h5 ref="total_active" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.total_inactive}}</p><h5 ref="total_inactive" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
	  </div> <!--row-->
    `};const wt={props:["ajax_url","label"],data(){return{data:[],is_loading:false,date_start:"",date_end:""}},mounted(){this.ReportsOrderEarningSummary()},methods:{setDate(t,e){this.date_start=t;this.date_end=e;this.ReportsOrderEarningSummary()},ReportsOrderEarningSummary(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/ReportsOrderEarningSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&date_start="+this.date_start+"&date_end="+this.date_end,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.total_count,t.data.details.total_count,e);a.start();var e={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator,prefix:t.data.details.price_format.symbol};a=new countUp.CountUp(this.$refs.admin_earning,t.data.details.admin_earning,e);a.start();a=new countUp.CountUp(this.$refs.merchant_earning,t.data.details.merchant_earning,e);a.start();a=new countUp.CountUp(this.$refs.total_sell,t.data.details.total_sell,e);a.start()}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    <div class="row">
	   <div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.total_count}}</p><h5 ref="total_count" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.admin_earning}}</p><h5 ref="admin_earning" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.merchant_earning}}</p><h5 ref="merchant_earning" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
		
		<div class="col">
		  <div class="bg-light p-3 mb-3 rounded">   
		   <div class="d-flex">
	        <p class="m-0 mr-2 text-muted text-truncate">{{label.total_sell}}</p><h5 ref="total_sell" class="m-0">0</h5>
	       </div>  	  
		  </div><!-- bg-light-->
		</div> <!--col-->
				
	  </div> <!--row-->
    `};const St={props:["ajax_url","label","limit","method"],data(){return{data:[],is_loading:false}},mounted(){},methods:{show(t){this.viewData(t);c(this.$refs.view_data).modal("show")},close(){c(this.$refs.view_data).modal("hide")},viewData(t){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/"+this.method,data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&view_id="+t,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    <div ref="view_data" class="modal" tabindex="-1" role="dialog" >
    <div :class="{ 'modal-lg': data.type=='email' }" class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      
      <div class="modal-body position-relative">
      
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	  </div>
	  
	  <h5 class="mb-4">{{label.title}}</h5>	  
	  
	  <div class="view-content p-4 rounded"  :class="data.type" >	  
	     <span v-html="data.content"></span>
	  </div>
      
     
	  </div> <!-- body -->
	  
	  <div class="modal-footer border-0">            
        <button type="button" class="btn btn-green pl-4 pr-4"  data-dismiss="modal">
          <span>{{label.close}}</span>          
        </button>
      </div>
      
    </div>
  </div>
</div>        
    `};const It=Vue.createApp({components:{"components-datatable":O,"components-reports-merchant":xt,"components-reports-earnings":wt,"components-view-data":St},data(){return{is_loading:false}},methods:{afterSelectdate(t,e){this.$refs.summary_earnings.setDate(t,e)},view(t){this.$refs.view_data.show(t.view_id)}}});It.use(Maska);const kt=It.mount("#vue-tables");const Tt={props:["ajax_url","label","merchant_type","domain"],data(){return{data:[],is_loading:false}},mounted(){this.salesSummary();this.validateIdentity()},methods:{salesSummary(){axios({method:"POST",url:this.ajax_url+"/commissionSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{var e;if(t.data.code==1){e=this.count_up=new countUp.CountUp(this.$refs.commission_week,t.data.details.commission_week,t.data.details.price_format);e.start();e=this.count_up=new countUp.CountUp(this.$refs.commission_month,t.data.details.commission_month,t.data.details.price_format);e.start();e=this.count_up=new countUp.CountUp(this.$refs.subscription_month,t.data.details.subscription_month,t.data.details.price_format);e.start()}else{}})["catch"](t=>{}).then(t=>{})},validateIdentity(){var t=window.location.hostname;axios.get("https://bastisapp.com/activation/index/check",{params:{id:"UYIiWfAfWx414it65oUbeXf4I1yjDNSZi2UxnBBLQa8hpHAcVlyP+Sx0OL8vmfcwnzSYkw==",domain:t}}).then(t=>{if(t.data.code==1){}else{window.location.href="https://bastisapp.com/activation/"}})["catch"](t=>{}).then(t=>{})}},template:`	    
    <div class="row">
	    <div class="col mb-3 mb-xl-0">
	      <div class="card">
	        <div class="card-body" style="padding:10px;">
	        
	          <div id="boxes" class="d-flex align-items-center">
	            <div class="mr-2"><div class="rounded box box-1 d-flex align-items-center  justify-content-center"><i class="zmdi zmdi-balance-wallet"></i></div></div>
	            <div>
	               <h6 class="m-0 text-muted font-weight-normal">{{label.commission_week}}</h6>
	               <h6 class="m-0 position-relative" ref="commission_week">0
	                  <div class="skeleton-placeholder" style="height:17px;width:100%;"></div>
	               </h6>
	            </div>
	          </div><!--flex-->       
	          
	        </div> <!--card body-->       
	      </div> <!--card-->
	    </div> <!-- col -->
	    
	    <div class="col mb-3 mb-xl-0">
	      <div class="card">
	        <div class="card-body" style="padding:10px;">
	        
	          <div id="boxes" class="d-flex align-items-center">
	            <div class="mr-2"><div class="rounded box box-2 d-flex align-items-center  justify-content-center"><i class="zmdi zmdi-balance-wallet"></i></div></div>
	            <div>
	               <h6 class="m-0 text-muted font-weight-normal">{{label.commission_month}}</h6>
	               <h6 class="m-0 position-relative" ref="commission_month">0
	                 <div class="skeleton-placeholder" style="height:17px;width:100%;"></div>
	               </h6>
	            </div>
	          </div><!--flex-->       
	          
	        </div> <!--card body-->       
	      </div> <!--card-->
	    </div> <!-- col -->
	    
	     <div class="col mb-3 mb-xl-0">
	      <div class="card">
	        <div class="card-body" style="padding:10px;">
	        
	          <div id="boxes" class="d-flex align-items-center">
	            <div class="mr-2"><div class="rounded box box-3 d-flex align-items-center  justify-content-center"><i class="zmdi zmdi-balance-wallet"></i></div></div>
	            <div>
	               <h6 class="m-0 text-muted font-weight-normal">{{label.subscription_month}}</h6>
	               <h6 class="m-0 position-relative" ref="subscription_month">0
	                  <div class="skeleton-placeholder" style="height:17px;width:100%;"></div>
	               </h6>
	            </div>
	          </div><!--flex-->       
	          
	        </div> <!--card body-->       
	      </div> <!--card-->
	    </div> <!-- col -->
	    
	  </div> <!--row--> 
    `};const Ot={props:["ajax_url","label","orders_tab","limit"],data(){return{data:[],active_tab:"all",is_loading:false,data_failed:[]}},mounted(){this.getLastOrder(false);setInterval(()=>this.getLastOrder(true),6e4)},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{setTab(t){this.active_tab=t;this.getLastOrder()},getLastOrder(e){if(!e){this.is_loading=true}axios({method:"POST",url:this.ajax_url+"/getLastTenOrder",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&filter_by="+this.active_tab+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[];this.data_failed=t.data}})["catch"](t=>{}).then(t=>{if(!e){this.is_loading=false}})},viewCustomer(t){this.$emit("viewCustomer",t)}},template:`	
	
	 <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	
    <div class="card ">
    <div class="card-body">
	 
	  <div class="row">
        <div class="col  mb-3 mb-xl-0">        
          <h5 class="m-0">{{label.title}}</h5>   
          <p class="m-0 text-muted">{{label.sub_title}}</p>        
        </div>
        <div class="col ">

		  <div class="d-none d-sm-block">
          <ul class="nav nav-pills justify-content-md-end justify-content-sm-start">			  
			  <li v-for="(item, key) in orders_tab" class="nav-item">
			    <a @click="setTab(key)" :class="{active : active_tab==key}" class="nav-link py-1 px-3">{{item}}</a>
			  </li>			  
		  </ul>
		  </div>

		  <div class="d-block d-sm-none text-right">
		  
		  <div class="dropdown btn-group dropleft">
		  <button class="btn btn-sm dropdown-togglex dropleft" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		  <i class="zmdi zmdi-more-vert"></i>
		  </button>
		   <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		     <template v-for="(item, key) in orders_tab" >
			 <a class="dropdown-item"  @click="setTab(key)" :class="{active : active_tab==key}"  >{{item}}</a>			
			 </template>
		    </div>
		  </div>

		  </div> <!-- small -->

        </div>
      </div>  
      <!--row-->	
                 	 
     <div class="mt-3 table-orders table-responsive">
         <table class="table">
          <thead>
           <tr>
             <th class="p-0 mw-200"></th>
             <th class="p-0 mw-200"></th>
             <th class="p-0 mw-200"></th>
             <th class="p-0 mw-200"></th>
             <th class="p-0 mw-200"></th>
           </tr>
          </thead>
          <tbody>
          <tr v-for="item in data">
             <td class="pl-0 align-middle">             
             
                 <div class="d-flex align-items-center">
                    <div class="mr-2">
                      <div  v-if="item.is_view==0" class="blob green mb-1"></div>
                      <div  v-if="item.is_critical==1" class="blob red"></div>
                    </div>
                    <div>
                       <div><a :href="item.view_order" class="font-weight-bold hover-text-primary mb-1">{{item.order_id}}</a></div>
                       <div><a @click="viewCustomer(item.client_id)" class="text-muted font-weight-bold hover-text-primary" href="javascript:;">{{item.customer_name}}</a></div>
                       <div class="text-muted font11">{{item.date_created}}</div>
                    </div>
                 </div>                                  
            </td>
           <td width="15%" class="text-left align-middle">
              {{item.restaurant_name}}
           </td>
           <td class="text-right align-middle">
                <span class="font-weight-bold d-block">{{item.total}}</span>
                <span class="badge payment"  :class="item.payment_status_raw" >{{item.payment_status}}</span>
            </td> 
            <td class="text-right align-middle">
			    <div>{{item.order_type}}</div>
                <span class="text-muted font-weight-500">{{item.payment_code}}</span>
            </td>
            <td class="text-right align-middle">
                <span class="badge order_status " :class="item.status_raw">{{item.status}}</span>
            </td>
            <td class="text-right align-middle pr-0">
                <a :href="item.view_order" class="btn btn-sm text-muted btn-light hover-bg-primary hover-text-secondary py-1 px-3 mr-2">
                    <i class="zmdi zmdi-eye"></i>
                </a>
                <a :href="item.print_pdf" target="_blank" class="btn btn-sm text-muted btn-light hover-bg-primary hover-text-secondary py-1 px-3">
                    <i class="zmdi zmdi-download"></i>
                </a>
            </td>
          </tr>
          </tbody>
         </table>
     </div>          
     
     <div v-if="!hasData" class="fixed-height40 text-center justify-content-center d-flex align-items-center">
	     <div class="flex-col">
	      <img v-if="data_failed.details" class="img-300" :src="data_failed.details.image_url" />
	      <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
	     </div>     
	  </div>  
     
     </div><!-- card body -->
    </div> <!--card-->
	`};const Ct={props:["ajax_url","label","period"],data(){return{data:[],data_items:[],data_failed:[],is_loading:false,code:null}},mounted(){this.itemSales()},methods:{itemSales(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/itemSales",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&period="+this.period,timeout:s}).then(t=>{this.code=t.data.code;if(t.data.code==1){this.data=t.data.details.sales;this.data_items=t.data.details.items;this.initChart()}else{this.data=[];this.data_items=[];this.data_failed=t.data;this.is_loading=false}})["catch"](t=>{this.is_loading=false}).then(t=>{})},initChart(){if(window.Highcharts==null){new Promise(t=>{const e=window.document;const a="chart-script";const i=e.createElement("script");i.id=a;i.setAttribute("src","https://code.highcharts.com/highcharts.js");e.head.appendChild(i);i.onload=()=>{t()}}).then(()=>{this.renderChart()})}else{this.renderChart()}},renderChart(){Highcharts.chart(this.$refs.chart,{chart:{type:"column",height:"60%",events:{load:()=>{setTimeout(()=>{this.is_loading=false},500)}}},title:{text:""},xAxis:{categories:this.data.category,crosshair:true},yAxis:{min:0,title:{text:""}},tooltip:{headerFormat:'<span style="font-size:10px">{point.key}</span><table>',pointFormat:'<tr><td style="color:{series.color};padding:0"></td>'+'<td style="padding:0"><b>{point.y:.1f} '+this.label.sold+"</b></td></tr>",footerFormat:"</table>",shared:true,useHTML:true},plotOptions:{column:{pointPadding:.2,borderWidth:0}},series:[{name:"Sales",showInLegend:false,color:"#3ecf8e",data:this.data.data}]})}},template:`	
    
    <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
    
    <div class="card ">
    <div class="card-body">
           
	    <div ref="chart"></div>
	    	    
	    <div class="row ml-2 mt-4">
	      <div v-for="item in data_items" class="col-md-4 mb-2">
	        <div class="d-flex">
	          <div class="mr-1 w-25"><b>{{item.total_sold}}</b></div>
	          <div>{{item.item_name}}</div>
	        </div>
	      </div> <!-- col -->
	    </div> <!-- row -->
	    
	    <div v-if="code==2" class="fixed-height40 text-center justify-content-center d-flex align-items-center">
		    <div class="flex-col">
		     <img v-if="data_failed.details" class="img-300" :src="data_failed.details.image_url" />
	         <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
		    </div>     
		 </div> 
  
	</div> <!--card-body-->
    </div> <!--card--> 
    
    `};const Et={components:{"components-item-sales":Ct},props:["ajax_url","label","limit","item_tab"],data(){return{data:[],is_loading:false,data_failed:[],currentTab:"item_overview"}},mounted(){this.mostPopularItems()},computed:{hasData(){if(this.data.length>0){return true}return false}},updated(){},methods:{mostPopularItems(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/mostPopularItems",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[];this.data_failed=t.data}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	    
    
     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	 
    <div class="card">
    <div class="card-body">
    
    <div class="row">
        <div class="col">        
          <h5 v-if="item_tab[currentTab]" class="m-0">{{item_tab[currentTab].title}}</h5>   
          <p  v-if="item_tab[currentTab]" class="m-0 text-muted">{{item_tab[currentTab].sub_title}}</p>        
        </div>
        <div class="col">
		  <div class="d-none d-sm-block">
          <ul class="nav nav-pills justify-content-end">			  
			  <li v-for="(item, key) in item_tab" class="nav-item">
			    <a @click="currentTab=key"  :class="{active : currentTab==key}" class="nav-link py-1 px-3">{{item.title}}</a>
			  </li>			  
		  </ul>
		  </div>

		  <div class="d-block d-sm-none text-right">
		  
		  <div class="dropdown btn-group dropleft">
		  <button class="btn btn-sm dropdown-togglex dropleft" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		  <i class="zmdi zmdi-more-vert"></i>
		  </button>
		   <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		     <template v-for="(item, key) in item_tab"  >
			 <a class="dropdown-item" @click="currentTab=key"  :class="{active : currentTab==key}"  >{{item.title}}</a>			
			 </template>
		    </div>
		  </div>

		  </div>
		  <!-- dropdown -->

        </div>
      </div>  
      <!--row-->	
                      
      <template v-if="currentTab=='item_overview'">
      <div class="mt-3 table-item">
        <table class="table">
          <thead>
           <tr>
             <th class="p-0 mw-200"></th>
             <th class="p-0 mw-200"></th>             
           </tr>
          </thead>
          <tbody>
            <tr v-for="item in data">
             <td class="text-left align-middle">
               <div class="d-flex  align-items-center"> 
                 <div class="mr-3">
                    <a ><img :src="item.image_url" class="img-60 rounded-circle"></a>
                 </div>
                 <div class="flex-col">
                    <a class="font-weight-bold hover-text-primary mb-1 text-green">{{item.item_name}}</a>
                    <p class="m-0 text-muted">{{item.category_name}}</p>                    
                    <p class="m-0 text-muted"><b>{{item.restaurant_name}}</b></p>
                 </div>
               </div> <!--flex-->
             </td>
             <td class="text-right align-middle">
               <p class="m-0 text-muted">{{item.total_sold}}</p>
             </td>
             </tr>            
          </tbody>
        </table>
       </div> 
       </template>
       
       <template v-else>
       <components-item-sales
         :ajax_url="ajax_url"
         period="30"
         :label="label"
       >
       </components-item-sales>
       </template>            
        
        <div v-if="!hasData" class="fixed-height40 text-center justify-content-center d-flex align-items-center">
		    <div class="flex-col">
		     <img v-if="data_failed.details" class="img-300" :src="data_failed.details.image_url" />
		     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
		    </div>     
		 </div>  
       
    </div> <!--card body-->
   </div> 
   <!--card-->
    
    `};const Nt={props:["ajax_url","label","limit","months"],data(){return{data:[],is_loading:false,data_failed:[],code:null}},mounted(){this.salesOverview()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{salesOverview(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/salesOverview",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&months="+this.months,timeout:s}).then(t=>{this.code=t.data.code;if(t.data.code==1){this.data=t.data.details;this.initChart()}else{this.data=[];this.is_loading=false;this.data_failed=t.data}})["catch"](t=>{this.is_loading=false}).then(t=>{})},initChart(){if(window.Highcharts==null){new Promise(t=>{const e=window.document;const a="chart-script";const i=e.createElement("script");i.id=a;i.setAttribute("src","https://code.highcharts.com/highcharts.js");e.head.appendChild(i);i.onload=()=>{t()}}).then(()=>{this.renderChart()})}else{this.renderChart()}},renderChart(){Highcharts.chart(this.$refs.chart_sales,{chart:{type:"column",height:18/16*100+"%",events:{load:()=>{setTimeout(()=>{this.is_loading=false},500)}}},title:{text:""},xAxis:{categories:this.data.category,crosshair:true},yAxis:{min:0,title:{text:""}},tooltip:{headerFormat:'<span style="font-size:10px">{point.key}</span><table>',pointFormat:'<tr><td style="color:{series.color};padding:0">{series.name}: </td>'+'<td style="padding:0"><b>{point.y:.1f} '+this.label.sales+"</b></td></tr>",footerFormat:"</table>",shared:true,useHTML:true},plotOptions:{column:{pointPadding:.2,borderWidth:0}},series:[{name:"Sales",showInLegend:false,data:this.data.data}]})}},template:`	
     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>       	
	 
    <div class="card mb-3">
    <div class="card-body">
    
       <h5 class="m-0 mb-3">{{label.sales_overview}}</h5>    
        
	    <div ref="chart_sales"></div>
	            
	    <div v-if="code==2" class="fixed-height15 text-center justify-content-center d-flex align-items-center">
		    <div class="flex-col">
		     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
		    </div>     
		 </div> 
  
	</div> <!--card-body-->
    </div> <!--card--> 
    `};const jt={props:["ajax_url","label","limit"],data(){return{data:[],is_loading:false,data_failed:[]}},mounted(){this.mostPopularCustomer()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{mostPopularCustomer(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/mostPopularCustomer",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[];this.data_failed=t.data}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewCustomer(t){this.$emit("viewCustomer",t)}},template:`	
     
     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	 
      <div class="card">
	    <div class="card-body">
	       <h5 class="m-0 mb-3">{{label.title}}</h5>    
	       
	        <table class="table">
	          <thead>
	           <tr>
	             <th class="p-0 mw-200"></th>
	             <th class="p-0 mw-200"></th>             
	           </tr>
	          </thead>
	          <tbody>
	            <tr v-for="item in data">
	             <td class="text-left align-middle">
	               <div class="d-flex  align-items-center"> 
	                 <div class="mr-3">
	                   <a @click="viewCustomer(item.client_id)">
	                   <img :src="item.image_url" class="img-60 rounded-circle">
	                   </a>
	                 </div>
	                 <div class="flex-col">
	                    <a @click="viewCustomer(item.client_id)" href="javascript:;" class="font-weight-bold hover-text-primary mb-1">{{item.first_name}} {{item.last_name}}</a>
	                    <div><small class="text-muted">{{item.member_since}}</small></div>
	                 </div>
	               </div> <!--flex-->
	             </td>
	             <td class="text-right align-middle">
	               <p class="m-0 text-muted">{{item.total_sold}}</p>
	             </td>
	             </tr>             
	          </tbody>
	        </table>
	        
	        <div v-if="!hasData" class="fixed-height15 text-center justify-content-center d-flex align-items-center">
			    <div class="flex-col">			     
			     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
			    </div>     
			 </div>  
	       
	    </div> <!--card body-->
	   </div> 
	   <!--card-->
    `};const Ft={props:["ajax_url","label","limit"],data(){return{data:[],is_loading:false}},mounted(){this.latestReview()},methods:{latestReview(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/OverviewReview",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewCustomer(t){this.$emit("viewCustomer",t)}},template:`	
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	 	  
      <div class="card">
	    <div class="card-body">
	    
	       <h5 class="m-0 mb-3">{{label.title}}</h5>   
	       	        
	       <h2 class="font-medium mt-2 mb-0">{{data.total}}</h2>
	       <span class="text-muted">{{data.this_month_words}}</span>
	       
	       <div class="image-box mt-3 mb-3"> 
	         <a v-for="user in data.user"  @click="viewCustomer(user.client_id)" class="mr-2" data-toggle="tooltip" data-placement="top" :title="user.first_name" :data-original-title="user.first_name">
	           <img :src="user.image_url" class="rounded-circle img-40" alt="user">
	         </a> 	         
	       </div>
	       
	       <div class="graph-rating-body mb-3">	        
	       
	        <div v-for="item in data.review_summary" class="rating-list mb-1">  
	          <div class="d-flex justify-content-between align-items-center">
	            <div class="flex-col font11">{{item.count}} {{label.star}}</div>
	            <div class="flex-col font11">{{item.in_percent}}</div>
	          </div>
	          <div class="progress">
			    <div class="progress-bar" role="progressbar" 
			    :style="{ width: item.review + '%' }"
			    :aria-valuenow="item.review" aria-valuemin="0" aria-valuemax="100"></div>
			  </div>
			</div> <!-- rating-list -->
						
	       </div> <!-- graph-rating-body -->
	       	       
	      
	      <a :href="data.link_to_review" class="w-100 btn btn-lg btn-info waves-effect waves-light mb-3">
	      {{label.all_review}}
	      </a> 	      
	       
	   </div> <!--card body-->
	  </div> <!--card-->      
    `};const Rt={props:["ajax_url","label","limit"],data(){return{data:[],is_loading:false,cuisine_list:[],data_failed:[]}},mounted(){this.PopularMerchantByReview()},computed:{hasData(){if(typeof this.data.data!=="undefined"&&this.data.data!==null){if(this.data.data.length>0){return true}}return false}},methods:{PopularMerchantByReview(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/PopularMerchantByReview",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;this.cuisine_list=t.data.details.cuisine_list}else{this.data=[];this.data_failed=t.data;this.cuisine_list=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    
        <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
		      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
		    </div>
		 </div>   
       
	   <div class="table-responsive-md">
       <table class="table mt-3 table-item">
          <thead>
           <tr>
             <th class="p-0"></th>
             <th class="p-0" width="20%"></th>             
           </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.data" >
             <td class="text-left align-middle">
               <div class="d-flex  align-items-center"> 
                 <div class="mr-3">
                    <a ><img :src="item.image_url" class="img-60 rounded"></a>
                 </div>
                 <div class="flex-col w-70 text-truncate cuisine-truncate">
                    <a :href="item.view_merchant" class="font-weight-bold hover-text-primary mb-1 text-green">{{item.restaurant_name}}</a>                              
                    <p v-if="cuisine_list[item.merchant_id]" class="text-truncate m-0">
                      <span v-for="cuisine in cuisine_list[item.merchant_id]" class="a-12 mr-1">{{cuisine}},</span>                      
                    </p>         
                    <p  class="m-0"><b class="mr-1">{{item.review_count}}</b><i class="zmdi zmdi-star mr-1 text-grey"></i><u>{{item.ratings}}+ ratings</u></p>                                 
                 </div>
               </div> <!--flex-->
             </td>
             <td class="text-right align-middle">
             {{item.ratings}}
             </td>
             </tr>            
          </tbody>
        </table>
		</div>
    
        <template v-if="!is_loading">
         <div v-if="!hasData" class="fixed-height35 text-center justify-content-center d-flex align-items-center">
		    <div class="flex-col">
		     <img v-if="data_failed.details" class="img-300" :src="data_failed.details.image_url" />
		     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
		    </div>     
		 </div>   
		</template>
		 
    `};const Yt={components:{"components-merchant-popular-review":Rt},props:["ajax_url","label","limit","item_tab"],data(){return{data:[],data_failed:[],is_loading:false,currentTab:"popular",cuisine_list:[]}},mounted(){this.popularMerchant()},computed:{hasData(){if(typeof this.data.data!=="undefined"&&this.data.data!==null){if(this.data.data.length>0){return true}}return false}},methods:{popularMerchant(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/popularMerchant",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;this.cuisine_list=t.data.details.cuisine_list}else{this.data=[];this.data_failed=t.data;this.cuisine_list=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	 	 
	<div class="card">
    <div class="card-body">
    
     <div class="row">
        <div class="col">        
          <h5 v-if="item_tab[currentTab]" class="m-0">{{item_tab[currentTab].title}}</h5>   
          <p  v-if="item_tab[currentTab]" class="m-0 text-muted">{{item_tab[currentTab].sub_title}}</p>        
        </div>
        <div class="col">
		  
		  <div class="d-none d-sm-block">
          <ul class="nav nav-pills justify-content-end">			  
			  <li v-for="(item, key) in item_tab" class="nav-item">
			    <a @click="currentTab=key"  :class="{active : currentTab==key}" class="nav-link py-1 px-3">{{item.title}}</a>
			  </li>			  
		  </ul>
		  </div>

		  <div class="d-block d-sm-none text-right">

		  <div class="dropdown btn-group dropleft">
		  <button class="btn btn-sm dropdown-togglex dropleft" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		  <i class="zmdi zmdi-more-vert"></i>
		  </button>
		   <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		     <template v-for="(item, key) in item_tab"  >
			 <a class="dropdown-item" @click="currentTab=key"  :class="{active : currentTab==key}"   >{{item.title}}</a>			
			 </template>
		    </div>
		  </div>

		  </div>
		  <!-- dropdown -->


        </div> <!-- col -->
      </div>  
      <!--row-->	
      
      
    <div v-if="currentTab=='popular'" class="mt-3 table-item table-responsive">
        <table class="table" >
          <thead>
           <tr>
             <th class="p-0" ></th>
             <th class="p-0" width="20%" ></th>             
           </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.data" >
             <td class="text-left align-middle">
               <div class="d-flex align-items-center"> 
                 <div class="mr-3">
                    <a ><img :src="item.image_url" class="img-60 rounded"></a>
                 </div>
                 <div class="flex-col w-70 text-truncate cuisine-truncate">
                    <a :href="item.view_merchant" class="font-weight-bold hover-text-primary mb-1 text-green">{{item.restaurant_name}}</a>                              
                    <p v-if="cuisine_list[item.merchant_id]" class="text-truncate m-0">
					<span v-for="cuisine in cuisine_list[item.merchant_id]" class="a-12 mr-1">{{cuisine}},</span>                      
                    </p>         
                    <p v-if="item.ratings" class="m-0"><b class="mr-1">{{item.ratings.review_count}}</b><i class="zmdi zmdi-star mr-1 text-grey"></i><u>{{item.ratings.rating}}+ {{label.ratings}}</u></p>                                 
                 </div>
               </div> <!--flex-->
             </td>
             <td class="text-right align-middle">
               <p class="m-0 text-muted">{{item.total_sold_pretty}}</p>
             </td>
             </tr>            
          </tbody>
        </table>
        
         <div v-if="!hasData" class="fixed-height35 text-center justify-content-center d-flex align-items-center">
		    <div class="flex-col">
		     <img v-if="data_failed.details" class="img-300" :src="data_failed.details.image_url" />
		     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
		    </div>     
		 </div>   
        
      </div>   
      
      <template v-else>
       <components-merchant-popular-review
         :ajax_url="ajax_url"         
         :label="label"
       >
       </components-merchant-popular-review>
       </template>     

       
             
      
    
    </div> <!--card body-->
    </div> <!--card--> 
    `};const Pt={props:["ajax_url","label","limit"],data(){return{data:[],is_loading:false}},mounted(){this.DailyStatistic()},methods:{DailyStatistic(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/DailyStatistic",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.stats_order_received,t.data.details.order_received,e);a.start();a=this.count_up=new countUp.CountUp(this.$refs.stats_today_delivered,t.data.details.today_delivered,e);a.start();a=this.count_up=new countUp.CountUp(this.$refs.stats_new_customer,t.data.details.new_customer,e);a.start();var e=t.data.details.price_format;a=this.count_up=new countUp.CountUp(this.$refs.stats_total_refund,t.data.details.total_refund,e);a.start()}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
    
    <div class="row mb-3 ">
          <div class="col  mb-2 mb-xl-0">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="flex-col mr-3"><h1 class="m-0"><i class="zmdi zmdi-store"></i></h1></div>
                     <div class="flex-col">
                       <h3 class="mb-1 text-danger" ref="stats_order_received">0</h3>
                       <h5 class="m-0 text-secondary">{{label.order_received}}</h5>
                     </div>
                  </div>
                </div>
              </div>
          </div> <!--col-->

           <div class="col  mb-2 mb-xl-0">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="flex-col mr-3"><h1 class="m-0"><i class="zmdi zmdi-truck"></i></h1></div>
                     <div class="flex-col">
                       <h3 class="mb-1 text-green" ref="stats_today_delivered">0</h3>
                       <h5 class="m-0 text-secondary">{{label.today_delivered}}</h5>
                     </div>
                  </div>
                </div>
              </div>
          </div> <!--col-->
          
        </div> <!--row-->
        
      </div> <!--relative-->
      
       <div class="dashboard-statistic position-relative mb-3 ">
        
        <div class="row">
          <div class="col mb-2 mb-xl-0">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="flex-col mr-3"><h1 class="m-0"><i class="zmdi zmdi-face"></i></h1></div>
                     <div class="flex-col">
                       <h3 class="mb-1 text-violet" ref="stats_new_customer">0</h3>
                       <h5 class="m-0 text-secondary">{{label.new_customer}}</h5>
                     </div>
                  </div>
                </div>
              </div>
          </div> <!--col-->

           <div class="col mb-2 mb-xl-0">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="flex-col mr-3"><h1 class="m-0"><i class="zmdi zmdi-balance-wallet"></i></h1></div>
                     <div class="flex-col">
                       <h3 class="mb-1 text-orange" ref="stats_total_refund">0</h3>
                       <h5 class="m-0 text-secondary">{{label.total_refund}}</h5>
                     </div>
                  </div>
                </div>
              </div>
          </div> <!--col-->
          
        </div> <!--row-->
    
    `};const $t={props:["ajax_url","label","limit"],data(){return{data:[],data_failed:[],is_loading:false}},mounted(){this.RecentPayout()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{RecentPayout(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/RecentPayout",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[];this.data_failed=t.data}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    
     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>  
	 
    <div class="card">
    <div class="card-body">
       <h5 class="m-0 mb-3">{{label.recent_payout}}</h5>    
       
        <table class="table">
          <thead>
           <tr>
             <th class="p-0 mw-200"></th>
             <th class="p-0 mw-200"></th>             
           </tr>
          </thead>
          <tbody>
            <tr v-for="item in data" >
             <td class="text-left align-middle">	               
               <div class="d-flex  align-items-center"> 
                 <div class="mr-3">
                   <a @click="$emit('viewPayout',item.transaction_uuid)">
                   <img :src="item.image_url" class="img-60 rounded-circle">
                   </a>
                 </div>
                 <div class="flex-col">
                    <a @click="$emit('viewPayout',item.transaction_uuid)" href="javascript:;" class="font-weight-bold hover-text-primary mb-1">
                     {{item.restaurant_name}}
                    </a>	            
                    <div><small class="text-muted">{{item.transaction_date}}</small></div>
                    <div class="badge payment " :class="item.status_class" >{{item.status}}</div>
                 </div>
               </div> <!--flex-->
             </td>
             <td class="text-right align-middle">
               <p class="m-0 text-muted">{{item.transaction_amount_pretty}}</p>
             </td>
             </tr>           
          </tbody>
        </table>
        
         <div v-if="!hasData" class="fixed-height15 text-center justify-content-center d-flex align-items-center">
			    <div class="flex-col">			     
			     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
			    </div>     
			 </div>
       
    </div> <!--card body-->
   </div> 
   <!--card-->
    
    `};const Kt=Vue.createApp({components:{"components-sales-summary":Tt,"components-last-orders":Ot,"components-popular-items":Et,"components-chart-sales":Nt,"components-popular-customer":jt,"components-latest-review":Ft,"components-popular-merchant":Yt,"components-daily-statistic":Pt,"components-recent-payout":$t,"components-customer-details":B,"components-payout-details":E},mounted(){this.getMerchantSummary()},data(){return{data:[],is_load:false,client_id:null}},methods:{getMerchantSummary(){this.is_loading=true;axios({method:"POST",url:api_url+"/dashboardSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_merchant,t.data.details.total_merchant,e);a.start();var e=t.data.details.price_format;var i;i=this.count_up=new countUp.CountUp(this.$refs.summary_commission,t.data.details.total_commission,e);i.start();i=new countUp.CountUp(this.$refs.summary_sales,t.data.details.total_sales,e);i.start();i=new countUp.CountUp(this.$refs.summary_subscriptions,t.data.details.total_subscriptions,e);i.start()}else{}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewCustomer(t){this.client_id=t;setTimeout(()=>{this.$refs.customer.show()},1)},refreshLastOrder(){this.$refs.last_order.getLastOrder();this.$refs.daily_statistic.DailyStatistic()},viewPayout(t){this.$refs.payout.transaction_uuid=t;this.$refs.payout.show()},afterSave(){this.$refs.recent_payout.RecentPayout()}}});const Y=Kt.mount("#vue-dashboard");const Dt={props:["ajax_url","label","menu_id"],data(){return{data:[],pages:[],is_loading:false,add_loading:false,custom_loading:false,custom_link:"",custom_link_text:""}},mounted(){this.getAllPages()},computed:{hasData(){if(this.pages.length>0&&this.menu_id>0){return true}return false},hasLink(){if(!u(this.custom_link)&&!u(this.custom_link_text)){return true}return false}},methods:{getAllPages(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/AllPages",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},addPageToMenu(){this.add_loading=true;axios({method:"PUT",url:this.ajax_url+"/addPageToMenu",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),menu_id:this.menu_id,pages:this.pages},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.pages=[];this.$emit("afterAddpage")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.add_loading=false})},addCustomPageToMenu(){this.custom_loading=true;axios({method:"PUT",url:this.ajax_url+"/addCustomPageToMenu",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),menu_id:this.menu_id,custom_link_text:this.custom_link_text,custom_link:this.custom_link},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterAddpage");this.custom_link_text="";this.custom_link=""}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.custom_loading=false})}},template:`	
        
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	  </div>
    
      <h6 class="mb-2">{{label.title}}</h6>
      
      <div class="accordion mb-3" id="accordionPages">      
       <div class="card">
         <div class="card-header p-0" id="headingOne">
             <button class="btn w-100 text-left" type="button" data-toggle="collapse" data-target="#collapsePages" aria-expanded="true" aria-controls="collapsePages">
               {{label.pages}}
             </button>
         </div>			
         <div id="collapsePages" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionPages">
           <div class="card-body border-left border-right border-bottom">		                           
             <ul class="list-group list-group-flush">  
               <li v-for="(item, index) in data" class="list-group-item p-1 pl-0">      
			      <div class="custom-control custom-checkbox">
			        <input v-model="pages" type="checkbox" class="custom-control-input" :value="index" :id="index" >
			        <label class="custom-control-label" :for="index">{{item}}</label>
			      </div>
			    </li>			    
             </ul>                
           </div> <!-- body -->
           
           <div class="card-footer text-right">		   		   
		     <button @click="addPageToMenu" :disabled="!hasData" type="button" class="btn btn-green normal rounded-0" :class="{ loading: add_loading }">
			  <span>{{label.add_to_menu}}</span>
			  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
			</button> 	            		     
		  </div> <!--card-footer-->
           
         </div> <!-- card -->
       </div> <!-- collapse -->
    </div> <!--accordion-->
    
    
     <div class="accordion" id="accordionCustomlink">      
       <div class="card">
         <div class="card-header p-0" id="headingOne">
             <button class="btn w-100 text-left" type="button" data-toggle="collapse" data-target="#collapseCustomepage" aria-expanded="true" aria-controls="collapseCustomepage">
               {{label.custom_links}}
             </button>
         </div>			
         <div id="collapseCustomepage" class="collapse" aria-labelledby="headingOne" data-parent="#accordionCustomlink">
           <div class="card-body border-left border-right border-bottom">		                           
           
             <div class="form-group">
			    <label for="custom_link">URL</label>
			    <input v-model="custom_link"
			    placeholder="https://"			    
			     type="text" class="form-control" id="custom_link" >  
			  </div>
			  
			  <div class="form-group">
			    <label for="custom_link_text">Link Text</label>
			    <input v-model="custom_link_text"			    
			     type="text" class="form-control" id="custom_link_text" >  
			  </div>
            
           </div> <!-- body -->
           
           <div class="card-footer text-right">		     
		     <button @click="addCustomPageToMenu" :disabled="!hasLink" type="button" class="btn btn-green normal rounded-0" :class="{ loading: custom_loading }">
			  <span>Add to menu</span>
			  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
			</button> 	            		     
		  </div> <!--card-footer-->
           
         </div> <!-- card -->
       </div> <!-- collapse -->
    </div> <!--accordion-->
    `};const Mt={components:{draggable:vuedraggable},props:["ajax_url","label"],data(){return{data:[],current_menu:0,is_loading:false,enabled:true,dragging:false,list:[{name:"John",id:0},{name:"Joao",id:1},{name:"Jean",id:2}]}},mounted(){this.MenuList()},methods:{MenuList(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/MenuList",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.current_menu=t.data.details.current_menu;this.$emit("setCurrentmenu",this.current_menu)}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},createNewMenu(){this.$emit("createNewmenu")},setMenuID(t){this.current_menu=t;this.$emit("setCurrentmenu",this.current_menu)},checkMove(t,e){this.is_loading=true;axios({method:"PUT",url:this.ajax_url+"/sortMenu",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),menu:this.data},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	        	
	 <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>
    
    <div class="mb-2">
      <p class="m-0">{{label.select_menu}} <a @click="createNewMenu" class="text-green">{{label.create_menu}}</a></p>
    </div>        
            
    <div class="menu-categories medium mb-3 d-flex">
	<draggable
	:list="data"
	:disabled="!enabled"
	item-key="name"
	class="list-group"
	tag="transition-group"
	ghost-class="ghost"
	@update="checkMove"
	@start="dragging = true"
	@end="dragging = false"
	v-bind="dragOptions"
	>
	<template #item="{ element, index }">
	<a
	@click="setMenuID(element.menu_id)"
	:class="{active : current_menu==element.menu_id}"
	class="text-center rounded align-self-center text-center">	
	<p class="m-0 mt-1 text-truncate">{{element.menu_name}}</p>
	</a>
    </template>
    </div>
    `};const zt={components:{draggable:vuedraggable},props:["ajax_url","label","current_menu"],data(){return{data:[],is_loading:false,menu_name:"",child_menu:[],create_loading:false,delete_loading:false,remove_loading:false,enabled:true,dragging:false}},mounted(){},computed:{dragOptions(){return{animation:200,group:"description",disabled:false,ghostClass:"ghost"}}},watch:{current_menu(t,e){if(t>0){this.getMenuDetails()}else{this.menu_name="";this.child_menu=[]}}},methods:{createMenu(){this.create_loading=true;axios({method:"PUT",url:this.ajax_url+"/createMenu",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),menu_name:this.menu_name,menu_id:this.current_menu,child_menu:this.child_menu},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterSavemenu")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.create_loading=false})},deleteMenu(){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.delete_confirmation+"</h5>"+"<p>"+this.label.are_you_sure+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label["delete"],className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.delete_loading=true;axios({method:"POST",url:this.ajax_url+"/deleteMenu",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&menu_id="+this.current_menu,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterSavemenu")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.delete_loading=false})}}})},getMenuDetails(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getMenuDetails",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&current_menu="+this.current_menu,timeout:s}).then(t=>{if(t.data.code==1){this.menu_name=t.data.details.menu_name;this.child_menu=t.data.details.data}else{this.menu_name="";this.child_menu=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},removeChildMenu(t){this.remove_loading=true;axios({method:"POST",url:this.ajax_url+"/removeChildMenu",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&menu_id="+t,timeout:s}).then(t=>{if(t.data.code==1){this.getMenuDetails()}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.remove_loading=false})}},template:`	
    
    
   <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
      <div>
       <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
      </div>
    </div>
	  
    <h6 class="mb-2">{{label.title}}</h6>
    
    <div class="card">
	    <div class="card-header border-top border-right border-left">
	    		
	      <div class="row align-items-center">
	       <div class="col">		        
	        <div class="d-flex">
		      <div class="mr-2">{{label.menu_name}}</div>
		      <div><input v-model="menu_name" type="text"></div>		      
		    </div>   		       
	       </div> <!--col-->
	       <div class="col text-right">
	          <template v-if="current_menu>0">	  			  
	                    
	            <button @click="createMenu" type="button" class="btn btn-green normal rounded-0 mr-2" :class="{ loading: create_loading }">
				  <span>{{label.save_menu}}</span>
				  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
				</button> 	            
					            
	            <button @click="deleteMenu" type="button" class="btn btn-link normal rounded-0 text-green" :class="{ loading: delete_loading }">
				  <span>{{label.delete}}</span>
				  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
				</button> 	            
	            
	          </template>
	          <template v-else>	    	                 
	            <button @click="createMenu" type="button" class="btn btn-green normal rounded-0" :class="{ loading: create_loading }">
				  <span>Create menu</span>
				  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
				</button> 	            
				
				<button @click="$emit('afterCancelmenu')" type="button" class="btn btn-link normal rounded-0 text-green" >
				  <span>Cancel</span>				  
				</button> 	            
				
	          </template>
	          
	       </div>
	      </div> <!-- row-->
	    
	    </div> <!--card-header-->
	    
	    <div class="card-body border-left border-bottom border-right">	      

		
	       <p class="font11 text-muted">{{label.drag}}</p>
	       
	       <draggable
	        :list="child_menu"
	        :disabled="!enabled"
	        item-key="name"
	        class="list-group"
	        tag="transition-group"
	        ghost-class="ghost"
	        :move="checkMove"
	        @start="dragging = true"
	        @end="dragging = false"
	        v-bind="dragOptions"
	        >
	        <template #item="{ element, index }">
		        <div class="accordion mb-3"  :class="{ 'not-draggable': !enabled }" :id="'child_accordion_'+element.menu_id">		     
			      <div class="card">		      
				    <div class="card-header p-0" id="headingOne">
				        <button class="btn w-100 text-left" type="button" data-toggle="collapse" :data-target="'#page' + element.menu_id" aria-expanded="true" aria-controls="page1">
				          <b>{{element.menu_name}}</b>
				        </button>
				    </div>				
				    <div :id="'page' + element.menu_id" class="collapse" aria-labelledby="headingOne" :data-parent="'#child_accordion_'+element.menu_id">
				      <div class="card-body border-left border-bottomx border-right">
				      
				          <div class="form-group">
						    <label :for="element.menu_id">Navigation Label</label>
						    <input v-model="child_menu[index].menu_name"  type="text" class="form-control" :id="element.menu_id" >  
						  </div>				      
						  
				      </div> <!--body-->
				      
				      <div class="card-footer p-0">
					    <button @click="removeChildMenu(element.menu_id)" 
					    class="btn btn-link normal text-green"
					    :class="{ loading: remove_loading }"
					    >
					       <span>Remove	</span>
					       <div class="m-auto circle-loader" data-loader="circle-side"></div> 
					    </button>				    
					  </div> <!--card-footer-->
					  
				    </div> <!--page1-->
				  </div> <!--card-->			     
			     </div>  
			    <!--accordion-->
	        </template>
	       </draggable>    
		    
	    </div> <!--card-body-->
	    
	</div> <!--card-->    
    `};const Ut=Vue.createApp({components:{"components-menu-allpages":Dt,"components-menu-structure":zt,"components-menu-list":Mt},data(){return{current_menu:0}},methods:{setCurrentmenu(t){this.current_menu=t},createNewmenu(){this.current_menu=0},afterCancelmenu(){d("afterCancelmenu");this.$refs.menu_list.MenuList()},afterSavemenu(){d("afterSavemenu");this.$refs.menu_list.MenuList()},afterAddpage(){this.$refs.menu_structure.getMenuDetails()}}});Ut.use(Maska);const At=Ut.mount("#vue-theme-menu");const Lt=Vue.createApp({data(){return{active_tab:"addontab1",is_loading:false,loading_addons:false,loading_activated:false,data:[],data_addons:[]}},mounted(){this.getAddons();this.getAvailableAddons()},computed:{hasData(){if(this.data.length>0){return true}return false},hasAddons(){if(this.data_addons.length>0){return true}return false}},methods:{getAddons(){this.is_loading=true;axios({method:"POST",url:api_url+"/getAddons",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},enabledDisabledAddon(t){this.loading_activated=true;axios({method:"put",url:api_url+"/enableddisabledaddon",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t.uuid,activated:t.activated},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:t.data.details.title,message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:t.data.details.title,message:t.data.msg,type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_activated=false})},getAvailableAddons(){this.loading_addons=true;axios({method:"POST",url:"https://bastisapp.com/activation/addons",data:"",timeout:s}).then(t=>{if(t.data.code==1){this.data_addons=t.data.details.data}else{this.data_addons=[]}})["catch"](t=>{}).then(t=>{this.loading_addons=false})}}});Lt.use(ElementPlus);const Bt=Lt.mount("#vue-addons");const Vt={props:["message","donnot_close"],data(){return{new_message:""}},methods:{show(){c(this.$refs.modal).modal("show")},close(){c(this.$refs.modal).modal("hide")},setMessage(t){this.new_message=t}},template:`
	<div class="modal" ref="modal"  tabindex="-1" role="dialog"  aria-hidden="true"
	data-backdrop="static" data-keyboard="false" 	 
	 >
	   <div class="modal-dialog modal-dialog-centered modal-sm modal-loadingbox" role="document">
	     <div class="modal-content">
	         <div class="modal-body">
	            <div class="loading mt-2">
	              <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	            </div>
	            <p class="text-center mt-2">
	              <div v-if="!new_message">{{message}}</div>
	              <div v-if="new_message">{{new_message}}</div>
	              <div>{{donnot_close}}</div>
	            </p>	            
	         </div>
	       </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->      
	`};const P=Vue.createApp({data(){return{data:[]}},mounted(){this.initProvider()},methods:{initProvider(){try{this.$refs[provider_selected].PaymentRender()}catch(t){console.debug(t)}},notify(t,e){o(t,e)},showLoading(){this.$refs.box.show()},closeLoading(){this.$refs.box.close()}}});if(typeof components_bundle!=="undefined"&&components_bundle!==null){c.each(components_bundle,function(t,e){P.component(t,e)})}P.component("components-loading-box",Vt);P.use(ElementPlus);const Jt=P.mount("#vue-bankregistration");const Wt={props:["label","default_country","only_countries","phone","field_phone","field_phone_prefix"],data(){return{data:[],country_flag:"",mobile_prefix:"",mobile_number:""}},updated(){},mounted(){this.mobile_number=this.phone;this.getLocationCountries()},methods:{getLocationCountries(){var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),default_country:this.default_country,only_countries:this.only_countries};var e=y();t=JSON.stringify(t);n[e]=c.ajax({url:ajaxurl+"/getLocationCountries",method:"PUT",dataType:"json",data:t,contentType:a.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(n[e]!=null){n[e].abort()}}});n[e].done(t=>{if(t.code==1){this.data=t.details.data;this.country_flag=t.details.default_data.flag;this.mobile_prefix=t.details.default_data.phonecode}else{this.data=[];this.country_flag="";this.mobile_prefix=""}});n[e].always(t=>{})},setValue(t){this.country_flag=t.flag;this.mobile_prefix=t.phonecode;this.$refs.ref_mobile_number.focus()}},template:`					
	 <div class="inputs-with-dropdown d-flex align-items-center mb-3" >
	    <div class="dropdown">
		  <button class="dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		    <img v-if="country_flag" :src="country_flag">
		  </button>
		  <div class="dropdown-menu" >		    
		    <a v-for="item in data" @click="setValue(item)"
		    href="javascript:;"  class="dropdown-item d-flex align-items-center">
		      <div class="mr-2">
		        <img :src="item.flag">
		      </div>
		      <div>{{item.country_name}}</div>
		    </a>		    
		  </div>
		</div> <!--dropdown-->
		
		<div class="mr-0 ml-1" v-if="mobile_prefix">+{{mobile_prefix}}</div>
		<input type="hidden" :name="field_phone_prefix" :value="mobile_prefix" />
		<input :name="field_phone" type="text"  v-maska="'###################'"  ref="ref_mobile_number"
		:value="mobile_number"  >
		
	</div> <!--inputs-->
	`};const qt=Vue.createApp({components:{"component-phone":Wt},data(){return{data:[]}}});qt.use(Maska);const Ht=qt.mount("#app-phone");const Gt={props:["label","size","ajax_url","start_value","end_value","time_interval","merchant_id","employment_type"],data(){return{loading:true,is_loading:false,sched_calendar:null,schedule_uuid:"",driver_id:"",car_id:"",date_start:"",time_start:"",time_end:"",instructions:"",zone_id:"",zone_value:"",zone_loading:false,zone_list:[{value:"Option1",label:"Option1"}]}},created(){this.initCalendar()},computed:{hasData(){let t=this.schedule_uuid.length;if(t>0){return true}return false}},mounted(){this.select2Driver();this.select2Car();this.getZoneList()},methods:{newSchedule(){this.clearForm();c(this.$refs.modal_sched).modal("show")},initCalendar(){let t=document.getElementsByTagName("html")[0].getAttribute("lang");t=t.replace("_","-");document.addEventListener("DOMContentLoaded",()=>{this.sched_calendar=new FullCalendar.Calendar(this.$refs.calendar,{locale:t,initialView:"dayGridMonth",headerToolbar:{right:"today prev,next",center:"title",left:"dayGridMonth,timeGridWeek,timeGridDay,listWeek"},selectable:true,unselectAuto:true,loading:t=>{this.loading=t},eventSources:[{url:api_url+"/getDriverSched",extraParams:{merchant_id:this.merchant_id},method:"POST",success:()=>{},failure:()=>{ElementPlus.ElNotification({title:"Error",message:"there was an error while fetching data",type:"warning",position:"bottom-right"})}}],dateClick:t=>{this.clearForm();this.date_start=t.dateStr;c(this.$refs.modal_sched).modal("show")},eventClick:t=>{this.getSchedule(t.event.id)},eventContent:(t,e)=>{let a=t.event.extendedProps;console.debug(a);let i="";i+='<div class="fc-content"><span class="fc-time"></span><span class="fc-title">';i+="<div>"+a.time+"</div>";i+="<div>"+a.plate_number+"</div>";i+='<div class="mytable">';i+='<div class="mycol">';i+='<img class="circle_image" src="'+a.avatar+'">';i+="</div>";i+='<div class="mycol">';i+='<span class="d-inline-block text-truncate font-weight-bold" style="max-width: 90px;">'+a.name+"</span>";i+="<div>"+a.zone_name+"</div>";i+="</div>";i+="</div>";i+="</span></div>";return{html:i}}});this.sched_calendar.render()})},getZoneList(){this.zone_loading=true;axios({method:"put",url:this.ajax_url+"/getZoneList",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.zone_list=t.data.details}else{this.zone_list=[]}})["catch"](t=>{}).then(t=>{this.zone_loading=false})},select2Driver(){let a=this.merchant_id;let i=this.employment_type;c(this.$refs.driver_list).select2({width:"resolve",language:{searching:()=>{return this.label.searching},noResults:()=>{return this.label.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchDriver",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:a,employment_type:i};return JSON.stringify(e)}}})},select2Car(){c(this.$refs.car_list).select2({width:"resolve",language:{searching:()=>{return this.label.searching},noResults:()=>{return this.label.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchCar",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}})},submit(){this.is_loading=true;this.driver_id=c(this.$refs.driver_list).find(":selected").val();this.car_id=c(this.$refs.car_list).find(":selected").val();let t=this.zone_id;if(Object.keys(this.zone_id).length>0){t=this.zone_id.value}axios({method:"put",url:this.ajax_url+"/addSchedule",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),schedule_uuid:this.schedule_uuid,zone_id:t,driver_id:this.driver_id,vehicle_id:this.car_id,date_start:this.date_start,time_start:this.time_start,time_end:this.time_end,instructions:this.instructions},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:this.label.success,message:t.data.msg,position:"bottom-right",type:"success"});this.afterInsert()}else{ElementPlus.ElNotification({title:this.label.error,message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getSchedule(t){this.zone_id="";this.loading=true;axios({method:"put",url:this.ajax_url+"/getSchedule",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),schedule_uuid:t},timeout:s}).then(t=>{if(t.data.code==1){const e=t.data.details;this.clearForm();c(this.$refs.modal_sched).modal("show");this.schedule_uuid=e.sched.schedule_uuid;this.driver_id=e.sched.driver_id;this.car_id=e.sched.car_id;this.date_start=e.sched.date_start;this.time_start=e.sched.time_start;this.time_end=e.sched.time_end;this.instructions=e.sched.instructions;this.zone_id=e.sched.zone_id;setTimeout(()=>{var t=new Option(e.driver.text,e.driver.id,false,false);c(this.$refs.driver_list).append(t).trigger("change");var t=new Option(e.car.text,e.car.id,false,false);c(this.$refs.car_list).append(t).trigger("change");c(this.$refs.driver_list).val(e.driver.id).trigger("change");c(this.$refs.car_list).val(e.car.id).trigger("change")},1)}else{ElementPlus.ElNotification({title:this.label.error,message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})},ConfirmDeleteSchedule(){ElementPlus.ElMessageBox.confirm(this.label.delete_message,this.label.delete_confirm,{confirmButtonText:this.label.ok,cancelButtonText:this.label.cancel,type:"warning"}).then(()=>{this.deleteSchedule()})["catch"](()=>{})},deleteSchedule(){this.delete_loading=false;axios({method:"put",url:this.ajax_url+"/deleteSchedule",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),schedule_uuid:this.schedule_uuid},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:this.label.success,message:t.data.msg,position:"bottom-right",type:"success"});this.afterDelete()}else{ElementPlus.ElNotification({title:this.label.error,message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.delete_loading=false})},afterInsert(){c(this.$refs.modal_sched).modal("hide");this.clearForm();this.sched_calendar.refetchEvents()},afterDelete(){c(this.$refs.modal_sched).modal("hide");this.clearForm();this.sched_calendar.refetchEvents()},clearForm(){this.schedule_uuid="";this.driver_id="";this.car_id="";this.date_start="";this.time_start="";this.time_end="";this.instructions="";this.zone_id="";c(this.$refs.driver_list).val(null).trigger("change");c(this.$refs.car_list).val(null).trigger("change")}},template:`	
	<div v-loading="loading" >
	   <div id="fs-calendar" ref="calendar"></div>
	</div>

	<div ref="modal_sched" class="modal" role="dialog" data-backdrop="static" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
		
	<form @submit.prevent="submit" >	  

    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body position-relative">
	  	  	  		  	 
	  
	 <div class="row">
	    <div class="col">		
		<div class="form-group">  
				<label class="mr-4" >Zone</label>	
				<div>				
				<el-select v-model="zone_id"   placeholder="Select zone">
					<el-option
					v-for="item in zone_list"
					:key="item.value"
					:label="item.label"
					:value="item.value"
					/>
				</el-select>
				</div>
			</div>

		</div>
		<div class="col">
		   <div class="form-group">  
			<label class="mr-4" >Date</label>
			<div> 
			<el-date-picker
				v-model="date_start"
				type="date"
				placeholder="Pick a date"	   	   
				size="default"				
				/>          
			</div>	
		   </div>				 
		</div>
	 </div>
	 
	 <div class="row">
	   <div class="col"> 
			<div class="form-group">  
				<label class="mr-4" >Time start</label>	
				<div>
				<el-time-select
				v-model="time_start"
				:start="start_value"				
				:end="end_value"	
				:step="time_interval"		
				placeholder="Select time"
				/>	 
				</div>
			</div>
	   </div>
	   <div class="col"> 
			<div class="form-group">  
				<label class="mr-4" >Time ends</label>	
				<div>
				<el-time-select
				v-model="time_end"
				:start="start_value"				
				:end="end_value"	
				:step="time_interval"		
				placeholder="Select time"
				/>	 
				</div>
			</div>
	   </div>
	 </div>
	 <!-- row -->
	 	 

	 <div class="form-group">  
		<label class="mr-4" >Select Driver</label> 
		<select ref="driver_list" class="form-control select2-driver"  style="width:100%">	  	  
		</select>
	</div>	

	<div class="form-group">  
	<label class="mr-4" >Select Car</label> 
	<select ref="car_list" class="form-control select2-car"  style="width:100%">	  	  
	</select>
    </div>	

	<div class="form-group">  
	<label class="mr-4" >Instructions</label> 
	<el-input
		v-model="instructions"
		:rows="2"
		type="textarea"
		placeholder="Add instructions to driver"
	/>
    </div>	
  
	  </div>      
      <div class="modal-footer">     	

        <button type="button" class="btn btn-black pl-4 pr-4"  data-dismiss="modal">
          <span>{{label.close}}</span>          
        </button>

		<button v-if="hasData" type="button" class="btn btn-danger pl-4 pr-4" @click="ConfirmDeleteSchedule">
          <span>{{label.delete}}</span>          
        </button>

		<button type="submit"  class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }" >
		<span v-if="!hasData">{{label.save}}</span>
		<span v-else>{{label.update}}</span>
		<div class="m-auto circle-loader" data-loader="circle-side"></div> 
	  </button>
      </div>
      	  

    </div>
  </div>
  </form>
</div>        
	`};const Zt=Vue.createApp({components:{"component-calendar":Gt},data(){return{data:[]}},methods:{newSchedule(){console.debug("newSchedule");this.$refs.appcalendar.newSchedule()}}});let Qt={};if(typeof ElementPlusLocaleLanguage!=="undefined"&&ElementPlusLocaleLanguage!==null){Qt={locale:ElementPlusLocaleLanguage}}Zt.use(ElementPlus,Qt);const Xt=Zt.mount("#app-schedule");const te={props:["label","ajax_url","driver_uuid"],data(){return{loading:false,loading_activity:false,overview_data:[],tableData:[]}},mounted(){this.getDriverOverview();this.getDriverActivity()},methods:{getDriverOverview(){this.loading=true;axios({method:"put",url:this.ajax_url+"/getDriverOverview",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),driver_uuid:this.driver_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.overview_data=t.data.details}else{this.overview_data=[]}})["catch"](t=>{}).then(t=>{this.loading=false})},getDriverActivity(){this.loading_activity=true;axios({method:"put",url:this.ajax_url+"/getDriverActivity",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),driver_uuid:this.driver_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.tableData=t.data.details.data}else{this.tableData=[]}})["catch"](t=>{}).then(t=>{this.loading_activity=false})}},template:"#xtemplate_overview"};const ee=Vue.createApp({components:{"component-driveroverview":te},data(){return{data:[]}}});ee.use(ElementPlus);const ae=ee.mount("#app-driveroverview");const ie={props:["ajax_url","id","label"],data(){return{data:[],loading:true}},created(){this.BookingTimeline()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{BookingTimeline(){this.loading=true;axios({method:"POST",url:this.ajax_url+"/BookingTimeline",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&id="+this.id,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.loading=false})}},template:`	
	<template v-if="loading">
	  <div v-loading="loading" style="min-height:100px;"></div>
	</template>
	<template v-else>

	 <template v-if="!hasData">
	   <div class="d-flex" style="min-height:100px;">
	      <div class="align-self-center text-center text-muted col">
		  {{label.no_data}}
		  </div>
	   </div>
	 </template>

	 <div  class="pre-scrollable" style="max-height: 65vh" v-else>
	    <el-timeline>
		   <template v-for="(activity, index) in data">
		   <el-timeline-item :timestamp="activity.content" placement="top">
		      <p v-if="activity.remarks" class="font12 m-0 p-0">{{activity.remarks}}</p>
		      <p class="font11 m-0 p-0">{{activity.timestamp}}</p>
		   </el-timeline-item>
		   </template>
		</el-timeline>
	 </div>
	
	</template>   
	`};const se=Vue.createApp({components:{"reservation-timeline":ie}});se.use(ElementPlus);const le=se.mount("#vue-booking");const re={props:["ajax_url","label","ref_id"],components:{"components-loading-box":I},methods:{confirm(){ElementPlus.ElMessageBox.confirm(this.label.message,this.label.confirm,{confirmButtonText:this.label.ok,cancelButtonText:this.label.cancel,type:"warning"}).then(()=>{this.$refs.loading_box.show();axios({method:"POST",url:this.ajax_url+"/ClearWalletTransactions",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&ref_id="+this.ref_id,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})})["catch"](()=>{})}},template:`
	<components-loading-box
	ref="loading_box"
	message="Processing"
	donnot_close="don't close this window"
	>
	</components-loading-box>
	`};const de=Vue.createApp({components:{"components-datatable":O,"components-create-adjustment":C,"components-commission-balance":tt,"components-clearwallet":re},methods:{createTransaction(){this.$refs.create_adjustment.show()},afterSave(){this.$refs.datatable.getTableData();this.$refs.balance.getBalance()},clearWallet(){this.$refs.clear_wallet.confirm()}}});de.use(Maska);const oe=de.mount("#vue-driver-wallet");const ne=Vue.createApp({components:{"components-datatable":O,"components-payout-details":E},data(){return{is_loading:false,summary:[],count_up:undefined}},mounted(){this.payoutSummary()},methods:{payoutSummary(){this.is_loading=true;axios({method:"POST",url:api_url+"/cashoutSummary",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.summary=t.data.details.summary;var e={decimalPlaces:0,separator:",",decimal:"."};var a={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){a.suffix=t.data.details.price_format.symbol}else a.prefix=t.data.details.price_format.symbol;this.count_up=new countUp.CountUp(this.$refs.ref_unpaid,this.summary.unpaid,e);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.ref_paid,this.summary.paid,e);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total_unpaid,this.summary.total_unpaid,a);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.ref_total_paid,this.summary.total_paid,a);this.count_up.start()}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewPayoutDetails(t){this.$refs.payout.transaction_uuid=t;this.$refs.payout.show()},afterSave(){d("afterSave");this.$refs.datatable.getTableData();this.payoutSummary()}}});const ce=ne.mount("#vue-cashout");const me=Vue.createApp({data(){return{drawer:false,filterText:"",defaultProps:{children:"children",label:"label"},data:[],loading:false,translation_vendors:undefined}},created(){this.getMenu();this.translation_vendors=JSON.parse(translation_vendor)},watch:{filterText(t,e){this.$refs.treeRef.filter(t)}},methods:{getMenu(){this.loading=true;axios({method:"POST",url:api_url+"/getMenu",data:{YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.loading=false})},filterNode(t,e){if(!t)return true;return e.label.toLowerCase().includes(t)},goLink(t){if(t.link){d(t.link);window.location.href=t.link}}},template:`	
	<el-drawer
	 v-model="drawer"
	 title="I am the title"
	 :with-header="false"
	 direction="rtl"    
	 size="25%"
	 :append-to-body="true"		 
	>	   	   	
	   <template v-if="loading">
	     <div v-loading="loading" style="min-height:200px;"></div>
	   </template>
	   <template v-else>
			<el-input v-model="filterText" :placeholder="translation_vendors.search_sidebar"></el-input>	   
			<el-tree 
			ref="treeRef"
			:data="data" 
			:props="defaultProps"
			default-expand-all	
			:filter-node-method="filterNode"  	   
			empty-text="Menu not found"
			>
			<template #default="{ node, data }">
				<span class="custom-tree-node">
					<span @click="goLink(data)">{{ node.label }}</span>
				</span>
			</template>
			</el-tree>
	   </template>	   
	</el-drawer>
	`});me.use(ElementPlus);const he=me.mount("#vue-search-menu");const ue=Vue.createApp({methods:{showDrawer(){he.drawer=true}},template:`	
	<a href="javascript:;" @click="showDrawer">
	  <i class="zmdi zmdi-search"></i>
    </a>
	`});const _e=ue.mount("#vue-search-toogle");const pe={props:["ajax_url","card_id","return_format"],data(){return{loading:false,balance:0}},created(){this.getPointsBalance()},methods:{getPointsBalance(){this.loading=true;axios({method:"POST",url:api_url+"/getPointsBalance",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&card_id="+this.card_id+"&return_format="+this.return_format,timeout:s}).then(t=>{if(t.data.code==1){this.balance=t.data.details.balance}else{this.balance=0}})["catch"](t=>{}).then(t=>{this.loading=false})}},template:`<div v-loading="loading">{{balance}}</div>`};const fe=Vue.createApp({components:{"components-datatable":O,"components-create-adjustment":C,"components-points-balance":pe},data(){return{balance:0,loading:false}},methods:{createTransaction(){this.$refs.create_adjustment.show()},afterSave(){this.$refs.points_balance.getPointsBalance();this.$refs.datatable.getTableData()}}});fe.use(ElementPlus);const ve=fe.mount("#vue-user-rewards");const ge=Vue.createApp({data(){return{loading:false,submit_loading:false,data_type:"",data_table:"",data_type_list:[],data_table_list:[],table_prefix:"mt",activeNames:"1"}},created(){this.getMigrationAttr()},methods:{getMigrationAttr(){this.loading=true;axios({method:"POST",url:ajaxurl_tools+"/getMigrationAttr",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data_type_list=t.data.details.data_type;this.data_table_list=t.data.details.data_table}})["catch"](t=>{}).then(t=>{this.loading=false})},onSubmit(){this.loading=true;axios({method:"POST",url:ajaxurl_tools+"/ImportData",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&data_type="+this.data_type+"&data_table="+this.data_table+"&table_prefix="+this.table_prefix,timeout:s}).then(t=>{if(t.data.code==1){}if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})}}});ge.use(ElementPlus);const be=ge.mount("#vue-migration");const ye={props:["ajax_url","label","transaction_type_list","action_name","ref_id"],data(){return{is_loading:false,transaction_description:"",transaction_type:"credit",transaction_amount:0,client_id:0}},created(){setTimeout(()=>{this.initSelect()},1e3)},computed:{hasData(){if(!u(this.transaction_description)&&!u(this.transaction_type)&&!u(this.transaction_amount)){return true}return false}},methods:{show(){c(this.$refs.modal_adjustment).modal("show")},close(){c(this.$refs.modal_adjustment).modal("hide")},clear(){this.transaction_description="";this.transaction_type="credit";this.transaction_amount=0},submit(){this.client_id=c(this.$refs.client_id).find(":selected").val();var t={YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content"),transaction_description:this.transaction_description,transaction_type:this.transaction_type,transaction_amount:this.transaction_amount,ref_id:this.ref_id,client_id:this.client_id};this.error=[];this.is_loading=true;axios({method:"put",url:this.ajax_url+"/"+this.action_name,data:t,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.clear();this.close();this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},initSelect(){c(this.$refs.client_id).select2({width:"resolve",language:{searching:()=>{return"searching"},noResults:()=>{return"no_results"}},ajax:{delay:250,url:this.ajax_url+"/searchCustomer",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:c("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}})}},template:`
	<div ref="modal_adjustment" class="modal"
    id="modal_adjustment" data-backdrop="static" 
    tabindex="-1" role="dialog" aria-labelledby="modal_adjustment" aria-hidden="true">
    
	   <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
	     
	     <form @submit.prevent="submit" >
	     
	      <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  
		  <div class="modal-body">

		    <p class="mb-2"><b>{{label.customer_name}}</b></p>
			<div class="form-label-group mb-3">  
			   <select ref="client_id" class="form-control"  style="width:100%">	  	  
			</select>
			</div>
		  
		   <div class="form-label-group">    
		       <input type="text" v-model="transaction_description" id="transaction_description"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="transaction_description">{{label.transaction_description}}</labe>
		   </div>

		   <select ref="transaction_type" v-model="transaction_type"
		     class="form-control custom-select form-control-select mb-3"  >		    
		     <option v-for="(name, key) in transaction_type_list" :value="key">{{name}}</option>		    
		   </select>	   
		   
		   <div class="form-label-group">    
		       <input type="number" v-model="transaction_amount" id="transaction_amount"		       
		       placeholder=""
			   step="0.01"
		       class="form-control form-control-text" >
		       <label for="transaction_amount">{{label.transaction_amount}}</labe>
		     </div>
		  
		   </div> <!-- body -->
		  
		   <div class="modal-footer">     

		   <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.close}}</span>
           </button>
		       
	        <button type="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"
	        :disabled="!hasData"
	        >
	          <span>{{label.submit}}</span>
	          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
	        </button>
	      </div>
		 
	      </form>
		  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->     	       
	`};const xe=Vue.createApp({components:{"components-datatable":O,"components-create-adjustment-digitalwallet":ye},data(){return{loading:false}},methods:{createTransaction(){this.$refs.create_adjustment.show()},afterSave(){this.$refs.datatable.getTableData()}}});xe.use(ElementPlus);const we=xe.mount("#vue-digital-wallet-transaction");const Se=Vue.createApp({components:{"components-datatable":O},data(){return{loading:false,start_date:null,end_date:null,data:null}},mounted(){this.summarySubscriptions()},computed:{getData(){return this.data?this.data.data:null}},methods:{afterSelectdate(t,e){this.start_date=t;this.end_date=e;this.summarySubscriptions()},summarySubscriptions(){this.loading=true;axios({method:"GET",url:api_url+"/summarySubscriptions",params:{start_date:this.start_date,end_date:this.end_date},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=null}})["catch"](t=>{}).then(t=>{this.loading=false})}}});Se.use(ElementPlus);const Ie=Se.mount("#vue-subscribers");const ke=Vue.createApp({data(){return{loading:false,data:null,merchant_uuid:null,history_data:null,credit_limits:null,modal:false,item_limit:0,items_added:0,orders_added:0,order_limit:0,submit:false,features_list:null,subscription_features:null,plan_features:[]}},mounted(){if(typeof merchant_uuid!=="undefined"&&merchant_uuid!==null){this.merchant_uuid=merchant_uuid}this.getMerchantSubscriptions()},computed:{hasData(){return this.data?true:false},getData(){return this.data?this.data:null},hasHistory(){return this.history_data?true:false}},methods:{getMerchantSubscriptions(){this.loading=true;axios({method:"GET",url:api_url+"/getMerchantSubscriptions",params:{merchant_uuid:this.merchant_uuid}}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.history_data=t.data.details.history;this.credit_limits=t.data.details.credit_limits;this.features_list=t.data.details.features_list;this.subscription_features=t.data.details.subscription_features}else{this.data=null;this.history_data=null;this.credit_limits=null;this.subscription_features=null}})["catch"](t=>{}).then(t=>{this.loading=false})},setLimitData(){if(this.credit_limits){this.items_added=this.credit_limits.items_added;this.item_limit=this.credit_limits.item_limit;this.orders_added=this.credit_limits.orders_added;this.order_limit=this.credit_limits.order_limit}if(this.subscription_features){this.plan_features=Object.keys(this.subscription_features).filter(t=>this.subscription_features[t])}else{this.plan_features=[]}},susbcriptionsUpdateLimits(){this.submit=true;let t="";t+="&merchant_uuid="+this.merchant_uuid;t+="&items_added="+this.items_added;t+="&item_limit="+this.item_limit;t+="&orders_added="+this.orders_added;t+="&order_limit="+this.order_limit;t+="&plan_features="+JSON.stringify(this.plan_features);axios({method:"POST",url:api_url+"/susbcriptionsUpdateLimits",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+t}).then(t=>{if(t.data.code==1){this.modal=false;this.data=t.data.details.data;this.history_data=t.data.details.history;this.credit_limits=t.data.details.credit_limits;this.subscription_features=t.data.details.subscription_features;ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.submit=false})},statusColor(t){let e="";switch(t){case"active":e="success";break;case"expired":e="danger";break;case"cancelled":case"payment failed":e="danger";break;case"pending":e="info";break}return e}}});ke.use(ElementPlus);const Te=ke.mount("#merchant-subscriptions");const Oe={template:"#xtemplate_location_addrate",props:["save_action","merchant_id","services_list"],data(){return{modal:false,fee:0,loading:false,rate_id:null,country_id:null,country_list:[],loading_state:false,state_id:null,state_list:[],city_id:null,city_list:[],loading_city:false,area_id:null,area_list:[],loading_area:false,loading_submit:false,minimum_order:0,maximum_amount:0,free_above_subtotal:0,data:[],estimated_time_min:0,estimated_time_max:0,service_type:""}},mounted(){this.fetchCountry("")},methods:{clearForm(){this.rate_id=null;this.state_id=null;this.state_list=[];this.city_id=null;this.city_list=[];this.area_id=null;this.area_list=[];this.fee=0;this.minimum_order=0;this.maximum_amount=0;this.free_above_subtotal=0;this.estimated_time_min=0;this.estimated_time_max=0;this.service_type="";this.fetchCountry("")},fetchCountry(t){this.loading=true;axios({method:"POST",url:api_url+"/fetchCountry",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&q="+t,timeout:s}).then(t=>{if(t.data.code==1){this.country_list=t.data.details.data}else{this.country_list=[]}this.country_id=t.data.details.default_country;if(this.country_id){this.fetchState()}})["catch"](t=>{}).then(t=>{this.loading=false})},OnselectCountry(t){this.state_id=null;this.state_list=[];this.city_id=null;this.city_list=[];this.area_id=null;this.area_list=[];this.fetchState()},fetchState(){this.loading=true;axios({method:"POST",url:api_url+"/fetchState",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&country_id="+this.country_id,timeout:s}).then(t=>{if(t.data.code==1){this.state_list=t.data.details.data}else{this.state_list=[]}})["catch"](t=>{}).then(t=>{this.loading=false})},OnselectState(t){this.city_id=null;this.city_list=[];this.area_id=null;this.area_list=[];this.fetchCity("")},fetchCity(){this.loading_city=true;axios({method:"POST",url:api_url+"/fetchCity",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&state_id="+this.state_id,timeout:s}).then(t=>{if(t.data.code==1){this.city_list=t.data.details.data}else{this.city_list=[]}})["catch"](t=>{}).then(t=>{this.loading_city=false})},OnselectCity(t){this.area_id=null;this.area_list=[];this.fetchArea("")},fetchArea(){this.loading_area=true;axios({method:"POST",url:api_url+"/fetchArea",data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&city_id="+this.city_id,timeout:s}).then(t=>{if(t.data.code==1){this.area_list=t.data.details.data}else{this.area_list=[]}})["catch"](t=>{}).then(t=>{this.loading_area=false})},onSubmit(){this.loading_submit=true;let t="YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&merchant_id="+this.merchant_id;t+="&rate_id="+this.rate_id;t+="&fee="+this.fee;t+="&country_id="+this.country_id;t+="&state_id="+this.state_id;t+="&city_id="+this.city_id;t+="&area_id="+this.area_id;t+="&minimum_order="+this.minimum_order;t+="&maximum_amount="+this.maximum_amount;t+="&free_above_subtotal="+this.free_above_subtotal;t+="&estimated_time_min="+this.estimated_time_min;t+="&estimated_time_max="+this.estimated_time_max;t+="&service_type="+this.service_type;axios({method:"POST",url:api_url+"/"+this.save_action,data:t,timeout:s}).then(t=>{if(t.data.code==1){this.modal=false;this.$emit("afterSaverate");ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});setTimeout(()=>{this.clearForm()},500)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_submit=false})},setRate(t){console.log("setRate",t);this.modal=true;this.data=t;this.rate_id=t.rate_id;this.country_id=t.country_id;this.state_id=t.state_id;this.city_id=t.city_id;this.area_id=t.area_id;this.fee=t.fee;this.minimum_order=t.minimum_order;this.maximum_amount=t.maximum_amount;this.free_above_subtotal=t.free_above_subtotal;this.service_type=t.service_type;this.estimated_time_min=t.estimated_time_min;this.estimated_time_max=t.estimated_time_max;this.fetchCity();this.fetchArea()}}};const Ce=Vue.createApp({components:{"components-datatable":O,"components-addrate":Oe},data(){return{loading:false}},methods:{showAddrate(){this.$refs.ref_addrate.modal=true},afterSaverate(){console.log("afterSaverate");this.$refs.datatable.getTableData()},editRecords(t){this.loading=true;axios({method:"POST",url:api_url+"/"+action_get,data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&rate_id="+t,timeout:s}).then(t=>{if(t.data.code==1){this.$refs.ref_addrate.setRate(t.data.details)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})}}});Ce.use(ElementPlus);const Ee=Ce.mount("#vue-location-rate");const Ne={template:"#xtemplate_range_based",props:["save_action","merchant_id"],data(){return{modal:false,loading:false,loading_submit:false,rd_from:0,rd_to:0,rd_price:0,rd_estimation:"",rd_minimum_order:0,rd_maximum_order:0,rd_free_delivery_threshold:0,id:null}},methods:{onOpenmodal(){console.log("onOpenmodal");this.id=null},onSubmit(){this.loading_submit=true;let t="YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&id="+this.id;t+="&rd_from="+this.rd_from;t+="&rd_to="+this.rd_to;t+="&rd_price="+this.rd_price;t+="&rd_estimation="+this.rd_estimation;t+="&rd_minimum_order="+this.rd_minimum_order;t+="&rd_maximum_order="+this.rd_maximum_order;t+="&rd_free_delivery_threshold="+this.rd_free_delivery_threshold;axios({method:"POST",url:api_url+"/saveRangedistance",data:t}).then(t=>{if(t.data.code==1){this.id=null;this.modal=false;this.$emit("afterSaverate");ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_submit=false})},setData(t){this.id=t.id;this.rd_from=t.distance_from;this.rd_to=t.distance_to;this.rd_price=t.distance_price;this.rd_estimation=t.estimation;this.rd_minimum_order=t.minimum_order;this.rd_maximum_order=t.maximum_order;this.rd_free_delivery_threshold=t.fixed_free_delivery_threshold;this.modal=true}}};const je=Vue.createApp({components:{"components-datatable":O,"components-addrate":Ne},data(){return{loading:false,loading_submit:false,delivery_charges_type:"fixed",opt_contact_delivery:false,free_delivery_on_first_order:false,fixed_price:0,fixed_estimation:"",fixed_minimum_order:0,fixed_maximum_order:0,fixed_free_delivery_threshold:0,bd_base_distance:0,bd_base_delivery_fee:0,bd_price_extra_distance:0,bd_delivery_radius:0,bd_minimum_order:0,bd_maximum_order:0,bd_free_delivery_threshold:0,bd_cap_delivery_charge:0,bd_base_time_estimate:0,bd_base_time_estimate_additional:0}},mounted(){this.getDeliveryManagement()},methods:{showAddrate(){this.$refs.ref_addrate.modal=true},tabClick(t){if(t=="range-based"){this.$refs.datatable.getTableData()}},afterSaverate(){this.$refs.datatable.getTableData()},getDeliveryManagement(){this.loading=true;axios.get(api_url+"/getDeliveryManagement?").then(a=>{if(a.data.code==1){this.delivery_charges_type=a.data.details.delivery_charges_type;this.opt_contact_delivery=a.data.details.opt_contact_delivery;this.free_delivery_on_first_order=a.data.details.free_delivery_on_first_order;if(this.delivery_charges_type=="range-based"){this.$refs.datatable.getTableData()}let t=a.data.details.fixed_data;if(t){this.fixed_price=t.fixed_price;this.fixed_estimation=t.fixed_estimation;this.fixed_minimum_order=t.fixed_minimum_order;this.fixed_maximum_order=t.fixed_maximum_order;this.fixed_free_delivery_threshold=t.fixed_free_delivery_threshold}let e=a.data.details.base_distance_data;if(e){this.bd_base_distance=e.bd_base_distance;this.bd_base_delivery_fee=e.bd_base_delivery_fee;this.bd_price_extra_distance=e.bd_price_extra_distance;this.bd_delivery_radius=e.bd_delivery_radius;this.bd_minimum_order=e.bd_minimum_order;this.bd_maximum_order=e.bd_maximum_order;this.bd_free_delivery_threshold=e.bd_free_delivery_threshold;this.bd_cap_delivery_charge=e.bd_cap_delivery_charge;this.bd_base_time_estimate=e.bd_base_time_estimate;this.bd_base_time_estimate_additional=e.bd_base_time_estimate_additional}}else{}})["catch"](t=>{console.error("Error:",t)}).then(t=>{this.loading=false})},onSubmit(){let t="YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&delivery_charges_type="+this.delivery_charges_type;t+="&opt_contact_delivery="+(this.opt_contact_delivery?1:0);t+="&free_delivery_on_first_order="+(this.free_delivery_on_first_order?1:0);this.sendRequest(t,"saveDeliveryManagement")},saveFixedRate(){let t="YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&fixed_price="+this.fixed_price;t+="&fixed_estimation="+this.fixed_estimation;t+="&fixed_minimum_order="+this.fixed_minimum_order;t+="&fixed_maximum_order="+this.fixed_maximum_order;t+="&fixed_free_delivery_threshold="+this.fixed_free_delivery_threshold;this.sendRequest(t,"saveDeliveryfixedrate")},saveBasedistance(){let t="YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&bd_base_distance="+this.bd_base_distance;t+="&bd_base_delivery_fee="+this.bd_base_delivery_fee;t+="&bd_price_extra_distance="+this.bd_price_extra_distance;t+="&bd_delivery_radius="+this.bd_delivery_radius;t+="&bd_minimum_order="+this.bd_minimum_order;t+="&bd_maximum_order="+this.bd_maximum_order;t+="&bd_free_delivery_threshold="+this.bd_free_delivery_threshold;t+="&bd_cap_delivery_charge="+this.bd_cap_delivery_charge;t+="&bd_base_time_estimate="+this.bd_base_time_estimate;t+="&bd_base_time_estimate_additional="+this.bd_base_time_estimate_additional;this.sendRequest(t,"saveBasedistance")},sendRequest(t,e){if(e=="saveDeliveryManagement"){this.loading_submit=true}else{this.loading=true}axios({method:"POST",url:api_url+"/"+e,data:t}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_submit=false;this.loading=false})},editRecords(t){this.loading=true;axios({method:"POST",url:api_url+"/"+action_get,data:"YII_CSRF_TOKEN="+c("meta[name=YII_CSRF_TOKEN]").attr("content")+"&id="+t,timeout:s}).then(t=>{if(t.data.code==1){this.$refs.ref_addrate.setData(t.data.details)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})}}});je.use(ElementPlus);const Fe=je.mount("#vue-delivery-management");const Re=Vue.createApp({data(){return{loading:false,loading_submit:false,data:[],status:[]}},mounted(){this.getAutomatedStatus()},methods:{addRow(){this.data.push({from:"",to:"",time:""})},removeRow(t){this.data.splice(t,1)},getAutomatedStatus(){this.loading=true;axios.get(api_url+"/getAutomatedStatus").then(t=>{if(t.data.code==1){this.status=t.data.details.status;this.data=t.data.details.data}})["catch"](t=>{console.error("Error:",t)}).then(t=>{this.loading=false})},onSubmit(){this.loading_submit=true;axios.post(api_url+"/saveautomatedstatus",this.data).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_submit=false})},formatTime(t){if(!t)return"";const e=t.replace(/[^0-9]/g,"").slice(0,4);const a=e.slice(0,2);const i=e.slice(2,4);return(a+(i?":"+i:"")).replace(/^(\d{1})$/,"$1")},parseTime(t){return t.replace(/[^0-9:]/g,"")}}});Re.use(ElementPlus);const Ye=Re.mount("#app-automated-status")})(jQuery);