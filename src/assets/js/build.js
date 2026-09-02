import { createApp } from "vue";
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import buildApp from "@/components/build.vue";

createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");

const app = createApp(buildApp);
app.use(ElementPlus);
app.mount("#buildApp");