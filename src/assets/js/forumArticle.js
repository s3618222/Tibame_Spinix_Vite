import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
import ForumArticle from "@/components/forumArticle.vue";

createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(ForumArticle).mount("#forumArticleApp");
