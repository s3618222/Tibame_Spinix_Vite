//會員中心使用的各區塊切換路由
import {
  createRouter,
  createWebHashHistory
} from "vue-router";

import MyProfile from "@/components/myProfile.vue";
import MyBattle from "@/components/member/battle/myBattle.vue";
import MyForum from "@/components/myForum.vue";
import MyExchange from "@/components/myExchange.vue";
import MyAppeal from "@/components/myAppeal.vue";
import MyAppealDetail from "@/components/myAppealDetail.vue";
import MyViolation from "@/components/myViolation.vue";
import MyViolationDetail from "@/components/myViolationDetail.vue";

const memberRouter = createRouter({
  history: createWebHashHistory(import.meta.env.BASE_URL),

  //後續新建區塊，需在此處增加對應路徑與名稱
  routes: [
    {
      path: "/",
      redirect: {
        name: "member-profile"
      }
      // 進入 member.html 時，預設先導覽至「個人資料」
    },
    {
      path: "/profile",
      name: "member-profile",
      component: MyProfile
    },
    {
      path: "/battle",
      name: "member-battle",
      component: MyBattle
    },
    {
      path: "/forum",
      name: "member-forum",
      component: MyForum
    },
    {
      path: "/exchange",
      name: "member-exchange",
      component: MyExchange
    },
    {
      path: "/appeal",
      name: "member-appeal",
      component: MyAppeal
    },
    {
      path: "/appeal/:type/:id",
      name: "member-appeal-detail",
      component: MyAppealDetail
    },
    {
      path: "/violation",
      name: "member-violation",
      component: MyViolation
    },
    {
      path: "/violation/:id",
      name: "member-violation-detail",
      component: MyViolationDetail
    }
  ]
});

export default memberRouter;