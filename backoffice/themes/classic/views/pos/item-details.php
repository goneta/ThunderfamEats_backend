<q-dialog
    v-model="dialog"
    @before-show="getMenuItem"
    @before-hide="beforeHide"
    :maximized="this.$q.screen.lt.sm?true:false"
    persistent
    transition-show="slide-up"
>
  <template v-if="loading">
    <q-card class="card-item-details">
        <q-card-section class="q-gutter-y-sm" >
          <q-skeleton height="150px" ></q-skeleton>

          <div>
            <q-skeleton type="text" class="text-subtitle1" ></q-skeleton>
            <q-skeleton type="text" width="50%" class="text-subtitle1" ></q-skeleton>
            <q-skeleton type="text" class="text-caption" ></q-skeleton>
          </div>

          <template v-for="items in 6" :key="items">
            <div class="row">
              <div class="col-2">
                <q-skeleton type="QRadio" size="25px" ></q-skeleton>
              </div>
              <div class="col">
                <q-skeleton type="text" class="text-caption"></q-skeleton>
              </div>
            </div>
          </template>

          <template v-for="items in 2" :key="items">
            <q-skeleton height="30px" ></q-skeleton>
            <q-skeleton height="60px" ></q-skeleton>
            <q-skeleton height="30px" ></q-skeleton>
          </template>
          <template v-for="items in 1" :key="items">
            <div class="row">
              <div class="col-2">
                <q-skeleton type="QRadio" size="25px" ></q-skeleton>
              </div>
              <div class="col">
                <q-skeleton type="text" class="text-caption" ></q-skeleton>
              </div>
            </div>
          </template>

          <q-skeleton height="60px" ></q-skeleton>
        </q-card-section>
      </q-card>
  </template>
  <template v-else>
     <q-card class="card-item-details">      

       <q-card-section
       class="no-wrap relative-position border-bottom"      
       >
        <div class="row q-gutter-x-sm">
            <div class="borderx">
               <q-img
                :src="this.image_featured ? this.image_featured : items.url_image"              
                style="height: 9em;width:9em;"
                fit="scale-down"
                spinner-color="primary"
                spinner-size="xs"
                class="rounded-borders"
                ></q-img>
            </div>        
            <div class="col">
                <div class="text-weight-regular text-body1 line-normal ellipsis">
                  <span v-html="items.item_name"></span>
                </div>

                <div class="ellipsis-3-lines text-weight-thin">
                  <span v-html="items.item_description"></span>
                </div>
                
                <div v-if="items.price[item_size_id]">  
                  <template v-if="points_data.points_enabled">
                    <template v-if="items.price[item_size_id].points_enabled">                  
                      <q-badge color="primary" text-color="white" :label="items.price[item_size_id].earning_points_label" ></q-badge>
                    </template>
                  </template>                              
                </div>
                
            </div>        
        </div>
        <div class="q-pa-sm absolute-top-right q-mr-sm">
            <q-btn icon="close" color="grey" flat round dense v-close-popup ></q-btn>
        </div>
       </q-card-section>

       <q-card-section style="max-height: 60vh" class="scroll">                     

          <div class="bg-grey-1 q-pa-sm radius10 q-mb-md ">
              <div class="text-weight-regular line-normal">              
                <?php echo t("Customize your Order")?>
              </div>
              <q-option-group
                v-model="item_size_id"
                :options="size_data"
                inline
                size="xs"
                color="primary"
                checked-icon="add"
              ></q-option-group>
          </div>

        <!-- COOKING REF -->
        <template v-if="cooking_data.length > 0">
            <div
            class="bg-grey-1 q-pa-sm radius10 q-mb-md"            
            >
            <div class="flex justify-between">
                <div class="text-weight-regular  line-normal">                   
                   <?php echo t("Cooking Reference")?>
                </div>
                <div class="text-grey font12">
                <template v-if="items.cooking_ref_required">
                    <span class="bg-blue-1 text-blue-7 q-pa-xs rounded-borders"><?php echo t("Required")?></span>
                </template>
                <template v-else>
                    <span>(<?php echo t("Optional")?>)</span>
                </template>
                </div>
            </div>
            <q-option-group
                v-model="cooking_ref"
                :options="cooking_data"
                inline
                size="xs"
                color="primary"
            />
            </div>
        </template>
        <!-- COOKING REF -->


        <!-- Ingredients -->
        <template v-if="ingredients_data.length > 0">
            <div
            class="bg-grey-1 q-pa-sm radius10 q-mb-md"            
            >
            <div class="flex justify-between">
                <div>
                    <div class="text-weight-regular  line-normal">                    
                    <?php echo t("Ingredients")?>
                    </div>
                </div>
            </div>
            <q-option-group
                v-model="ingredients"
                :options="ingredients_data"
                inline
                type="checkbox"
                size="xs"
                checked-icon="check_box"
                unchecked-icon="square"
                color="primary"
            ></q-option-group>
            </div>
        </template>
        <!-- Ingredients -->

        <!-- addons -->
        <template v-if="addons[item_size_id]">
              <template
                v-for="addons in addons[item_size_id]"
                :key="addons.subcat_id"
              >
                <div class="bg-grey-1 q-pa-sm radius10 q-mb-md">
                  <div class="flex justify-between">
                    <div class="text-weight-regular  line-normal">
                      {{ addons.subcategory_name }}
                    </div>
                    <div class="text-grey font12">
                      <template v-if="addons.multi_option === 'one'">                         
                         <?php echo t("Select 1")?>
                        <template v-if="addons.require_addon == 1">
                          <span class="bg-blue-1 text-blue-7 q-pa-xs rounded-borders"><?php echo t("Required")?></span>
                        </template>
                      </template>
                      <template v-else-if="addons.multi_option === 'multiple'">
                        <template v-if="addons.multi_option_min > 0">                          
                          <?php echo t("Select minimum")?>
                          {{ addons.multi_option_min }} <?php echo t("to maximum")?>
                          {{ addons.multi_option_value }}
                        </template>
                        <template v-else>                          
                          <?php echo t("Choose up to")?>
                          {{ addons.multi_option_value }}
                        </template>

                        <template v-if="addons.require_addon == 1">
                          <span class="bg-blue-1 text-blue-7 q-pa-xs rounded-borders"><?php echo t("Required")?></span>
                        </template>
                      </template>
                      <template v-else-if="addons.multi_option === 'custom'">
                        <template v-if="addons.multi_option_min > 0">
                        <?php echo t("Select minimum")?> {{ addons.multi_option_min }} to maximum {{ addons.multi_option_value }}
                        </template>
                        <template v-else>
                        <?php echo t("Choose up to")?>
                          {{ addons.multi_option_value }}
                        </template>

                        <template v-if="addons.require_addon == 1">
                          <span class="bg-blue-1 text-blue-7 q-pa-xs rounded-borders"><?php echo t("Required")?></span>
                        </template>
                      </template>
                      <template v-else> (<?php echo t("Optional")?>) </template>
                    </div>
                  </div>

                  <q-list>
                    <q-item
                      v-for="sub_items in addons.sub_items"
                      :key="sub_items.sub_item_id"
                      v-ripple
                      :tag="
                        addons.multi_option === 'multiple' ? 'div' : 'label'
                      "
                    >
                      <template v-if="addons.multi_option === 'one'">
                        <q-item-section avatar>
                          <q-radio
                            v-model="addons.sub_items_checked"
                            :val="sub_items.sub_item_id"
                            color="primary"
                            size="sm"
                          />
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>{{
                            sub_items.sub_item_name
                          }}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                          <q-item-label caption>{{
                            sub_items.pretty_price
                          }}</q-item-label>
                        </q-item-section>
                      </template>

                      <template v-else-if="addons.multi_option === 'custom'">
                        <q-item-section avatar>
                          <q-checkbox
                            v-model="sub_items.checked"
                            :val="sub_items.sub_item_id"
                            label=""
                            :disable="sub_items.disabled"
                            color="primary"
                            size="sm"
                          >
                          </q-checkbox>
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>{{sub_items.sub_item_name}}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                          <q-item-label caption>{{sub_items.pretty_price}}</q-item-label>
                        </q-item-section>
                      </template>

                      <template v-else-if="addons.multi_option === 'multiple'">
                        <q-item-section :side="!sub_items.checked">
                          <div
                            v-if="sub_items.checked == true"
                            class="row items-center justify-center"
                          >
                            <div class="col no-padding text-center">
                              <q-btn
                                @click="
                                  sub_items.qty > 1
                                    ? sub_items.qty--
                                    : (sub_items.checked = false)
                                "
                                round
                                unelevated
                                dense
                                size="11px"
                                color="primary"
                                icon="remove"
                              />
                            </div>
                            <div class="col no-padding text-center">
                              {{ sub_items.qty }}
                            </div>
                            <div class="col no-padding text-center">
                              <q-btn
                                @click="sub_items.qty++"
                                round
                                unelevated
                                dense
                                size="11px"
                                color="primary"
                                icon="add"
                                :disabled="sub_items.disabled"
                              />
                            </div>
                          </div>
                          <div v-else>
                            <q-btn
                              @click="sub_items.checked = true"
                              round
                              unelevated
                              dense
                              size="11px"
                              color="grey-4"
                              icon="add"
                              :disabled="sub_items.disabled"
                            />
                          </div>
                        </q-item-section>
                        <q-item-section>
                          <q-item-label>{{sub_items.sub_item_name}}</q-item-label>
                        </q-item-section>
                        <q-item-section side>
                          <q-item-label caption>{{sub_items.pretty_price}}</q-item-label>
                        </q-item-section>
                      </template>
                    </q-item>
                  </q-list>
                </div>
              </template>
        </template>
        <!-- addons -->

        <div class="bg-grey-1 q-pa-sm radius10 q-mb-md">
              <div class="text-weight-regular line-normal">
              <?php echo t("Special Instructions")?>
              </div>
              <q-input
                v-model="special_instructions"
                autogrow
                class="q-mb-md full-width"
                color="primary"
              ></q-input>
              <div class="text-weight-regular line-normal">
              <?php echo t("If sold out")?>
              </div>
              <q-select
                dense
                v-model="if_sold_out"
                :options="sold_out_options"
                class="q-mb-md"
                transition-show="scale"
                transition-hide="scale"
                color="primary"
              ></q-select>
        </div>

       </q-card-section>     

       <template v-if="!$q.screen.lt.sm" >
       <q-card-actions class="border-top">               
        <div class="row fit q-gutter-x-sm items-center">
            <div class="col-3">
                <div class="q-pl-sm">
                    <div class="text-weight-medium"><?php echo t("Total Price")?></div>
                    <div class="text-weight-bold text-negative">                      
                      <money-format :amount="item_total" ></money-format>
                    </div>
                </div>
            </div>
            <div class="col-4">

            <!-- qty -->

            <div class="flex items-center q-col-gutter-x-md">
              <div class="borderx">
              <q-btn
                  icon="remove"                  
                  outline round color="grey"
                  unelevated                  
                  rounded
                  @click="item_qty > 1 ? item_qty-- : 1"       
                  style="min-width:5px"
              ></q-btn>
              </div>
              <div class="borderx text-body2">
              {{item_qty}}
              </div>
              <div class="borderx">
              <q-btn
                  icon="add"
                  outline round color="grey"
                  unelevated                  
                  rounded
                  @click="item_qty++"      
                  style="min-width:5px"            
              ></q-btn>
              </div>
              </div>
            <!-- qty -->

            </div>
            <div class="col-4">
            <template v-if="items.not_for_sale">
                <q-btn
                    unelevated
                    color="grey"
                    label="<?php echo t("Not for sale")?>"
                    class="radius20"
                    no-caps
                    :disable="true"
                    size="16px"
                ></q-btn>
                </template>
                <template v-else>
                <q-btn
                    unelevated
                    color="primary"
                    text-color="white"
                    label="<?php echo t("Add")?>"
                    class="radius20 fit"
                    no-caps
                    size="16px"
                    @click="AddToCart()"
                    :loading="loading_add"
                    :disable="disabled_cart"                    
                ></q-btn>
                </template>
            </div>
        </div>
       </q-card-actions>
       </template>

       <template v-else>
       <q-page-sticky
            position="bottom"
            :offset="[0, 0]"
            class="q-pt-sm q-pb-sm bg-white text-black border-top"
            expand          
        >

        <div class="row fit q-gutter-x-sm items-center">
            <div class="col-3">
                <div class="q-pl-sm">
                    <div class="text-weight-medium"><?php echo t("Total Price")?></div>
                    <div class="text-weight-bold text-negative">                      
                      <money-format :amount="item_total" ></money-format>
                    </div>
                </div>
            </div>
            <div class="col-4">

            <q-input
                v-model="item_qty"
                outlined
                color="primary"                
                bg-color="primary"
                maxlength="14"
                dense
                class="input-to-white radius20"
                style="overflow: hidden"
                >
                <template v-slot:prepend>
                    <q-btn
                    color="primary"
                    text-color="white"
                    icon="remove"
                    unelevated
                    dense
                    size="sm"
                    @click="item_qty > 1 ? item_qty-- : 1"                    
                    style="min-width:30px;"
                    ></q-btn>
                </template>
                <template v-slot:append>
                    <q-btn
                    color="primary"
                    text-color="white"
                    icon="add"
                    unelevated
                    dense
                    size="sm"
                    @click="item_qty++"
                    style="min-width:30px;"                    
                    ></q-btn>
                </template>
                </q-input>

            </div>
            <div class="col-4">
            <template v-if="items.not_for_sale">
                <q-btn
                    unelevated
                    color="grey"
                    label="<?php echo t("Not for sale")?>"
                    class="radius20"
                    no-caps
                    :disable="true"
                ></q-btn>
                </template>
                <template v-else>
                <q-btn
                    unelevated
                    color="primary"
                    text-color="white"
                    label="<?php echo t("Add")?>"
                    class="radius20 fit"
                    no-caps
                    @click="AddToCart()"
                    :loading="loading_add"
                    :disable="disabled_cart"                    
                ></q-btn>
                </template>
            </div>
        </div>

       </q-page-sticky>
       </template>
      

     </q-card>
  </template>
</q-dialog>