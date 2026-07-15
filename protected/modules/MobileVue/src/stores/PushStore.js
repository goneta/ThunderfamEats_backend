import { defineStore } from "pinia";

export const usePushStore = defineStore("push_store", {
  state: () => ({
    push_modal: false,
    push_message: [],
    counter: 0,
    timer: undefined,
  }),

  getters: {},

  actions: {
    ShowModal(data) {
      this.push_modal = data;
      if (data) {
        this.startTimer();
      }
    },
    stopTimer() {
      clearInterval(this.timer);
    },
    startTimer() {
      this.stopTimer();
      this.counter = 10;
      this.timer = setInterval(() => {
        this.counter--;
        if (this.counter < 0) {
          this.push_modal = false;
          this.stopTimer();
        }
      }, 1000);
    },
  },
});
