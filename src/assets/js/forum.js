import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import Forum from "@/components/forum.vue";
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";

createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(Forum).use(ElementPlus).mount("#forumApp");
