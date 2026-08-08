import { createApp } from "vue";

import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
// 分頁器
import Pagination from "@/components/pagination.vue";
import ElementPlus from "element-plus";

createApp(Header).mount("#headerApp");
createApp(Footer).mount("#footerApp");
createApp(Pagination, {
   currentPage: 1,
   pageSize: 20,
   total: 24
}).use(ElementPlus).mount("#paginationApp");

