import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import forumForm from "@/components/forumForm.vue";

createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(forumForm).mount("#forumForm");