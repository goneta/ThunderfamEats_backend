<div id="app-chat" v-cloak>

<!-- <div class="q-pa-md"> -->
    <q-layout view="lHh Lpr lff" >
      <q-header reveal class="bg-white text-dark q-ma-md" >        
        <template v-if="chatting_to">                     
        <q-toolbar style="border: 1px solid #ddd" class="radius10 q-pa-sm" >            
          <q-avatar size="55px">           
           <q-img
                :src="chatting_to.photo"
                spinner-color="primary"
                spinner-size="md"
                style="height: 55px; max-width: 55px"
            >
           </q-img>
          </q-avatar>
          <q-toolbar-title>
            <div>                
              <h6 class="q-ma-none q-pa-none line-normal">{{ chatting_to.first_name }}</h6>
              <p class="q-ma-none text-grey q-pa-none font13">{{ chatting_to.user_type }}</p>
            </div>
          </q-toolbar-title>
          <div>                         
             <q-btn round color="dark" flat icon="menu" @click="drawer=!drawer" ></q-btn>
             <q-btn  round color="dark" flat icon="more_vert" >
                 <q-popup-proxy style="min-width: 150px;">                  
                  <q-list>
                      <q-item clickable v-ripple  target="_blank">
                          <q-item-section><?php echo t("View Profile")?></q-item-section>
                      </q-item>
                      <?php if($delete_chat): ?>
                      <q-item clickable v-ripple @click="deleteChatConfirm" >
                          <q-item-section><?php echo t("Delete chat")?></q-item-section>
                      </q-item>
                      <?php endif;?>
                  </q-list>
                 </q-popup-proxy>
             </q-btn>
          </div>          
        </q-toolbar>
        </template>
        <template v-else>
           <q-toolbar style="border: 1px solid #ddd" class="radius10 q-pa-sm" >
               <q-toolbar-title><?php echo t("Chats")?></q-toolbar-title>
               <div>
                  <q-btn round color="dark" flat icon="menu" @click="drawer=!drawer" ></q-btn>
               </div>
           </q-toolbar>
        </template>
      </q-header>
      

      <q-drawer
        v-model="drawer"
        show-if-above
        :width="300"
        :breakpoint="600"
      >
        <q-scroll-area  style="height: calc(100% - 10px); border-right: 1px solid #ddd">
        <div class="q-pa-md">

          
          <!-- <q-input color="primary" outlined v-model="search" label="Search chat" class="q-mb-md"
          @click="is_search=true"
          @blur="closeSearch"
          >            
            <template v-slot:prepend>              
              <q-btn v-if="is_search" @click="closeSearch" flat round color="primary" icon="keyboard_backspace" ></q-btn>
            </template>
            <template v-slot:append>
               <q-icon v-if="!is_search" name="search" size="md" ></q-icon>
               <q-btn v-if="hasSearch" @click="search=''" flat round color="primary" icon="highlight_off" ></q-btn>
            </template>
          </q-input> -->
          
          <components-search-chat
          ref="search_chat"
          api="<?php echo $ajax_url;?>"    
          language="<?php echo Yii::app()->language ;?>"                
          :search_type='<?php echo json_encode($search_type)?>'
          :label="{              
              search_chat : '<?php echo CJavaScript::quote( $search_chat )?>',               
          }"  			       
          @on-searchchat="onSearchchat"      
          @on-searchloading="onSearchloading"  
          @set-searchtext="setSearchtext"  
          @on-searchresults="onSearchresults"                    
          >
          </components-search-chat>


                    
          <!-- SEARCH RESULTS -->
          <template v-if="search_chat">
            <div class="search-wrap">
              <div v-if="hasSearch" class="text-body2x text-weight-regular q-mb-md">
              <q-icon name="search" size="2em"  ></q-icon> <?php echo t("Search for")?> {{search_text}}
              </div>
              
              <div class="text-center q-pa-xl" v-if="search_loading">                
                <q-spinner                  
                  color="primary"
                  size="2em"
                >
                </q-spinner>
              </div>
              
              <template v-if="hasSearchData && !search_loading && hasSearch">                
                <q-list class="list-custom">
                   <template v-for="items in getSearchData" :key="items">
                      <q-item clickable v-ripple @click="chatToUser(items.client_uuid,items)"> 
                       <q-item-section avatar>
                            <q-avatar>                                              
                              <img :src="items.photo_url" />
                            </q-avatar>
                        </q-item-section>

                        <q-item-section>
                          <q-item-label class="text-weight-bold">
                            {{items.first_name}} {{items.last_name}}
                          </q-item-label>
                          <q-item-label caption>{{ items.user_type }}</q-item-label>                                                
                        </q-item-section>              

                      </q-item>
                   </template>
                </q-list>
              </template>
              <template v-else>
                <p v-if="hasSearch && !search_loading" class="text-dark">
                <?php echo t("No matching records found")?>
                </p>
              </template>

            </div>
            <!-- search wrap -->
          </template>
          <!-- SEARCH RESULTS -->

          <template v-else>            
            <components-participants
            ref="participants"
            api="<?php echo $ajax_url;?>"        
            user_uuid="<?php echo $main_user_uuid;?>"        
            language="<?php echo Yii::app()->language ;?>"                     
            main_user_type="<?php echo $main_user_type ;?>" 
            @after-clickconversation="afterClickconversation"          
            @set-userdata="setUserdata" 
            @set-whoistyping="setWhoistyping" 
            :label="{              
                is_typing : '<?php echo CJavaScript::quote( t("is typing") )?>',     
                order_number : '<?php echo CJavaScript::quote( t("Order#") )?>',            
            }"  			                            
            >
            </components-participants>
          </template>

        </div>
        </q-scroll-area>

       
      </q-drawer>

      <q-page-container>
        <q-page class="flex items-stretch content-end q-pa-md">
      
         <div class="fit">
          <q-scroll-area style="height: calc(60vh)" ref="scroll_ref"  >
          
            <div class="chat-wrapper">                      
              <components-messages
              ref="messages"
              api="<?php echo $ajax_url;?>" 
              user_uuid="<?php echo $main_user_uuid;?>"    
              :conversation_id="conversation_id"                    
              :label="{              
                    uknown : '<?php echo CJavaScript::quote( t("Uknown") )?>',       
                    please_wait : '<?php echo CJavaScript::quote( t("Please wait...") )?>',       
                    no_chat_selected : '<?php echo CJavaScript::quote( t("No chats selected") )?>',  
                    you : '<?php echo CJavaScript::quote( t("You") )?>',  
                    is_typing : '<?php echo CJavaScript::quote( t("typing...") )?>', 
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
  <!-- </div> -->

</div>
<!-- app-chat -->