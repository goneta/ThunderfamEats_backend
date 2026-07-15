import { api } from "boot/axios";
import { LocalStorage, SessionStorage } from "quasar";
import auth from "src/api/auth";
import config from "src/api/config";
import { Toast } from "@capacitor/toast";

const APIinterface = {
  setStorage(key, value) {
    try {
      LocalStorage.set(key, value);
    } catch (e) {
      console.debug(e);
    }
  },
  getStorage(key) {
    return LocalStorage.getItem(key);
  },

  setSession(key, value) {
    try {
      SessionStorage.set(key, value);
    } catch (e) {
      console.debug(e);
    }
  },
  getSession(key) {
    return SessionStorage.getItem(key);
  },

  notify(color, message, icon, $q) {
    $q.notify({
      message,
      color,
      icon,
      position: "bottom",
      html: true,
      timeout: 3000,
      multiLine: false,
    });
  },

  async showToast(message) {
    await Toast.show({
      text: message,
      duration: "long",
    });
  },

  notify2(color, textColor, message, icon, position, $q) {
    $q.notify({
      message,
      color,
      textColor,
      icon,
      position,
      html: true,
      timeout: 3000,
      multiLine: false,
      actions: [
        {
          // label: 'Dismiss',
          noCaps: true,
          color: "white",
          handler: () => {
            /* ... */
          },
        },
      ],
    });
  },

  dialog(title, message, $q) {
    $q.dialog({
      title: title,
      message: message,
      transitionShow: "fade",
      transitionHide: "fade",
      ok: {
        unelevated: true,
        color: "primary",
        rounded: false,
        "text-color": "white",
        size: "md",
        label: "Okay",
        "no-caps": true,
      },
    })
      .onOk(() => {
        // console.log('OK')
      })
      .onCancel(() => {
        // console.log('Cancel')
      })
      .onDismiss(() => {
        // console.log('I am triggered on both OK and Cancel')
      });
  },

  showLoadingBox(message, $q) {
    $q.loading.show({
      message,
      boxClass: "bg-white text-dark",
      spinnerColor: "grey-4",
      spinnerSize: "30",
      html: true,
    });
  },

  getLocalID() {
    const errorMessage = { code: 2, message: "Local id not found" };
    const localId = APIinterface.getStorage("local_id");
    if (!APIinterface.empty(localId)) {
      return localId;
    }
    throw errorMessage;
  },

  hideLoadingBox($q) {
    $q.loading.hide();
  },

  empty(data) {
    if (
      typeof data === "undefined" ||
      data === null ||
      data === "" ||
      data === "null" ||
      data === "undefined"
    ) {
      return true;
    }
    return false;
  },

  inArray(needle, haystack) {
    const length = haystack.length;
    for (let i = 0; i < length; i++) {
      if (haystack[i] === needle) return true;
    }
    return false;
  },

  getDateNow() {
    var currentDate = new Date();
    var formatted_date = new Date().toJSON().slice(0, 10).replace(/-/g, "-");
    return formatted_date;
  },

  getIcon(data) {
    let $icons = [];
    $icons["driver"] = {
      text: "\ue52f",
      fontFamily: "Material Icons",
      color: "#ffffff",
      fontSize: "18px",
    };
    $icons["merchant"] = {
      text: "\uea12",
      fontFamily: "Material Icons",
      color: "#ffffff",
      fontSize: "18px",
    };
    $icons["customer"] = {
      text: "\uea44",
      fontFamily: "Material Icons",
      color: "#ffffff",
      fontSize: "18px",
    };
    return $icons[data];
  },

  async CuisineList(rows, q) {
    return api
      .post("/CuisineList", "rows=" + rows + "&q=" + q)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getFeaturedMerchant(featured, placeID) {
    return api
      .post(
        "/getFeaturedMerchant",
        "featured=" + featured + "&place_id=" + placeID
      )
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getMerchantFeed(data) {
    return api
      .post("/getMerchantFeed", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else if (result.data.code === 3) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getMerchantInfo(slug, place_id, currency_code) {
    currency_code = !APIinterface.empty(currency_code) ? currency_code : "";
    if (auth.authenticated()) {
      return api
        .get(
          "/getMerchantInfo2?slug=" +
            slug +
            "&place_id=" +
            place_id +
            "&currency_code=" +
            currency_code,
          {
            headers: {
              "Content-Type": "application/json",
              Authorization: `token ${auth.getToken()}`,
            },
          }
        )
        .then((result) => {
          if (result.data.code === 1) {
            return result.data;
          } else {
            throw result.data.msg;
          }
        })
        .catch((error) => {
          throw error;
        });
    } else {
      return api
        .get("/getMerchantInfo/?slug=" + slug + "&place_id=" + place_id)
        .then((result) => {
          if (result.data.code === 1) {
            return result.data;
          } else {
            throw result.data.msg;
          }
        })
        .catch((error) => {
          throw error;
        });
    }
  },

  async servicesList(data) {
    return api
      .post("/servicesList", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async ItemFeatured(meta, rows) {
    return api
      .post("/itemfeatured", "meta=" + meta + "&rows=" + rows)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async searchAttributes() {
    return api
      .get("/searchAttributes")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async saveTransactionType(data) {
    return api
      .post("/saveTransactionType", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getFeaturedList() {
    return api
      .get("/getFeaturedList")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getBanner(rows) {
    return api
      .get("/getBanner")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getFooter() {
    return api
      .post("/getFooter")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async Category(rows) {
    return api
      .get("/category/?rows=" + rows)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async MenuCategory() {
    return api
      .get("/MenuCategory")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async geStoreMenu(slug, currency_code) {
    currency_code = !APIinterface.empty(currency_code) ? currency_code : "";
    return api
      .post("/geStoreMenu", "slug=" + slug + "&currency_code=" + currency_code)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  // async getMenuItem (catId, itemUuid) {
  //   return api.post('/getMenuItem', 'cat_id=' + catId + '&item_uuid=' + itemUuid)
  //     .then(result => {
  //       if (result.data.code === 1) {
  //         return result.data
  //       } else {
  //         throw result.data.msg
  //       }
  //     })
  //     .catch(error => {
  //       throw error
  //     })
  // },

  async getMenuItem(catId, itemUuid, slug, currency_code) {
    currency_code = !APIinterface.empty(currency_code) ? currency_code : "";
    if (auth.authenticated()) {
      return api
        .post(
          "/getMenuItem2",
          "cat_id=" +
            catId +
            "&item_uuid=" +
            itemUuid +
            "&slug=" +
            slug +
            "&currency_code=" +
            currency_code,
          {
            headers: {
              Authorization: `token ${auth.getToken()}`,
            },
          }
        )
        .then((result) => {
          if (result.data.code === 1) {
            return result.data;
          } else {
            throw result.data.msg;
          }
        })
        .catch((error) => {
          throw error;
        });
    } else {
      return api
        .post(
          "/getMenuItem",
          "cat_id=" + catId + "&item_uuid=" + itemUuid + "&slug=" + slug
        )
        .then((result) => {
          if (result.data.code === 1) {
            return result.data;
          } else {
            throw result.data.msg;
          }
        })
        .catch((error) => {
          throw error;
        });
    }
  },

  async getReview(slug, page) {
    return api
      .post("/getReview", "page=" + page + "&slug=" + slug)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async SimilarItems(cartUuid, rows) {
    return api
      .post("/SimilarItems", "cart_uuid=" + cartUuid + "&rows=" + rows)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async AddToCart(data) {
    return api
      .post("/addCartItems", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getCart(data, ischeckout) {
    console.debug("ischeckout =>" + ischeckout);
    const $method = ischeckout === true ? "getCart" : "getCartCheckout";
    let $headers = {
      "Content-Type": "application/json",
    };
    if ($method === "getCartCheckout") {
      $headers = {
        Authorization: `token ${auth.getToken()}`,
        "Content-Type": "application/json",
      };
    }
    return api
      .post("/" + $method, data, {
        headers: $headers,
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async clearCart(cartUuid) {
    console.debug(cartUuid);
    return api
      .post("/clearCart", "cart_uuid=" + cartUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async removeCartItem(cartUuid, Row) {
    return api
      .post("/removeCartItem", "cart_uuid=" + cartUuid + "&row=" + Row)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async updateCartItems(cartUuid, cartRow, itemQty) {
    return api
      .post(
        "/updateCartItems",
        "cart_uuid=" +
          cartUuid +
          "&cart_row=" +
          cartRow +
          "&item_qty=" +
          itemQty
      )
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getlocationAutocomplete(q) {
    return api
      .post("/getlocationAutocomplete", "q=" + q)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async reverseGeocoding(lat, lng) {
    return api
      .post("/reverseGeocoding", "lat=" + lat + "&lng=" + lng)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getLocationDetails(id) {
    return api
      .post("/getLocationDetails", "place_id=" + id)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getDeliveryDetails(cartUuid) {
    return api
      .post("/getDeliveryDetails", "cart_uuid=" + cartUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },
  async getDeliveryTimes(cartUuid) {
    return api
      .post("/getDeliveryTimes", "cart_uuid=" + cartUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async saveTransactionInfo(data) {
    return api
      .post("/saveTransactionInfo", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async TransactionInfo(data) {
    return api
      .post("/TransactionInfo", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async userLogin(data) {
    return api
      .post("/userLogin", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async addressAtttibues() {
    return api
      .get("/addressAtttibues")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },
  async saveClientAddress(data) {
    return api
      .post("/saveClientAddress", data, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `token ${auth.getToken()}`,
          // Authorization: auth.getToken()
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async clientAddresses(data) {
    return api
      .post("/clientAddresses", data, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async deleteAddress(addressUuid) {
    return api
      .post("/deleteAddress", "address_uuid=" + addressUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async validateCoordinates(data) {
    return api
      .post("/validateCoordinates", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async checkoutAddress(placeid) {
    return api
      .post("/checkoutAddress", "place_id=" + placeid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getPhone(cartUuid) {
    return api
      .post("/getPhone", "cart_uuid=" + cartUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async RequestEmailCode(cartUuid) {
    return api
      .post("/RequestEmailCode", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },
  async verifyCode(code) {
    return api
      .post("/verifyCode", "code=" + code, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async ChangePhone(data) {
    return api
      .post("/ChangePhone", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async loadPromo(cartUuid) {
    return api
      .post("/loadPromo", "cart_uuid=" + cartUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async applyPromo(data) {
    return api
      .post("/applyPromo", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },
  async removePromo(data) {
    return api
      .post("/removePromo", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async applyPromoCode(data) {
    return api
      .post("/applyPromoCode", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async loadTips(cartUuid) {
    return api
      .post("/loadTips", "cart_uuid=" + cartUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async checkoutAddTips(data) {
    return api
      .post("/checkoutAddTips", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async PaymentList(cartUuid) {
    return api
      .post("/PaymentList", "cart_uuid=" + cartUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async SavedPaymentProvider(data) {
    return api
      .post("/SavedPaymentProvider", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async SavedPaymentList(cartUuid) {
    return api
      .post("/SavedPaymentList", "cart_uuid=" + cartUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async setDefaultPayment(paymentUuid) {
    return api
      .post("/setDefaultPayment", "payment_uuid=" + paymentUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async deleteSavedPaymentMethod(data) {
    return api
      .post("/deleteSavedPaymentMethod", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async savedCards(data) {
    return api
      .post("/savedCards", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async StripeCreateCustomer(data) {
    return api
      .post(config.api_payment_url + "/StripeCreateCustomer", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async StripeSavePayment(data) {
    return api
      .post(config.api_payment_url + "/StripeSavePayment", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async StripeCreateIntent(data) {
    return api
      .post(config.api_payment_url + "/StripeCreateIntent", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async StripePaymentIntent(data) {
    return api
      .post(config.api_payment_url + "/StripePaymentIntent", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async PaypalVerifyPayment(data) {
    return api
      .post(config.api_payment_url + "/PaypalVerifyPayment", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async RazorpayCreateCustomer(data) {
    return api
      .post(config.api_payment_url + "/RazorpayCreateCustomer", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async RazorpayCreateOrder(data) {
    return api
      .post(config.api_payment_url + "/RazorpayCreateOrder", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async RazorpayVerifyPayment(data) {
    return api
      .post(config.api_payment_url + "/RazorpayVerifyPayment", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async MercadopagoCustomer(data) {
    return api
      .post(config.api_payment_url + "/MercadopagoCustomer", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async MercadopagoAddcard(data) {
    return api
      .post(config.api_payment_url + "/MercadopagoAddcard", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async MercadopagoGetcard(data) {
    return api
      .post(config.api_payment_url + "/MercadopagoGetcard", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async MercadopagoCapturePayment(data) {
    return api
      .post(config.api_payment_url + "/MercadopagoCapturePayment", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async PlaceOrder(data) {
    return api
      .post("/PlaceOrder", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getOrder(orderUuid) {
    return api
      .post("/getOrder", "order_uuid=" + orderUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getMapsConfig() {
    return api
      .post("/getMapsConfig", "")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async orderHistory(page, q) {
    return api
      .post("/orderHistory", "page=" + page + "&q=" + q, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async orderDetails(orderUuid) {
    return api
      .post("/orderDetails", "order_uuid=" + orderUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async orderBuyAgain(data) {
    return api
      .post("/orderBuyAgain", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async cancelOrderStatus(orderUuid) {
    return api
      .post("/cancelOrderStatus", "order_uuid=" + orderUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async applyCancelOrder(orderUuid) {
    return api
      .post("/applyCancelOrder", "order_uuid=" + orderUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async addReview(data) {
    return api
      .post("/addReview", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getProfile() {
    return api
      .post("/getProfile", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async saveProfile(data) {
    return api
      .post("/saveProfile", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async updatePassword(data) {
    return api
      .post("/updatePassword", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getAddresses() {
    return api
      .post("/getAddresses", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async MyPayments() {
    return api
      .post("/MyPayments", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async deletePayment(paymentUuid) {
    return api
      .post("/deletePayment", "payment_uuid=" + paymentUuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async PaymentMethod() {
    return api
      .post("/PaymentMethod", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async addTofav(itemToken, catId) {
    return api
      .post("/addTofav", "item_token=" + itemToken + "&cat_id=" + catId, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getSaveItems() {
    return api
      .post("/getSaveItems", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async registerUser(data) {
    return api
      .post("/registerUser", data, {
        headers: {
          // Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async requestCode(clientUuid) {
    return api
      .post("/requestCode", "client_uuid=" + clientUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async verifyCodeSignup(data) {
    return api
      .post("/verifyCodeSignup", data, {
        headers: {
          // Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getSignupSettings() {
    return api
      .post("/getSignupSettings", "")
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async socialRegistration(data) {
    return api
      .post("/SocialRegister", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getAccountStatus(clientUuid) {
    return api
      .post("/getAccountStatus", "client_uuid=" + clientUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getCustomerInfo(clientUuid) {
    return api
      .post("/getCustomerInfo", "client_uuid=" + clientUuid)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async completeSocialSignup(data) {
    return api
      .post("/completeSocialSignup", data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async storeAvailable(merchantUUID) {
    return api
      .post("/storeAvailable", "merchant_uuid=" + merchantUUID)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async menuSearch(q, slug) {
    return api
      .post("/menuSearch", "q=" + q + "&slug=" + slug)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getRealtime(data) {
    return api
      .post("/getRealtime", "getevent=" + data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async subscribeNews(emailAddress) {
    return api
      .post("/subscribeNews", "email_address=" + emailAddress)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async SavePlaceByID(placeId) {
    return api
      .post("/SavePlaceByID", "place_id=" + placeId, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async saveStoreList() {
    return api
      .post("/saveStoreList", "", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async SaveStore(merchantID) {
    return api
      .post("/SaveStore", "merchant_id=" + merchantID, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async requestData() {
    return api
      .get("/requestData", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async verifyAccountDelete(code) {
    return api
      .post("/verifyAccountDelete", "code=" + code, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async deleteAccount(code) {
    return api
      .post("/deleteAccount", "code=" + code, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getSettings() {
    return api
      .get("/getSettings", {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async saveSettings(data) {
    return api
      .post("/saveSettings", data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async requestResetPassword(emailAddress) {
    return api
      .post("/requestResetPassword", "email_address=" + emailAddress, {})
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async resendResetEmail(clientUUD) {
    return api
      .post("/resendResetEmail", "client_uuid=" + clientUUD, {})
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async checkStoreOpen(cartUuid) {
    return api
      .post("/checkStoreOpen", "cart_uuid=" + cartUuid, {})
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getMoneyConfig() {
    return api
      .post("/getMoneyConfig", {})
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getMapconfig() {
    return api
      .post("/getMapconfig", {})
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async Search(q, placeID, currency_code) {
    currency_code = !APIinterface.empty(currency_code) ? currency_code : "";
    return api
      .post(
        "/Search",
        "q=" + q + "&place_id=" + placeID + "&currency_code=" + currency_code
      )
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getNotification(page) {
    return api
      .post("/getNotification", "page=" + page, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async getLocationCountries() {
    return api
      .post("/getLocationCountries", {})
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async deleteNotification(uuid) {
    return api
      .post("/deleteNotification", "uuid=" + uuid, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async registerDevice(token, deviceUUID, platform) {
    return api
      .post(
        "/registerDevice",
        "token=" +
          token +
          "&device_uiid=" +
          deviceUUID +
          "&platform=" +
          platform,
        {}
      )
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async updateDevice(token, deviceUUID, platform) {
    return api
      .post(
        "/updateDevice",
        "token=" +
          token +
          "&device_uiid=" +
          deviceUUID +
          "&platform=" +
          platform,
        {
          headers: {
            Authorization: `token ${auth.getToken()}`,
          },
        }
      )
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchData(method, data) {
    return api
      .post("/" + method, data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataPost(method, data) {
    return api
      .post("/" + method, data)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataByBearer(method, data) {
    return api
      .post("/" + method, data, {
        headers: {
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataByToken(method, data) {
    return api
      .post("/" + method, data, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else if (result.data.code === 3) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataByTokenPost(method, data) {
    return api
      .post("/" + method, data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else if (result.data.code === 3) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataByTokenPostPayment(method, data) {
    return api
      .post(config.api_payment_url + "/" + method, data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
          "Content-Type": "application/json",
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataPostTable(method, data) {
    return api
      .post(config.api_reservation_url + "/" + method, data)
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },

  async fetchDataPostTable2(method, data) {
    return api
      .post(config.api_reservation_url + "/" + method, data, {
        headers: {
          Authorization: `token ${auth.getToken()}`,
        },
      })
      .then((result) => {
        if (result.data.code === 1) {
          return result.data;
        } else {
          throw result.data.msg;
        }
      })
      .catch((error) => {
        throw error;
      });
  },
};
export default APIinterface;
