import { api } from "boot/axios";
import { LocalStorage, SessionStorage } from "quasar";
import jwtDecode from "jwt-decode";

const auth = {
  authenticated() {
    if (this.getToken()) {
      return true;
    }
    return false;
  },

  setUser(user) {
    LocalStorage.set("client_identity", user);
  },

  getUser() {
    if (LocalStorage.has("client_identity")) {
      const $data = LocalStorage.getItem("client_identity");
      if (!this.empty($data)) {
        if (Object.keys($data).length > 0) {
          try {
            return jwtDecode($data);
          } catch (err) {
            return false;
          }
        }
      }
    }
    return false;
  },

  setToken(token) {
    LocalStorage.set("client_token", token);
  },

  getToken() {
    if (LocalStorage.has("client_token")) {
      const $data = LocalStorage.getItem("client_token");
      if (!this.empty($data)) {
        return $data;
      }
    }
    return false;
  },

  setStorage(storage_name, data) {
    LocalStorage.set(storage_name, data);
  },

  getStorage(storage_name) {
    if (LocalStorage.has(storage_name)) {
      const $data = LocalStorage.getItem(storage_name);
      if (!this.empty($data)) {
        return $data;
      }
    }
    return false;
  },

  logout() {
    LocalStorage.remove("client_token");
    LocalStorage.remove("client_identity");
    LocalStorage.remove("user_settings");
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

  async authenticate() {
    return api
      .post("/authenticate", "token=" + this.getToken())
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
export default auth;
