(function(h){"use strict";var d;var P;var n;var c;const s=2e4;const i={form:"application/x-www-form-urlencoded; charset=UTF-8",json:"application/json"};var r=function(t){console.debug(t)};var K=function(t){alert(JSON.stringify(t))};jQuery.fn.exists=function(){return this.length>0};var u=function(t){if(typeof t==="undefined"||t==null||t==""||t=="null"||t=="undefined"){return true}return false};jQuery(document).ready(function(){if(h(".sidebar-nav").exists()){h("ul.sidebar-nav ul > li.active").parent().addClass("open");h(document).on("click","ul li a",function(){h(this).parent().find(".sidebar-nav-sub-menu").slideToggle("fast");if(h(".nice-scroll").exists()){setTimeout(function(){h(".nice-scroll").getNiceScroll().resize()},100)}})}h(document).ready(function(){h(".dropdown").on("show.bs.dropdown",function(){h(this).find(".dropdown-menu").first().stop(true,true).slideDown(150)});h(".dropdown").on("hide.bs.dropdown",function(){h(this).find(".dropdown-menu").first().stop(true,true).slideUp(150)})});if(h(".top-container").exists()){}if(h(".headroom").exists()){var t=document.querySelector(".headroom");var e=new Headroom(t);e.init()}if(h(".headroom2").exists()){var t=document.querySelector(".headroom2");var e=new Headroom(t);e.init()}if(h(".select_two").exists()){h(".select_two").select2({allowClear:false,templateResult:A,theme:"classic"})}if(h(".select_two_ajax").exists()){var a=h(".select_two_ajax").attr("action");h(".select_two_ajax").select2({theme:"classic",language:{searching:function(){return"Searching..."},noResults:function(t){return"No results"}},ajax:{delay:250,url:ajaxurl+"/"+a,type:"POST",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")};return e}}})}if(h(".select_two_ajax2").exists()){var a=h(".select_two_ajax2").attr("action");h(".select_two_ajax2").select2({language:{searching:function(){return"Searching..."},noResults:function(t){return"No results"}},ajax:{delay:250,url:ajaxurl+"/"+a,type:"POST",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")};return e}}})}let i=["Su","Mo","Tu","We","Th","Fr","Sa"];let l=["January","February","March","April","May","June","July","August","September","October","November","December"];if(typeof daysofweek!=="undefined"&&daysofweek!==null){i=JSON.parse(daysofweek)}if(typeof monthsname!=="undefined"&&monthsname!==null){l=JSON.parse(monthsname)}if(h(".datepick").exists()){h(".datepick").each(function(t,s){h(s).daterangepicker({singleDatePicker:true,autoUpdateInput:false,locale:{format:"YYYY-MM-DD",daysOfWeek:i,monthNames:l},autoApply:true},function(t,e,a){h(s).val(t.format("YYYY-MM-DD"))})})}if(h(".datepick2").exists()){var s=h(".datepick2");h(".datepick2").daterangepicker({singleDatePicker:true,autoUpdateInput:false,locale:{format:"YYYY-MM-DD",daysOfWeek:i,monthNames:l},autoApply:true},function(t,e,a){s.val(t.format("YYYY-MM-DD"))})}if(h(".date_range_picker").exists()){var o=h(".date_range_picker");var r=o.data("separator");h(".date_range_picker").daterangepicker({autoUpdateInput:false,showWeekNumbers:true,alwaysShowCalendars:true,autoApply:true,locale:{format:"YYYY-MM-DD",daysOfWeek:i,monthNames:l},ranges:{Today:[moment(),moment()],Yesterday:[moment().subtract(1,"days"),moment().subtract(1,"days")],"Last 7 Days":[moment().subtract(6,"days"),moment()],"Last 30 Days":[moment().subtract(29,"days"),moment()],"This Month":[moment().startOf("month"),moment().endOf("month")],"Last Month":[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")]}},function(t,e,a){o.val(t.format("YYYY-MM-DD")+" "+r+" "+e.format("YYYY-MM-DD"))})}if(h(".timepick").exists()){let t=!u(website_twentyfour_format)?website_twentyfour_format:false;let e=t==1?"HH:mm":"hh:mm A";h(".timepick").datetimepicker({format:e})}if(h(".tool_tips").exists()){h(".tool_tips").tooltip()}if(h(".colorpicker").exists()){h(".colorpicker").spectrum({type:"component",showAlpha:false})}h("#frm_search").submit(function(t){d.search(h(".search").val()).draw();h(".search_close").show()});h(document).on("click",".search_close",function(){h("#frm_search").find(".search").val("");d.search("").draw();h(".search_close").hide()});h("#frm_search_app").submit(function(t){Z();g();h(".search_close_app").show()});h(document).on("click",".search_close_app",function(){h("#frm_search_app").find(".search").val("");h(".search_close_app").hide();Z(true);g()});h(".frm_search_filter").submit(function(t){h(".search_close_filter").show();d.destroy();d.clear();m(h(".table_datatables"),h(".frm_datatables"))});h(document).on("click",".search_close_filter",function(){h(".frm_search_filter").find(".search,.date_range_picker").val("");h(".search_close_filter").hide();d.destroy();d.clear();m(h(".table_datatables"),h(".frm_datatables"))});h(".image_file").on("change",function(){var t=h(this).val().split("\\").pop();h(this).siblings(".image_label").addClass("selected").html(t)});h(".image2_file").on("change",function(){var t=h(this).val().split("\\").pop();h(this).siblings(".image2_label").addClass("selected").html(t)});if(h(".mask_time").exists()){h(".mask_time").mask("00:00")}if(h(".mask_minutes").exists()){h(".mask_minutes").mask("00")}if(h(".mask_phone").exists()){h(".mask_phone").mask("(00) 0000-0000")}if(h(".mask_mobile").exists()){let t="+000000000000";if(typeof backend_phone_mask!=="undefined"&&backend_phone_mask!==null){t=backend_phone_mask}h(".mask_mobile").mask(t)}if(h(".card_number").exists()){h(".card_number").mask("0000 0000 0000 0000")}if(h(".card_expiration").exists()){h(".card_expiration").mask("00/00")}if(h(".card_cvv").exists()){h(".card_cvv").mask("000")}if(h(".estimation").exists()){h(".estimation").mask("00-00")}if(h(".mask_date").exists()){h(".mask_date").mask("0000/00/00")}if(h(".summernote").exists()){h(".summernote").summernote({height:200,toolbar:[["font",["bold","underline","italic","clear"]],["para",["ul","ol","paragraph"]],["style",["style"]],["color",["color"]],["table",["table"]],["insert",["link","picture","video"]],["view",["fullscreen","undo","redo"]]]})}if(h(".copy_text_to").exists()){h(".copy_text_to").keyup(function(t){var e=h(this).val();var a=h(this).data("id");e=z(e);h(a).val(e)})}if(typeof is_mobile!=="undefined"&&is_mobile!==null){P=is_mobile}if(!P){if(h(".nice-scroll").exists()){h(".nice-scroll").niceScroll({autohidemode:true,horizrailenabled:false})}}h(".sidebar-panel").slideAndSwipe();h(document).on("click",".hamburger",function(){h(this).toggleClass("is-active")});h(document).on("click",function(t){if(h(t.target).closest(".hamburger").length===0){if(h(".hamburger").hasClass("is-active")){h(".hamburger").removeClass("is-active")}}});h(document).on("click",".checkbox_select_all",function(){h(this).toggleClass("checked");if(!h(this).hasClass("checked")){h(".checkbox_child").prop("checked",false)}else{h(".checkbox_child").prop("checked",true)}});h(".item_multi_options").on("change",function(){D(h(this).val())});if(h(".item_multi_options").exists()){D(h(".item_multi_options").val())}if(h("#lazy-start").exists()){g()}h(".broadcast_send_to").on("change",function(){M(h(this).val())});if(h(".broadcast_send_to").exists()){M(h(".broadcast_send_to").val())}h(".table_review tbody").on("click","td",function(){c=h(this).closest("tr");n=d.row(c);var t=h(this).find("a.review_viewcomments").data("id");if(t>0){v("customer_reply","parent_id="+t)}});h(function(){h('[data-toggle="tooltip"]').tooltip()});if(h("#printer-form").exists()){let t=h(".printer_type_list").val();if(!u(t)){h(".element-"+t).removeClass("d-none");if(t=="wifi"){h(".element-feieyun").addClass("d-none")}else{h(".element-wifi").addClass("d-none")}}h(".printer_type_list").on("change",function(){let t=h(this).find(":selected").val();h(".element-"+t).removeClass("d-none");if(t=="wifi"){h(".element-feieyun").addClass("d-none")}else{h(".element-wifi").addClass("d-none")}})}});function D(t){switch(t){case"custom":h(".multi_option_value_text").show();h(".multi_option_value_selection").hide();h(".multi_option_min").show();break;case"multiple":h(".multi_option_value_text").show();h(".multi_option_value_selection").hide();h(".multi_option_min").show();break;case"two_flavor":h(".multi_option_value_text").hide();h(".multi_option_value_selection").show();h(".multi_option_min").hide();break;default:h(".multi_option_value_text").hide();h(".multi_option_value_selection").hide();h(".multi_option_min").hide();break}}function M(t){switch(t){case 3:case"3":h(".broadcast_list_mobile").show();break;default:h(".broadcast_list_mobile").hide();break}}function z(t){return t.toString().normalize("NFD").replace(/[\u0300-\u036f]/g,"").toLowerCase().replace(/\s+/g,"-").replace(/&/g,"-and-").replace(/[^\w\-]+/g,"").replace(/\-\-+/g,"-").replace(/^-+/,"").replace(/-+$/,"")}function A(t){if(t&&!t.selected){return h("<span>"+t.text+"</span>")}}var L=function(t){return t};const U=new Notyf({duration:1e3*4});var o=function(t,e){switch(e){case"error":U.error(t);break;default:U.success(t);break}};var m=function(t,e){h.fn.dataTable.ext.errMode="none";var a=e.serializeArray();var s={};h.each(a,function(){if(s[this.name]){if(!s[this.name].push){s[this.name]=[s[this.name]]}s[this.name].push(this.value||"")}else{s[this.name]=this.value||""}});var i="";if(typeof action_name!=="undefined"&&action_name!==null){i=action_name}let l=false;if(typeof datatable_export!=="undefined"&&datatable_export!==null){l=datatable_export}let o={aaSorting:[[0,"DESC"]],processing:true,serverSide:true,bFilter:true,dom:'<"top">rt<"row"<"col-md-6"i><"col-md-6"p>><"clear">',pageLength:10,ajax:{url:ajaxurl+"/"+i,type:"POST",data:s},language:{url:ajaxurl+"/DatableLocalize"},buttons:["excelHtml5","csvHtml5","pdfHtml5"]};if(l==1){o.dom="lBrtip"}else{o.dom="lrtip"}d=t.on("preXhr.dt",function(t,e,a){r("loading")}).on("xhr.dt",function(t,e,a,s){r("done");setTimeout(function(){h(".tool_tips").tooltip()},100)}).on("error.dt",function(t,e,a,s){r("error")}).DataTable(o)};var t="";var _={};var p={};var l;var q;jQuery(document).ready(function(){if(h(".table_datatables").exists()){m(h(".table_datatables"),h(".frm_datatables"))}h(document).on("click",".datatables_delete",function(){t=h(this).data("id");h(".delete_confirm_modal").modal("show")});h(".delete_confirm_modal").on("shown.bs.modal",function(){if(typeof delete_custom_link!=="undefined"&&delete_custom_link!==null){h(".item_delete").attr("href",delete_custom_link+"&id="+t)}else{h(".item_delete").attr("href",delete_link+"?id="+t)}});h(document).on("click",".delete_image",function(){t=h(this).data("id");h(".delete_image_confirm_modal").modal("show")});h(".delete_image_confirm_modal").on("shown.bs.modal",function(){h(".item_delete").attr("href",t)});h(document).on("click",".order_history",function(){t=h(this).data("id");h(".order_history_modal").modal("show")});h(".order_history_modal").on("show.bs.modal",function(){v("order_history","id="+t)});if(h("#dropzone_multiple").exists()){W()}h(document).on("change",".set_item_available",function(t){var e=h(t.target).val();var a=h(this).is(":checked");a=a==true?1:0;setTimeout(function(){v("update_item_available","id="+e+"&checked="+a)},100)});h(document).on("change",".set_payment_provider",function(){var t=h(this).val();var e=h(this).prop("checked");e=e==true?"active":"inactive";setTimeout(function(){v("set_payment_provider","id="+t+"&status="+e)},100)});h(document).on("change",".set_banner_status",function(t){var e=h(t.target).val();var a=h(this).is(":checked");a=a==true?1:0;setTimeout(function(){v("set_banner_status","id="+e+"&checked="+a)},100)});h(".coupon_options").change(function(){B(h(this).val())});if(h(".coupon_options").exists()){B(h(".coupon_options").val())}});var B=function(t){h(".coupon_customer").hide();h(".coupon_max_number_use").hide();if(t==6){h(".coupon_customer").show()}else if(t==5){h(".coupon_max_number_use").show()}else{}};var f=function(){var t=Date.now()+(Math.random()*1e5).toFixed();return t};var V=function(){var t="";var e=h("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&YII_CSRF_TOKEN="+e;return t};var v=function(t,e,a,s,i){l=f();if(!u(a)){var l=a}if(u(i)){i="POST";e+=V()}_[l]=h.ajax({url:ajaxurl+"/"+t,method:i,data:e,dataType:"json",timeout:2e4,crossDomain:true,beforeSend:function(t){if(typeof s!=="undefined"&&s!==null){}else{}if(_[l]!=null){r("request aborted");_[l].abort();clearTimeout(p[l])}else{p[l]=setTimeout(function(){_[l].abort();o(L("Request taking lot of time. Please try again"))},2e4)}}});_[l].done(function(t){r("done");var e="";if(typeof t.details.next_action!=="undefined"&&t.details.next_action!==null){e=t.details.next_action}if(t.code==1){switch(e){case"csv_continue":h(".csv_progress_"+t.details.id).html(t.msg);setTimeout(function(){processCSV(t.details.id)},1*1e3);break;case"csv_done":h(".csv_progress_"+t.details.id).html(t.msg);h('a.view_delete_process[data-id="'+t.details.id+'"]').html('<i class="zmdi zmdi-mail-send"></i>');break;case"order_history":J(t.details.data,".order_history_modal table tbody");break;case"review_reply":X(t.details.data);break;case"silent":break;default:o(t.msg,"success");break}}else{switch(e){case"clear_order_history":h(".order_history_modal table tbody").html("");break;case"silent":break;default:o(t.msg,"danger");break}}});_[l].always(function(){r("ajax always");_[l]=null;clearTimeout(p[l])});_[l].fail(function(t,e){clearTimeout(p[l]);o(L("Failed")+": "+e,"danger")})};var J=function(t,e){var a="";h.each(t,function(t,e){a+="<tr>";a+="<td>"+e.date_created+"</td>";a+="<td>"+e.status+"</td>";a+="<td>"+e.remarks+"</td>";a+="</tr>"});h(e).html(a)};var W=function(){if(typeof upload_params!=="undefined"&&upload_params!==null){var t=JSON.parse(upload_params)}else var t={};var e=h("#dropzone_multiple").data("action");q=h("#dropzone_multiple").dropzone({paramName:"file",url:upload_ajaxurl+"/"+e,maxFiles:20,params:t,addRemoveLinks:false,success:function(t,e){t.previewElement.innerHTML="";var a=JSON.parse(e);r(a);var s="";if(typeof a.details!=="undefined"&&a.details!==null){if(typeof a.details.next_action!=="undefined"&&a.details.next_action!==null){s=a.details.next_action}}if(a.code==1){switch(s){case"display_image":var i=a.details.file_url;var l=a.details.remove_url;H(".item_gallery_preview .row",i,l);break;default:o(a.msg,"success");break}}else{switch(s){default:o(a.msg,"danger");break}}}})};var H=function(t,e,a){var s="";s+='<div class="col-lg-4 mb-4 mb-lg-0 preview-image">';s+='<a type="button" class="btn btn-black btn-circle delete_image" href="javascript:;" data-id="'+a+'"><i class="zmdi zmdi-plus"></i></a>';s+='<img src="'+e+'" class="img-fluid mb-2">';s+="</div>";h(t).append(s)};var g=function(){l=h("#lazy-start").infiniteScroll({path:function(){var t=h(".frm_search").serializeArray();var e={};var a="";h.each(t,function(){if(e[this.name]){if(!e[this.name].push){e[this.name]=[e[this.name]]}e[this.name].push(this.value||"")}else{e[this.name]=this.value||""}});h.each(e,function(t,e){a+="&"+t+"="+e});a+="&page="+this.pageIndex;return ajaxurl+"/"+action_name+"/?"+a},responseBody:"json",history:false,status:".lazy-load-status"});l.on("load.infiniteScroll",function(t,e){if(e.code==1){h(".page-no-results").hide();if(e.details.is_search){r("search==");l.html("")}var a="";if(typeof e.details.next_action!=="undefined"&&e.details.next_action!==null){a=e.details.next_action}r(e);switch(a){case"display_gallery":h("#lazy-start").addClass("row");Q(e.details.data);break;default:G(e.details.data);break}}else{var s=parseInt(e.details.page);if(s<=0){l.html("");h(".page-no-results").show()}else{l.infiniteScroll("option",{loadOnScroll:false})}}});l.infiniteScroll("loadNextPage")};var G=function(t){var a="";h.each(t,function(t,e){a+='<div class="kmrs-row">';a+='<div class="d-flex bd-highlight">';a+='<div class="p-2 bd-highlight">';a+=e[0];a+="</div>";a+='<div class="p-2 bd-highlight flex-grow-1">';a+=e[1];a+="</div>";a+="</div>";a+='<div class="d-flex justify-content-end">';if(h.isArray(e[2])){h.each(e[2],function(t){a+='<div class="p-2" >';a+=e[2][t];a+="</div>"})}a+="</div>";a+="</div>"});l.append(a)};var Z=function(t){try{if(t){l.html("")}l.infiniteScroll("destroy");l.removeData("infiniteScroll");l.off("load.infiniteScroll")}catch(e){r(e.message)}};var Q=function(t){var a="";h.each(t,function(t,e){a+='<div class="col-lg-3 col-md-12 mb-4 mb-lg-3">';a+='<div class="card" >';a+=e[0];a+='<div class="card-body">';a+=e[1];a+='<div class="d-flex justify-content-end">';a+='<div class="btn-group btn-group-actions" role="group">';a+=e[2][1];a+="</div>";a+="</div>";a+="</div>";a+="</div>";a+="</div>"});l.append(a)};var X=function(t){var a="";h.each(t,function(t,e){a+='<div class="d-flex">';a+='<div class="w-100 ml-5"><h6>'+e.reply_from+"</h6> <p>"+e.review+"</p>";a+='<div class="btn-group btn-group-actions mr-4" role="group">';a+='<a href="'+e.edit_link+'" class="btn btn-light tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Update">';a+='<i class="zmdi zmdi-border-color"></i>';a+="</a>";a+='<a href="javascript:;" data-id="'+e.id+'" class="btn btn-light datatables_delete tool_tips" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete">';a+='<i class="zmdi zmdi-delete"></i>';a+="</a>";a+="</div>";a+="</div>";a+="</div>"});if(n.child.isShown()){n.child.hide();c.removeClass("shown")}else{n.child(a).show();c.addClass("shown")}};const tt={props:["ajax_url","message"],data(){return{settings:[],beams:undefined}},mounted(){this.getWebpushSettings()},methods:{getWebpushSettings(){axios({method:"POST",url:this.ajax_url+"/getWebpushSettings",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.settings=t.data.details;if(this.settings.enabled==1){this.webPushInit()}}else{this.settings=[]}})["catch"](t=>{}).then(t=>{})},webPushInit(){if(this.settings.provider=="pusher"&&this.settings.user_settings.enabled==1){this.beams=new PusherPushNotifications.Client({instanceId:this.settings.pusher_instance_id});this.beams.start().then(()=>{this.beams.setDeviceInterests(this.settings.user_settings.interest).then(()=>{console.log("Device interests have been set")}).then(()=>this.beams.getDeviceInterests()).then(t=>console.log("Current interests:",t))["catch"](t=>{var e={notification_type:"push",message:"Beams "+t,date:"",image_type:"icon",image:"zmdi zmdi-info-outline"};if(typeof vm_notifications!=="undefined"&&vm_notifications!==null){vm_notifications.$refs.notification.addData(e)}})})["catch"](t=>{var e={notification_type:"push",message:"Beams "+t,date:"",image_type:"icon",image:"zmdi zmdi-info-outline"};if(typeof vm_notifications!=="undefined"&&vm_notifications!==null){vm_notifications.$refs.notification.addData(e)}})}else if(this.settings.provider=="onesignal"){}}},template:`	   
	`};const et={components:{"components-webpush":tt},props:["ajax_url","label","realtime","view_url"],data(){return{data:[],count:0,new_message:false,player:undefined,ably:undefined,channel:undefined,piesocket:undefined}},mounted(){this.getAllNotification();if(this.realtime.enabled){this.initRealTime()}},computed:{hasData(){if(this.data.length>0){return true}return false},ReceiveMessage(){if(this.new_message){return true}return false}},methods:{initRealTime(){if(this.realtime.provider=="pusher"){Pusher.logToConsole=false;var t=new Pusher(this.realtime.key,{cluster:this.realtime.cluster});var e=t.subscribe(this.realtime.channel);e.bind(this.realtime.event,e=>{r("receive pusher");r(e);r(e.notification_type);if(e.notification_type=="silent"){}else if(e.notification_type=="order_update"){this.playAlert();this.addData(e);if(typeof St!=="undefined"&&St!==null){let t=e.meta_data;St.refreshOrderInformation(t.order_uuid)}}else{this.playAlert();this.addData(e)}})}else if(this.realtime.provider=="ably"){this.ably=new Ably.Realtime(this.realtime.ably_apikey);this.ably.connection.on("connected",()=>{this.channel=this.ably.channels.get(this.realtime.channel);this.channel.subscribe(this.realtime.event,t=>{r("receive ably");r(t.data);this.playAlert();this.addData(t.data)})})}else if(this.realtime.provider=="piesocket"){this.piesocket=new PieSocket({clusterId:this.realtime.piesocket_clusterid,apiKey:this.realtime.piesocket_api_key});this.channel=this.piesocket.subscribe(this.realtime.channel);this.channel.listen(this.realtime.event,t=>{r("receive piesocket");r(t);this.playAlert();this.addData(t)})}},playAlert(){this.player=new Howl({src:["../assets/sound/notify.mp3","../assets/sound/notify.ogg"],html5:true});this.player.play()},getAllNotification(){axios({method:"POST",url:this.ajax_url+"/getNotifications",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.count=t.data.details.count}else{this.data=[];this.count=0}})["catch"](t=>{}).then(t=>{})},addData(t){this.data.unshift(t);this.count++;this.new_message=true;setTimeout(()=>{this.new_message=false},1e3);if(typeof j!=="undefined"&&j!==null){j.getOrdersCount()}if(typeof F!=="undefined"&&F!==null){F.$refs.orderlist.getList()}if(typeof ie!=="undefined"&&ie!==null){ie.refreshLastOrder()}},clearAll(){axios({method:"POST",url:this.ajax_url+"/clearNotifications",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=[];this.count=0}else{o(t.data.msg,"error")}this.new_message=false})["catch"](t=>{}).then(t=>{})}},template:`
	
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
	`};const at={props:["label","ajax_url","tpl"],data(){return{is_loading:false,data:[]}},mounted(){this.merchantPlanStatus()},methods:{merchantPlanStatus(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/merchantPlanStatus",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`		
	  <div v-if="tpl==='1'" class="card m-auto">
	  
		<div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
		    <div>
		      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
		    </div>
		</div>
	  
	     <div class="card-body">
	        <h5 class="mb-1">{{data.restaurant_name}}</h5>
	        
	         <div class="d-flex justify-content-between">
			   <div class="flex-col">{{label.current_status}}</div>
			   <div class="flex-col"><span class="badge customer" :class="data.status_raw">{{data.status}}</span></div>
			 </div>
	        
	     </div>  <!-- body -->
	  </div> <!-- card -->
	  
	 <template v-else-if="tpl==='2'" >
	 <div v-if="data.status_raw=='expired'" class="p-2 align-self-center">
      <i class="zmdi zmdi-alarm text-danger"></i><span class="ml-2"><b>{{label.trial_ended}}</b></span>
     </div>
     </template>
	`};const e={props:["ajax_url","amount"],data(){return{data:0,config:JSON.parse(money_config)}},mounted(){this.data=window["v-money3"].format(this.amount,this.config)},updated(){this.data=window["v-money3"].format(this.amount,this.config)},methods:{formatNumber(t){return window["v-money3"].format(t,this.config)}},template:`	
    {{data}}
    `};var st=function(t){return window["v-money3"].format(t,JSON.parse(money_config))};let b;let y=16.6;const it=[{featureType:"administrative",elementType:"labels.text.fill",stylers:[{color:"#686868"}]},{featureType:"landscape",elementType:"all",stylers:[{color:"#f2f2f2"}]},{featureType:"poi",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"road",elementType:"all",stylers:[{saturation:-100},{lightness:45}]},{featureType:"road.highway",elementType:"all",stylers:[{visibility:"simplified"}]},{featureType:"road.highway",elementType:"geometry.fill",stylers:[{lightness:"-22"}]},{featureType:"road.highway",elementType:"geometry.stroke",stylers:[{saturation:"11"},{lightness:"-51"}]},{featureType:"road.highway",elementType:"labels.text",stylers:[{saturation:"3"},{lightness:"-56"},{weight:"2.20"}]},{featureType:"road.highway",elementType:"labels.text.fill",stylers:[{lightness:"-52"}]},{featureType:"road.highway",elementType:"labels.text.stroke",stylers:[{weight:"6.13"}]},{featureType:"road.highway",elementType:"labels.icon",stylers:[{lightness:"-10"},{gamma:"0.94"},{weight:"1.24"},{saturation:"-100"},{visibility:"off"}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{lightness:"-16"}]},{featureType:"road.arterial",elementType:"labels.text.fill",stylers:[{saturation:"-41"},{lightness:"-41"}]},{featureType:"road.arterial",elementType:"labels.text.stroke",stylers:[{weight:"5.46"}]},{featureType:"road.arterial",elementType:"labels.icon",stylers:[{visibility:"off"}]},{featureType:"road.local",elementType:"geometry.fill",stylers:[{weight:"0.72"},{lightness:"-16"}]},{featureType:"road.local",elementType:"labels.text.fill",stylers:[{lightness:"-37"}]},{featureType:"transit",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"water",elementType:"all",stylers:[{color:"#b7e4f4"},{visibility:"on"}]}];const lt={props:["markers","center","zoom","maps_config"],data(){return{bounds:[],cmaps:undefined,cmapsMarker:[],allMarkers:[]}},mounted(){},watch:{markers(t,e){this.clearMarkers()}},methods:{renderMap(){if(this.maps_config.provider=="google.maps"){this.bounds=new window.google.maps.LatLngBounds;this.cmaps=new window.google.maps.Map(this.$refs.maploc,{center:{lat:parseFloat(this.center.lat),lng:parseFloat(this.center.lng)},zoom:parseInt(this.zoom),disableDefaultUI:false,styles:it})}else if(this.maps_config.provider=="mapbox"){this.bounds=new mapboxgl.LngLatBounds;mapboxgl.accessToken=this.maps_config.key;this.cmaps=new mapboxgl.Map({container:this.$refs.maploc,style:"mapbox://styles/mapbox/streets-v12",center:[parseFloat(this.center.lng),parseFloat(this.center.lat)],zoom:14});this.cmaps.on("error",t=>{alert(t.error.message)});this.mapBoxResize()}else if(this.maps_config.provider=="yandex"){this.initYandex()}this.instantiateDriverMarker()},async initYandex(){await ymaps3.ready;const{YMap:t,YMapDefaultSchemeLayer:e,YMapMarker:l,YMapDefaultFeaturesLayer:a,YMapListener:s,YMapControls:o}=ymaps3;const{YMapDefaultMarker:i}=await ymaps3["import"]("@yandex/ymaps3-markers@0.0.1");const{YMapZoomControl:r,YMapGeolocationControl:d}=await ymaps3["import"]("@yandex/ymaps3-controls@0.0.1");const n={center:[parseFloat(this.center.lng),parseFloat(this.center.lat)],zoom:y};if(b){b.destroy();b=null}if(!b){b=new t(this.$refs.maploc,{location:n,showScaleInCopyrights:false,behaviors:["drag","scrollZoom"]},[new e({}),new a({})]);b.addChild(new o({position:"right"}).addChild(new r({})));let i=[];Object.entries(this.markers).forEach(([t,e])=>{let a=[parseFloat(e.lng),parseFloat(e.lat)];i.push(a);const s=document.createElement("div");s.className=this.getIconMapbox(e.type);console.log("markerElement.className",s.className);this.cmapsMarker[t]=b.addChild(new l({coordinates:a},s))});if(Object.keys(i).length>1){const c={bounds:i,zoom:y};b.update({location:c})}else if(Object.keys(i).length>0){const m={center:[i[0][0],i[0][1]],zoom:y};b.update({location:m})}}},clearMarkers(){if(this.maps_config.provider=="google.maps"){for(var t=0;t<this.allMarkers.length;t++){this.allMarkers[t].setVisible(false);this.allMarkers[t].setMap(null)}}this.allMarkers=[];this.instantiateDriverMarker()},instantiateDriverMarker(){Object.entries(this.markers).forEach(([t,e])=>{let a="";if(this.maps_config.provider=="google.maps"){a={path:google.maps.SymbolPath.CIRCLE,scale:8,strokeColor:"#f44336"}}else if(this.maps_config.provider=="mapbox"){a=this.getIconMapbox(e.type)}let s={lat:parseFloat(e.lat),lng:parseFloat(e.lng)};let i=e.name;this.addMarker({position:s,map:this.cmaps,icon:a,label:this.getIcon(e.type),info:i},e.index)});this.FitBounds()},getIconMapbox(t){let e=[];e["driver"]="marker_icon_driver";e["merchant"]="marker_icon_merchant";e["customer"]="marker_icon_destination";return e[t]},getIcon(t){let e=[];e["driver"]={text:"",fontFamily:"Material Icons",color:"#ffffff",fontSize:"17px"};e["merchant"]={text:"",fontFamily:"Material Icons",color:"#ffffff",fontSize:"17px"};e["customer"]={text:"",fontFamily:"Material Icons",color:"#ffffff",fontSize:"17px"};return e[t]},addMarker(a,s){if(u(this.cmaps)){return}if(this.maps_config.provider=="google.maps"){this.cmapsMarker[s]=new window.google.maps.Marker(a);this.cmaps.panTo(new window.google.maps.LatLng(a.position.lat,a.position.lng));this.bounds.extend(this.cmapsMarker[s].position);this.allMarkers.push(this.cmapsMarker[s]);const i=new google.maps.InfoWindow({content:a.info});let t=this.cmaps;let e=this.cmapsMarker[s];e.addListener("click",()=>{i.open({anchor:e,cmaps:t,shouldFocus:false})})}else if(this.maps_config.provider=="mapbox"){const e=new mapboxgl.Popup({offset:25}).setHTML(a.info);const t=document.createElement("div");t.className=a.icon;this.cmapsMarker[s]=new mapboxgl.Marker(t).setLngLat([a.position.lng,a.position.lat]).setPopup(e).addTo(this.cmaps);this.bounds.extend(new mapboxgl.LngLat(a.position.lng,a.position.lat))}},FitBounds(){if(u(this.cmaps)){return}if(this.maps_config.provider=="google.maps"){try{this.cmaps.fitBounds(this.bounds)}catch(t){console.error(t)}}else if(this.maps_config.provider=="mapbox"){this.cmaps.fitBounds(this.bounds,{padding:30})}},mapBoxResize(){if(this.maps_config.provider=="mapbox"){setTimeout(()=>{this.cmaps.resize()},500)}}},template:`			
	<div ref="maploc" class="map-small" style="border:1px solid #fff;" ></div>
	`};const ot={props:["order_uuid","ajax_url","map_center","zoom"],components:{"components-map":lt},data(){return{loading:false,data:[],zone_id:0,group_selected:0,group_data:[],zone_data:[],markers:[],active_task:[]}},created(){if(!u(this.order_uuid)){this.getAvailableDriver()}this.getGroupList();this.getZoneList()},watch:{order_uuid(t,e){this.getAvailableDriver()},zone_id(t,e){this.getAvailableDriver()},group_selected(t,e){this.getAvailableDriver()}},computed:{hasData(){if(Object.keys(this.data).length>0){return true}return false},hasMarkers(){if(Object.keys(this.markers).length>0){return true}return false},hasFilter(){let t=false;if(!u(this.zone_id)){t=true}if(this.group_selected>0){t=true}return t}},methods:{show(){h(this.$refs.modal).modal("show");if(Object.keys(this.markers).length>0){this.$refs.map_components.renderMap()}},hide(){h(this.$refs.modal).modal("hide")},getGroupList(){axios({method:"post",url:this.ajax_url+"/getGroupList",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{this.group_data=t.data.details})["catch"](t=>{this.group_data=[]}).then(t=>{})},getZoneList(){axios({method:"post",url:this.ajax_url+"/getZoneList",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{this.zone_data=t.data.details})["catch"](t=>{this.zone_data=[]}).then(t=>{})},clearFilter(){this.zone_id=0;this.group_selected=0;this.getAvailableDriver()},getAvailableDriver(){this.loading=true;axios({method:"post",url:this.ajax_url+"/getAvailableDriver",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid+"&zone_id="+this.zone_id+"&group_selected="+this.group_selected,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.merchant_data=t.data.details.merchant_data;this.active_task=t.data.details.active_task}else{this.data=[];this.merchant_data=t.data.details.merchant_data;this.active_task=[]}this.SetMarker()})["catch"](t=>{this.data=[];this.merchant_data=[];this.active_task=[]}).then(t=>{this.loading=false})},SetMarker(){this.markers=[];if(Object.keys(this.data).length>0){Object.entries(this.data).forEach(([t,e])=>{this.markers.push({type:"driver",name:e.name,lat:e.latitude,lng:e.longitude})})}if(Object.keys(this.merchant_data).length>0){this.markers.push({type:"merchant",name:this.merchant_data.restaurant_name,lat:this.merchant_data.latitude,lng:this.merchant_data.longitude})}},AssignDriver(t){this.loading=true;let e="YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content");e+="&driver_id="+t;e+="&order_uuid="+this.order_uuid;axios({method:"post",url:this.ajax_url+"/AssignDriver",data:e,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder",this.order_uuid);ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});this.hide()}else{ElementPlus.ElNotification({title:"",message:t.data.msg,type:"warning"})}})["catch"](t=>{ElementPlus.ElNotification({title:"",message:t,type:"warning"})}).then(t=>{this.loading=false})}},template:"#xtemplate_assign_driver"};const rt={props:["label","size"],methods:{confirm(){bootbox.confirm({size:this.size,title:this.label.confirm,message:this.label.are_you_sure,centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.yes,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{this.$emit("callback",t)}})},alert(t,e){bootbox.alert({size:!u(e.size)?e.size:"",closeButton:false,message:t,animate:false,centerVertical:true,buttons:{ok:{label:this.label.ok,className:"btn btn-green small pl-4 pr-4"}}})}}};const a=Vue.createApp({components:{"component-bootbox":rt},data(){return{resolvePromise:undefined,rejectPromise:undefined}},methods:{confirm(){return new Promise((t,e)=>{this.resolvePromise=t;this.rejectPromise=e;this.$refs.bootbox.confirm()})},Callback(t){this.resolvePromise(t)},alert(t,e){this.$refs.bootbox.alert(t,e)}}}).mount("#vue-bootbox");const dt={props:["label","max_file","select_type","field","field_path","selected_file","selected_multiple_file","max_file_size","inline","upload_path","save_path"],components:{"component-bootbox":rt},data(){return{data:[],q:"",page_count:0,current_page:0,preview:false,dropzone:undefined,tab:1,is_loading:false,page:1,item_selected:[],added_files:[],awaitingSearch:false,data_message:""}},mounted(){this.getMedia();this.getMediaSeleted();this.getMediaMultipleSeleted();this.initDropzone()},updated(){r("inline=>"+this.inline)},watch:{q(t,e){if(!this.awaitingSearch){if(u(t)){this.getMedia();return false}setTimeout(()=>{var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),page:this.page,q:this.q};var e=f();t=JSON.stringify(t);_[e]=h.ajax({url:upload_ajaxurl+"/getMedia",method:"PUT",dataType:"json",data:t,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{if(_[e]!=null){_[e].abort()}}}).done(t=>{this.data_message=t.msg;if(t.code==1){this.data=t.details.data;this.page_count=t.details.page_count;this.current_page=t.details.current_page}else{this.data=[];this.page_count=0;this.current_page=0}}).always(t=>{this.awaitingSearch=false})},1e3);this.data=[];this.awaitingSearch=true}}},computed:{hasData(){if(this.data.length>0){return true}return false},hasSelected(){if(this.item_selected.length>0){return true}return false},totalSelected(){return this.item_selected.length},hasAddedFiles(){if(this.added_files.length>0){return true}return false},noFiles(){if(this.data.length>0){return false}if(this.awaitingSearch){return false}return true},hasSearch(){if(!u(this.q)){return true}return false}},methods:{show(){h(this.$refs.modal_uplader).modal("show")},close(){h(this.$refs.modal_uplader).modal("hide")},previewTemplate(){var t=`
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
  	  	 `;return t},initDropzone(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),upload_path:this.upload_path};this.dropzone=new Dropzone(this.$refs.dropzone,{paramName:"file",maxFilesize:parseInt(this.max_file_size),url:upload_ajaxurl+"/file",maxFiles:this.max_file,params:t,clickable:this.$refs.fileinput,previewsContainer:this.$refs.ref_preview,previewTemplate:this.previewTemplate(),acceptedFiles:"image/*"});this.dropzone.on("addedfile",t=>{this.preview=true;r("added file=>"+t.type);switch(t.type){case"image/jpeg":case"image/png":case"image/svg+xml":case"image/webp":case"image/apng":break;default:this.dropzone.removeFile(t);break}});this.dropzone.on("queuecomplete",t=>{r("All files have uploaded ");this.getMedia()});this.dropzone.on("success",(t,e)=>{r("success");e=JSON.parse(e);r(e);if(e.code==2){o(e.msg);this.dropzone.removeFile(t)}})},getMedia(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),page:this.page,selected_file:this.selected_multiple_file,item_selected:this.item_selected};var e=f();t=JSON.stringify(t);_[e]=h.ajax({url:upload_ajaxurl+"/getMedia",method:"PUT",dataType:"json",data:t,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(_[e]!=null){_[e].abort()}}}).done(t=>{this.data_message=t.msg;if(t.code==1){this.data=t.details.data;this.page_count=t.details.page_count;this.current_page=t.details.current_page}else{this.data=[];this.page_count=0;this.current_page=0}}).always(t=>{this.is_loading=false})},addMore(){this.preview=false},pageNum(t){this.page=t;this.getMedia()},pageNext(){this.page=parseInt(this.page)+1;if(this.page>=this.page_count){this.page=this.page_count}this.getMedia()},pagePrev(){this.page=parseInt(this.page)-1;r(this.page+"=>"+this.page_count);if(this.page<=1){this.page=1}this.getMedia()},itemSelect(a,t){a.is_selected=!a.is_selected;if(this.select_type=="single"){this.removeAllSelected(a.id);if(a.is_selected){this.item_selected[0]={filename:a.filename,image_url:a.image_url,path:a.path}}else{this.item_selected.splice(0,1)}}else{if(a.is_selected){this.item_selected.push({filename:a.filename,image_url:a.image_url,path:a.path})}else{this.item_selected.forEach((t,e)=>{if(t.filename==a.filename){this.item_selected.splice(e,1)}})}}},removeAllSelected(a){this.data.forEach((t,e)=>{if(t.id!=a){t.is_selected=false}})},addFiles(){var a=[];this.item_selected.forEach((t,e)=>{a[e]={id:t.id,filename:t.filename,image_url:t.image_url,path:t.path}});this.added_files=a;this.close()},removeAddedFiles(t){this.added_files.splice(t,1)},getMediaSeleted(){if(u(this.selected_file)){return}var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),selected_file:this.selected_file,save_path:this.save_path};var e=f();t=JSON.stringify(t);_[e]=h.ajax({url:upload_ajaxurl+"/getMediaSeleted",method:"PUT",dataType:"json",data:t,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{if(_[e]!=null){_[e].abort()}}}).done(t=>{if(t.code==1){this.added_files=t.details}else{}})},getMediaMultipleSeleted(){if(u(this.selected_multiple_file)){return}var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),selected_file:this.selected_multiple_file,save_path:this.save_path};var e=f();t=JSON.stringify(t);_[e]=h.ajax({url:upload_ajaxurl+"/getMediaMultipleSeleted",method:"PUT",dataType:"json",data:t,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{if(_[e]!=null){_[e].abort()}}}).done(t=>{if(t.code==1){this.added_files=t.details;var a=[];this.added_files.forEach((t,e)=>{a[e]={filename:t.filename,image_url:t.image_url}});this.item_selected=a}else{this.added_files=[];this.item_selected=[]}})},clearData(){this.q="";this.getMedia()},beforeDeleteFiles(){a.confirm().then(t=>{if(t){this.deleteFiles()}})},deleteFiles(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),item_selected:this.item_selected};var e=f();t=JSON.stringify(t);_[e]=h.ajax({url:upload_ajaxurl+"/deleteFiles",method:"PUT",dataType:"json",data:t,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(_[e]!=null){_[e].abort()}}}).done(t=>{if(t.code==1){this.item_selected=[];this.getMedia()}else{a.alert(t.msg,{})}}).always(t=>{this.is_loading=false})},clearSelected(t){if(this.item_selected.length>0){this.item_selected=[];t.forEach((t,e)=>{t.is_selected=false})}else{if(this.select_type=="multiple"){var a=[];t.forEach((t,e)=>{t.is_selected=true;a[e]={filename:t.filename,image_url:t.image_url}});this.item_selected=a}}}},template:`   

   
   <!---
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
        <input :name="field_path+'[]'" type="hidden" :value="item.path" />
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
          
    <div ref="modal_uplader" :class="{'modal fade':this.inline=='false'}" 
    id="modalUploader" data-backdrop="static" 
    tabindex="-1" role="dialog" aria-labelledby="modalUploader" aria-hidden="true">
    
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
			      <h5>{{label.drop_files}} <br/> {{label.or}}</h5>
	             <a ref="fileinput" class="btn btn-green fileinput-button" href="javascript:;">
				 {{label.select_files}}
				 </a>
	           </div>
	         </div> 	         
	         
	         <!-- file_preview_container -->
	         <div :class="{ 'd-block': preview }" class="file_preview_container">	 
		          <nav class="navbar bg-light d-flex justify-content-end">
		             <button @click="addMore" type="button" class="btn">
					 + 
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
   `};const nt=Vue.createApp({components:{"component-uploader":dt},data(){return{data:[]}},mounted(){},methods:{}}).mount("#vue-uploader");const ct={props:["order_status","ajax_url","label","show_critical","show_status","schedule"],data(){return{error:[],is_loading:false,data:[],meta:[],status:[],services:[],total:0,order_uuid:"",order_type:"",response_code:0,count_up:undefined}},mounted(){this.getList()},methods:{getList(t){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/orderList",data:{order_status:this.order_status,schedule:this.schedule,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),filter:t},timeout:s}).then(t=>{this.response_code=t.data.code;if(t.data.code==1){this.data=t.data.details.data;this.total=t.data.details.total;this.meta=t.data.details.meta;this.status=t.data.details.status;this.services=t.data.details.services;this.order_uuid=t.data.details.data[0].order_uuid;this.order_type=t.data.details.data[0].service_code;this.$emit("afterSelect",this.order_uuid,this.order_type)}else{this.error=t.data.msg;this.data=[];this.meta=[];this.status=[];this.services=[];this.total=0;this.$emit("afterSelect","")}var e=new countUp.CountUp(this.$refs.total,this.total,{decimalPlaces:0,separator:",",decimal:"."});e.start()})["catch"](t=>{}).then(t=>{this.updateScroll();this.is_loading=false})},updateScroll(){setTimeout(function(){h(".nice-scroll").getNiceScroll().resize()},100)},select(t){this.order_uuid=t.order_uuid;this.order_type=t.service_code;t.is_view=1;this.$emit("afterSelect",t.order_uuid,this.order_type)}},template:`			
	
	<div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	</div>
	
	<div class="make-sticky d-flex align-items-center justify-content-between bg-white">
	    <div><h5 class="head m-0">{{label.title}}</h5></div>
	    <div>
	      <div ref="total" class="ronded-green">0</div>
	    </div>
    </div> 
    
    <template v-if="response_code==1">
	<ul class="list-unstyled m-0 grey-list-chevron">
    <li v-for="(item, index) in data" class="chevron" 
    :class="{selected:item.order_uuid == order_uuid}" @click="select(item)" >
    
    <div class="row align-items-start">      
      <div class="col">
        <div class="d-flex justify-content-between align-items-center">
         <div><p class="m-0" v-if="meta[item.order_id]"><b>{{meta[item.order_id].customer_name}}</b></p></div>
         <div>
         <span v-if="status[item.status]" class="ml-2 badge" 
          :style="{background:status[item.status].background_color_hex,color:status[item.status].font_color_hex}"
          >          
          {{status[item.status].status}} 
         </span>  
         <span v-else class="ml-2 badge badge-info"  >
           {{item.status}}
         </span>
         </div>
        </div>
        <p class="m-0">{{item.total_items}}
        
         <span v-if="services[item.service_code]" class="ml-2 badge services" 
         :style="{background:services[item.service_code].background_color_hex,color:services[item.service_code].font_color_hex}"
          >          
          {{services[item.service_code].service_name}} 
         </span>      
         <span v-else class="ml-2 badge badge-info"  >
           {{item.service_code}}
         </span>
        
        </p>
        
        <div class="d-flex align-items-center">
          <div v-if="item.is_view==0" class="mr-1"><div class="blob green"></div></div>
          <div><p class="m-0">{{item.order_name}}</p></div>
        </div>
        
        <div class="d-flex align-items-center">
          <template v-if="show_critical">
          <div v-if="item.is_critical==1" class="mr-1"><div class="blob red"></div></div>
          </template>
          <div><p class="m-0"><u>{{item.delivery_date}}</u></p></div>
        </div>        
      </div> <!--col-->
    </div>
    <hr class="m-0">
   </li>    
   </ul>
   </template>
   <template v-else>
    <div class="fixed-height40 text-center justify-content-center d-flex align-items-center">
    
    <div v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
	    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
	 </div>      
    
    </div>
   </template>
	`};const mt={props:["ajax_url","label","order_uuid"],data(){return{data:[],reason:"",resolvePromise:undefined,rejectPromise:undefined}},computed:{hasData(){if(!u(this.reason)){return true}return false}},mounted(){this.orderRejectionList();autosize(this.$refs.reason)},methods:{confirm(){h(this.$refs.rejection_modal).modal("show");return new Promise((t,e)=>{this.resolvePromise=t;this.rejectPromise=e})},close(){h(this.$refs.rejection_modal).modal("hide")},orderRejectionList(){axios({method:"put",url:this.ajax_url+"/orderRejectionList",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{})},submit(){this.close();this.resolvePromise(this.reason)}},template:`	
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
</div>            
	`};const ht={props:["ajax_url","label","order_uuid"],data(){return{amount:0,refund_type:"full",resolvePromise:undefined,rejectPromise:undefined,is_loading:false,data:[]}},methods:{confirm(t){this.data=t;this.refund_type=t.refund_type;h(this.$refs.refund_modal).modal("show");return new Promise((t,e)=>{this.resolvePromise=t;this.rejectPromise=e})},close(){h(this.$refs.refund_modal).modal("hide")},submit(){this.close();this.resolvePromise(true)}},template:`	
	
	<div ref="refund_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">                
        <p>{{label.refund_full}} {{data.pretty_total}}</p>        
      </div> <!-- body -->
      
       <div class="modal-footer">
          <button type="buttton" class="btn btn-black" data-dismiss="modal" aria-label="Close" >
          <span class="pl-2 pr-2" >{{label.cancel}}</span>
          </button>
          <button type="button" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"           
          >
          <span>{{label.refund}}</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
      </div>
     </div>
     </div>      
	`};const ut={props:["message","donnot_close"],data(){return{new_message:""}},methods:{show(){h(this.$refs.modal).modal("show")},close(){h(this.$refs.modal).modal("hide")},setMessage(t){this.new_message=t}},template:`
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
	`};const _t={props:["ajax_url","group_name","refund_label","remove_item","out_stock_label","manual_status","modify_order","update_order_label","filter_buttons","enabled_delay_order"],components:{"components-rejection-forms":mt,"components-refund-forms":ht,"components-loading-box":ut},data(){return{is_loading:false,loading:true,order_uuid:"",merchant:[],order_info:[],items:[],order_summary:[],summary_changes:[],summary_transaction:[],summary_total:0,merchant_direction:"",delivery_direction:"",order_status:[],services:[],payment_status:[],response_code:0,customer:[],buttons:[],status_data:[],stats_id:"",sold_out_options:[],out_stock_options:"",item_row:[],additional_charge:0,additional_charge_name:"",customer_name:"",contact_number:"",delivery_address:"",latitude:"",longitude:"",error:[],link_pdf:[],payment_history:[],screen_size:{width:0,height:0},show_as_popup:false,load_count:0,credit_card_details:[],driver_data:[],zone_list:[],merchant_zone:[],delivery_status:[],order_table_data:[],formatted_address:"",address1:"",location_name:"",address2:"",postal_code:"",company:"",address_format_use:1,kitchen_addon:false,found_in_kitchen:false,print_data:[],socket_error:"",webSocket:null}},computed:{hasData(){if(this.stats_id>0){return true}return false},outStockOptions(){if(this.out_stock_options>0){return true}return false},hasValidCharge(){if(this.additional_charge>0){return true}return false},refundAvailable(){if(this.order_info.payment_status=="paid"){return true}return false},hasRefund(){if(this.summary_changes){if(this.summary_changes.method==="total_decrease"){if(this.summary_changes.refund_due>0){return true}}}return false},hasAmountToCollect(){if(this.summary_changes){if(this.summary_changes.method==="total_increase"){if(this.summary_changes.refund_due>0){return true}}}return false},hasTotalDecrease(){if(this.summary_changes){if(this.summary_changes.method==="total_decrease"){return true}}return false},hasTotalIncrease(){if(this.summary_changes){if(this.summary_changes.method==="total_increase"){return true}}return false},summaryTransaction(){if(this.summary_transaction){if(typeof this.summary_transaction.summary_list!=="undefined"&&this.summary_transaction.summary_list!==null){if(this.summary_transaction.summary_list.length>0){return true}}}return false},hasInvoiceUnpaid(){if(this.summary_changes.unpaid_invoice){return true}if(this.summary_changes.paid_invoice){return true}return false},hasBooking(){if(Object.keys(this.order_table_data).length>0){return true}return false}},watch:{load_count(t,e){if(typeof F!=="undefined"&&F!==null){if(t>=2){if(this.screen_size.width<=576){F.show_as_popup=true}else{F.show_as_popup=false}}else{F.show_as_popup=false}}}},mounted(){this.getOrderStatusList();this.handleResize();window.addEventListener("resize",this.handleResize)},methods:{handleResize(){this.screen_size.width=window.innerWidth;this.screen_size.height=window.innerHeight},orderDetails(t,e){this.order_uuid=t;this.is_loading=true;this.loading=true;var a=["payment_history","print_settings","buttons"];axios({method:"put",url:this.ajax_url+"/orderDetails",data:{order_uuid:this.order_uuid,group_name:this.group_name,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),payload:a,modify_order:this.modify_order,filter_buttons:this.filter_buttons},timeout:s}).then(t=>{this.load_count++;this.response_code=t.data.code;if(t.data.code==1){this.merchant=t.data.details.data.merchant;this.order_info=t.data.details.data.order.order_info;this.driver_data=t.data.details.data.driver_data;this.zone_list=t.data.details.data.zone_list;this.merchant_zone=t.data.details.data.merchant_zone;this.order_table_data=t.data.details.data.order_table_data;this.customer_name=this.order_info.customer_name;this.contact_number=this.order_info.contact_number;this.delivery_address=this.order_info.delivery_address;this.address1=this.order_info.address1;this.location_name=this.order_info.location_name;this.address2=this.order_info.address2;this.postal_code=this.order_info.postal_code;this.company=this.order_info.company;this.address_format_use=this.order_info.address_format_use;this.latitude=this.order_info.latitude;this.longitude=this.order_info.longitude;this.customer=t.data.details.data.customer;if(typeof F!=="undefined"&&F!==null){F.client_id=this.customer.client_id}this.order_status=t.data.details.data.order.status;this.services=t.data.details.data.order.services;this.payment_status=t.data.details.data.order.payment_status;this.delivery_status=t.data.details.data.order.delivery_status;this.items=t.data.details.data.items;this.order_summary=t.data.details.data.summary;this.summary_total=t.data.details.data.summary_total;this.summary_changes=t.data.details.data.summary_changes;this.summary_transaction=t.data.details.data.summary_transaction;this.merchant_direction="https://www.google.com/maps/dir/?api=1&destination=";this.merchant_direction+=this.merchant.latitude+",";this.merchant_direction+=this.merchant.longitude;this.delivery_direction=this.order_info.delivery_direction;this.buttons=t.data.details.data.buttons;this.sold_out_options=t.data.details.data.sold_out_options;this.link_pdf=t.data.details.data.link_pdf;this.payment_history=t.data.details.data.payment_history;this.credit_card_details=t.data.details.data.credit_card_details;this.kitchen_addon=t.data.details.data.kitchen_addon;this.found_in_kitchen=t.data.details.data.found_in_kitchen}else{this.merchant_direction="";this.delivery_direction="";this.merchant=[];this.order_info=[];this.items=[];this.order_summary=[];this.buttons=[];this.sold_out_options=[];this.link_pdf=[];this.payment_history=[];this.credit_card_details=[];this.driver_data=[];this.kitchen_addon=false;this.found_in_kitchen=false}})["catch"](t=>{}).then(t=>{this.is_loading=false;this.loading=false})},doUpdateOrderStatus(e,a,t){r("do_actions=>"+t);console.log("summary_changes=>"+this.summary_changes.method);if(t=="reject_form"){this.$refs.rejection.confirm().then(t=>{if(t){r("rejection reason =>"+t);this.updateOrderStatus(e,a,t)}})}else{var s="";if(this.summary_changes.method=="total_decrease"){var s=this.update_order_label.content;s=s.replace("{{amount}}",this.summary_changes.refund_due_pretty);bootbox.confirm({size:"small",title:"",message:"<h5>"+this.update_order_label.title+"</h5>"+"<p>"+s+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.update_order_label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.createRefund(e,a)}}})}else if(this.summary_changes.method=="total_increase"){var s=this.update_order_label.content_collect;s=s.replace("{{amount}}",this.summary_changes.refund_due_pretty);if(this.summary_changes.unpaid_invoice){var i=bootbox.dialog({title:"",message:"<h5>"+this.update_order_label.title_increase+"</h5>"+"<p>"+this.update_order_label.content_payment+"</p>",size:"medium",centerVertical:true,buttons:{cancel:{label:this.update_order_label.close,className:"btn btn-black small pl-4 pr-4",callback:()=>{}}}})}else if(this.summary_changes.paid_invoice){this.updateOrderStatus(e,a)}else{var i=bootbox.dialog({title:"",message:"<h5>"+this.update_order_label.title_increase+"</h5>"+"<p>"+s+"</p>",size:"medium",centerVertical:true,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4",callback:()=>{}},account:{label:this.update_order_label.less_acccount,className:"btn btn-yellow small",callback:()=>{this.AcceptOrder(e,a,"lessOnAccount")}},ok:{label:this.update_order_label.send_invoice,className:"btn btn-green small pl-4 pr-4",callback:()=>{this.AcceptOrder(e,a,"createInvoice")}}}})}}else if(this.summary_changes.method=="less_on_account"){var s=this.update_order_label.collect_cash;s=s.replace("{{amount}}",st(parseFloat(this.order_info.commission)));var i=bootbox.dialog({title:"",message:"<h5>Accept Order</h5>"+"<p>"+s+"</p>",size:"medium",centerVertical:true,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4",callback:()=>{}},ok:{label:this.update_order_label.less_acccount,className:"btn btn-green small pl-4 pr-4",callback:()=>{this.AcceptOrder(e,a,"lessCashOnAccount")}}}})}else{this.updateOrderStatus(e,a)}}},updateOrderStatus(t,e,a){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/updateOrderStatus",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t,order_uuid:e,reason:a},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},createRefund(t,e){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/createRefund",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t,order_uuid:e},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},AcceptOrder(t,e,a){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/"+a,data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),uuid:t,order_uuid:e},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getOrderStatusList(){axios({method:"put",url:this.ajax_url+"/getOrderStatusList",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.status_data=t.data.details}else{this.status_data=false}})["catch"](t=>{}).then(t=>{})},manualStatusList(t){this.stats_id="";h(this.$refs.manual_status_modal).modal("show")},confirm(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/updateOrderStatusManual",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,stats_id:this.stats_id},timeout:s}).then(t=>{if(t.data.code==1){h(this.$refs.manual_status_modal).modal("hide");this.$emit("afterUpdate")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},cancelOrder(){this.$refs.rejection.confirm().then(t=>{if(t){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/cancelOrder",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,reason:t},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterUpdate");o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}})},delayOrder(){this.$emit("delayOrderform",this.order_uuid)},contactCustomer(){a.alert(this.order_info.contact_number,{size:"small"})},orderHistory(){this.$emit("order-history",this.order_uuid)},markItemOutStock(t){this.item_row=t;h(this.$refs.out_stock_modal).modal("show")},setOutOfStocks(){h(this.$refs.out_stock_modal).modal("hide");bootbox.confirm({size:"medium",title:"",message:"<h5>"+this.out_stock_label.title+"</h5>"+"<p>"+this.refund_label.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.refund_label.go_back,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.refund_label.complete,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.itemChanges("out_stock")}else{h(this.$refs.out_stock_modal).modal("show")}}})},itemChanges(e){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/itemChanges",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,item_row:this.item_row.item_row,item_changes:e,out_stock_options:this.out_stock_options},timeout:s}).then(t=>{if(t.data.code==1){switch(e){default:h(this.$refs.out_stock_modal).modal("hide");this.orderDetails(this.order_uuid,true);break}}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},adjustOrder(t){this.item_row=t;h(this.$refs.adjust_order_modal).modal("show")},refundItem(){h(this.$refs.adjust_order_modal).modal("hide");bootbox.confirm({size:"medium",title:"",message:"<h5>"+this.refund_label.title+"</h5>"+"<p>"+this.refund_label.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.refund_label.go_back,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.refund_label.complete,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.doItemRefund()}else{h(this.$refs.adjust_order_modal).modal("show")}}})},doItemRefund(){this.itemChanges("refund")},cancelEntireOrder(){h(this.$refs.adjust_order_modal).modal("hide");this.cancelOrder()},removeItem(){h(this.$refs.adjust_order_modal).modal("hide");bootbox.confirm({size:"medium",title:"",message:"<h5>"+this.remove_item.title+"</h5>"+"<p>"+this.remove_item.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.remove_item.go_back,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.remove_item.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.doRemoveItem()}else{h(this.$refs.adjust_order_modal).modal("show")}}})},doRemoveItem(){this.itemChanges("remove")},additionalCharge(t){this.item_row=t;h(this.$refs.additional_charge_modal).modal("show")},doAdditionalCharge(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/additionalCharge",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,item_row:this.item_row.item_row,additional_charge:this.additional_charge,additional_charge_name:this.additional_charge_name},timeout:s}).then(t=>{if(t.data.code==1){h(this.$refs.additional_charge_modal).modal("hide");this.orderDetails(this.order_uuid,true)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},updateOrderSummary(){axios({method:"put",url:this.ajax_url+"/updateOrderSummary",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid},timeout:s}).then(t=>{if(t.data.code==1){}else{}})["catch"](t=>{}).then(t=>{})},replaceItem(){h(this.$refs.adjust_order_modal).modal("hide");this.$emit("showMenu",this.item_row)},editOrderInformation(){h(this.$refs.update_info_modal).modal("show")},updateOrderDeliveryInformation(){this.is_loading=true;this.error=[];axios({method:"put",url:this.ajax_url+"/updateOrderDeliveryInformation",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,customer_name:this.customer_name,contact_number:this.contact_number,delivery_address:this.delivery_address,address1:this.address1,location_name:this.location_name,address2:this.address2,postal_code:this.postal_code,company:this.company,address_format_use:this.address_format_use,latitude:this.latitude,longitude:this.longitude},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);h(this.$refs.update_info_modal).modal("hide");this.$emit("refreshOrder",this.order_uuid)}else{this.error=t.data.details}})["catch"](t=>{}).then(t=>{this.is_loading=false})},showCustomer(){this.$emit("viewCustomer")},printOrder(){this.$emit("to-print",this.order_uuid)},refundFull(){var t={refund_type:"full",order_uuid:this.order_info.order_uuid,total:this.order_info.total,pretty_total:this.order_info.pretty_total};this.$refs.refund.confirm(t).then(t=>{r(t)})},refundPartial(){r("refundPartial")},updateOrder(){r(this.summary_changes.method);if(this.summary_changes.method=="total_decrease"){var t=this.update_order_label.content;t=t.replace("{{amount}}",this.summary_changes.refund_due_pretty);bootbox.confirm({size:"small",title:"",message:"<h5>"+this.update_order_label.title+"</h5>"+"<p>"+t+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.update_order_label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.update_order_label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){r(t)}}})}},SwitchPrinter(t,e){if(e=="feieyun"){this.FPprint(t)}else{this.wifiPrint(t)}},FPprint(t){this.$refs.loading_box.show();axios({method:"put",url:this.ajax_url+"/FPprint",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,printer_id:t},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})},wifiPrint(t){this.$refs.loading_box.show();axios({method:"POST",url:this.ajax_url+"/wifiPrint",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&printerId="+t+"&order_uuid="+this.order_uuid}).then(t=>{if(t.data.code==1){this.ConnectToPrintServer();setTimeout(()=>{this.sendServerToPrinter(t.data.details.data,t.data.msg)},500)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})},ConnectToPrintServer(){let t=!u(printerServer)?printerServer:null;if(this.webSocket==null){console.log("CONNECT ConnectToPrintServer");this.webSocket=new WebSocket(t);this.webSocket.onopen=function(t){console.log("WebSocket is open now.")};this.webSocket.onmessage=function(t){console.log("Received message from server:",t.data)};this.webSocket.onclose=function(t){console.log("WebSocket is closed now.");this.webSocket=null};this.webSocket.onerror=t=>{this.webSocket=null;this.socket_error="WebSocket error occurred, but no detailed ErrorEvent is available.";if(t instanceof ErrorEvent){this.socket_error=t.message}}}},sendServerToPrinter(t,e){if(this.webSocket.readyState===WebSocket.OPEN){this.webSocket.send(JSON.stringify(t));ElementPlus.ElNotification({title:"",message:e,position:"bottom-right",type:"success"})}else{if(u(this.socket_error)){this.socket_error="WebSocket is not open. Ready state is:"+this.webSocket.readyState}ElementPlus.ElNotification({title:"",message:this.socket_error,position:"bottom-right",type:"warning"})}},SendToKitchen(){this.$refs.loading_box.show();axios({method:"PUT",url:this.ajax_url+"/SendToKitchen",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,order_info:{customer_name:this.order_info.customer_name,order_id:this.order_info.order_id,merchant_id:this.merchant.merchant_id,merchant_uuid:this.merchant.merchant_uuid,order_type:this.order_info.order_type,transaction_type:this.order_info.transaction_type,delivery_date:this.order_info.delivery_date,whento_deliver:this.order_info.whento_deliver,delivery_time:this.order_info.delivery_time,timezone:this.order_info.timezone},order_table_data:this.order_table_data,items:this.items}}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});this.found_in_kitchen=true}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})}},template:"#xtemplate_order_details"};const pt={props:["ajax_url","label","order_uuid"],data(){return{data:[],is_loading:false,time_delay:""}},mounted(){this.getDelayedMinutes()},computed:{hasData(){if(!u(this.time_delay)){return true}return false}},methods:{show(){this.time_delay="";h(this.$refs.delay_modal).modal("show")},close(){h(this.$refs.delay_modal).modal("hide")},getDelayedMinutes(){axios({method:"put",url:this.ajax_url+"/getDelayedMinutes",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{})},confirm(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/setDelayToOrder",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),time_delay:this.time_delay,order_uuid:this.order_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.close();o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
	<div ref="delay_modal" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      
       <p class="m-0">{{label.sub1}}</p>
       <p class="m-0">{{label.sub2}}</p>
                     
       <div class="w-75 m-auto">
       <div class="row mt-4">
         <div v-for="item in data" class="col-lg-4 col-md-4 col-sm-6 col-4  mb-2">
           <button 
           :class="{active:time_delay==item.id}" 
           @click="time_delay=item.id"
           class="btn btn-light delay-btn">
           {{item.value}}
           </button>
         </div>
       </div>
       </div>
      
       </div>      
      <div class="modal-footer">            
        <button type="button" @click="confirm" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
         :disabled="!hasData"
         >
          <span>{{label.confirm}}</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
    </div>
  </div>
</div>        
	`};const ft={props:["ajax_url","label","order_uuid"],data(){return{is_loading:false,data:[],order_status:[],error:[]}},methods:{show(){this.data=[];this.order_status=[];h(this.$refs.history_modal).modal("show");this.getHistory()},close(){h(this.$refs.history_modal).modal("hide")},getHistory(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/getOrderHistory",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.order_status=t.data.details.order_status;this.error=[]}else{this.error=t.data.msg;this.data=[];this.order_status=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
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
	`};const x={props:["ajax_url","label","image_placeholder","merchant_id","responsive"],data(){return{is_loading:false,category_list:[],active_category:"all",item_list:[],observer:undefined,total_results:"",current_page:0,page_count:0,page:0,awaitingSearch:false,q:"",owl:undefined,replace_item:[]}},mounted(){this.getCategory();setTimeout(()=>{this.categoryItem(0)},500);this.observer=lozad(".lozad",{loaded:function(t){t.classList.add("loaded")}})},updated(){this.observer.observe()},watch:{q(t,e){if(!this.awaitingSearch){if(u(t)){return false}setTimeout(()=>{axios({method:"put",url:this.ajax_url+"/categoryItem",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:this.merchant_id,cat_id:0,page:0,q:this.q},timeout:s}).then(t=>{if(t.data.code==1){this.item_list=t.data.details.data;this.total_results=t.data.details.total_records;this.current_page=t.data.details.current_page;this.page_count=t.data.details.page_count}else{this.item_list=[];this.current_page=0;this.page_count=0;this.total_results=t.data.msg}})["catch"](t=>{}).then(t=>{this.awaitingSearch=false})},1e3)}this.item_list=[];this.awaitingSearch=true}},methods:{show(){this.q="";h(this.$refs.menu_modal).modal("show")},close(){h(this.$refs.menu_modal).modal("hide")},getCategory(){axios({method:"put",url:this.ajax_url+"/getCategory",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:this.merchant_id},timeout:s}).then(t=>{if(t.data.code==1){this.category_list=t.data.details.data.category}else{this.category_list=[]}})["catch"](t=>{}).then(t=>{})},pageNext(){this.page=parseInt(this.page)+1;if(this.page>=this.page_count){this.page=this.page_count}this.categoryItem(this.active_category)},pagePrev(){this.page=parseInt(this.page)-1;if(this.page<=0){this.page=0}this.categoryItem(this.active_category)},pageWithID(t){this.page=t;this.categoryItem(this.active_category)},categoryItem(t){this.active_category=t;this.is_loading=true;axios({method:"put",url:this.ajax_url+"/categoryItem",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:this.merchant_id,cat_id:t,page:this.page},timeout:s}).then(t=>{if(t.data.code==1){this.item_list=t.data.details.data;this.total_results=t.data.details.total_records;this.current_page=t.data.details.current_page;this.page_count=t.data.details.page_count}else{this.item_list=[];this.current_page=0;this.page_count=0;this.total_results=t.data.msg}})["catch"](t=>{}).then(t=>{this.is_loading=false;this.RenderCarousel()})},itemShow(t){var e={merchant_id:this.merchant_id,item_uuid:t.item_uuid,category_id:t.category_id[0],replace_item:this.replace_item};this.close();this.$emit("showItem",e)},RenderCarousel(){this.owl=h(this.$refs.carousel).owlCarousel({nav:true,dots:false,responsive:this.responsive})}},template:"#xtemplate_menu"};var w=[];var k=[];var S=[];var I=[];var T;var O=0;const C={components:{"money-format":e},props:["ajax_url","label","image_placeholder","merchant_id","order_type","order_uuid"],data(){return{is_loading:false,items:[],item_addons:[],item_addons_load:false,size_id:0,disabled_cart:true,item_qty:1,item_total:0,add_to_cart:false,meta:[],special_instructions:"",sold_out_options:[],if_sold_out:"substitute",transaction_type:"",observer:undefined,old_item:[]}},mounted(){h(this.$refs.modal_item_details).on("hide.bs.modal",t=>{this.$emit("goBack",this.old_item)});this.observer=lozad(".lozad",{loaded:function(t){r("image loaded");t.classList.add("loaded")}})},updated(){if(this.item_addons_load==true){this.ItemSummary()}},methods:{show(t){h(this.$refs.modal_item_details).modal("show");this.old_item=t.replace_item;this.viewItem(t)},close(){this.items=[];this.item_addons=[];this.meta=[];h(this.$refs.modal_item_details).modal("hide");this.$emit("goBack",this.old_item)},viewItem(t){var e={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:this.merchant_id,item_uuid:t.item_uuid,cat_id:t.category_id};var a=1;e=JSON.stringify(e);_[a]=h.ajax({url:this.ajax_url+"/getMenuItem",method:"PUT",dataType:"json",data:e,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(_[a]!=null){_[a].abort()}}}).done(t=>{if(t.code==1){w=t.details.data.items;k=t.details.data.addons;S=t.details.data.addon_items;I=t.details.data.items.item_addons;var e=t.details.data.meta;var i=t.details.data.meta_details;var l={cooking_ref:[],ingredients:[],dish:[]};let s=t.details.data.items.ingredients_preselected;r("ingredients_preselected");r(s);if(!u(e)){h.each(e,function(a,t){h.each(t,function(t,e){if(!u(i[a])){if(!u(i[a][e])){let t=false;if(a=="ingredients"&&s){t=true}l[a].push({meta_id:i[a][e].meta_id,meta_name:i[a][e].meta_name,checked:t})}}})})}var a=w.price;var o=Object.keys(a)[0];this.item_qty=1;this.items=w;this.size_id=o;this.meta=l;this.getSizeData(o);this.sold_out_options=t.details.sold_out_options}}).always(t=>{this.is_loading=false;this.observer.observe()})},setItemSize(t){var e=t.currentTarget.firstElementChild.value;this.size_id=e;this.getSizeData(e)},getSizeData(a){T=[];var s=[];if(!u(I[a])){h.each(I[a],function(t,e){if(!u(k[a])){if(!u(k[a][e])){k[a][e].subcat_id;h.each(k[a][e].sub_items,function(t,e){if(!u(S[e])){s.push({sub_item_id:S[e].sub_item_id,sub_item_name:S[e].sub_item_name,item_description:S[e].item_description,price:S[e].price,pretty_price:S[e].pretty_price,checked:false,disabled:false,qty:1})}});T.push({subcat_id:k[a][e].subcat_id,subcategory_name:k[a][e].subcategory_name,subcategory_description:k[a][e].subcategory_description,multi_option:k[a][e].multi_option,multi_option_min:k[a][e].multi_option_min,multi_option_value:k[a][e].multi_option_value,require_addon:k[a][e].require_addon,pre_selected:k[a][e].pre_selected,sub_items_checked:"",sub_items:s});s=[]}}})}this.item_addons=T;this.item_addons_load=true},ItemSummary(t){O=0;var d=[];var n=[];let c=[];let m=[];if(!u(this.items.price)){if(!u(this.items.price[this.size_id])){var e=this.items.price[this.size_id];if(e.discount>0){O+=this.item_qty*parseFloat(e.price_after_discount)}else O+=this.item_qty*parseFloat(e.price)}}this.item_addons.forEach((a,s)=>{if(a.require_addon==1){d.push(a.subcat_id)}if(a.multi_option=="custom"){var i=0;let t=a.multi_option_min;var e=a.multi_option_value;var l=[];var o=[];if(e>0){c.push({subcat_id:a.subcat_id,min:t,max:e})}a.sub_items.forEach((t,e)=>{if(t.checked==true){i++;O+=this.item_qty*parseFloat(t.price);n.push(a.subcat_id)}else l.push(e);if(t.disabled==true){o.push(e)}});m[a.subcat_id]={total:i};if(i>=e){l.forEach((t,e)=>{this.item_addons[s].sub_items[t].disabled=true})}else{o.forEach((t,e)=>{this.item_addons[s].sub_items[t].disabled=false})}}else if(a.multi_option=="one"){a.sub_items.forEach((t,e)=>{if(t.sub_item_id==a.sub_items_checked){O+=this.item_qty*parseFloat(t.price);n.push(a.subcat_id)}})}else if(a.multi_option=="multiple"){var l=[];let t=a.multi_option_min;var e=a.multi_option_value;var r=0;if(e>0){c.push({subcat_id:a.subcat_id,min:t,max:e})}a.sub_items.forEach((t,e)=>{if(t.checked==true){O+=t.qty*parseFloat(t.price);n.push(a.subcat_id);r+=t.qty}l.push(e)});m[a.subcat_id]={total:r};this.item_addons[s].qty_selected=r;if(this.item_addons[s].qty_selected>=e){l.forEach((t,e)=>{this.item_addons[s].sub_items[t].disabled=true})}else{l.forEach((t,e)=>{this.item_addons[s].sub_items[t].disabled=false})}}});this.item_total=O;var i=true;if(d.length>0){h.each(d,function(t,e){if(n.includes(e)===false){i=false;return false}})}if(this.items.cooking_ref_required){let a=false;if(Object.keys(this.meta.cooking_ref).length>0){Object.entries(this.meta.cooking_ref).forEach(([t,e])=>{if(e.checked){a=true}})}if(!a){i=false}}if(Object.keys(c).length>0){let a,s;Object.entries(c).forEach(([t,e])=>{a=parseInt(e.min);if(m[e.subcat_id]){s=parseInt(m[e.subcat_id].total)}if(s>0){if(a>s){i=false}}})}if(i){this.disabled_cart=false}else this.disabled_cart=true},CheckaddCartItems(){this.addCartItems()},addCartItems(t){if(t){t.preventDefault()}this.add_to_cart=true;var e={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:this.items.merchant_id,cat_id:this.items.cat_id,item_token:this.items.item_token,item_size_id:this.size_id,item_qty:this.item_qty,item_addons:this.item_addons,special_instructions:this.special_instructions,meta:this.meta,order_uuid:this.order_uuid,transaction_type:this.order_type,if_sold_out:this.if_sold_out};if(!u(this.old_item)){e.old_item_token=this.old_item.item_token;e.item_row=this.old_item.item_row}var a=1;e=JSON.stringify(e);_[a]=h.ajax({url:this.ajax_url+"/addCartItems",method:"PUT",dataType:"json",data:e,contentType:i.json,timeout:s,crossDomain:true,beforeSend:function(t){if(_[a]!=null){_[a].abort()}}});_[a].done(t=>{if(t.code==1){h(this.$refs.modal_item_details).modal("hide");this.$emit("refreshOrder",this.order_uuid);o(t.msg,"success");if(!u(this.old_item)){this.$emit("close-menu")}}else{o(t.msg,"error")}});_[a].always(t=>{this.add_to_cart=false})}},template:"#xtemplate_item"};const N={props:["label","ajax_url","client_id","image_placeholder","page_limit","merchant_id"],data(){return{is_loading:false,customer:[],addresses:[],block_from_ordering:false,datatables:undefined,count_up:undefined}},mounted(){this.observer=lozad(".lozad",{loaded:function(t){t.classList.add("loaded");r("image loaded")}})},computed:{hasData(){if(Object.keys(this.customer).length>0){return true}return false}},updated(){this.observer.observe()},methods:{show(){this.getCustomerDetails();this.getCustomerOrders();this.getCustomerSummary();h(this.$refs.customer_modal).modal("show")},close(){h(this.$refs.customer_modal).modal("hide")},getCustomerDetails(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/getCustomerDetails",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),client_id:this.client_id,merchant_id:this.merchant_id},timeout:s}).then(t=>{if(t.data.code==1){this.customer=t.data.details.customer;this.addresses=t.data.details.addresses;this.block_from_ordering=t.data.details.block_from_ordering}else{this.customer=[];this.addresses=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getCustomerOrders(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),client_id:this.client_id};this.datatables=h(this.$refs.order_table).DataTable({ajax:{url:this.ajax_url+"/getCustomerOrders",contentType:"application/json",type:"PUT",data:t=>{t.YII_CSRF_TOKEN=h("meta[name=YII_CSRF_TOKEN]").attr("content");t.client_id=this.client_id;t.merchant_id=this.merchant_id;return JSON.stringify(t)}},language:{url:ajaxurl+"/DatableLocalize"},serverSide:true,processing:true,pageLength:parseInt(this.page_limit),destroy:true,lengthChange:false,order:[[0,"desc"]],columns:[{data:"order_id"},{data:"total"},{data:"status"},{data:"order_uuid"}]})},blockCustomerConfirmation(){if(!this.block_from_ordering){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.block_customer+"</h5>"+"<p>"+this.label.block_content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.blockOrUnlockCustomer(1)}}})}else{this.blockOrUnlockCustomer(0)}},blockOrUnlockCustomer(t){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/blockCustomer",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),client_id:this.client_id,merchant_id:this.merchant_id,block:t},timeout:s}).then(t=>{if(t.data.code==1){if(t.data.details==1){this.block_from_ordering=true}else this.block_from_ordering=false}else{this.block_from_ordering=false}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getCustomerSummary(){axios({method:"POST",url:this.ajax_url+"/getCustomerSummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&client_id="+this.client_id+"&merchant_id="+this.merchant_id,timeout:s}).then(t=>{if(t.data.code==1){var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_orders,t.data.details.orders,e);a.start();var s=new countUp.CountUp(this.$refs.summary_cancel,t.data.details.order_cancel,e);s.start();var e=t.data.details.price_format;var i=this.count_up=new countUp.CountUp(this.$refs.summary_total,t.data.details.total,e);i.start();var l=this.count_up=new countUp.CountUp(this.$refs.summary_refund,t.data.details.total_refund,e);l.start()}else{}})["catch"](t=>{}).then(t=>{})}},template:"#xtemplate_customer"};const E={components:{"money-format":e},props:["label","ajax_url","order_uuid","mode","line"],template:"#xtemplate_print_order",data(){return{is_loading:false,merchant:[],order_info:[],order_status:[],services:[],payment_status:[],items:[],order_summary:[],print_settings:[],payment_list:[],order_table_data:[],loading_printing:false}},computed:{hasData(){if(this.order_summary.length>0){return true}return false},hasBooking(){if(Object.keys(this.order_table_data).length>0){return true}return false}},methods:{show(){this.orderDetails();h(this.$refs.print_modal).modal("show")},close(){h(this.$refs.print_modal).modal("hide")},orderDetails(){this.order_summary=[];this.is_loading=true;var t=["print_settings"];axios({method:"put",url:this.ajax_url+"/orderDetails",data:{order_uuid:this.order_uuid,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),payload:t},timeout:s}).then(t=>{this.response_code=t.data.code;if(t.data.code==1){this.merchant=t.data.details.data.merchant;this.order_info=t.data.details.data.order.order_info;this.payment_list=t.data.details.data.order.payment_list;this.order_status=t.data.details.data.order.status;this.services=t.data.details.data.order.services;this.payment_status=t.data.details.data.order.payment_status;this.items=t.data.details.data.items;this.order_summary=t.data.details.data.summary;this.print_settings=t.data.details.data.print_settings;this.order_table_data=t.data.details.data.order_table_data}else{o(t.data.msg,"error");this.merchant_direction="";this.delivery_direction="";this.merchant=[];this.order_info=[];this.items=[];this.order_summary=[];this.print_settings=[];this.order_table_data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false;this.loading=false})},print(){h(".printhis").printThis();this.$refs.print_button.disabled=true;setTimeout(()=>{this.$refs.print_button.disabled=false},1e3)},FPprint(t){console.log("printerId",t);console.log("order_info",this.order_info.order_uuid);this.loading_printing=true;axios({method:"put",url:this.ajax_url+"/FPprint",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_info.order_uuid,printer_id:t},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_printing=false})}}};const vt={props:["label","ajax_url","filter_status"],data(){return{is_loading:false,status_list:[],order_type_list:[],payment_status_list:[],sort_list:[],status:[],order_type:[],payment_status:[],sort:"",search_filter:"",awaitingSearch:false,search_toggle:false}},mounted(){this.getOrderFilterSettings();this.selectPicker()},watch:{search_filter(t,e){if(!this.awaitingSearch){if(u(t)){return false}setTimeout(()=>{this.search_toggle=true;this.$emit("afterFilter",{search_filter:this.search_filter,order_type:this.order_type,payment_status:this.payment_status,sort:this.sort});this.awaitingSearch=false},1e3)}this.awaitingSearch=true}},methods:{getOrderFilterSettings(){axios({method:"POST",url:this.ajax_url+"/getOrderFilterSettings",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.status_list=t.data.details.status_list;this.order_type_list=t.data.details.order_type_list;this.payment_status_list=t.data.details.payment_status_list;this.sort_list=t.data.details.sort_list;setTimeout(()=>{h(".selectpicker").selectpicker("refresh")},1)}else{}})["catch"](t=>{}).then(t=>{})},selectPicker(){h(this.$refs.order_type_list).on("changed.bs.select",(t,e,a,s)=>{this.order_type=h(this.$refs.order_type_list).selectpicker("val");this.$emit("afterFilter",{order_type:this.order_type,payment_status:this.payment_status,sort:this.sort})});h(this.$refs.payment_status_list).on("changed.bs.select",(t,e,a,s)=>{this.payment_status=h(this.$refs.payment_status_list).selectpicker("val");this.$emit("afterFilter",{order_type:this.order_type,payment_status:this.payment_status,sort:this.sort})});h(this.$refs.sort_list).on("changed.bs.select",(t,e,a,s)=>{this.sort=h(this.$refs.sort_list).selectpicker("val");this.$emit("afterFilter",{order_type:this.order_type,payment_status:this.payment_status,sort:this.sort})})},clearFiler(){this.search_toggle=false;this.search_filter="";this.$emit("afterFilter",{search_filter:"",order_type:this.order_type,payment_status:this.payment_status,sort:this.sort})}},template:`
	<div class="order-search-nav p-2">
	
	  <div class="row">
	    <div class="col-md-6 pl-2 mb-2 mb-lg-0">
	    
	     <div class="input-group mr-2">
		    <input v-model="search_filter" class="form-control py-2 border-right-0 border" type="search" 
		    :placeholder="label.placeholder"
		    >
		    <span class="input-group-append">
		        <div v-if="!awaitingSearch" class="input-group-text bg-transparent"><i class="zmdi zmdi-search"></i></div>
		        <div v-if="awaitingSearch" class="input-group-text bg-transparent"><i class="fas fa-circle-notch fa-spin"></i></div>
		        <div v-if="search_toggle" @click="clearFiler" class="input-group-text bg-transparent"><a class="m-0 link font12">Clear</a></div>
		    </span>
		  </div>  
	    
	    </div> <!--col-->
	    
	    <div class="col-md-6">	   	  
	    
	       <div class="d-flex selectpicker-group rounded">
	         <div v-if="filter_status" class="flex-col">
	         <select ref="status_list" data-width="fit" class="selectpicker" multiple="multiple"  :title="label.status" data-selected-text-format="static" >		    		    
		       <option v-for="(item, key) in status_list" :value="key">{{item}}</option>		    
		     </select>	
	         </div>
	         <div class="flex-col">
	          <select ref="order_type_list"  data-width="fit" class="selectpicker" multiple="multiple" :title="label.order_type_list" data-selected-text-format="static" >		    		    
		       <option v-for="(item, key) in order_type_list" :value="key">{{item}}</option>		    
		      </select>		
	         </div>
	         <div class="flex-col">
	          <select ref="payment_status_list"  data-width="fit" class="selectpicker" multiple="multiple" :title="label.payment_status_list" data-selected-text-format="static" >		    		    
		       <option v-for="(item, key) in payment_status_list" :value="key">{{item}}</option>		    
		      </select>		
	         </div>
	         <div class="flex-col">	          
	          <select ref="sort_list"  data-width="fit" class="selectpicker" :title="label.sort" data-selected-text-format="static" >		    		    
		       <option v-for="(item, key) in sort_list" :value="key" :data-icon="item.icon" >{{item.text}}</option>		    
		      </select>		
	         </div>
	       </div>		  	    
	    </div> <!--col-->
	    
	  </div>
	  <!--flex-->
	
	</div>
	<!--order searcb-nav-->		
	`};const gt={props:{label:Array,ajax_url:String,pause_interval:{type:Number,"default":10}},data(){return{is_loading:false,data:[],time_delay:"",steps:1,pause_reason:[],reason:"",pause_hours:0,pause_minutes:0}},mounted(){this.getDelayedMinutes()},computed:{hasData(){if(this.time_delay=="other"){if(this.pause_hours>0){return true}if(this.pause_minutes>0){return true}}else{if(!u(this.time_delay)){return true}}return false},hasReason(){if(!u(this.reason)){return true}return false}},methods:{show(){h(this.$refs.modal_pause_order).modal("show")},close(){h(this.$refs.modal_pause_order).modal("hide")},getDelayedMinutes(){axios({method:"post",url:this.ajax_url+"/getPauseOptions",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{})},submit(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/setPauseOrder",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),time_delay:this.time_delay,reason:this.reason,pause_hours:this.pause_hours,pause_minutes:this.pause_minutes},timeout:s}).then(t=>{if(t.data.code==1){this.steps=1;this.time_delay="";this.close();this.$emit("afterPause",t.data.details)}else{o(t.data.msg)}})["catch"](t=>{}).then(t=>{this.is_loading=false})},addMins(){if(this.pause_minutes>=60){this.pause_minutes=0;this.pause_hours+=1}else{this.pause_minutes+=this.pause_interval}},lessMins(){if(this.pause_minutes<=0){if(this.pause_hours>0){this.pause_minutes=60;this.pause_hours-=1}else{this.pause_minutes=0}}else{this.pause_minutes-=this.pause_interval}},cancel(){if(this.time_delay=="other"){this.steps=1;this.time_delay=""}else{this.close()}}},template:`	
	   <div ref="modal_pause_order" class="modal"
	    id="modal_pause_order"  data-backdrop="static" 
	    tabindex="-1" role="dialog" aria-labelledby="modal_pause_order" aria-hidden="true">
	    	   
	       <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
		     <div class="modal-content pt-2">
		     		      		   		     
		     <template v-if="steps==1">
		      <div class="modal-body">	
		        			    
			   <div class="w-75 m-auto">
				    <h5>{{label.pause_new_order}}</h5>
				    <p>{{label.how_long}}</p>
				    				    
				   <template v-if="time_delay=='other'">				   
				   <div class="d-flex justify-content-center align-items-center text-center">
				     <div class="flex-col mr-3"><button @click="lessMins" class="btn rounded-button-icon rounded-circle"><i class="zmdi zmdi-minus"></i></button></div>
				     <div class="flex-col mr-1"><h1>{{pause_hours}}</h2> <p class="m-0 font9 font-weight-bold">{{label.hours}}</p></div>
				     <div class="flex-col mr-1"><h1>:</h2><p  class="m-0 font11">&nbsp;</p></div>
				     <div class="flex-col m-0"><h1>{{pause_minutes}}</h2> <p class="m-0 font9 bold font-weight-bold">{{label.minute}}</p></div>
				     <div class="flex-col ml-3"><button @click="addMins" class="btn rounded-button-icon rounded-circle"><i class="zmdi zmdi-plus"></i></button></div>
				   </div>				   				   				  
				   </template>
				   
				   <template v-else>				   
			       <div class="row mt-4">
			         <div v-for="item in data.times" class="col-lg-4 col-md-4 col-sm-6 col-4 mb-2 mb-2 ">
			           <button 
			           :class="{active:time_delay==item.id}" 
			           @click="time_delay=item.id"
			           class="btn btn-light">
			           {{item.value}}
			           </button>
			         </div>
			       </div>
			       </template>
			        
		       </div> <!-- w-75 -->
			    
			  </div> <!-- body -->
			  
	          <div class="modal-footer">         
			   <button type="button" class="btn btn-black" @click="cancel">
	            <span class="pl-3 pr-3">{{label.cancel}}</span>
	           </button>	           
		        <button type="submit"  class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"   
		        :disabled="!hasData"
		        @click="steps=2"
		         >
		          <span>{{label.next}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
		        </button>
		     </div>
		     </template>
		     
		     <template v-else-if="steps==2">
		     <div class="modal-body">		 
			 
		      <div class="w-75 m-auto">
			    <h5>{{label.reason}}</h5>
			  </div>
			    
		       <div class="list-group list-group-flush mt-4">
		         <a v-for="item in data.pause_reason" @click="reason=item" 
		         :class="{active:reason==item}"
		         class="text-center list-group-item list-group-item-action">
		         {{item}}
		         </a>
		       </div>
		     
			  </div> <!-- body -->
			  			  
	          <div class="modal-footer">               
			   <button type="button" class="btn btn-black"
			   @click="steps=1"
			    >
	            <span class="pl-3 pr-3">{{label.cancel}}</span>
	           </button>
			       
		        <button type="submit"  class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"   
		        :disabled="!hasReason"
		        @click="submit"
		         >
		          <span>{{label.confirm}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
		        </button>
		     </div>
		     </template>
			     		     
		     </div> <!--content-->		     
		  </div> <!--dialog-->		  
		</div> <!--modal-->     	
	`};const bt={props:{endDate:{type:Date,"default"(){return new Date}},negative:{type:Boolean,"default":false}},data(){return{now:new Date,timer:null}},computed:{hour(){let t=Math.trunc((this.endDate-this.now)/1e3/3600);return t>9?t:"0"+t},min(){let t=Math.trunc((this.endDate-this.now)/1e3/60)%60;return t>9?t:"0"+t},sec(){let t=Math.trunc((this.endDate-this.now)/1e3)%60;return t>9?t:"0"+t}},watch:{endDate:{immediate:true,handler(t){if(this.timer){clearInterval(this.timer)}this.timer=setInterval(()=>{this.now=new Date;if(this.negative)return;if(this.now>t){this.now=t;this.$emit("endTime");clearInterval(this.timer)}},1e3)}}},beforeUnmount(){clearInterval(this.timer)},template:`	
	  <p class="m-0 mt-1 text-center font-weight-bold"><slot></slot> {{hour}}:{{min}}:{{sec}} </p>
	`};const yt={props:["label","ajax_url"],components:{"components-timer-countdown":bt},data(){return{is_load:false,accepting_order:false,data:[],pause_time:undefined,luxon:undefined}},mounted(){this.getMerchantOrderingStatus();this.luxon=luxon.DateTime},methods:{getMerchantOrderingStatus(){this.is_load=true;axios({method:"post",url:this.ajax_url+"/MerchantOrderingStatus",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;this.accepting_order=t.data.details.accepting_order;this.pause_time=this.luxon.fromISO(t.data.details.pause_time)}else{this.data=[];this.pause_order=false}})["catch"](t=>{}).then(t=>{this.is_load=false})},PauseOrdering(){this.$emit("afterClickpause",this.accepting_order)},pauseOrderEnds(){axios({method:"put",url:this.ajax_url+"/UpdateOrderingStatus",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),accepting_order:true},timeout:s}).then(t=>{if(t.data.code==1){this.accepting_order=t.data.details.accepting_order}else{this.accepting_order=false}})["catch"](t=>{}).then(t=>{this.is_load=false})},updateStatus(t){r(t);this.accepting_order=t.accepting_order;this.pause_time=this.luxon.fromISO(t.pause_time)}},template:`
	 <div class="position-relative">
      <div v-if="is_load" class="skeleton-placeholder" style="height:50px;width:100%;"></div>
      
       <button @click="PauseOrdering" class="btn" :class="{'btn-green' :accepting_order, 'btn-yellow': accepting_order==false}">   
	     <div class="d-flex justify-content-between align-items-center">
	       <template v-if="accepting_order" >
		       <div class="mr-0 mr-lg-2"><i style="font-size:20px;" class="zmdi zmdi-check-circle"></i></div>
		       <div class="xd-none xd-lg-block" >{{label.accepting_orders}}</div>       
	       </template>
	       <template v-else>  
	           <div class="mr-2"><i style="font-size:20px;" class="zmdi zmdi-pause"></i></div>
	           <div>{{label.not_accepting_orders}}</div>
	       </template>
	     </div>
	   </button>
	   	   
	   <template v-if="!is_load">
	   <template v-if="!accepting_order">
	   <components-timer-countdown
	    :endDate='pause_time'
	    @end-time="pauseOrderEnds"
	     >
	     {{label.store_pause}}
	   </components-timer-countdown>
	   </template>
	   </template>
	   
      
      </div>          
	`};const xt={props:["label","ajax_url"],data(){return{is_loading:false}},methods:{show(){h(this.$refs.modal).modal("show")},close(){h(this.$refs.modal).modal("hide")},submit(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/UpdateOrderingStatus",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),accepting_order:true},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterPause",t.data.details);this.close()}else{o(t.data.msg)}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
		<div ref="modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
	    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
	    <div class="modal-content">
	      <div class="modal-body">	            	     
	        
	        <div class="w-75 m-auto">
			    <h5>{{label.store_pause}}</h5>
			    <p>{{label.resume_orders}}</p>
			</div>  
	      
	      </div>      
	      <div class="modal-footer">            
	      
	            <button type="button" class="btn btn-black" data-dismiss="modal">
	            <span class="pl-3 pr-3">{{label.cancel}}</span>
	           </button>
			       
		        <button type="submit"  class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"   		        		        
		        @click="submit"
		         >
		          <span>{{label.confirm}}</span>
		          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
		        </button>  
	      
	      </div>	      
	    </div>
	  </div>
	</div>            
	`};const wt=Vue.createApp({components:{"components-orderlist":ct,"components-orderinfo":_t,"components-delay-order":pt,"components-order-history":ft,"components-menu":x,"components-item-details":C,"components-customer-details":N,"components-order-print":E,"components-order-search-nav":vt,"components-pause-order":yt,"components-pause-modal":gt,"components-resume-order-modal":xt,"components-notification":et,"components-assign-driver":ot},data(){return{order_uuid:"",order_type:"",client_id:"",resolvePromise:undefined,rejectPromise:undefined,show_as_popup:false}},mounted(){},methods:{showAssigndriver(){this.$refs.assign_driver.show()},afterSelectOrder(t,e){this.order_uuid=t;this.order_type=e;this.$refs.orderinfo.orderDetails(t)},refreshOrderInformation(t){this.$refs.orderinfo.orderDetails(t)},afterUpdateStatus(){this.$refs.orderlist.getList();if(typeof j!=="undefined"&&j!==null){j.getOrdersCount()}},orderReject(t){this.$refs.rejection.confirm()},delayOrder(t){this.$refs.delay.show()},orderHistory(t){this.$refs.history.show()},showMerchantMenu(t){this.$refs.menu.replace_item=t;this.$refs.menu.show()},hideMerchantMenu(){this.$refs.menu.close()},showItemDetails(t){this.$refs.item.show(t)},viewCustomer(){this.$refs.customer.show()},toPrint(){this.$refs.print.show()},afterFilter(t){this.$refs.orderlist.getList(t)},afterClickpause(t){r(t);if(t){this.$refs.pause_modal.show()}else{this.$refs.resume_order.show()}},afterPause(t){this.$refs.pause_order.updateStatus(t)},closeOrderModal(){this.show_as_popup=false}}});wt.use(Maska);wt.use(ElementPlus);const F=wt.mount("#vue-order-management");const kt=Vue.createApp({components:{"components-orderinfo":_t,"components-delay-order":pt,"components-rejection-forms":mt,"components-order-history":ft,"components-order-print":E,"components-menu":x,"components-item-details":C,"components-customer-details":N,"components-assign-driver":ot},data(){return{order_uuid:"",client_id:"",merchant_id:"",is_loading:false,group_name:"",manual_status:false,modify_order:false,filter_buttons:false}},mounted(){this.order_uuid=_order_uuid;this.getGroupname()},methods:{showAssigndriver(){this.$refs.assign_driver.show()},afterUpdateStatus(){this.getGroupname();if(typeof j!=="undefined"&&j!==null){j.getOrdersCount()}},refreshOrderInformation(t){this.$refs.orderinfo.orderDetails(this.order_uuid)},loadOrderDetails(){this.$refs.orderinfo.orderDetails(this.order_uuid)},delayOrder(t){this.$refs.delay.show()},orderReject(t){this.$refs.rejection.confirm()},orderHistory(t){this.$refs.history.show()},toPrint(){this.$refs.print.show()},showMerchantMenu(t){this.$refs.menu.replace_item=t;this.$refs.menu.show()},showItemDetails(t){this.$refs.item.show(t)},viewCustomer(){this.$refs.customer.show()},getGroupname(){this.is_loading=true;axios({method:"POST",url:_ajax_url+"/getGroupname",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.group_name=t.data.details.group_name;this.manual_status=t.data.details.manual_status;this.modify_order=t.data.details.modify_order;this.client_id=t.data.details.client_id;this.merchant_id=t.data.details.merchant_id;this.filter_buttons=t.data.details.filter_buttons}else{this.client_id="";this.group_name="";this.manual_status=false;this.modify_order=false;this.filter_buttons=false}setTimeout(()=>{this.loadOrderDetails()},1)})["catch"](t=>{}).then(t=>{this.is_loading=false})}}});kt.use(Maska);kt.use(ElementPlus);const St=kt.mount("#vue-order-view");const j=Vue.createApp({mounted(){this.getOrdersCount()},data(){return{data:[],is_load:false}},methods:{getOrdersCount(){axios({method:"POST",url:apibackend+"/getOrdersCount",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){if(this.is_load){h(this.$refs.orders_new).find(".badge-notification").remove()}this.data=t.data.details;if(this.data.not_viewed>0){h(this.$refs.orders_new).append('<div class="blob green badge-pill pull-right badge-notification bg-new">'+this.data.new_order+"</div>")}else{h(this.$refs.orders_new).append('<div class="badge-pill pull-right badge-notification bg-new">'+this.data.new_order+"</div>")}h(this.$refs.orders_processing).append('<div class="badge-pill pull-right badge-notification bg-processing">'+this.data.order_processing+"</div>");h(this.$refs.orders_ready).append('<div class="badge-pill pull-right badge-notification bg-ready">'+this.data.order_ready+"</div>");h(this.$refs.orders_completed).append('<div class="badge-pill pull-right badge-notification bg-completed">'+this.data.completed_today+"</div>");h(this.$refs.orders_scheduled).append('<div class="badge-pill pull-right badge-notification bg-scheduled">'+this.data.scheduled+"</div>");h(this.$refs.orders_history).append('<div class="badge-pill pull-right badge-notification bg-history">'+this.data.all_orders+"</div>")}})["catch"](t=>{}).then(t=>{this.is_load=true})}}}).mount("#vue-siderbar-menu");const It={props:["ajax_url","settings"],template:"#xtemplate_order_filter",mounted(){this.initeSelect2();this.getFilterData()},data(){return{status_list:[],order_type_list:[],order_status:"",order_type:"",client_id:""}},methods:{initeSelect2(){h(".select2-single").select2({width:"resolve"});h(".select2-customer").select2({width:"resolve",language:{searching:()=>{return this.settings.searching},noResults:()=>{return this.settings.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchCustomer",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}});if(!u(this.$refs.driver_id)){h(".select2-driver").select2({width:"resolve",language:{searching:()=>{return this.settings.searching},noResults:()=>{return this.settings.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchDriver",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}})}},getFilterData(){axios({method:"put",url:this.ajax_url+"/getFilterData",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.status_list=t.data.details.status_list;this.order_type_list=t.data.details.order_type_list}else{this.status_list=[];this.order_type_list=[]}})["catch"](t=>{}).then(t=>{})},clearFilter(){h(this.$refs.order_status).val(null).trigger("change");h(this.$refs.order_type).val(null).trigger("change");h(this.$refs.client_id).val(null).trigger("change")},submitFilter(){this.order_status=h(this.$refs.order_status).find(":selected").val();this.order_type=h(this.$refs.order_type).find(":selected").val();this.client_id=h(this.$refs.client_id).find(":selected").val();this.$emit("afterFilter",{order_status:this.order_status,order_type:this.order_type,client_id:this.client_id})},closePanel(){this.$emit("closePanel")}}};const R={components:{"components-order-filter":It},props:["settings","actions","ajax_url","table_col","columns","page_limit","transaction_type_list","filter","filter_id","ref_id"],mounted(){this.getTableData();this.initDateRange();this.selectPicker();this.initSiderbar()},data(){return{datatables:undefined,date_range:"",date_start:"",date_end:"",transaction_type:[],sidebarjs:undefined,filter_by:[]}},methods:{initDateRange(){let t=["Su","Mo","Tu","We","Th","Fr","Sa"];let e=["January","February","March","April","May","June","July","August","September","October","November","December"];if(typeof daysofweek!=="undefined"&&daysofweek!==null){t=JSON.parse(daysofweek)}if(typeof monthsname!=="undefined"&&monthsname!==null){e=JSON.parse(monthsname)}var a=JSON.parse(translation_vendor);var s={};s[a.today]=[moment(),moment()];s[a.Yesterday]=[moment().subtract(1,"days"),moment().subtract(1,"days")];s[a.last_7_days]=[moment().subtract(6,"days"),moment()];s[a.last_30_days]=[moment().subtract(29,"days"),moment()];s[a.this_month]=[moment().startOf("month"),moment().endOf("month")];s[a.last_month]=[moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")];h(this.$refs.date_range).daterangepicker({autoUpdateInput:false,showWeekNumbers:true,alwaysShowCalendars:true,autoApply:true,locale:{format:"YYYY-MM-DD",daysOfWeek:t,monthNames:e,customRangeLabel:a.custom_range},ranges:s},(t,e,a)=>{this.date_range=t.format("YYYY-MM-DD")+" "+this.settings.separator+" "+e.format("YYYY-MM-DD");this.date_start=t.format("YYYY-MM-DD");this.date_end=e.format("YYYY-MM-DD");this.getTableData()})},selectPicker(){h(this.$refs.transaction_type).on("changed.bs.select",(t,e,a,s)=>{this.transaction_type=h(this.$refs.transaction_type).selectpicker("val");this.getTableData()})},initSiderbar(){if(this.filter==1){this.sidebarjs=new SidebarJS.SidebarElement({position:"right"})}},getTableData(){var a;var s=this;this.datatables=h(this.$refs.vue_table).DataTable({ajax:{url:this.ajax_url+"/"+this.actions,contentType:"application/json",type:"PUT",data:t=>{t.YII_CSRF_TOKEN=h("meta[name=YII_CSRF_TOKEN]").attr("content");t.date_start=this.date_start;t.date_end=this.date_end;t.transaction_type=this.transaction_type;t.filter=this.filter_by;t.filter_id=this.filter_id;t.ref_id=this.ref_id;return JSON.stringify(t)}},language:{url:ajaxurl+"/DatableLocalize"},serverSide:true,processing:true,pageLength:parseInt(this.page_limit),destroy:true,bFilter:this.settings.filter,ordering:this.settings.ordering,order:[[this.settings.order_col,this.settings.sortby]],columns:this.columns,dom:"<'row'<'col-sm-6'l><'col-sm-6'f>>"+"<'row'<'col-sm-12 text-right'B>>"+"<'row'<'col-sm-12'tr>>"+"<'row'<'col-sm-5'i><'col-sm-7'p>>",buttons:["excelHtml5","csvHtml5","pdfHtml5","print"]});a=this.datatables;let i=this.date_start;let l=this.date_end;a.on("draw.dt",function(){setTimeout(()=>{h(function(){h('[data-toggle="tooltip"]').tooltip()})},500)});a.on("preXhr.dt",function(t,e,a){if(!u(i)&&!u(l)){s.$emit("afterSelectdate",i,l)}});h(".vue_table tbody").on("click",".ref_invoice",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){s.$emit("viewInvoice",t.invoice_ref_number,t.payment_code)}});h(".vue_table tbody").on("click",".ref_tax_edit",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){s.$emit("editTax",t.tax_uuid)}});h(".vue_table tbody").on("click",".ref_tax_delete",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){s.$emit("deleteTax",t.tax_uuid)}});h(".vue_table tbody").on("click",".ref_edit",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){window.location.href=t.update_url}});h(".vue_table tbody").on("click",".ref_view_url",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){window.location.href=t.view_url}});h(".vue_table tbody").on("click",".ref_delete",function(){var e=a.row(h(this).parents("tr")).data();if(!u(e)){let t=JSON.parse(translation_vendor);bootbox.confirm({size:"small",title:"",message:"<h5>"+t["delete"]+"</h5>"+"<p>"+t.are_you_sure+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:t.cancel,className:"btn"},confirm:{label:t["delete"],className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){window.location.href=e.delete_url}}})}});h(".vue_table tbody").on("click",".ref_payout",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){s.viewTransaction(t.transaction_uuid)}});h(".vue_table tbody").on("click",".ref_duplicate",function(){let e=a.row(h(this).parents("tr")).data();let t=JSON.parse(translation_vendor);bootbox.confirm({size:"small",title:"",message:"<h5>"+t.duplicate_item+"</h5>"+"<p>"+t.duplicate_confirmation+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:t.cancel,className:"btn"},confirm:{label:t.duplicate,className:"btn btn-primary small pl-4 pr-4"}},callback:t=>{if(t){window.location.href=e.duplicate_url}}})});h(".vue_table tbody").on("click",".ref_edit_popup",function(){var t=a.row(h(this).parents("tr")).data();if(!u(t)){s.$emit("editRecords",t.rate_id)}})},openFilter(){this.sidebarjs.toggle()},afterFilter(t){this.sidebarjs.toggle();this.filter_by=t;this.getTableData()},closePanel(){this.sidebarjs.toggle()},viewTransaction(t){this.$emit("viewTransaction",t)}},template:`			
	
	 <div class="row mb-3">
	  <div class="col">
	      
	      <div class="d-flex">
	      	      
		  <div v-if="!settings.filter_date_disabled" class="input-group fixed-width-field mr-2">
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
		  
		  <button v-if="filter==1" class="btn btn-yellow normal" @click="openFilter" >		   		   
		   <div class="d-flex">
		     <div class="mr-2"><i class="zmdi zmdi-filter-list"></i></div>
		     <div>{{settings.filters}}</div>
		   </div>
		  </button>
		  
		  </div> <!-- flex -->
		  
	  </div>	  
	  <div class="col"></div>
	</div> <!--row-->
	
	<div class="table-responsive">
	<table ref="vue_table" class="table vue_table"  style="width:100%" >
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
    `};const Tt={props:["ajax_url"],data(){return{is_loading:false}},mounted(){this.getGetMerchantBalance()},methods:{getGetMerchantBalance(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/getGetMerchantBalance",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{var e;if(t.data.code==1){e=this.count_up=new countUp.CountUp(this.$refs.balance,t.data.details.balance,t.data.details.price_format);e.start();this.$emit("afterBalance",t.data.details.balance)}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<span ref="balance"></span>
	`};const Ot={props:["ajax_url","label","balance"],data(){return{is_loading:false,error:[],amount:0}},computed:{hasData(){var t=true;if(this.amount<=0){t=false}if(parseFloat(this.amount)>=parseFloat(this.balance)){t=false}return t}},methods:{show(){h(this.$refs.modal_payout).modal("show");this.$refs.amount.focus()},close(){h(this.$refs.modal_payout).modal("hide")},submit(){this.error=[];this.is_loading=true;axios({method:"put",url:this.ajax_url+"/requestPayout",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),amount:this.amount},timeout:s}).then(t=>{var e;if(t.data.code==1){o(t.data.msg);this.close();this.$emit("afterRequestpayout")}else{this.error=t.data.msg;location.href="#error_message"}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<div ref="modal_payout" class="modal"
    id="modal_payout" data-backdrop="static" 
    tabindex="-1" role="dialog" aria-labelledby="modal_payout" aria-hidden="true">
    
	   <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
		 
  		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  
		 <div class="modal-body">
		 
		 <div class="form-label-group">    
	       <input type="text" v-model="amount" id="amount"
	       v-maska="'#*.##'" 
	       ref="amount"
	       placeholder=""
	       class="form-control form-control-text" >
	       <label for="amount">{{label.amount}}</labe>
	     </div>
		     
		 
	     <div id="error_message" v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
	        <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
	     </div>
	     
         </div> <!-- body -->
         
          <div class="modal-footer">               
		   <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.close}}</span>
           </button>
		       
	        <button type="submit" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
	         :disabled="!hasData"
	         >
	          <span>{{label.submit}}</span>
	          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
	        </button>
	     </div>

	  </div> <!--content-->
	  </div> <!--dialog-->
	</div> <!--modal-->     	
	`};const Ct={props:["cash_link","label","amount_selection","minimum_cashin"],data(){return{data:[],cashin_amount:0,is_loading:false}},computed:{hasData(){if(this.cashin_amount<this.minimum_cashin){return false}return true}},methods:{show(){h(this.$refs.modal_cashin).modal("show")},close(){h(this.$refs.modal_cashin).modal("hide")},ContinueToPayment(){window.location.href=this.cash_link+"&amount="+parseFloat(this.cashin_amount)}},template:`	
   <div ref="modal_cashin" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">      
    
      <div class="modal-body">      
        <a @click="close" href="javascript:;" class="btn btn-black btn-circle rounded-pill"><i class="zmdi zmdi-close font20"></i></a>      
        
        <div class="d-flex justify-content-between align-items-center">
          <div><h4 class="m-0 mb-3 mt-3">{{label.add_to_balance}}</h4></div>
          <div class="text-muted font11">{{label.minimum_amount}}</div>
        </div>
        
        <p>{{label.how_much}}</p>
        
        <div  class="menu-categories medium mb-3 d-flex">
		  <a v-for="(item, key) in amount_selection" 
		  @click="cashin_amount=key"
		  :class="{active:key==cashin_amount}"  
		  class="text-center rounded align-self-center text-center">		    
		    <h5 class="m-0">{{item}}</h5>
		    <p class="m-0 mt-1 text-truncate">{{label.cashin}}</p>
		  </a>		  
		</div>
		
		<div class="mt-1 mb-2">
		    <div class="form-label-group">    
		       <input type="text" v-model="cashin_amount" id="cashin_amount"
		       placeholder=""
		       v-maska="'#*.##'" 
		       class="form-control form-control-text" >
		       <label for="cashin_amount">{{label.enter_amount}}</labe>
		     </div>
		</div>
        
                 		                                
      </div>  <!-- modal body -->      
      
      <div class="modal-footer border-0">                            
      
        <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.cancel}}</span>
           </button>
		       
        <button type="button"  class="btn btn-green pl-4 pr-4" 
         :disabled="!hasData"
         @click="ContinueToPayment"
         >
          <span>{{label.continue}}</span>          
        </button>  
      
      </div> <!-- modal footer -->
      
    </div> <!--content-->
   </div> <!--dialog-->
   </div> <!--modal-->     	              

    `};const Nt=Vue.createApp({components:{"components-datatable":R,"components-merchant-balance":Tt,"components-request-payout":Ot,"components-cash-in":Ct},data(){return{is_loading:false,balance:0}},methods:{requestPayout(){this.$refs.payout.show()},afterRequestpayout(){this.$refs.datatable.getTableData();this.$refs.balance.getGetMerchantBalance()},afterBalance(t){this.balance=t},showCashin(){this.$refs.cashin.show()}}});Nt.use(Maska);const Et=Nt.mount("#vue-commission-statement");const Ft={props:["ajax_url","label"],data(){return{is_loading:false,payment_provider_list:[],account_type:[],payment_provider:"",country_list:[],email_address:"",account_number:"",account_holder_name:"",account_holder_type:"individual",currency:"",routing_number:"",country:"",error:[],currency_list:[]}},computed:{hasData(){var t=true;if(u(this.payment_provider)){t=false}return t}},mounted(){this.getPayoutSettings()},methods:{show(){h(this.$refs.modal_payout_account).modal("show")},close(){h(this.$refs.modal_payout_account).modal("hide")},getPayoutSettings(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/getPayoutSettings",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{var e;if(t.data.code==1){this.payment_provider_list=t.data.details.provider;this.country_list=t.data.details.country_list;this.account_type=t.data.details.account_type;this.currency_list=t.data.details.currency_list;this.currency=t.data.details.default_currency;this.country=t.data.details.default_country}else{this.payment_provider_list=[];this.country_list=[];this.currency_list=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},submit(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_provider:this.payment_provider};switch(this.payment_provider){case"paypal":t.email_address=this.email_address;break;case"stripe":t.account_number=this.account_number;t.account_holder_name=this.account_holder_name;t.account_holder_type=this.account_holder_type;t.currency=this.currency;t.routing_number=this.routing_number;t.country=this.country;break;case"bank":t.full_name=this.full_name;t.billing_address1=this.billing_address1;t.billing_address2=this.billing_address2;t.city=this.city;t.state=this.state;t.post_code=this.post_code;t.country=this.country;t.account_name=this.account_name;t.account_number_iban=this.account_number_iban;t.swift_code=this.swift_code;t.bank_name=this.bank_name;t.bank_branch=this.bank_branch;break}this.error=[];this.is_loading=true;axios({method:"put",url:this.ajax_url+"/SetPayoutAccount",data:t,timeout:s}).then(t=>{var e;if(t.data.code==1){o(t.data.msg);this.close();this.$emit("afterSave")}else{this.error=t.data.msg;location.href="#error_message"}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
	<div ref="modal_payout_account" class="modal"
    id="modal_payout_account" data-backdrop="static" 
    tabindex="-1" role="dialog" aria-labelledby="modal_payout_account" aria-hidden="true">
    
	   <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
	     <div class="modal-content"> 
		 
  		  <div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			  <span aria-hidden="true">&times;</span>
			</button>
		  </div>
		  
		 <div class="modal-body">
		 		 
		 <form @submit.prevent="submit" >
		 		 
		 <div class="menu-categories medium mb-3 d-flex">
		  		   
		   <a v-for="(item, key) in payment_provider_list"  class="text-center"
	       @click="payment_provider=item.payment_code"	      
	       :class="{active:payment_provider==item.payment_code}"  
	        class="rounded align-self-center text-center"
	       >
	       
	       <template v-if="item.logo_type=='icon'">
	         <i :class="item.logo_class"></i>
	       </template>
	       <template v-else>
	         <img class="rounded lozad" 		        
		        :src="item.logo_image" 
		        class="rounded-pill lozad"
		     >    
	       </template>
	       
	       <p class="m-0 mt-1 text-truncate">{{item.payment_name}}</p>
	       </a>   
	       
		 </div>
		<!-- menu -->
		  
		  
		  <!-- PAYPAL -->
		  <div v-if="payment_provider==='paypal'">
		     <div class="form-label-group">    
		       <input type="text" v-model="email_address" id="email_address"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="email_address">{{label.email_address}}</labe>
		     </div>
		  </div>
		  <!-- PAYPAL --> 
		  
		  <!-- STRIPE -->		  
		  <div v-if="payment_provider==='stripe'">
		     <div class="form-label-group">    
		       <input type="text" v-model="account_number" id="account_number" 
		       v-maska="'#################'" 
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="account_number">{{label.account_number}}</labe>
		     </div>
		     
		     <div class="form-label-group">    
		       <input type="text" v-model="account_holder_name" id="account_holder_name"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="account_holder_name">{{label.account_holder_name}}</labe>
		     </div>
		     		     
		     <select ref="account_holder_type" v-model="account_holder_type"
		     class="form-control custom-select form-control-select mb-3"  >		    
		     <option v-for="(account_type_name, account_type_key) in account_type" :value="account_type_key">{{account_type_name}}</option>		    
		     </select>
		     		     
		     <select ref="currency" v-model="currency"
		     class="form-control custom-select form-control-select mb-3"  >		    
		     <option v-for="(currency_name, currency_key) in currency_list" :value="currency_key">{{currency_name}}</option>		    
		     </select>
		     		     
		     <div class="form-label-group">    
		       <input type="text" v-model="routing_number" id="routing_number"
		       v-maska="'#################'" 
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="routing_number">{{label.routing_number}}</labe>
		     </div>
		     
		     <select ref="country" v-model="country"
		     class="form-control custom-select form-control-select mb-3"  >		    
		     <option v-for="(country_name, country_key) in country_list" :value="country_key">{{country_name}}</option>		    
		     </select>
		     
		  </div>
		  <!-- STRIPE -->
		  
		  <!-- BANK -->
		  <div v-if="payment_provider==='bank'">
		  
		     <h6>{{label.account_information}}</h6>      
		     
		     <div class="form-label-group">    
		       <input type="text" v-model="account_name" id="account_name"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="account_name">{{label.account_name}}</labe>
		     </div>
		     
		     <div class="form-label-group">    
		       <input type="text" v-model="account_number_iban" id="account_number_iban"		       
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="account_number_iban">{{label.account_number_iban}}</labe>
		     </div>
		     
		     <div class="form-label-group">    
		       <input type="text" v-model="swift_code" id="swift_code"		       
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="swift_code">{{label.swift_code}}</labe>
		     </div>
		     
		     <div class="form-label-group">    
		       <input type="text" v-model="bank_name" id="bank_name"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="bank_name">{{label.bank_name}}</labe>
		     </div>
		     
		      <div class="form-label-group">    
		       <input type="text" v-model="bank_branch" id="bank_branch"
		       placeholder=""
		       class="form-control form-control-text" >
		       <label for="bank_branch">{{label.bank_branch}}</labe>
		     </div>
		     
		  </div>
		  <!-- BANK --> 
		  
		  <div id="error_message" v-if="error.length>0" class="alert alert-warning mb-2" role="alert">
	        <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
	       </div>      
		  
		  </div> <!-- body -->
		  
		   <div class="modal-footer">     

		   <button type="button" class="btn btn-black" data-dismiss="modal">
            <span class="pl-3 pr-3">{{label.close}}</span>
           </button>
		       
	        <button type="submit" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
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
	
	`};const jt=Vue.createApp({components:{"components-datatable":R,"components-merchant-balance":Tt,"components-set-account":Ft,"components-request-payout":Ot},data(){return{is_loading:false,payout_account:"",provider:"",balance:0}},mounted(){this.getPayoutAccount()},methods:{showAccountForm(){this.$refs.account.show()},afterSave(){this.getPayoutAccount()},afterBalance(t){this.balance=t},getPayoutAccount(){this.is_loading=true;axios({method:"put",url:apibackend+"/getPayoutAccount",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{var e;if(t.data.code==1){this.payout_account=t.data.details.account;this.provider=t.data.details.provider}else{this.payout_account="";this.provider=""}})["catch"](t=>{}).then(t=>{this.is_loading=false})},requestPayout(){this.$refs.payout.show()},afterRequestpayout(){this.$refs.datatable.getTableData();this.$refs.balance.getGetMerchantBalance()}}});jt.use(Maska);const Rt=jt.mount("#vue-withdrawals");const Yt=Vue.createApp({components:{"components-datatable":R},data(){return{is_loading:false}},mounted(){this.getOrderSummary()},methods:{getOrderSummary(){this.is_loading=true;axios({method:"POST",url:apibackend+"/getOrderSummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_orders,t.data.details.orders,e);a.start();var s=new countUp.CountUp(this.$refs.summary_cancel,t.data.details.order_cancel,e);s.start();var e=t.data.details.price_format;var i=this.count_up=new countUp.CountUp(this.$refs.summary_total,t.data.details.total,e);i.start();var l=this.count_up=new countUp.CountUp(this.$refs.total_refund,t.data.details.total_refund,e);l.start()}else{}})["catch"](t=>{}).then(t=>{this.is_loading=false})}}});const $t=Yt.mount("#vue-order-history");const Pt={template:"#xtemplate_webpushsettings",props:["ajax_url","settings","iterest_list","message"],data(){return{beams:undefined,is_loading:false,is_submitted:false,webpush_enabled:"",interest:[],device_id:""}},mounted(){this.initBeam()},methods:{initBeam(){this.beams=new PusherPushNotifications.Client({instanceId:this.settings.instance_id});this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getwebnotifications",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.webpush_enabled=t.data.details.enabled==1?true:false;this.device_id=t.data.details.device_token;this.interest=t.data.details.interest}else{this.webpush_enabled=false;this.device_id="";this.interest=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},enabledWebPush(){this.is_loading=true;if(this.webpush_enabled){this.beams.start().then(()=>{r("start");this.beams.getDeviceId().then(t=>{this.device_id=t})})["catch"](t=>{o(this.message.notification_start,"error")}).then(t=>{this.is_loading=false})}else{this.beams.stop().then(()=>{r("stop");this.device_id=""})["catch"](t=>{o(this.message.notification_stop,"error")}).then(t=>{this.is_loading=false})}},saveWebNotifications(){this.is_submitted=true;axios({method:"PUT",url:this.ajax_url+"/savewebnotifications",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),webpush_enabled:this.webpush_enabled,interest:this.interest,device_id:this.device_id},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_submitted=false})}}};const Kt=Vue.createApp({components:{"components-web-pusher":Pt}});const Dt=Kt.mount("#vue-webpush-settings");const Mt={props:["message","donnot_close"],data(){return{new_message:""}},methods:{show(){h("#loadingBox").modal("show")},close(){h("#loadingBox").modal("hide")},setMessage(t){this.new_message=t}},template:`
	<div class="modal" id="loadingBox" tabindex="-1" role="dialog" aria-labelledby="loadingBox" aria-hidden="true"
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
	`};const zt={props:["label","ajax_url"],data(){return{is_loading:false,error:[],data:[],package_uuid:"",current_package_uuid:"",payment_code:"",methods:"",agree:false,payment_list:[],payment_code:""}},mounted(){this.getPlanList()},computed:{hasPlan(){if(u(this.package_uuid)){return false}if(this.current_package_uuid==this.package_uuid){return false}return true}},methods:{show(){h(this.$refs.modal_plan).modal("show")},close(){h(this.$refs.modal_plan).modal("hide")},getPlanList(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getPlanList",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.current_package_uuid=t.data.details.package_uuid;this.package_uuid=t.data.details.package_uuid;this.payment_code=t.data.details.payment_code}else{this.data=[];this.package_uuid="";this.payment_code=""}})["catch"](t=>{}).then(t=>{this.is_loading=false})},confirmPlan(){this.$emit("changePlan",this.package_uuid,this.payment_code)}},template:`	
    <div ref="modal_plan" class="modal" tabindex="-1" role="dialog" >
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.subscription_plan}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body">
      
       <div class="menu-categories medium mb-3 d-flex">
         <a v-for="(item, key) in data"  class="rounded align-self-center text-center"
         @click="package_uuid=item.package_uuid"	      
         :class="{active:package_uuid==item.package_uuid}"  
         >
         <h5 class="text-truncate">{{item.title}}</h5>
         <p class="m-0 mt-1 text-truncate">
         
         <template v-if="item.promo_price_raw>0" >
           {{item.promo_price}}
         </template>
         <template v-else >
           {{item.price}}
         </template>
         
         /{{item.package_period}}</p>         
         </a>
       </div>
                                          		                                
      </div>  <!-- modal body -->      
      
      <div class="modal-footer">        
              
        <button type="button" class="btn btn-black" data-dismiss="modal">
          <span class="pl-3 pr-3">Cancel</span>
        </button>
	                
        <button type="button" @click="confirmPlan" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"         
         :disabled="!hasPlan"
         >
          <span>Continue</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
        
      </div> <!-- modal footer -->
      
    </div> <!--content-->
   </div> <!--dialog-->
   </div> <!--modal-->     	              
	`};const Y=Vue.createApp({data(){return{error:[],payment_code:""}},created(){this.defaultPaymentGateway()},methods:{defaultPaymentGateway(){axios({method:"POST",url:apibackend+"/defaultPaymentGateway",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.payment_code=t.data.details.payment_code}else{this.payment_code=""}})["catch"](t=>{}).then(t=>{})},showPlan(t){this.$refs.planlist.show(t)},changePlan(t,e){try{this.$refs.planlist.close();this.$refs[e].changePlan(t)}catch(a){o(a.message,"danger")}},notify(t,e){o(t,e)},showLoading(){this.$refs.box.show()},closeLoading(){this.$refs.box.close()},afterChangeplan(){this.$refs.merchant_status.merchantPlanStatus()},afterCancelplan(){this.$refs.merchant_status.merchantPlanStatus()},viewInvoice(t,e){try{this.$refs[this.payment_code].invoiceDetails(t)}catch(a){o(a.message,"danger")}},refreshDatatables(){this.$refs.datatable.getTableData()}}});Y.component("components-datatable",R);Y.component("components-planlist",zt);Y.component("components-loading-box",Mt);Y.component("components-merchant-status",at);if(typeof components_bundle!=="undefined"&&components_bundle!==null){h.each(components_bundle,function(t,e){Y.component(t,e)})}const At=Y.mount("#vue-manage-plan");const Lt=Vue.createApp({data(){return{available_at_specific:null}},mounted(){this.available_at_specific=this.$refs.available_at_specific.value==1?true:false},methods:{}}).mount("#vue-availability");const Ut={props:["ajax_url","label","tax_in_price_list","tax_type"],data(){return{tax_uuid:0,tax_name:"",tax_in_price:0,tax_rate:0,active:true,default_tax:true,error:[],is_loading:false}},methods:{show(){this.clearData();h(this.$refs.modal_tax).modal("show")},close(){h(this.$refs.modal_tax).modal("hide")},submit(){this.is_loading=true;this.error=[];axios({method:"PUT",url:this.ajax_url+"/saveTax",data:{tax_uuid:this.tax_uuid,tax_name:this.tax_name,tax_rate:this.tax_rate,default_tax:this.default_tax,active:this.active,tax_in_price:this.tax_in_price,tax_type:this.tax_type,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.close();o(t.data.msg,"success");this.$emit("afterSave")}else{this.error=t.data.msg}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getTax(t){this.show();this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getTax",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&tax_uuid="+t,timeout:s}).then(t=>{if(t.data.code==1){this.tax_uuid=t.data.details.tax_uuid;this.tax_name=t.data.details.tax_name;this.tax_in_price=t.data.details.tax_in_price;this.tax_rate=t.data.details.tax_rate;this.default_tax=t.data.details.default_tax;this.active=t.data.details.active;this.default_tax=this.default_tax==1?true:false;this.active=this.active==1?true:false}else{o(data.msg,"danger")}})["catch"](t=>{}).then(t=>{this.is_loading=false})},clearData(){this.tax_uuid="";this.tax_name="";this.tax_rate=0;this.default_tax=true;this.active=true},deleteTax(e){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.confirmation+"</h5>"+"<p>"+this.label.content+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.taxDelete(e)}}})},taxDelete(t){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/taxDelete",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&tax_uuid="+t,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg,"success");this.$emit("afterSave")}else{o(data.msg,"danger")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
	
	<div ref="modal_tax" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  > 
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{label.title}}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
            
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
		    <div>
		      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
		    </div>
		</div>
      
      <div class="modal-body">          
      
      <form @submit.prevent="submit" >
          <div class="form-group">
		    <label for="tax_name">{{label.tax_name}}</label>
		    <input v-model="tax_name" type="text" class="form-control form-control-text" id="tax_name" >		    
		  </div>  
		  
		  
		  <template v-if="tax_type=='standard'">
		  <div v-if="default_tax" class="input-group mb-3">  
		   <select  v-model="tax_in_price" class="custom-select form-control-select" id="tax_in_price">	
		    <option v-for="(item, index) in tax_in_price_list" :value="index" >{{item}}</option>	    
		   </select>
		  </div>
		  </template>
		  
		  <div class="form-group">
		    <label for="tax_rate">{{label.rate}}</label>
		    <input v-model="tax_rate" type="text" class="form-control form-control-text" id="tax_rate" >		    
		  </div>  
		  		
		  
		 <template v-if="tax_type=='standard' || tax_type=='euro'">
		 <div class="row">
		   <div class="col">
		   
		     <div class="custom-control custom-switch">
			  <input v-model="default_tax" type="checkbox" class="custom-control-input" id="default_tax">
			  <label class="custom-control-label" for="default_tax">{{label.default_tax}}</label>
			</div>
		   
		   </div>
		   <div class="col">
		   
		     <div class="custom-control custom-switch">
			  <input v-model="active" type="checkbox" class="custom-control-input" id="active">
			  <label class="custom-control-label" for="active">{{label.active}}</label>
			</div> 
		   
		   </div>
		 </div>
		 </template>
		 <template v-else>
		   <div class="custom-control custom-switch">
			  <input v-model="active" type="checkbox" class="custom-control-input" id="active">
			  <label class="custom-control-label" for="active">{{label.active}}</label>
			</div> 
		 </template>
		 
      </form>
      
        <div v-if="error.length>0" class="alert alert-warning mb-2 mt-2" role="alert">
	    <p v-cloak v-for="err in error" class="m-0">{{err}}</p>	    
	    </div>  
      
      </div> <!-- body -->
      
       <div class="modal-footer">
          <button type="buttton" class="btn btn-black" data-dismiss="modal" aria-label="Close" >
          <span class="pl-2 pr-2" >{{label.cancel}}</span>
          </button>
          
          <button type="button" @click="submit" class="btn btn-green pl-4 pr-4" :class="{ loading: is_loading }"           
          >
          <span>{{label.save}}</span>
          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
        </button>
      </div>
      
      </div>
     </div>
     </div>      
	`};const qt=Vue.createApp({components:{"components-datatable":R,"components-tax":Ut},data(){return{data:[]}},mounted(){this.$refs.datatable.transaction_type=this.$refs.tax.tax_type},methods:{newTax(){this.$refs.tax.show()},afterSave(){this.$refs.datatable.getTableData()},editTax(t){this.$refs.tax.getTax(t)},deleteTax(t){this.$refs.tax.deleteTax(t)}}});const Bt=qt.mount("#vue-tax");const Vt={props:["ajax_url","enabled_interval","interval_seconds"],data(){return{handle:undefined}},created(){if(this.enabled_interval){this.requestNewOrder()}},methods:{startRequest(){if(this.handle){clearInterval(this.handle)}let t=3e4;if(typeof continues_alert_interval!=="undefined"&&continues_alert_interval!==null){t=parseFloat(continues_alert_interval)*1e3}this.handle=setInterval(()=>{this.requestNewOrder()},t)},requestNewOrder(){axios({method:"POST",url:this.ajax_url+"/requestNewOrder",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){if(Object.keys(t.data.details).length>0){Object.entries(t.data.details).forEach(([t,e])=>{if(t==0){this.playAlert();ElementPlus.ElNotification({title:e.title,message:e.message,duration:4500})}else{setTimeout(()=>{this.playAlert();ElementPlus.ElNotification({title:e.title,message:e.message,duration:4500})},500)}})}}})["catch"](t=>{}).then(t=>{this.startRequest()})},playAlert(){this.player=new Howl({src:["../assets/sound/notify.mp3","../assets/sound/notify.ogg"],html5:true});this.player.play()}}};const Jt=Vue.createApp({components:{"components-notification":et,"components-merchant-status":at,"components-pause-order":yt,"components-pause-modal":gt,"components-resume-order-modal":xt,"components-continuesalert":Vt},mounted(){},data(){return{data:[],is_load:false}},methods:{getOrdersCount(){axios({method:"POST",url:apibackend+"/getOrdersCount",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){if(this.is_load){h(this.$refs.orders_new).find(".badge-notification").remove()}this.data=t.data.details;if(this.data.not_viewed>0){h(this.$refs.orders_new).append('<div class="blob green badge-pill pull-right badge-notification bg-new">'+this.data.new_order+"</div>")}else{h(this.$refs.orders_new).append('<div class="badge-pill pull-right badge-notification bg-new">'+this.data.new_order+"</div>")}h(this.$refs.orders_processing).append('<div class="badge-pill pull-right badge-notification bg-processing">'+this.data.order_processing+"</div>");h(this.$refs.orders_ready).append('<div class="badge-pill pull-right badge-notification bg-ready">'+this.data.order_ready+"</div>");h(this.$refs.orders_completed).append('<div class="badge-pill pull-right badge-notification bg-completed">'+this.data.completed_today+"</div>");h(this.$refs.orders_scheduled).append('<div class="badge-pill pull-right badge-notification bg-scheduled">'+this.data.scheduled+"</div>");h(this.$refs.orders_history).append('<div class="badge-pill pull-right badge-notification bg-history">'+this.data.all_orders+"</div>")}})["catch"](t=>{}).then(t=>{this.is_load=true})},afterClickpause(t){r(t);if(t){this.$refs.pause_modal.show()}else{this.$refs.resume_order.show()}},afterPause(t){this.$refs.pause_order.updateStatus(t)}}});const Wt=Jt.mount("#vue-top-nav");const Ht={props:["ajax_url","label","orders_tab","limit"],data(){return{data:[],active_tab:"all",is_loading:false,data_failed:[]}},mounted(){this.getLastOrder(false);setInterval(()=>this.getLastOrder(true),6e4)},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{setTab(t){this.active_tab=t;this.getLastOrder()},getLastOrder(e){if(!e){this.is_loading=true}axios({method:"POST",url:this.ajax_url+"/getLastTenOrder",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&filter_by="+this.active_tab+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[];this.data_failed=t.data}})["catch"](t=>{}).then(t=>{if(!e){this.is_loading=false}})},viewCustomer(t){this.$emit("viewCustomer",t)}},template:`	
	
	 <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	
    <div class="card ">
    <div class="card-body">
	 
	<div class="row align-items-center">
        <div class="col col-lg-6 col-md-6 col-9">        
          <h5 class="m-0">{{label.title}}</h5>   
          <p class="m-0 text-muted">{{label.sub_title}}</p>        
        </div>
        <div class="col col-lg-6 col-md-6 col-3 ">

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
                  
                <!--- <div>                    
                    <a @click="viewCustomer(item.client_id)" class="text-muted font-weight-bold hover-text-primary" href="javascript:;">{{item.customer_name}}</a>
                </div> -->
            </td>
           <td class="text-right align-middle">
                <span class="font-weight-bold d-block">{{item.total}}</span>
                <span class="badge payment"  :class="item.payment_status_raw" >{{item.payment_status}}</span>
            </td> 
            <td class="text-right align-middle">
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
	`};const Gt={props:["ajax_url","label","period"],data(){return{data:[],data_items:[],data_failed:[],is_loading:false,code:null}},mounted(){this.itemSales()},methods:{itemSales(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/itemSales",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&period="+this.period,timeout:s}).then(t=>{this.code=t.data.code;if(t.data.code==1){this.data=t.data.details.sales;this.data_items=t.data.details.items;this.initChart()}else{this.data=[];this.data_items=[];this.data_failed=t.data;this.is_loading=false}})["catch"](t=>{this.is_loading=false}).then(t=>{})},initChart(){if(window.Highcharts==null){new Promise(t=>{const e=window.document;const a="chart-script";const s=e.createElement("script");s.id=a;s.setAttribute("src","https://code.highcharts.com/highcharts.js");e.head.appendChild(s);s.onload=()=>{t()}}).then(()=>{this.renderChart()})}else{this.renderChart()}},renderChart(){Highcharts.chart(this.$refs.chart,{chart:{type:"column",height:"60%",events:{load:()=>{setTimeout(()=>{this.is_loading=false},500)}}},title:{text:""},xAxis:{categories:this.data.category,crosshair:true},yAxis:{min:0,title:{text:""}},tooltip:{headerFormat:'<span style="font-size:10px">{point.key}</span><table>',pointFormat:'<tr><td style="color:{series.color};padding:0"></td>'+'<td style="padding:0"><b>{point.y:.1f} '+this.label.sold+"</b></td></tr>",footerFormat:"</table>",shared:true,useHTML:true},plotOptions:{column:{pointPadding:.2,borderWidth:0}},series:[{name:"Sales",showInLegend:false,color:"#3ecf8e",data:this.data.data}]})}},template:`	
    
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
    
    `};const Zt={components:{"components-item-sales":Gt},props:["ajax_url","label","limit","item_tab"],data(){return{data:[],is_loading:false,data_failed:[],currentTab:"item_overview"}},mounted(){this.mostPopularItems()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{mostPopularItems(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/mostPopularItems",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[];this.data_failed=t.data}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	    
    
     <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
	 
    <div class="card">
    <div class="card-body">
    
	<div class="row">
		<div class="col col-lg-6 col-md-6 col-9">        
		<h5 v-if="item_tab[currentTab]" class="m-0">{{item_tab[currentTab].title}}</h5>   
		<p  v-if="item_tab[currentTab]" class="m-0 text-muted">{{item_tab[currentTab].sub_title}}</p>        
		</div>

		<div class="col col-lg-6 col-md-6 col-3">        
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
      <div class="mt-3 table-item table-responsive">
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
                    <a :href="item.item_link"><img :src="item.image_url" class="img-60 rounded-circle"></a>
                 </div>
                 <div class="flex-col">
                    <a :href="item.item_link" class="font-weight-bold hover-text-primary mb-1">{{item.item_name}}</a>
                    <p class="m-0 text-muted">{{item.category_name}}</p>
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
       
        <div v-if="!hasData" class="fixed-height40 text-center justify-content-center d-flex align-items-center">
		    <div class="flex-col">
		     <img v-if="data_failed.details" class="img-300" :src="data_failed.details.image_url" />
		     <h6 class="mt-3 text-muted font-weight-normal">{{data_failed.msg}}</h6>
		    </div>     
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
                
    </div> <!--card body-->
   </div> 
   <!--card-->
    
    `};const Qt={props:["ajax_url","label","limit"],data(){return{data:[],data2:[],is_loading:false,data_failed:[]}},mounted(){this.mostPopularCustomer()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{mostPopularCustomer(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/mostPopularCustomer",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.data2=t.data.details.data2}else{this.data=[];this.data_failed=t.data;this.data2=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewCustomer(t){this.$emit("viewCustomer",t)}},template:`	
     
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
			 			 
			<a :href="data2.link" class="w-100 btn btn-lg btn-info waves-effect waves-light mb-3">
			  {{data2.label}}
			</a> 	      	 			 
	       
	    </div> <!--card body-->
	   </div> 
	   <!--card-->
    `};const Xt={props:["ajax_url","label","limit","months"],data(){return{data:[],is_loading:false,data_failed:[],code:null}},mounted(){this.salesOverview()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{salesOverview(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/salesOverview",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&months="+this.months,timeout:s}).then(t=>{this.code=t.data.code;if(t.data.code==1){this.data=t.data.details;this.initChart()}else{this.data=[];this.is_loading=false;this.data_failed=t.data}})["catch"](t=>{this.is_loading=false}).then(t=>{})},initChart(){if(window.Highcharts==null){new Promise(t=>{const e=window.document;const a="chart-script";const s=e.createElement("script");s.id=a;s.setAttribute("src","https://code.highcharts.com/highcharts.js");e.head.appendChild(s);s.onload=()=>{t()}}).then(()=>{this.renderChart()})}else{this.renderChart()}},renderChart(){Highcharts.chart(this.$refs.chart_sales,{chart:{type:"column",height:18/16*100+"%",events:{load:()=>{setTimeout(()=>{this.is_loading=false},500)}}},title:{text:""},xAxis:{categories:this.data.category,crosshair:true},yAxis:{min:0,title:{text:""}},tooltip:{headerFormat:'<span style="font-size:10px">{point.key}</span><table>',pointFormat:'<tr><td style="color:{series.color};padding:0">{series.name}: </td>'+'<td style="padding:0"><b>{point.y:.1f} '+this.label.sales+"</b></td></tr>",footerFormat:"</table>",shared:true,useHTML:true},plotOptions:{column:{pointPadding:.2,borderWidth:0}},series:[{name:"Sales",showInLegend:false,data:this.data.data}]})}},template:`	
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
    `};const te={props:["ajax_url","label","limit"],data(){return{data:[],is_loading:false}},mounted(){this.latestReview()},methods:{latestReview(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/OverviewReview",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&limit="+this.limit,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewCustomer(t){this.$emit("viewCustomer",t)}},template:`	
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
    `};const ee={props:["ajax_url","label","merchant_type"],data(){return{data:[],is_loading:false}},mounted(){this.salesSummary()},methods:{salesSummary(){axios({method:"POST",url:this.ajax_url+"/salesSummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{var e,a,s;if(t.data.code==1){e=this.count_up=new countUp.CountUp(this.$refs.your_balance,t.data.details.balance,t.data.details.price_format);e.start();a=this.count_up=new countUp.CountUp(this.$refs.sales_this_week,t.data.details.sales_week,t.data.details.price_format);a.start();s=this.count_up=new countUp.CountUp(this.$refs.earning_this_week,t.data.details.earning_week,t.data.details.price_format);s.start()}else{}})["catch"](t=>{}).then(t=>{})}},template:`	    
    <div class="row">
	    <div class="col mb-3 mb-xl-0">
	      <div class="card">
	        <div class="card-body" style="padding:10px;">
	        
	          <div id="boxes" class="d-flex align-items-center">
	            <div class="mr-2"><div class="rounded box box-1 d-flex align-items-center  justify-content-center"><i class="zmdi zmdi-balance-wallet"></i></div></div>
	            <div>
	               <h6 class="m-0 text-muted font-weight-normal">{{label.sales_this_week}}</h6>
	               <h6 class="m-0 position-relative" ref="sales_this_week">0
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
	               <h6 class="m-0 text-muted font-weight-normal">{{label.earning_this_week}}</h6>
	               <h6 class="m-0 position-relative" ref="earning_this_week">0
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
	               <h6 class="m-0 text-muted font-weight-normal">{{label.your_balance}}</h6>
	               <h6 class="m-0 position-relative" ref="your_balance">0
	                  <div class="skeleton-placeholder" style="height:17px;width:100%;"></div>
	               </h6>
	            </div>
	          </div><!--flex-->       
	          
	        </div> <!--card body-->       
	      </div> <!--card-->
	    </div> <!-- col -->
	    
	  </div> <!--row--> 
    `};const ae={props:["ajax_url","label","limit"],data(){return{data:[],is_loading:false}},mounted(){this.DailyStatistic()},methods:{DailyStatistic(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/DailyStatistic",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.stats_order_received,t.data.details.order_received,e);a.start();a=this.count_up=new countUp.CountUp(this.$refs.stats_today_delivered,t.data.details.today_delivered,e);a.start();var e=t.data.details.price_format;a=this.count_up=new countUp.CountUp(this.$refs.stats_today_sales,t.data.details.today_sales,e);a.start();a=this.count_up=new countUp.CountUp(this.$refs.stats_total_refund,t.data.details.total_refund,e);a.start()}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	
    
      <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>   
    
    <div class="row mb-3">
          <div class="col">
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

           <div class="col">
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
      
       <div class="dashboard-statistic position-relative mb-3">
        
        <div class="row">
          <div class="col  mb-3 mb-xl-0">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex align-items-center">
                     <div class="flex-col mr-3"><h1 class="m-0"><i class="zmdi zmdi-balance-wallet"></i></h1></div>
                     <div class="flex-col">
                       <h3 class="mb-1 text-violet" ref="stats_today_sales">0</h3>
                       <h5 class="m-0 text-secondary">{{label.today_sales}}</h5>
                     </div>
                  </div>
                </div>
              </div>
          </div> <!--col-->

           <div class="col  mb-3 mb-xl-0">
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
    
    `};const se=Vue.createApp({components:{"components-last-orders":Ht,"components-customer-details":N,"components-popular-items":Zt,"components-popular-customer":Qt,"components-chart-sales":Xt,"components-latest-review":te,"components-sales-summary":ee,"components-daily-statistic":ae},data(){return{data:[],is_load:false,client_id:null,dashboard_access:[]}},mounted(){this.getMerchantSummary();if(typeof dashboard_access!=="undefined"&&dashboard_access!==null){this.dashboard_access=JSON.parse(dashboard_access)}},methods:{hasPermission(t){if(this.dashboard_access.includes(t)===false){return false}return true},getMerchantSummary(){this.is_loading=true;axios({method:"POST",url:apibackend+"/getOrderSummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){var e={decimalPlaces:0,separator:",",decimal:"."};var a=new countUp.CountUp(this.$refs.summary_orders,t.data.details.orders,e);a.start();var s=new countUp.CountUp(this.$refs.summary_cancel,t.data.details.order_cancel,e);s.start();var e=t.data.details.price_format;var i=this.count_up=new countUp.CountUp(this.$refs.summary_total,t.data.details.total,e);i.start();var l=this.count_up=new countUp.CountUp(this.$refs.total_refund,t.data.details.total_refund,e);l.start()}else{}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewCustomer(t){this.client_id=t;setTimeout(()=>{this.$refs.customer.show()},1)},refreshLastOrder(){this.$refs.last_order.getLastOrder();this.$refs.daily_statistic.DailyStatistic()},refreshDailyStatistic(){this.$refs.daily_statistic.DailyStatistic()}}});const ie=se.mount("#vue-dashboard");const le=Vue.createApp({components:{"components-datatable":R},data(){return{is_loading:false}},methods:{}});const oe=le.mount("#vue-tables");const re={props:["ajax_url","label"],data(){return{data:[],data_items:[],data_failed:[],is_loading:false,code:null,period:null}},mounted(){this.itemSales()},methods:{itemSales(){this.is_loading=true;axios({method:"POST",url:apibackend+"/itemSalesSummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&period="+this.period,timeout:s}).then(t=>{this.code=t.data.code;if(t.data.code==1){this.data=t.data.details.sales;this.data_items=t.data.details.items;this.initChart()}else{this.data=[];this.data_items=[];this.data_failed=t.data;this.is_loading=false}})["catch"](t=>{this.is_loading=false}).then(t=>{})},initChart(){if(window.Highcharts==null){new Promise(t=>{const e=window.document;const a="chart-script";const s=e.createElement("script");s.id=a;s.setAttribute("src","https://code.highcharts.com/highcharts.js");e.head.appendChild(s);s.onload=()=>{t()}}).then(()=>{this.renderChart()})}else{this.renderChart()}},renderChart(){Highcharts.chart(this.$refs.chart,{chart:{type:"column",height:"60%",events:{load:()=>{setTimeout(()=>{this.is_loading=false},500)}}},title:{text:""},xAxis:{categories:this.data.category,crosshair:true},yAxis:{min:0,title:{text:""}},tooltip:{headerFormat:'<span style="font-size:10px">{point.key}</span><table>',pointFormat:'<tr><td style="color:{series.color};padding:0"></td>'+'<td style="padding:0"><b>{point.y:.1f} '+this.label.sold+"</b></td></tr>",footerFormat:"</table>",shared:true,useHTML:true},colors:this.data.colors,plotOptions:{column:{pointPadding:.2,borderWidth:0,colorByPoint:true}},series:[{name:"Sales",showInLegend:false,color:"#3ecf8e",data:this.data.data}]})}},template:`	
    
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
    
    `};const de=Vue.createApp({components:{"components-datatable":R,"components-report-sales-summary-chart":re},data(){return{is_loading:false}},methods:{}});const ne=de.mount("#vue-report-sales-summary");const ce={props:["ajax_url","label"],data(){return{data:[],is_loading:false,first_name:"",last_name:"",email_address:"",contact_phone:""}},mounted(){},computed:{hasData(){if(u(this.first_name)||u(this.last_name)||u(this.email_address)||u(this.contact_phone)){return false}return true}},methods:{show(){h(this.$refs.customer_entry_modal).modal("show")},close(){h(this.$refs.customer_entry_modal).modal("hide")},submit(){this.is_loading=true;axios({method:"put",url:this.ajax_url+"/createCustomer",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),first_name:this.first_name,last_name:this.last_name,email_address:this.email_address,contact_phone:this.contact_phone},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterSavecustomer",t.data.details)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`		    
	<div ref="customer_entry_modal" class="modal" tabindex="-1" role="dialog" data-backdrop="static"  >
	    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
	    <div class="modal-content">

		<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLongTitle">{{label.customer}}</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			<span aria-hidden="true">&times;</span>
			</button>
		</div>
	      
	      <div class="modal-body">
	      	      
	      <form @submit.prevent="submit"  class="forms mt-2">
		  
	      <div class="row">
	        <div class="col">
	           <div class="form-label-group">
			    <input v-model="first_name" class="form-control form-control-text" placeholder="" id="first_name" type="text" maxlength="255">
			    <label for="first_name" class="required">{{label.first_name}}</label>
			  </div>
	        </div> <!-- col -->
	        
	        <div class="col">
	           <div class="form-label-group">
			    <input v-model="last_name" class="form-control form-control-text" placeholder="" id="last_name" type="text" maxlength="255">
			    <label for="last_name" class="required">{{label.last_name}}</label>
			  </div>
	        </div> <!-- col -->
	        
	      </div><!-- row -->	      
	      
	       <div class="row">
	        <div class="col">
	           <div class="form-label-group">
			    <input v-model="email_address" class="form-control form-control-text" placeholder="" id="email_address" type="text" maxlength="200">
			    <label for="email_address" class="required">{{label.emaiL_address}}</label>
			  </div>
	        </div> <!-- col -->
	        
	        <div class="col">
	           <div class="form-label-group">
			    <input v-model="contact_phone" class="form-control form-control-text" placeholder="" id="contact_phone" type="text" maxlength="20">
			    <label for="contact_phone" class="required">{{label.contact_phone}}</label>
			  </div>
	        </div> <!-- col -->
	        
	      </div><!-- row -->	      
	      
		  </form>
	      
	      </div>      
	      <div class="modal-footer">
	        <button type="button" @click="submit" class="btn btn-green w-100" :class="{ loading: is_loading }"         
	         :disabled="!hasData"
	         >
	          <span>{{label.submit}}</span>
	          <div class="m-auto circle-loader" data-loader="circle-side"></div> 
	        </button>
	      </div>
	      
	    </div>
	  </div>
	</div>  
    `};const me={props:["label","modelValue","formatted_address","enabled_locate"],emits:["update:modelValue"],data(){return{q:"",awaitingSearch:false,show_list:false,data:[],error:[],hasSelected:false,loading:false,addressData:[],auto_locate:false}},created(){this.q=this.formatted_address},watch:{formatted_address(t,e){this.q=t},q(t,e){this.$emit("update:modelValue",t);if(!this.awaitingSearch){if(u(t)){return false}if(this.q.length>20){return false}this.show_list=true;setTimeout(()=>{axios({method:"POST",url:apibackend+"/getlocationAutocomplete",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&q="+this.q,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.awaitingSearch=false})},1e3)}this.data=[];this.awaitingSearch=true}},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{showList(){this.show_list=true},clearData(){this.data=[];this.q=""},setData(t){this.clearData();this.q=t.description;this.$emit("update:modelValue",t.description);this.$emit("afterChoose",t)},locateLocation(){this.loading=true;if(navigator.geolocation){navigator.geolocation.getCurrentPosition(t=>{this.reverseGeocoding(t.coords.latitude,t.coords.longitude)},t=>{this.loading=false;ElementPlus.ElNotification({title:"",message:t.message,position:"bottom-right",type:"warning"})})}else{this.loading=false;ElementPlus.ElNotification({title:"",message:"Your browser doesn't support geolocation.",position:"bottom-right",type:"warning"})}},reverseGeocoding(t,e){axios({method:"POST",url:apibackend+"/reverseGeocoding",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&lat="+t+"&lng="+e,timeout:s}).then(t=>{if(t.data.code==1){this.q=t.data.details.data.address.formatted_address;this.addressData=t.data.details.data;if(this.auto_locate){this.$emit("afterGetcurrentlocation",this.addressData)}}else{this.addressData=[];ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false;this.auto_locate=false})},setAddressData(t){this.addressData=t},setLoading(t){this.loading=t},onSubmit(){if(Object.keys(this.addressData).length>0){this.$emit("afterGetcurrentlocation",this.addressData)}else{this.auto_locate=true;this.locateLocation()}}},template:`	
	<div class="d-flex flex-sm-row flex-column align-items-center justify-content-between">
		<div class="position-relative search-geocomplete" v-loading="loading" :class="{width_87 : enabled_locate , 'w-100' : !enabled_locate}"  > 	
			<div v-if="!awaitingSearch" class="img-20 m-auto pin_placeholder icon"></div>
			<div v-if="awaitingSearch" class="icon" data-loader="circle"></div>    

			<input v-model="q" class="form-control form-control-text" 
			:placeholder="label.enter_address" >
			
			<template v-if="hasData">
			<div @click="clearData" class="icon-remove"><i class="zmdi zmdi-close"></i></div>
			</template>
			<template v-else>
			<div v-if="enabled_locate" @click="locateLocation" class="icon-remove"><i class="zmdi zmdi-my-location font20"></i></div>
			</template>

			<div class="dropdown-menu" :class="{show :hasData}">
			<template v-for="items in data">
				<button @click="setData(items)" type="button" class="dropdown-item" data-value="1">
					{{items.description}}
				</button>
			</template>
			</div>
		</div>    
		<div v-if="enabled_locate" class="flex-enabled-locate">
		<button @click="onSubmit" type="button" class="btn btn-green pl-3 pr-3" :disabled="loading">
		   <i class="zmdi zmdi-arrow-right font25"></i>
		</button>
		</div>	
	</div>      			
	`};const he={props:["keys","provider","zoom","center","markers","size"],template:`     	
    <div ref="cmaps" class="map border" :class="size"></div>  	
    `,data(){return{cmapsMarker:[],latLng:[],cmaps:undefined,bounds:undefined}},mounted(){this.renderMap()},watch:{markers(t,e){this.renderMap()}},methods:{renderMap(){try{switch(this.provider){case"google.maps":this.bounds=new window.google.maps.LatLngBounds;if(typeof this.cmaps!=="undefined"&&this.cmaps!==null&&Object.keys(this.cmapsMarker).length>0){this.moveAllMarker()}else{this.cmaps=new window.google.maps.Map(this.$refs.cmaps,{center:{lat:parseFloat(this.center.lat),lng:parseFloat(this.center.lng)},zoom:parseInt(this.zoom),disableDefaultUI:true});Object.entries(this.markers).forEach(([t,e])=>{this.addMarker({position:{lat:parseFloat(e.lat),lng:parseFloat(e.lng)},map:this.cmaps,draggable:e.draggable==1?true:false,label:e.label},e.id,e.draggable)})}break;case"mapbox":if(typeof this.cmaps!=="undefined"&&this.cmaps!==null&&Object.keys(this.cmapsMarker).length>0){this.moveAllMarker()}else{mapboxgl.accessToken=this.keys;this.bounds=new mapboxgl.LngLatBounds;this.cmaps=new mapboxgl.Map({container:this.$refs.cmaps,style:"mapbox://styles/mapbox/streets-v12",center:[parseFloat(this.center.lng),parseFloat(this.center.lat)],zoom:14});this.cmaps.on("error",t=>{r(t.error.message)});Object.entries(this.markers).forEach(([t,e])=>{this.addMarker({position:{lat:parseFloat(e.lat),lng:parseFloat(e.lng)},map:this.cmaps,draggable:e.draggable==1?true:false},e.id,e.draggable)});this.FitBounds()}break}}catch(t){console.error(t)}},addMarker(t,a,e){try{switch(this.provider){case"google.maps":this.cmapsMarker[a]=new window.google.maps.Marker(t);this.cmaps.panTo(new window.google.maps.LatLng(t.position.lat,t.position.lng));this.bounds.extend(this.cmapsMarker[a].position);if(e===true){window.google.maps.event.addListener(this.cmapsMarker[a],"drag",t=>{this.$emit("dragMarker",true)});window.google.maps.event.addListener(this.cmapsMarker[a],"dragend",t=>{const e=t.latLng;this.latLng={lat:e.lat(),lng:e.lng()};this.$emit("dragMarker",false);this.$emit("afterSelectmap",e.lat(),e.lng())})}break;case"mapbox":this.cmapsMarker[a]=new mapboxgl.Marker(t).setLngLat([t.position.lng,t.position.lat]).addTo(this.cmaps);this.bounds.extend(new mapboxgl.LngLat(t.position.lng,t.position.lat));if(e===true){this.cmapsMarker[a].on("dragend",t=>{const e=this.cmapsMarker[a].getLngLat();r(e.lat+"=>"+e.lng);this.$emit("afterSelectmap",e.lat,e.lng)});this.mapBoxResize()}break}}catch(s){console.error(s)}},mapBoxResize(){if(this.provider=="mapbox"){setTimeout(()=>{this.cmaps.resize()},500)}},moveAllMarker(){console.log("moveAllMarker");console.log(this.markers);if(this.provider=="google.maps"){if(Object.keys(this.markers).length>0){Object.entries(this.markers).forEach(([t,e])=>{let a=new google.maps.LatLng(parseFloat(e.lat),parseFloat(e.lng));if(!u(this.cmapsMarker[e.id])){this.cmapsMarker[e.id].setPosition(a)}});if(Object.keys(this.markers).length>1){this.FitBounds()}else{this.cmaps.panTo(new window.google.maps.LatLng(this.markers[0].lat,this.markers[0].lng))}}}else{if(Object.keys(this.markers).length>0){Object.entries(this.markers).forEach(([t,e])=>{if(!u(this.cmapsMarker[e.id])){this.cmapsMarker[e.id].setLngLat([e.lng,e.lat]).addTo(this.cmaps)}});if(Object.keys(this.markers).length>1){this.FitBounds()}else{this.cmaps.flyTo({center:[this.markers[0].lng,this.markers[0].lat],essential:true,zoom:14})}}}},centerMap(){this.FitBounds()},FitBounds(){try{switch(this.provider){case"google.maps":if(!u(this.bounds)){this.cmaps.fitBounds(this.bounds)}break;case"mapbox":if(!u(this.bounds)){this.cmaps.fitBounds(this.bounds,{duration:0,padding:50})}break}}catch(t){}},setCenter(t,e){try{switch(this.provider){case"google.maps":this.cmaps.setCenter(new window.google.maps.LatLng(t,e));break;case"mapbox":this.cmaps.flyTo({center:[e,t],essential:true});break}}catch(a){console.error(a)}},setPlusZoom(){r("setPlusZoom");this.cmaps.setZoom(this.cmaps.getZoom()+2)},setLessZoom(){r("setLessZoom");this.cmaps.setZoom(this.cmaps.getZoom()-2)}}};const ue={props:["keys","provider","zoom","center","label","order_uuid","transaction_type"],components:{"component-auto-complete":me,"components-maps":he},data(){return{data:[],dialog_address:false,markers:[],address_data:[],loading:false,drag:false,address1:"",formatted_address:"",address_label:"Home",delivery_options:"Leave it at my door",location_name:"",delivery_instructions:""}},created(){this.markers=[{id:0,lat:parseFloat(this.center.lat),lng:parseFloat(this.center.lng),draggable:true}]},computed:{hasFormatAddress(){if(Object.keys(this.address_data).length>0){return true}return false},getFormatAddress(){return this.address_data},hasDeliveryAddress(){if(Object.keys(this.address_data).length>0){return true}return false}},watch:{address_data(t,e){if(Object.keys(t).length>0){this.address1=t.address.address1;this.formatted_address=t.address.formatted_address;this.dataMarkers(t.latitude,t.longitude)}},order_uuid(t,e){this.getAddressdetails()}},methods:{show(){this.dialog_address=true},dataMarkers(t,e){this.markers=[{id:0,lat:parseFloat(t),lng:parseFloat(e),draggable:true}]},setLoading(t){this.loading=t},afterChoose(t){r(t);this.getLocationDetails(t.id)},getAddressdetails(){axios({method:"POST",url:apibackend+"/getAddressdetails",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details;this.address_data=t.data.details.data;if(Object.keys(t.data.details.address_data).length>0){this.address_label=t.data.details.address_data.address_label;this.delivery_options=t.data.details.address_data.delivery_options;this.delivery_instructions=t.data.details.address_data.delivery_instructions;this.location_name=t.data.details.address_data.location_name}}else{this.data=[]}})["catch"](t=>{}).then(t=>{})},getLocationDetails(t){axios({method:"POST",url:apibackend+"/getLocationDetails",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&id="+t+"&saveplace=0",timeout:s}).then(t=>{if(t.data.code==1){this.dataMarkers(t.data.details.data.latitude,t.data.details.data.longitude);this.address_data=t.data.details.data}else{}})["catch"](t=>{}).then(t=>{})},afterSelectmap(e,a){this.loading=true;axios({method:"POST",url:apibackend+"/reverseGeocoding",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&lat="+e+"&lng="+a,timeout:s}).then(t=>{if(t.data.code==1){if(Object.keys(t.data.details.data).length>0){this.address_data=t.data.details.data;this.address_data.latitude=e;this.address_data.longitude=a}}else{}})["catch"](t=>{}).then(t=>{this.loading=false})},dragMarker(t){this.drag=t},setNewAdress(){this.$emit("afterChangeaddress",this.address_data)},saveAddress(){this.loading=true;axios({method:"PUT",url:apibackend+"/saveAddress",data:{order_uuid:this.order_uuid,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),address_label:this.address_label,delivery_options:this.delivery_options,address_data:this.address_data,transaction_type:this.transaction_type,address1:this.address1,formatted_address:this.formatted_address,location_name:this.location_name,delivery_instructions:this.delivery_instructions},timeout:s}).then(t=>{if(t.data.code==1){this.dialog_address=false;this.$emit("refreshOrder")}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})}},template:"#xtemplate_delivery_address"};const _e={components:{"components-customer-entry":ce,"money-format":e,"components-customer-address":ue},props:["ajax_url","label","limit","order_uuid"],data(){return{is_loading:false,items:[],order_summary:[],summary_total:0,items_row:[],item_qty:0,promo_code:"",promo_loading:false,pay_left:0,change:0,receive_amount:0,payment_code:"",payment_list:[],create_payment_loading:false,client_id:0,order_notes:"",transaction_type:"",transaction_list:[],order_status:"",order_status_list:[],guest_number:1,room_list:[],table_list:[],room_id:"",table_id:"",payment_reference:"",discount:0,loading_discount:false,inner_loading:false,additional_fee:0,additional_fee_type:"",additional_list:[],whento_deliver:"now",delivery_option:[],opening_hours:[],delivery_date:"",delivery_time:"",delivery_address:""}},computed:{hasData(){if(this.items.length>0){return true}return false},hasCoopon(){if(!u(this.promo_code)){return true}return false},hasValidPayment(){if(u(this.transaction_type)){return false}if(this.receive_amount<=0){return false}if(u(this.payment_code)){return false}if(this.pay_left.toFixed(2)>0){return false}return true},getTimelist(){if(Object.keys(this.opening_hours).length>0){if(this.opening_hours.time_ranges[this.delivery_date]){return this.opening_hours.time_ranges[this.delivery_date]}}return[]}},mounted(){this.initeSelect2();this.posAttributes()},watch:{item_qty(t,e){this.inner_loading=true;axios({method:"POST",url:this.ajax_url+"/updatePosQty",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&qty="+t+"&item_row="+this.items_row.item_row,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.inner_loading=false})},receive_amount(t,e){this.pay_left=parseFloat(this.summary_total)-parseFloat(t);if(this.pay_left<=0){this.pay_left=0}this.change=parseFloat(t)-parseFloat(this.summary_total);if(this.change<=0){this.change=0}}},methods:{SetAddress(t){r("SetAddress");r(t)},showAddress(){this.$refs.customer_address.show()},POSdetails(){var t=[];axios({method:"put",url:this.ajax_url+"/orderDetails",data:{order_uuid:this.order_uuid,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),payload:t},timeout:s}).then(t=>{if(t.data.code==1){this.items=t.data.details.data.items;this.order_summary=t.data.details.data.summary;this.summary_total=t.data.details.data.summary_total;this.transaction_type=t.data.details.data.order.order_info.order_type;this.delivery_address=t.data.details.data.order.order_info.delivery_address}else{this.items=[];this.order_summary=[];this.summary_total=0;this.delivery_address=""}})["catch"](t=>{}).then(t=>{})},resetPos(){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.clear_items+"</h5>"+"<p>"+this.label.are_you_sure+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label.confirm,className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.clearItemPos()}}})},clearItemPos(){axios({method:"POST",url:this.ajax_url+"/resetPos",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid,timeout:s}).then(t=>{r(t.data.code);if(t.data.code==1){this.$emit("afterReset")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{})},removeItem(t){axios({method:"POST",url:this.ajax_url+"/removeItem",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&item_row="+t.item_row,timeout:s}).then(t=>{r(t.data.code);if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{})},changeQty(t,e){this.items_row=t;this.item_qty=this.items_row.qty;if(e=="add"){this.items_row.qty++;this.item_qty++}else{this.items_row.qty>1?this.items_row.qty--:1;this.item_qty>1?this.item_qty--:1}},initeSelect2(){h(this.$refs.customer).select2({placeholder:this.label.walkin_customer,width:"resolve",language:{searching:()=>{return this.label.searching},noResults:()=>{return this.label.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchCustomer",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),POS:true};return JSON.stringify(e)}}})},showPromo(){this.$refs.promo_code.focus();h(this.$refs.promo_modal).modal("show")},closePromo(){h(this.$refs.promo_modal).modal("hide")},applyPromoCode(){this.promo_loading=true;axios({method:"PUT",url:this.ajax_url+"/applyPromoCode",data:{order_uuid:this.order_uuid,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),promo_code:this.promo_code},timeout:s}).then(t=>{if(t.data.code==1){this.promo_code="";this.closePromo();this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.promo_loading=false})},removeVoucher(){this.inner_loading=true;axios({method:"PUT",url:this.ajax_url+"/removeVoucher",data:{order_uuid:this.order_uuid,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.inner_loading=false})},showCustomer(){this.$refs.customer_entry.show()},closeCustomer(){},afterSavecustomer(t){r(t);this.$refs.customer_entry.close();h(this.$refs.customer).select2("trigger","select",{data:{id:t.client_id,text:t.client_name}})},showPayment(){this.receive_amount=this.summary_total;h(this.$refs.submit_order_modal).modal("show")},clearFields(){this.room_id="";this.table_id="";this.guest_number=1;this.payment_reference="";this.order_notes=""},closePayment(){h(this.$refs.submit_order_modal).modal("hide")},posAttributes(){axios({method:"POST",url:this.ajax_url+"/posAttributes",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.payment_list=t.data.details.data;this.payment_code=t.data.details.default_payment;this.transaction_list=t.data.details.transaction_list;this.transaction_type=t.data.details.transaction_type;this.order_status_list=t.data.details.order_status_list;this.order_status=t.data.details.order_status;this.room_list=t.data.details.room_list;this.table_list=t.data.details.table_list;this.additional_list=t.data.details.additional_list;this.delivery_option=t.data.details.delivery_option;this.opening_hours=t.data.details.opening_hours}else{this.payment_list=[];this.payment_code="";this.transaction_list=[];this.transaction_type="";this.order_status_list=[];this.delivery_option=[];this.opening_hours=[]}})["catch"](t=>{}).then(t=>{})},submitOrder(){this.create_payment_loading=true;this.client_id=h(this.$refs.customer).find(":selected").val();axios({method:"PUT",url:this.ajax_url+"/submitPOSOrder",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),order_uuid:this.order_uuid,receive_amount:this.receive_amount,payment_code:this.payment_code,order_notes:this.order_notes,client_id:this.client_id,order_change:this.change,transaction_type:this.transaction_type,order_status:this.order_status,guest_number:this.guest_number,room_id:this.room_id,table_id:this.table_id,payment_reference:this.payment_reference,whento_deliver:this.whento_deliver,delivery_date:this.delivery_date,delivery_time:this.delivery_time},timeout:s}).then(t=>{if(t.data.code==1){this.clearFields();this.closePayment();this.$emit("afterCreateorder",this.order_uuid)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.create_payment_loading=false})},roomChange(){this.table_id=""},showDiscount(){h(this.$refs.promo_discount).modal("show")},applyDiscount(){this.loading_discount=true;axios({method:"POST",url:this.ajax_url+"/applyDiscount",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&discount="+this.discount+"&order_uuid="+this.order_uuid,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);h(this.$refs.promo_discount).modal("hide");this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.loading_discount=false})},removeDiscount(){this.inner_loading=true;axios({method:"POST",url:this.ajax_url+"/removeDiscount",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.inner_loading=false})},addAdditionalFee(){this.inner_loading=true;axios({method:"POST",url:this.ajax_url+"/addAdditionalFee",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid+"&additional_fee="+this.additional_fee+"&additional_fee_type="+this.additional_fee_type,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.inner_loading=false})},removeAdditionalFee(t){this.inner_loading=true;axios({method:"POST",url:this.ajax_url+"/removeAdditionalFee",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid+"&additional_fee_type="+t,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.inner_loading=false})},udatePosTransaction(){this.inner_loading=true;axios({method:"POST",url:this.ajax_url+"/udatePosTransaction",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&order_uuid="+this.order_uuid+"&transaction_type="+this.transaction_type,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("refreshOrder")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.inner_loading=false})}},template:"#xtemplate_order_details_pos"};const $=Vue.createApp({components:{"components-menu":x,"components-item-details":C,"components-order-pos":_e,"components-order-print":E},data(){return{is_loading:false,order_uuid:"",order_type:"",order_uuid_print:""}},mounted(){this.createOrder()},methods:{createOrder(){this.is_loading=true;axios({method:"POST",url:apibackend+"/createOrder",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.order_uuid=t.data.details.order_uuid;this.order_type=t.data.details.order_type;setTimeout(()=>{this.$refs.pos_details.POSdetails()},100)}else{this.order_uuid="";this.order_type=""}})["catch"](t=>{}).then(t=>{this.is_loading=false})},showItemDetails(t){this.$refs.item.show(t)},refreshOrderInformation(){this.$refs.pos_details.POSdetails()},afterReset(){this.createOrder()},afterCreateorder(t){this.order_uuid_print=t;this.createOrder();setTimeout(()=>{this.$refs.print.show()},100)}}});$.use(Maska);$.use(window["v-money3"]["default"]);$.use(ElementPlus);const pe=$.mount("#vue-pos");const fe={props:["ajax_url","label","menu_id"],data(){return{data:[],pages:[],is_loading:false,add_loading:false,custom_loading:false,custom_link:"",custom_link_text:""}},mounted(){this.getAllPages()},computed:{hasData(){if(this.pages.length>0&&this.menu_id>0){return true}return false},hasLink(){if(!u(this.custom_link)&&!u(this.custom_link_text)){return true}return false}},methods:{getAllPages(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/AllPages",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},addPageToMenu(){this.add_loading=true;axios({method:"PUT",url:this.ajax_url+"/addPageToMenu",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),menu_id:this.menu_id,pages:this.pages},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.pages=[];this.$emit("afterAddpage")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.add_loading=false})},addCustomPageToMenu(){this.custom_loading=true;axios({method:"PUT",url:this.ajax_url+"/addCustomPageToMenu",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),menu_id:this.menu_id,custom_link_text:this.custom_link_text,custom_link:this.custom_link},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterAddpage");this.custom_link_text="";this.custom_link=""}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.custom_loading=false})}},template:`	
        
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
			  <span>Add to menu</span>
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
    `};const ve={components:{draggable:vuedraggable},props:["ajax_url","label"],data(){return{data:[],current_menu:0,is_loading:false,enabled:true,dragging:false,list:[{name:"John",id:0},{name:"Joao",id:1},{name:"Jean",id:2}]}},mounted(){this.MenuList()},methods:{MenuList(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/MenuList",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.current_menu=t.data.details.current_menu;this.$emit("setCurrentmenu",this.current_menu)}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},createNewMenu(){this.$emit("createNewmenu")},setMenuID(t){this.current_menu=t;this.$emit("setCurrentmenu",this.current_menu)},checkMove(t,e){this.is_loading=true;axios({method:"PUT",url:this.ajax_url+"/sortMenu",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),menu:this.data},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg)}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`	        
	 <div v-if="is_loading" class="loading cover-loader d-flex align-items-center justify-content-center">
	    <div>
	      <div class="m-auto circle-loader medium" data-loader="circle-side"></div> 
	    </div>
	 </div>
    
    <div class="mb-2">
      <p class="m-0">Select a menu to edit or <a @click="createNewMenu" class="text-green">create new menu</a></p>
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
    `};const ge={components:{draggable:vuedraggable},props:["ajax_url","label","current_menu"],data(){return{data:[],is_loading:false,menu_name:"",child_menu:[],create_loading:false,delete_loading:false,remove_loading:false,enabled:true,dragging:false}},mounted(){},computed:{dragOptions(){return{animation:200,group:"description",disabled:false,ghostClass:"ghost"}}},watch:{current_menu(t,e){if(t>0){this.getMenuDetails()}else{this.menu_name="";this.child_menu=[]}}},methods:{createMenu(){this.create_loading=true;axios({method:"PUT",url:this.ajax_url+"/createMenu",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),menu_name:this.menu_name,menu_id:this.current_menu,child_menu:this.child_menu},timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterSavemenu")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.create_loading=false})},deleteMenu(){bootbox.confirm({size:"small",title:"",message:"<h5>"+this.label.delete_confirmation+"</h5>"+"<p>"+this.label.are_you_sure+"</p>",centerVertical:true,animate:false,buttons:{cancel:{label:this.label.cancel,className:"btn btn-black small pl-4 pr-4"},confirm:{label:this.label["delete"],className:"btn btn-green small pl-4 pr-4"}},callback:t=>{if(t){this.delete_loading=true;axios({method:"POST",url:this.ajax_url+"/deleteMenu",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&menu_id="+this.current_menu,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.$emit("afterSavemenu")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.delete_loading=false})}}})},getMenuDetails(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getMenuDetails",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&current_menu="+this.current_menu,timeout:s}).then(t=>{if(t.data.code==1){this.menu_name=t.data.details.menu_name;this.child_menu=t.data.details.data}else{this.menu_name="";this.child_menu=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},removeChildMenu(t){this.remove_loading=true;axios({method:"POST",url:this.ajax_url+"/removeChildMenu",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&menu_id="+t,timeout:s}).then(t=>{if(t.data.code==1){this.getMenuDetails()}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.remove_loading=false})}},template:`	    
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
		      <div class="mr-2">Menu name</div>
		      <div><input v-model="menu_name" type="text"></div>		      
		    </div>   		       
	       </div> <!--col-->
	       <div class="col text-right">
	          <template v-if="current_menu>0">	  
	                    
	            <button @click="createMenu" type="button" class="btn btn-green normal rounded-0 mr-2" :class="{ loading: create_loading }">
				  <span>Save menu</span>
				  <div class="m-auto circle-loader" data-loader="circle-side"></div> 
				</button> 	            
					            
	            <button @click="deleteMenu" type="button" class="btn btn-link normal rounded-0 text-green" :class="{ loading: delete_loading }">
				  <span>Delete</span>
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

	       <p class="font11 text-muted">Drag the items into the order you prefer.</p>
	       
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
    `};const be=Vue.createApp({components:{"components-menu-allpages":fe,"components-menu-structure":ge,"components-menu-list":ve},data(){return{current_menu:0}},methods:{setCurrentmenu(t){this.current_menu=t},createNewmenu(){this.current_menu=0},afterCancelmenu(){r("afterCancelmenu");this.$refs.menu_list.MenuList()},afterSavemenu(){r("afterSavemenu");this.$refs.menu_list.MenuList()},afterAddpage(){this.$refs.menu_structure.getMenuDetails()}}});be.use(Maska);const ye=be.mount("#vue-theme-menu");if(h("#sort-items").exists()){var xe=document.getElementById("sort-items");var we=Sortable.create(xe,{swapThreshold:1,animation:150,direction:"horizontal"})}if(h("#sort-menu-items").exists()){let t=JSON.parse(category_group);h.each(t,function(t,e){console.log(e);Sortable.create(document.getElementById(e),{swapThreshold:1,group:e,animation:100})})}const ke={props:["ajax_url","id","label"],data(){return{data:[],loading:true}},created(){this.BookingTimeline()},computed:{hasData(){if(this.data.length>0){return true}return false}},methods:{BookingTimeline(){this.loading=true;axios({method:"POST",url:this.ajax_url+"/BookingTimeline",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&id="+this.id,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.loading=false})}},template:`	
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
	`};const Se=Vue.createApp({components:{"reservation-timeline":ke}});Se.use(ElementPlus);const Ie=Se.mount("#vue-booking");const Te=Vue.createApp({data(){return{drawer:false,filterText:"",defaultProps:{children:"children",label:"label"},data:[],loading:false,translation_vendors:[]}},created(){this.getMenu();this.translation_vendors=JSON.parse(translation_vendor)},watch:{filterText(t,e){this.$refs.treeRef.filter(t)}},methods:{getMenu(){this.loading=true;axios({method:"put",url:apibackend+"/getMenu",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details}else{this.data=[]}})["catch"](t=>{}).then(t=>{this.loading=false})},filterNode(t,e){if(!t)return true;return e.label.toLowerCase().includes(t)},goLink(t){if(t.link){r(t.link);window.location.href=t.link}}},template:`	
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
	`});Te.use(ElementPlus);const Oe=Te.mount("#vue-search-menu");const Ce=Vue.createApp({methods:{showDrawer(){Oe.drawer=true}},template:`	
	<a href="javascript:;" @click="showDrawer">
	  <i class="zmdi zmdi-search"></i>
    </a>
	`});const Ne=Ce.mount("#vue-search-toogle");const Ee=Vue.createApp({components:{"components-datatable":R},data(){return{loading:false,date_start:"",date_end:"",count_up:undefined,socket_error:"",webSocket:null}},created(){this.getDailySummary()},methods:{afterSelectdate(t,e){this.date_start=t;this.date_end=e;this.getDailySummary()},getDailySummary(){this.loading=true;axios({method:"POST",url:apibackend+"/getDailySummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&date_start="+this.date_start+"&date_end="+this.date_end,timeout:s}).then(t=>{let e=0;let a=0;let s=0;let i=0;let l=0;if(t.data.code==1){e=t.data.details.data.total_sales;a=t.data.details.data.total_delivery_fee;s=t.data.details.data.tax_total;i=t.data.details.data.total_tips;l=t.data.details.data.total}else{e=0;a=0;s=0;i=0;l=0}var o={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){o.suffix=t.data.details.price_format.symbol}else o.prefix=t.data.details.price_format.symbol;this.count_up=new countUp.CountUp(this.$refs.total_sales,e,o);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total_delivery_fee,a,o);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total_tax,s,o);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total_tips,i,o);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total,l,o);this.count_up.start()})["catch"](t=>{}).then(t=>{this.loading=false})},SwitchPrinter(t,e){if(e=="feieyun"){this.FPprint(t)}else{this.wifiPrint(t)}},FPprint(t){this.loading=true;axios({method:"POST",url:apibackend+"/FPprint_dailysalesreport",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&printerId="+t+"&date_start="+this.date_start+"&date_end="+this.date_end,timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"})}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})},wifiPrint(t){this.loading=true;axios({method:"POST",url:apibackend+"/WIFIprint_dailysalesreport",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&printerId="+t+"&date_start="+this.date_start+"&date_end="+this.date_end}).then(t=>{if(t.data.code==1){this.ConnectToPrintServer();setTimeout(()=>{this.sendServerToPrinter(t.data.details.data,t.data.msg)},500)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})},ConnectToPrintServer(){let t=!u(printerServer)?printerServer:null;if(this.webSocket==null){console.log("CONNECT ConnectToPrintServer");this.webSocket=new WebSocket(t);this.webSocket.onopen=function(t){console.log("WebSocket is open now.")};this.webSocket.onmessage=function(t){console.log("Received message from server:",t.data)};this.webSocket.onclose=function(t){console.log("WebSocket is closed now.");this.webSocket=null};this.webSocket.onerror=t=>{this.webSocket=null;this.socket_error="WebSocket error occurred, but no detailed ErrorEvent is available.";if(t instanceof ErrorEvent){this.socket_error=t.message}}}},sendServerToPrinter(t,e){if(this.webSocket.readyState===WebSocket.OPEN){this.webSocket.send(JSON.stringify(t));ElementPlus.ElNotification({title:"",message:e,position:"bottom-right",type:"success"})}else{if(u(this.socket_error)){this.socket_error="WebSocket is not open. Ready state is:"+this.webSocket.readyState}ElementPlus.ElNotification({title:"",message:this.socket_error,position:"bottom-right",type:"warning"})}}}});Ee.use(ElementPlus);const Fe=Ee.mount("#vue-daily-sales-report");const je={props:["merchant_id","days","time_range","data","data_loading"],data(){return{loading:false,start_time:[],datas:[],errors:[]}},created(){this.datas=this.data},watch:{data(t,e){this.datas=t}},computed:{hasError(){if(Object.keys(this.errors).length>0){return true}return false}},methods:{addRow(t){this.datas[t].push({id:0,status:"open",start_time:"00:00",end_time:"00:00",custom_text:""})},removeRow(t,e){this.datas[t].splice(e,1)},onSubmit(){this.loading=true;this.errors=[];axios({method:"PUT",url:apibackend+"/updatestorehours",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),data:this.datas},timeout:s}).then(t=>{if(t.data.code==1){if(t.data.details.error_count<=0){ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});this.$emit("afterUpdate")}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}this.errors=t.data.details.error}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"});this.errors=t.data.details.error}})["catch"](t=>{}).then(t=>{this.loading=false})}},template:"#xtemplate_opening_hours"};const Re=Vue.createApp({components:{"components-opening-hours":je},data(){return{data:[],days:[],time_range:[],loading:false}},created(){this.getOpeningHoursAttribute()},methods:{afterUpdate(){r("afterUpdate");this.getOpeningHoursAttribute()},getOpeningHoursAttribute(){this.loading=true;axios({method:"POST",url:apibackend+"/getOpeningHoursAttribute",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.days=t.data.details.days;this.data=t.data.details.data;this.time_range=t.data.details.time_range}else{}})["catch"](t=>{}).then(t=>{this.loading=false})}}});Re.use(ElementPlus);const Ye=Re.mount("#vue-opening-hours");const $e={props:["label","default_country","only_countries","phone","field_phone","field_phone_prefix"],data(){return{data:[],country_flag:"",mobile_prefix:"",mobile_number:""}},updated(){},mounted(){this.mobile_number=this.phone;this.getLocationCountries()},methods:{getLocationCountries(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),default_country:this.default_country,only_countries:this.only_countries};var e=f();t=JSON.stringify(t);_[e]=h.ajax({url:ajaxurl+"/getLocationCountries",method:"PUT",dataType:"json",data:t,contentType:i.json,timeout:s,crossDomain:true,beforeSend:t=>{this.is_loading=true;if(_[e]!=null){_[e].abort()}}});_[e].done(t=>{if(t.code==1){this.data=t.details.data;this.country_flag=t.details.default_data.flag;this.mobile_prefix=t.details.default_data.phonecode}else{this.data=[];this.country_flag="";this.mobile_prefix=""}});_[e].always(t=>{})},setValue(t){this.country_flag=t.flag;this.mobile_prefix=t.phonecode;this.$refs.ref_mobile_number.focus()}},template:`					
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
	`};const Pe=Vue.createApp({components:{"component-phone":$e},data(){return{data:[]}}});Pe.use(Maska);const Ke=Pe.mount("#app-phone");const De={props:["label","ajax_url","driver_uuid"],data(){return{loading:false,loading_activity:false,overview_data:[],tableData:[]}},mounted(){this.getDriverOverview();this.getDriverActivity()},methods:{getDriverOverview(){this.loading=true;axios({method:"put",url:this.ajax_url+"/getDriverOverview",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),driver_uuid:this.driver_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.overview_data=t.data.details}else{this.overview_data=[]}})["catch"](t=>{}).then(t=>{this.loading=false})},getDriverActivity(){this.loading_activity=true;axios({method:"put",url:this.ajax_url+"/getDriverActivity",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),driver_uuid:this.driver_uuid},timeout:s}).then(t=>{if(t.data.code==1){this.tableData=t.data.details.data}else{this.tableData=[]}})["catch"](t=>{}).then(t=>{this.loading_activity=false})}},template:"#xtemplate_overview"};const Me=Vue.createApp({components:{"component-driveroverview":De},data(){return{data:[]}}});Me.use(ElementPlus);const ze=Me.mount("#app-driveroverview");const Ae={props:["ajax_url","label","transaction_type_list","action_name","ref_id"],data(){return{is_loading:false,transaction_description:"",transaction_type:"credit",transaction_amount:0}},computed:{hasData(){if(!u(this.transaction_description)&&!u(this.transaction_type)&&!u(this.transaction_amount)){return true}return false}},mounted(){},methods:{show(){h(this.$refs.modal_adjustment).modal("show")},close(){h(this.$refs.modal_adjustment).modal("hide")},clear(){this.transaction_description="";this.transaction_type="credit";this.transaction_amount=0},submit(){var t={YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),payment_provider:this.payment_provider,transaction_description:this.transaction_description,transaction_type:this.transaction_type,transaction_amount:this.transaction_amount,ref_id:this.ref_id};this.error=[];this.is_loading=true;axios({method:"put",url:this.ajax_url+"/"+this.action_name,data:t,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg);this.clear();this.close();this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
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
	`};const Le={props:["ajax_url","action_name","ref_id"],data(){return{is_loading:false}},mounted(){this.getBalance()},methods:{getBalance(){this.is_loading=true;axios({method:"post",url:this.ajax_url+"/"+this.action_name,data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&ref_id="+this.ref_id,timeout:s}).then(t=>{var e;if(t.data.code==1){var a={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){a.suffix=t.data.details.price_format.symbol}else a.prefix=t.data.details.price_format.symbol;e=new countUp.CountUp(this.$refs.balance,t.data.details.balance,a);e.start();this.$emit("afterBalance",t.data.details.balance)}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
	<span ref="balance"></span>
	`};const Ue={props:["ajax_url","label","ref_id"],components:{"components-loading-box":ut},methods:{confirm(){ElementPlus.ElMessageBox.confirm(this.label.message,this.label.confirm,{confirmButtonText:this.label.ok,cancelButtonText:this.label.cancel,type:"warning"}).then(()=>{this.$refs.loading_box.show();axios({method:"POST",url:this.ajax_url+"/ClearWalletTransactions",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&ref_id="+this.ref_id,timeout:s}).then(t=>{if(t.data.code==1){this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.$refs.loading_box.close()})})["catch"](()=>{})}},template:`
	<components-loading-box
	ref="loading_box"
	message="Processing"
	donnot_close="don't close this window"
	>
	</components-loading-box>
	`};const qe=Vue.createApp({components:{"components-datatable":R,"components-create-adjustment":Ae,"components-commission-balance":Le,"components-clearwallet":Ue},methods:{createTransaction(){this.$refs.create_adjustment.show()},afterSave(){this.$refs.datatable.getTableData();this.$refs.balance.getBalance()},clearWallet(){this.$refs.clear_wallet.confirm()}}});qe.use(Maska);const Be=qe.mount("#vue-driver-wallet");const Ve={props:["label","size","ajax_url","start_value","end_value","time_interval","merchant_id","employment_type"],data(){return{loading:true,is_loading:false,sched_calendar:null,schedule_uuid:"",driver_id:"",car_id:"",date_start:"",time_start:"",time_end:"",instructions:"",zone_id:"",zone_value:"",zone_loading:false,zone_list:[{value:"Option1",label:"Option1"}]}},created(){this.initCalendar()},computed:{hasData(){let t=this.schedule_uuid.length;if(t>0){return true}return false}},mounted(){this.select2Driver();this.select2Car();this.getZoneList()},methods:{newSchedule(){this.clearForm();h(this.$refs.modal_sched).modal("show")},initCalendar(){let t=document.getElementsByTagName("html")[0].getAttribute("lang");t=t.replace("_","-");document.addEventListener("DOMContentLoaded",()=>{this.sched_calendar=new FullCalendar.Calendar(this.$refs.calendar,{locale:t,initialView:"dayGridMonth",headerToolbar:{right:"today prev,next",center:"title",left:"dayGridMonth,timeGridWeek,timeGridDay,listWeek"},selectable:true,unselectAuto:true,loading:t=>{this.loading=t},eventSources:[{url:this.ajax_url+"/getDriverSched",extraParams:{merchant_id:this.merchant_id},method:"POST",success:()=>{},failure:()=>{ElementPlus.ElNotification({title:"Error",message:"there was an error while fetching data",type:"warning",position:"bottom-right"})}}],dateClick:t=>{this.clearForm();this.date_start=t.dateStr;h(this.$refs.modal_sched).modal("show")},eventClick:t=>{this.getSchedule(t.event.id)},eventContent:(t,e)=>{let a=t.event.extendedProps;console.debug(a);let s="";s+='<div class="fc-content"><span class="fc-time"></span><span class="fc-title">';s+="<div>"+a.time+"</div>";s+="<div>"+a.plate_number+"</div>";s+='<div class="mytable">';s+='<div class="mycol">';s+='<img class="circle_image" src="'+a.avatar+'">';s+="</div>";s+='<div class="mycol">';s+='<span class="d-inline-block text-truncate font-weight-bold" style="max-width: 90px;">'+a.name+"</span>";s+="<div>"+a.zone_name+"</div>";s+="</div>";s+="</div>";s+="</span></div>";return{html:s}}});this.sched_calendar.render()})},getZoneList(){this.zone_loading=true;axios({method:"put",url:this.ajax_url+"/getZoneList2",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")},timeout:s}).then(t=>{if(t.data.code==1){this.zone_list=t.data.details}else{this.zone_list=[]}})["catch"](t=>{}).then(t=>{this.zone_loading=false})},select2Driver(){let a=this.merchant_id;let s=this.employment_type;h(this.$refs.driver_list).select2({width:"resolve",language:{searching:()=>{return this.label.searching},noResults:()=>{return this.label.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchDriver",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),merchant_id:a,employment_type:s};return JSON.stringify(e)}}})},select2Car(){h(this.$refs.car_list).select2({width:"resolve",language:{searching:()=>{return this.label.searching},noResults:()=>{return this.label.no_results}},ajax:{delay:250,url:this.ajax_url+"/searchCar",type:"PUT",contentType:"application/json",data:function(t){var e={search:t.term,YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content")};return JSON.stringify(e)}}})},submit(){this.is_loading=true;this.driver_id=h(this.$refs.driver_list).find(":selected").val();this.car_id=h(this.$refs.car_list).find(":selected").val();let t=this.zone_id;if(Object.keys(this.zone_id).length>0){t=this.zone_id.value}axios({method:"put",url:this.ajax_url+"/addSchedule",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),schedule_uuid:this.schedule_uuid,zone_id:t,driver_id:this.driver_id,vehicle_id:this.car_id,date_start:this.date_start,time_start:this.time_start,time_end:this.time_end,instructions:this.instructions},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:this.label.success,message:t.data.msg,position:"bottom-right",type:"success"});this.afterInsert()}else{ElementPlus.ElNotification({title:this.label.error,message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.is_loading=false})},getSchedule(t){this.zone_id="";this.loading=true;axios({method:"put",url:this.ajax_url+"/getSchedule",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),schedule_uuid:t},timeout:s}).then(t=>{if(t.data.code==1){const e=t.data.details;this.clearForm();h(this.$refs.modal_sched).modal("show");this.schedule_uuid=e.sched.schedule_uuid;this.driver_id=e.sched.driver_id;this.car_id=e.sched.car_id;this.date_start=e.sched.date_start;this.time_start=e.sched.time_start;this.time_end=e.sched.time_end;this.instructions=e.sched.instructions;this.zone_id=e.sched.zone_id;setTimeout(()=>{var t=new Option(e.driver.text,e.driver.id,false,false);h(this.$refs.driver_list).append(t).trigger("change");var t=new Option(e.car.text,e.car.id,false,false);h(this.$refs.car_list).append(t).trigger("change");h(this.$refs.driver_list).val(e.driver.id).trigger("change");h(this.$refs.car_list).val(e.car.id).trigger("change")},1)}else{ElementPlus.ElNotification({title:this.label.error,message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})},ConfirmDeleteSchedule(){ElementPlus.ElMessageBox.confirm(this.label.delete_message,this.label.delete_confirm,{confirmButtonText:this.label.ok,cancelButtonText:this.label.cancel,type:"warning"}).then(()=>{this.deleteSchedule()})["catch"](()=>{})},deleteSchedule(){this.delete_loading=false;axios({method:"put",url:this.ajax_url+"/deleteSchedule",data:{YII_CSRF_TOKEN:h("meta[name=YII_CSRF_TOKEN]").attr("content"),schedule_uuid:this.schedule_uuid},timeout:s}).then(t=>{if(t.data.code==1){ElementPlus.ElNotification({title:this.label.success,message:t.data.msg,position:"bottom-right",type:"success"});this.afterDelete()}else{ElementPlus.ElNotification({title:this.label.error,message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.delete_loading=false})},afterInsert(){h(this.$refs.modal_sched).modal("hide");this.clearForm();this.sched_calendar.refetchEvents()},afterDelete(){h(this.$refs.modal_sched).modal("hide");this.clearForm();this.sched_calendar.refetchEvents()},clearForm(){this.schedule_uuid="";this.driver_id="";this.car_id="";this.date_start="";this.time_start="";this.time_end="";this.instructions="";this.zone_id="";h(this.$refs.driver_list).val(null).trigger("change");h(this.$refs.car_list).val(null).trigger("change")}},template:`	
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
	`};const Je=Vue.createApp({components:{"component-calendar":Ve},data(){return{data:[]}},methods:{newSchedule(){console.debug("newSchedule");this.$refs.appcalendar.newSchedule()}}});let We={};if(typeof ElementPlusLocaleLanguage!=="undefined"&&ElementPlusLocaleLanguage!==null){We={locale:ElementPlusLocaleLanguage}}Je.use(ElementPlus,We);const He=Je.mount("#app-schedule");const Ge={props:["ajax_url","label","transaction_type"],data(){return{is_loading:false,transaction_uuid:"",data:[],merchant:[],provider:[]}},computed:{isUnpaid(){if(this.data.status=="unpaid"){return false}return true}},methods:{show(){h(this.$refs.modal_payout).modal("show");this.getPayoutDetails()},close(){h(this.$refs.modal_payout).modal("hide")},getPayoutDetails(){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/getPayoutDetails",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&transaction_uuid="+this.transaction_uuid,timeout:s}).then(t=>{if(t.data.code==1){this.data=t.data.details.data;this.merchant=t.data.details.merchant;this.provider=t.data.details.provider}else{o(t.data.msg,"error");this.data=[];this.merchant=[];this.provider=[]}})["catch"](t=>{}).then(t=>{this.is_loading=false})},changePayoutStatus(t){this.is_loading=true;axios({method:"POST",url:this.ajax_url+"/"+t,data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&transaction_uuid="+this.transaction_uuid+"&transaction_type="+this.transaction_type,timeout:s}).then(t=>{if(t.data.code==1){o(t.data.msg,"success");this.close();this.$emit("afterSave")}else{o(t.data.msg,"error")}})["catch"](t=>{}).then(t=>{this.is_loading=false})}},template:`
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
	`};const Ze=Vue.createApp({components:{"components-datatable":R,"components-payout-details":Ge},data(){return{is_loading:false,summary:[],count_up:undefined}},mounted(){this.payoutSummary()},methods:{payoutSummary(){this.is_loading=true;axios({method:"POST",url:apibackend+"/cashoutSummary",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content"),timeout:s}).then(t=>{if(t.data.code==1){this.summary=t.data.details.summary;var e={decimalPlaces:0,separator:",",decimal:"."};var a={decimalPlaces:t.data.details.price_format.decimals,separator:t.data.details.price_format.thousand_separator,decimal:t.data.details.price_format.decimal_separator};if(t.data.details.price_format.position=="right"){a.suffix=t.data.details.price_format.symbol}else a.prefix=t.data.details.price_format.symbol;this.count_up=new countUp.CountUp(this.$refs.ref_unpaid,this.summary.unpaid,e);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.ref_paid,this.summary.paid,e);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.total_unpaid,this.summary.total_unpaid,a);this.count_up.start();this.count_up=new countUp.CountUp(this.$refs.ref_total_paid,this.summary.total_paid,a);this.count_up.start()}})["catch"](t=>{}).then(t=>{this.is_loading=false})},viewPayoutDetails(t){this.$refs.payout.transaction_uuid=t;this.$refs.payout.show()},afterSave(){r("afterSave");this.$refs.datatable.getTableData();this.payoutSummary()}}});const Qe=Ze.mount("#vue-cashout");const Xe=Vue.createApp({data(){return{is_printing:false}},methods:{printDiv(){this.is_printing=true;h(".printhis").printThis({pageTitle:"Dining Tables",base:false});setTimeout(()=>{this.is_printing=false},1e3)}}});const ta=Xe.mount("#vue-printhis");const ea={template:"#xtemplate_location_addrate",props:["save_action","merchant_id","services_list"],data(){return{modal:false,fee:0,loading:false,rate_id:null,country_id:null,country_list:[],loading_state:false,state_id:null,state_list:[],city_id:null,city_list:[],loading_city:false,area_id:null,area_list:[],loading_area:false,loading_submit:false,minimum_order:0,maximum_amount:0,free_above_subtotal:0,data:[],estimated_time_min:0,estimated_time_max:0,service_type:""}},mounted(){this.fetchCountry("")},methods:{clearForm(){this.rate_id=null;this.state_id=null;this.state_list=[];this.city_id=null;this.city_list=[];this.area_id=null;this.area_list=[];this.fee=0;this.minimum_order=0;this.maximum_amount=0;this.free_above_subtotal=0;this.estimated_time_min=0;this.estimated_time_max=0;this.service_type="";this.fetchCountry("")},fetchCountry(t){this.loading=true;axios({method:"POST",url:apibackend+"/fetchCountry",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&q="+t,timeout:s}).then(t=>{if(t.data.code==1){this.country_list=t.data.details.data}else{this.country_list=[]}this.country_id=t.data.details.default_country;if(this.country_id){this.fetchState()}})["catch"](t=>{}).then(t=>{this.loading=false})},OnselectCountry(t){this.state_id=null;this.state_list=[];this.city_id=null;this.city_list=[];this.area_id=null;this.area_list=[];this.fetchState()},fetchState(){this.loading=true;axios({method:"POST",url:apibackend+"/fetchState",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&country_id="+this.country_id,timeout:s}).then(t=>{if(t.data.code==1){this.state_list=t.data.details.data}else{this.state_list=[]}})["catch"](t=>{}).then(t=>{this.loading=false})},OnselectState(t){this.city_id=null;this.city_list=[];this.area_id=null;this.area_list=[];this.fetchCity("")},fetchCity(){this.loading_city=true;axios({method:"POST",url:apibackend+"/fetchCity",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&state_id="+this.state_id,timeout:s}).then(t=>{if(t.data.code==1){this.city_list=t.data.details.data}else{this.city_list=[]}})["catch"](t=>{}).then(t=>{this.loading_city=false})},OnselectCity(t){this.area_id=null;this.area_list=[];this.fetchArea("")},fetchArea(){this.loading_area=true;axios({method:"POST",url:apibackend+"/fetchArea",data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&city_id="+this.city_id,timeout:s}).then(t=>{if(t.data.code==1){this.area_list=t.data.details.data}else{this.area_list=[]}})["catch"](t=>{}).then(t=>{this.loading_area=false})},onSubmit(){this.loading_submit=true;let t="YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content");t+="&merchant_id="+this.merchant_id;t+="&rate_id="+this.rate_id;t+="&fee="+this.fee;t+="&country_id="+this.country_id;t+="&state_id="+this.state_id;t+="&city_id="+this.city_id;t+="&area_id="+this.area_id;t+="&minimum_order="+this.minimum_order;t+="&maximum_amount="+this.maximum_amount;t+="&free_above_subtotal="+this.free_above_subtotal;t+="&estimated_time_min="+this.estimated_time_min;t+="&estimated_time_max="+this.estimated_time_max;t+="&service_type="+this.service_type;axios({method:"POST",url:apibackend+"/"+this.save_action,data:t,timeout:s}).then(t=>{if(t.data.code==1){this.modal=false;this.$emit("afterSaverate");ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"success"});setTimeout(()=>{this.clearForm()},500)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading_submit=false})},setRate(t){console.log("setRate",t);this.modal=true;this.data=t;this.rate_id=t.rate_id;this.country_id=t.country_id;this.state_id=t.state_id;this.city_id=t.city_id;this.area_id=t.area_id;this.fee=t.fee;this.minimum_order=t.minimum_order;this.maximum_amount=t.maximum_amount;this.free_above_subtotal=t.free_above_subtotal;this.service_type=t.service_type;this.estimated_time_min=t.estimated_time_min;this.estimated_time_max=t.estimated_time_max;this.fetchCity();this.fetchArea()}}};const aa=Vue.createApp({components:{"components-datatable":R,"components-addrate":ea},data(){return{loading:false}},methods:{showAddrate(){this.$refs.ref_addrate.modal=true},afterSaverate(){console.log("afterSaverate");this.$refs.datatable.getTableData()},editRecords(t){this.loading=true;axios({method:"POST",url:apibackend+"/"+action_get,data:"YII_CSRF_TOKEN="+h("meta[name=YII_CSRF_TOKEN]").attr("content")+"&rate_id="+t,timeout:s}).then(t=>{if(t.data.code==1){this.$refs.ref_addrate.setRate(t.data.details)}else{ElementPlus.ElNotification({title:"",message:t.data.msg,position:"bottom-right",type:"warning"})}})["catch"](t=>{}).then(t=>{this.loading=false})}}});aa.use(ElementPlus);const sa=aa.mount("#vue-location-rate")})(jQuery);