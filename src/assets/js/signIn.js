import "@/assets/scss/style.scss";
import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import signInForm from "@/components/signInForm.vue";

createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(signInForm).mount("#signInApp");
