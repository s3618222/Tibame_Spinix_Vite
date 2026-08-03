import { createApp } from "vue";
import Member from "@/views/member.vue";
import memberRouter from "@/router/memberRouter.js"; //將建立好的router匯入

const memberApp = createApp(Member);

memberApp.use(memberRouter);

memberApp.mount("#memberApp");