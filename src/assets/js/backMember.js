import { createApp } from "vue";

import BackMember from "@/views/backend/backMember.vue";
import backMemberRouter from "@/router/backMemberRouter.js";

// 導入 Element Plus
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";

createApp(BackMember)
  .use(backMemberRouter)
  .use(ElementPlus)
  .mount("#backMemberApp");