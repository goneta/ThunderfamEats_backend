import { route } from "quasar/wrappers";
import {
  createRouter,
  createMemoryHistory,
  createWebHistory,
  createWebHashHistory,
} from "vue-router";
import routes from "./routes";
import auth from "src/api/auth";
import APIinterface from "src/api/APIinterface";

/*
 * If not building with SSR mode, you can
 * directly export the Router instantiation;
 *
 * The function below can be async too; either use
 * async/await or return a Promise which resolves
 * with the Router instance.
 */

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === "history"
    ? createWebHistory
    : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,

    // Leave this as is and make changes in quasar.conf.js instead!
    // quasar.conf.js -> build -> vueRouterMode
    // quasar.conf.js -> build -> publicPath
    //history: createHistory(process.env.MODE === 'ssr' ? void 0 : process.env.VUE_ROUTER_BASE)
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });

  Router.beforeEach((to, from, next) => {
    console.debug(to.meta);
    const intro = APIinterface.getStorage("intro");
    const placeId = APIinterface.getStorage("place_id");
    const placeData = APIinterface.getStorage("place_data");

    if (to.meta.checkAuth) {
      console.debug(intro);
      if (APIinterface.empty(intro)) {
        next();
      } else {
        if (auth.authenticated()) {
          if (APIinterface.empty(placeId)) {
            next({ path: "/location" });
          } else {
            next({ path: "/home" });
          }
        } else {
          //next({ path: "/user/login" });
          next({ path: "/home" });
        }
      }
    } else if (to.meta.checkPlaceID) {
      if (auth.authenticated()) {
        if (APIinterface.empty(placeId) && APIinterface.empty(placeData)) {
          next({ path: "/location" });
        } else {
          next();
        }
      } else {
        if (APIinterface.empty(placeId) && APIinterface.empty(placeData)) {
          next({ path: "/location" });
        } else {
          next();
        }
      }
    } else if (to.meta.checkAuthLogin) {
      if (auth.authenticated()) {
        next({ path: "/home" });
      } else {
        next();
      }
    } else if (!to.meta.requiresAuth || auth.authenticated()) {
      next();
    } else {
      console.log("Not authenticated");
      console.debug(to);
      next({ path: "/user/login", query: { redirect: to.fullPath } });
    }
  });

  return Router;
});
