<template>
  <div ref="recaptcha_target"></div>
</template>

<script>
export default {
  name: "componentsRecaptcha",
  props: ["sitekey", "size", "theme", "is_enabled", "language_code"],
  data() {
    return {
      recaptcha: null,
    };
  },
  mounted() {
    if (this.is_enabled === "1") {
      this.initCapcha();
    }
  },
  methods: {
    initCapcha() {
      if (window.grecaptcha == null) {
        new Promise((resolve) => {
          window.recaptchaReady = function () {
            resolve();
          };

          const doc = window.document;
          const scriptId = "recaptcha-script";
          const scriptTag = doc.createElement("script");
          scriptTag.id = scriptId;
          scriptTag.setAttribute(
            "src",
            "https://www.google.com/recaptcha/api.js?onload=recaptchaReady&render=explicit&hl=" +
              this.language_code
          );
          doc.head.appendChild(scriptTag);
        }).then(() => {
          this.renderRecaptcha();
        });
      } else {
        this.renderRecaptcha();
      }
    },
    renderRecaptcha() {
      /* eslint-disable */
      this.recaptcha = grecaptcha.render(this.$refs.recaptcha_target, {
        sitekey: this.sitekey,
        theme: this.theme,
        size: this.size,
        tabindex: this.tabindex,
        callback: (response) => this.$emit("verify", response),
        "expired-callback": () => this.$emit("expire"),
        "error-callback": () => this.$emit("fail"),
      });
    },
    reset() {
      /* eslint-disable */
      grecaptcha.reset(this.recaptcha);
    },
  },
};
</script>
