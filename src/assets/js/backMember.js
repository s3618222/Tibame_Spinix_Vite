import { createApp } from "vue";

import BackMember from "@/views/backend/backMember.vue";
import backMemberRouter from "@/router/backMemberRouter.js";

createApp(BackMember).use(backMemberRouter).mount("#backMemberApp");