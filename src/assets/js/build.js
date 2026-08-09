import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import buildApp from "@/components/build.vue";



// createApp(Header, { solid: true }).mount("#headerApp");
// createApp(Footer).mount("#footerApp");
createApp(buildApp).mount("#buildApp");