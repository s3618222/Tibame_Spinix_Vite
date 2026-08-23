import { createApp } from "vue";
import Member from "@/views/member.vue";
import memberRouter from "@/router/memberRouter.js"; //將建立好的router匯入
// 導入 Element Plus
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";

// const memberApp = createApp(Member);

// memberApp.use(memberRouter);

// memberApp.mount("#memberApp");

createApp(Member)
   .use(memberRouter)
   .use(ElementPlus)
   .mount("#memberApp");