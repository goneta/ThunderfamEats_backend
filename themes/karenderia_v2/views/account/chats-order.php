<div id="app-chat-order" v-cloak>

<q-layout view="lHh lpr lFf" >
      <q-header reveal class="bg-white text-dark">
      <q-toolbar style="border: 1px solid #ddd" class="radius10 q-pa-sm" >
               <q-toolbar-title><?php echo t("Chats")?></q-toolbar-title>
               <div>
                  <q-btn round color="dark" flat icon="menu" @click="drawer=!drawer" ></q-btn>
               </div>
           </q-toolbar>
      </q-header>

      <q-page-container>
        <q-page class="flex items-stretch content-end q-pa-md" >
        
          <div class="fit">
              <q-scroll-area style="height: calc(65vh)" ref="scroll_ref" >
              
                <div class="chat-wrapper">                              
                    =>{{conversation_id}}
                    <pre>{{user_data}}</pre>
                    <components-messages
                    ref="messages"
                    api="<?php echo $ajax_url;?>" 
                    user_uuid="<?php echo $main_user_uuid;?>"    
                    :conversation_id="conversation_id"
                    :user_data="user_data"           
                    :label="{              
                            uknown : '<?php echo CJavaScript::quote( t("Uknown") )?>',       
                            please_wait : '<?php echo CJavaScript::quote( t("Please wait...") )?>',       
                            no_chat_selected : '<?php echo CJavaScript::quote( t("No chats selected") )?>',  
                    }"  			                            
                    no_chat_image_url="<?php echo Yii::app()->theme->baseUrl?>/assets/images/no-chat-selected.png"
                    @set-chattingwith="setChattingwith" 
                    @scroll-tobottom="scrollTobottom" 
                    >
                    </components-messages>
                </div>
                <!-- chat-wrapper -->

              </q-scroll-area>

              <div class="q-pa-sm"></div>
          
            <!-- CHAT MESSAGES -->                   
                <components-chat
                ref="chat"
                api="<?php echo $ajax_url;?>" 
                api_upload="<?php echo $ajax_url."/uploadimage";?>" 
                user_uuid="<?php echo $main_user_uuid;?>"             
                max_file_size="<?php echo Helper_maxSize;?>"    
                :conversation_id="conversation_id"
                :user_data="user_data"      
                :label="{              
                    your_message : '<?php echo CJavaScript::quote( t("Your message") )?>',      
                    please_wait : '<?php echo CJavaScript::quote( t("Please wait...") )?>',                                  
                }"  			         
                @after-addmessage="afterAddmessage"
                >        
                </components-chat>        
            <!-- CHAT MESSAGES -->      


          </div>
          <!-- fit -->

        </q-page>
      </q-page-container>
</q-layout>

</div>
<!-- app-chat -->