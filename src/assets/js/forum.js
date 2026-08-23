import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import Forum from "@/components/forum.vue";

createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(Forum).mount("#forumApp");
