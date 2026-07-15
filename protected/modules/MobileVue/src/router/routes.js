const routes = [
  {
    path: "/",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("pages/Intro/ScreenPage.vue"),
        meta: { checkAuth: true },
      },
      {
        path: "select-language",
        component: () => import("pages/Intro/SelectLanguage.vue"),
      },
    ],
  },
  {
    path: "/location",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      { path: "", component: () => import("pages/Location/LocationPage.vue") },
      {
        path: "map",
        name: "map",
        props: true,
        component: () => import("pages/Location/MapPage.vue"),
      },
    ],
  },
  {
    path: "/feed",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "",
        name: "feed",
        props: true,
        component: () => import("src/pages/Feed/FeedPage.vue"),
      },
    ],
  },
  {
    path: "/home",
    component: () => import("layouts/MainLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("src/pages/Home/HomePage.vue"),
        meta: { checkPlaceID: true },
      },
      {
        path: "browse",
        name: "browse",
        props: true,
        component: () => import("src/pages/Home/BrowsePage.vue"),
      },
      // { path: 'feed', name: 'feed', component: () => import('src/pages/Home/FeedPage.vue') },
      // { path: 'feed', name: 'feed', props: true, component: () => import('src/pages/Home/FeedPage.vue') },
      // { path: 'nearby', component: () => import('src/pages/Home/NearbyPage.vue') }
    ],
  },
  {
    path: "/view",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      { path: "", component: () => import("src/pages/Feed/FeedPage.vue") },
      {
        path: "categories",
        component: () => import("src/pages/Category/CategoryPage.vue"),
      },
      {
        path: "quick-results",
        component: () => import("src/pages/Search/QuickSearchResultsPage.vue"),
      },
    ],
  },
  {
    path: "/search",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      { path: "", component: () => import("pages/Search/SearchPage.vue") },
      {
        path: "items",
        name: "items",
        props: true,
        component: () => import("pages/Search/SearchItems.vue"),
      },

      // {
      //   path: "results",
      //   name: "quicksearch",
      //   props: true,
      //   component: () => import("pages/Search/QuickSearch.vue"),
      // },
    ],
  },
  {
    path: "/menu",
    component: () => import("layouts/NotopfooterLayout2.vue"),
    children: [
      // { path: '', name: 'menu', props: true, component: () => import('pages/MenuPage.vue') },
      {
        path: "/:slug",
        name: "menu",
        props: true,
        component: () => import("src/pages/Menu/MenuPage.vue"),
      },
      // { path: 'search', component: () => import('src/pages/Menu/MenuSearchPage.vue') },
      {
        path: "/search-menu/:slug",
        name: "menu_search",
        props: true,
        component: () => import("src/pages/Menu/MenuSearchPage.vue"),
      },
      // {
      //   path: "info",
      //   name: "info",
      //   component: () => import("pages/Menu/StoreInfo.vue"),
      // },
      // { path: 'product', component: () => import('pages/ProductPage.vue') }
      // { path: 'review', component: () => import('pages/ReviewPage.vue') }
    ],
  },
  {
    path: "/store",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "info",
        name: "info",
        props: true,
        component: () => import("pages/Menu/StoreInfo.vue"),
      },
      {
        path: "review",
        name: "storereview",
        props: true,
        component: () => import("pages/Menu/StoreReview.vue"),
      },
      {
        path: "booking",
        name: "booking",
        props: true,
        component: () => import("pages/Booking/BookingPage.vue"),
      },
    ],
  },
  {
    path: "/cart",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      { path: "", component: () => import("src/pages/Cart/CartPage.vue") },
      // { path: 'add-promotions', component: () => import('pages/PromotionPage.vue') },
      // { path: 'checkout', component: () => import('src/pages/Cart/CheckoutPage.vue') },
      // { path: 'delivery-options', component: () => import('pages/DeliveryOptionPage.vue') },
      // { path: 'delivery-time', component: () => import('pages/DeliveryTimePage.vue') },
      // { path: 'change-phone', component: () => import('pages/ChangePhonePage.vue') }
    ],
  },
  {
    path: "/checkout",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("src/pages/Cart/CheckoutPage.vue"),
        meta: { requiresAuth: true },
      },
      // { path: 'add-promotions', component: () => import('pages/PromotionPage.vue'), meta: { requiresAuth: false } },
      // { path: 'delivery-time', component: () => import('pages/DeliveryTimePage.vue') },
      // { path: 'change-phone', component: () => import('pages/ChangePhonePage.vue') }
    ],
  },
  {
    path: "/address",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      // { path: 'select', component: () => import('src/pages/Address/AddressSelect.vue') }
      {
        path: "select",
        name: "address",
        props: true,
        component: () => import("src/pages/Address/AddressSelect.vue"),
      },
    ],
  },

  {
    path: "/account-menu",
    component: () => import("layouts/NotopnavLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("src/pages/Account/AccountMenu.vue"),
        meta: { requiresAuth: true },
      },
    ],
  },

  {
    path: "/legal",
    component: () => import("layouts/NotopnavLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("src/pages/Legal/LegalMenu.vue"),
        meta: { requiresAuth: true },
      },
      {
        name: "page",
        path: "page/:page_id",
        props: true,
        component: () => import("src/pages/Legal/PageRender.vue"),
      },
    ],
  },

  {
    path: "/user",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "login",
        component: () => import("src/pages/User/LoginPage.vue"),
        meta: { checkAuthLogin: true },
      },
      // {
      //   path: "login-email",
      //   component: () => import("src/pages/User/LoginWithEmail.vue"),
      //   meta: { checkAuthLogin: true },
      // },
      {
        path: "login-phone",
        component: () => import("src/pages/User/LoginWithPhone.vue"),
        meta: { checkAuthLogin: true },
      },
      {
        path: "signup",
        component: () => import("src/pages/User/SignupPage.vue"),
      },
      {
        path: "forgotpass",
        component: () => import("src/pages/User/ForgotPassword.vue"),
      },
      {
        path: "verification",
        component: () => import("src/pages/User/VerificationPage.vue"),
      },
      {
        path: "guest",
        component: () => import("src/pages/User/GuestInformation.vue"),
      },
    ],
  },

  {
    path: "/account",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "allorder",
        component: () => import("src/pages/Order/OrderList.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "verify",
        component: () => import("pages/Account/VerifyAccount.vue"),
      },
      {
        path: "complete-registration",
        component: () => import("pages/Account/CompleteRegistration.vue"),
      },
      {
        path: "profile",
        component: () => import("src/pages/Account/ProfilePage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "edit-profile",
        component: () => import("src/pages/Account/EditProfile.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "change-password",
        component: () => import("src/pages/Account/ChangePassword.vue"),
        meta: { requiresAuth: true },
      },
      // { path: 'payment', component: () => import('pages/PaymentSelect.vue') },
      // { path: 'add-credit-card', component: () => import('pages/CreditCard.vue') },
      {
        path: "trackorder",
        component: () => import("src/pages/Account/TrackOrder.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "manage-account",
        component: () => import("src/pages/Account/ManageAccount.vue"),
        meta: { requiresAuth: true },
      },
      // { path: 'my-orders', component: () => import('pages/MyOrders.vue'), meta: { requiresAuth: true } },
      {
        path: "my-address",
        component: () => import("src/pages/Account/MyAddress.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "address",
        component: () => import("src/pages/Account/AddressPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "payments",
        component: () => import("src/pages/Account/MyPayment.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "payments/new",
        component: () => import("src/pages/Account/PaymentNew.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "favourites",
        component: () => import("src/pages/Account/FavouritesPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "order-details",
        component: () => import("src/pages/Account/OrderDetails.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "settings",
        component: () => import("pages/Account/SettingsPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "notifications",
        component: () => import("pages/Account/NotificationsPage.vue"),
      },
      {
        path: "language",
        component: () => import("pages/Account/LanguagePage.vue"),
      },
      {
        path: "currency",
        component: () => import("pages/Account/CurrencyPage.vue"),
      },
      {
        path: "delete",
        component: () => import("pages/Account/DeleteAccount.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "points",
        component: () => import("pages/Account/PointsPage.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "upload-deposit",
        component: () => import("pages/Account/UploadDeposit.vue"),
        meta: { requiresAuth: true },
      },
    ],
  },

  {
    path: "/order",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "/order/write-review/:order_uuid/:back_url",
        name: "write-review",
        props: true,
        component: () => import("src/pages/Order/WriteReview.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "successful",
        name: "order-successful",
        props: true,
        component: () => import("src/pages/Order/OrderSuccess.vue"),
        meta: { requiresAuth: true },
      },
    ],
  },

  {
    path: "/orders",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("src/pages/Order/OrderList.vue"),
        meta: { requiresAuth: true },
      },
    ],
  },

  {
    path: "/booking",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("src/pages/Booking/BookingList.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "track",
        component: () => import("src/pages/Booking/TrackBooking.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "cancel",
        component: () => import("src/pages/Booking/BookingCancel.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "update",
        component: () => import("src/pages/Booking/BookingUpdate.vue"),
        meta: { requiresAuth: true },
      },
      {
        path: "search",
        component: () => import("src/pages/Booking/BookingSearch.vue"),
        meta: { requiresAuth: true },
      },
    ],
  },

  {
    path: "/errornetwork",
    component: () => import("layouts/NotopfooterLayout.vue"),
    children: [
      {
        path: "",
        component: () => import("pages/NetworkError.vue"),
      },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: "/:catchAll(.*)*",
    component: () => import("pages/ErrorNotFound.vue"),
  },
];

export default routes;
