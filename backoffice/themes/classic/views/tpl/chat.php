
<div id="app-driver-chat"  class="chat-half-wrap" v-cloak>

<q-layout>  
 <?php if($mode_view=="mobile"):?>
 <q-header class="bg-transparent">
    <q-toolbar class="bg-grey-2 radius10 text-dark" >
        <q-btn href="<?php echo Yii::app()->request->urlReferrer?>" flat round dense icon="arrow_back" class="q-mr-sm" />
    </q-toolbar>
 </q-header>
 <?php endif;?>
  <q-page-container>
    <q-page>        

      <div class="column justify-end"  style="<?php echo $mode_view=="mobile"?'height: calc(89vh)':'height: calc(97vh)'; ?>">
        <div class="col">

        <q-scroll-area style="height: calc(75vh)" ref="scroll_ref" class="q-pr-sm q-pl-sm "  >
               <q-inner-loading 
                    :showing="loading || loading_create"
                    color="primary"
                    label="Please wait..."
                    label-class="text-dark"
                    label-style="font-size: 1em"
                >
                </q-inner-loading>    

                <template v-if="!hasMessage && !loading">
                    <div class="flex justify-center items-center full-height q-mt-xl">                                                
                        <div class="text-center">
                            <q-avatar size="80px">
                                <img :src="driver_info.photo">
                            </q-avatar>
                            <h6 class="q-ma-none">{{driver_info.full_name}}</h6>
                            <p class="text-grey">Kindly enter your message in the chat box below.</p>
                        </div>
                    </div>
                </template>
                                
                <template v-for="items in getChatmessage" :key="items">    
                <template v-if="items.messageType == 'audio'">
                
                    <q-chat-message
                        :name="items.senderID == user_uuid ? 'You' : items.sender"
                        :avatar="items.photo"
                        :stamp="items.time"
                        :text-color="items.senderID == user_uuid ? 'white' : 'dark'"
                        :bg-color="items.senderID == user_uuid ? 'blue' : 'grey-2'"
                        :sent="items.senderID == user_uuid ? true : false"
                    >
                        <template #avatar>
                        <q-avatar class="q-ml-sm">
                            <q-img
                            :src="items.photo"
                            spinner-size="sm"
                            spinner-color="primary"
                            style="height: 48px; max-width: 48px; min-width: 48px"
                            fit="cover"
                            loading="lazy"
                            ></q-img>
                        </q-avatar>
                        </template>
                        <div style="min-width: 250px; max-width: 250px">                                              
                            <audioplayback
                                :audio_path="items.fileUrl"
                                :show_media="true"
                                :layout="items.senderID == user_uuid ? 1 : 0"
                                :uploading_status="upload_audio_loading"
                             ></audioplayback>               
                        </div>
                    </q-chat-message>


                </template>
                <template v-else>
                    <q-chat-message
                        :text-color="items.senderID==user_uuid?'white':'dark'"
                        :bg-color="items.senderID==user_uuid?'primary':'mygrey'"
                        :sent="items.senderID==user_uuid?true:false"  
                        :stamp="items.time"
                    >
                        <template v-slot:name>
                            <template v-if="items.senderID==user_uuid">
                                <?php echo t("You")?>
                            </template>
                            <template v-else>
                            {{items.sender}}
                            </template>                        
                            <template v-if="items.senderID!=user_uuid">
                                <q-badge rounded color="yellow" class="q-ml-sm q-mr-sm"></q-badge>                    
                            </template>
                        </template>
                        <!--- slot-name -->
                        
                        <template v-slot:avatar>
                            <template v-if="items.senderID!=user_uuid">
                            <img
                                class="q-message-avatar q-message-avatar--received"
                                :src="items.photo"
                            >
                            </img>
                            </template>                
                        </template>
                        
                        <div>
                        <template v-if="items.message && items.fileUrl">
                            <div>{{items.message}}</div>
                            <q-img
                            :src="items.fileUrl"
                            spinner-size="sm"
                            spinner-color="primary"
                            style="height: 80px; min-width:80px;max-width:80px"
                            >
                            </q-img>  
                        </template>
                        <template v-else-if="items.fileUrl">              
                            <q-img
                            :src="items.fileUrl"
                            spinner-size="sm"
                            spinner-color="primary"
                            style="height: 180px; min-width:300px;max-width:300px"
                            >
                            </q-img>           
                        </template>
                        <template v-else>
                            {{items.message}}          
                        </template>          
                        </div>                
                    </q-chat-message>         
                </template>   
            </template>
            <!--- END MESSAGES -->

          </q-scroll-area>

        </div>
        <!-- col -->
        <div class="col-2 flex items-end">

            <!-- CHAT MESSAGES -->                   
            <components-chat
                ref="chat"
                api="<?php echo $ajax_url;?>" 
                api_upload="<?php echo $ajax_url."/uploadimage";?>" 
                user_uuid="<?php echo $main_user_uuid;?>"             
                max_file_size="<?php echo Helper_maxSize;?>"    
                :conversation_id="conversation_id"            
                :from_data="from_data" 
                :to_data="to_data" 
                :label="{              
                    your_message : '<?php echo CJavaScript::quote( t("Your message") )?>',      
                    please_wait : '<?php echo CJavaScript::quote( t("Please wait...") )?>',                                  
                }"  			         
                @after-addmessage="afterAddmessage"
                >        
            </components-chat>        
            <!-- CHAT MESSAGES -->     

        </div>
      </div>
      <!-- column -->

    </q-page>
  </q-page-container>  
</q-layout>

</div>
<!-- app driver chat -->
