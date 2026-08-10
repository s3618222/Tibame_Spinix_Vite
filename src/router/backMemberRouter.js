//後台管理頁使用的各區塊切換路由
import {
  createRouter,
  createWebHashHistory
} from "vue-router";

import MemberManage from "@/views/backend/memberManage.vue";
import BattleManage from "@/views/backend/battleManage.vue";
import BeybladeManage from "@/views/backend/beybladeManage.vue";
import ForumManage from "@/views/backend/forumManage.vue";
import ExchangeManage from "@/views/backend/exchangeManage.vue";
import ComplaintManage from "@/views/backend/complaintManage.vue";
import AdminAccountManage from "@/views/backend/adminAccountManage.vue";

const backMemberRouter = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: "/",
      redirect: {
        name: "backend-member"
      }
    },
    {
      path: "/member",
      name: "backend-member",
      component: MemberManage
    },
    {
      path: "/battle",
      name: "backend-battle",
      component: BattleManage
    },
    {
      path: "/beyblade",
      name: "backend-beyblade",
      component: BeybladeManage
    },
    {
      path: "/forum",
      name: "backend-forum",
      component: ForumManage
    },
    {
      path: "/exchange",
      name: "backend-exchange",
      component: ExchangeManage
    },
    {
      path: "/complaint",
      name: "backend-complaint",
      component: ComplaintManage
    },
    {
      path: "/admin-account",
      name: "backend-admin-account",
      component: AdminAccountManage
    }
  ]
});

export default backMemberRouter;