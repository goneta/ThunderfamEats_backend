<template>
  <q-page padding>
     <q-carousel
      v-model="slide"
      transition-prev="slide-right"
      transition-next="slide-left"
      swipeable
      animated
      height="120px"
      class="q-mb-md"
      autoplay
      infinite
    >
    <q-carousel-slide v-for="i in 2" :key="i" :name="i"  class="column no-wrap flex-center q-pa-none"  >
       <q-img class="fit" src="banner3.jpg" fit="fill" loading="lazy" />
     </q-carousel-slide>
    </q-carousel>

    <q-input
    v-model="q"
    label="Search food and restaurants"
    dense
    outlined
    color="dark"
    bg-color="mygrey1"
    borderless
    class="full-width font12 input-borderless"
    @click="goSearch"
    >
    <template v-slot:prepend>
        <q-icon name="search" size="xs" />
    </template>
    <template v-slot:append>
      <q-icon name="eva-funnel-outline" class="cursor-pointer" @click="dialog=true"  size="xs" />
    </template>
    </q-input>

   <div class="q-mt-md q-mb-md">
    <div class="text-h5 text-weight-medium no-margin line-normal">Near by Restaurants</div>
    <p class="font11 text-grey">200+ Restaurants found near you</p>
   </div>

   <q-list class="qlist-no-padding">
     <q-item  to="/menu"  clickable v-ripple >
       <q-item-section side>
          <q-img
          src="newfood1.jpg"
          lazy
          fit="cover"
          style="height:80px; width:80px;"
          class="rounded-borders"
        />
       </q-item-section>
       <q-item-section>
         <div class="text-h6 text-weight-medium no-margin line-normal">More than Cafe</div>
         <div class="font11 full-width text-grey ellipsis">Chinese, american, italian</div>
         <div class="row q-mt-sm">
           <div class="col-3">
             <q-chip size="sm" color="white" text-color="warning" icon="star" class="no-padding transparent" >
               <span class="text-weight-medium text-dark">4.5</span>
             </q-chip>
           </div>
           <div class="col">
             <q-chip size="sm" color="white" icon="o_delivery_dining" class="no-padding transparent"  >Delivery <span class="q-ml-xs text-weight-medium">$65</span></q-chip>
          </div>
         </div>
       </q-item-section>
     </q-item>
     <q-item  to="/menu"  clickable v-ripple v-for="i in 3" :key="i" >
       <q-item-section side>
          <q-img
          src="newfood2.jpg"
          lazy
          fit="cover"
          style="height:80px; width:80px;"
          class="rounded-borders"
        />
       </q-item-section>
       <q-item-section>
         <div class="text-h6 text-weight-medium no-margin line-normal">Cafe Raybin</div>
         <div class="font11 text-grey ellipsis">Chinese, american, italian</div>
         <div class="row q-mt-sm">
           <div class="col-3">
             <q-chip size="sm" color="white" text-color="warning" icon="star" class="no-padding transparent" >
               <span class="text-weight-medium text-dark">4.5</span>
             </q-chip>
           </div>
           <div class="col">
             <q-chip size="sm" color="white" icon="o_delivery_dining" class="no-padding transparent"  >Delivery <span class="q-ml-xs text-weight-medium">$65</span></q-chip>
          </div>
         </div>
       </q-item-section>
     </q-item>
   </q-list>

  </q-page>
</template>

<script>
export default {
  name: 'NearbyPage',
  data () {
    return {
      slide: 1
    }
  }
}
</script>
