//會員中心使用的各區塊切換路由
import {
  createRouter,
  createWebHashHistory
} from "vue-router";

import MyBattle from "@/components/member/battle/myBattle.vue";
import MyForum from "@/components/myForum.vue";
import MyExchange from "@/components/myExchange.vue";

const memberRouter = createRouter({
  history: createWebHashHistory(),

  //後續新建區塊，需在此處增加對應路徑與名稱
  routes: [
    {
      path: "/",
      redirect: "/battle"
      //當前台透過href="./member.html"，進入會員中心時，會預設導覽至我的約戰專區，後續再調整成側邊欄位第一個顯示的區塊連結是什麼
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
    }
  ]
});

export default memberRouter;