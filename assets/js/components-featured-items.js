window.componentsFeaturedItemList = {
    props : ['title'],    
    data() {
        return {
            swiperList : null,
            loading : true,
            data : null
        }
    },
    mounted() {                
        this.getFeaturedItems();
    },
    computed: {
        hasFeaturedItems(){
            if(this.data){
                return true;
            }
            return false;
        }
    },
    methods: {        
        getFeaturedItems(){        
            let currency_code = getCookie('currency_code');
			currency_code = currency_code?currency_code:'';
            const location = getLocation(); 
			const selectedAddress = getFromLocalStorageStore(StoreMapName,"selectedAddress");

            const params_location = new URLSearchParams(location);
            const params = new URLSearchParams({
                currency_code : currency_code,
                language : language,
                cart_uuid : getCookie('cart_uuid'),
                'location':params_location,
				'latitude': selectedAddress?selectedAddress.latitude :'',
				'longitude': selectedAddress?selectedAddress.longitude :'',
            }).toString();   
            axios.get(ajaxurl+"/getFeaturedItems?"+ params ).then(response => {                                
                if(response.data.code==1){       
                    this.data = response.data.details.data;                         
                    setTimeout(() => {
                        this.initSwiper();  
                    }, 1);                     
                }  else {
                    this.data = null;                                        
                }
            })
            .catch(error => {                
                console.error('Error:', error);
            }).then(data => {		
				this.loading = false;
			});         
        },
        initSwiper(){
            this.swiperList = new Swiper( this.$refs.refSwiperList, {
                lazy: true,
                slidesPerView: 3.5,
                spaceBetween: 15,         
                autoHeight: true,                           
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    310: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    320: {
                        slidesPerView: 1,
                        spaceBetween: 0
                      },
                    480: {
                        slidesPerView: 1,
                        spaceBetween: 0
                    },
                    640: {
                       slidesPerView: 2,
                       spaceBetween: 10,
                    },
                    768: {
                       slidesPerView: 3,
                       spaceBetween: 10,
                    },
                    1024: {
                       slidesPerView: 3.5,
                       spaceBetween: 15,
                    },
                },        
            });
        },
    },
    template: '#xtemplate_featured_item_list',
};