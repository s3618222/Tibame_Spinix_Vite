<template>
  <div class="article-list">
    <!-- 卡片區 -->
    <ArticleCard v-for="item in articleList" :key="item.id" :article="item"></ArticleCard>

    <!-- 分頁器 -->
  </div>

</template>

<script>
  import ArticleCard from './articleCard.vue';

  export default {
    name: "ArticleList",

    components: {ArticleCard},

    data(){
      return {
        articleList: [
          {
            id: 1,
            type: "公告",
            title: "2026 年度 SPINIX 全國大賽規章與獎金細節發佈",
            content: "請所有參賽選手務必在 12/20 前完成設備檢核...",
            imgArticle: "",
            imgWriter: "",
            name: "官方管理員",
            time: "2 小時前",
            comment: "1258"
          },
          {
            id: 2,
            type: "配裝開箱",
            title: "【心得】關於 B-201 的配重塊最佳化方案",
            content: "實測了 50 次對戰，發現使用特定軸心可以大幅增加穩定度...",
            imgArticle: "",
            imgWriter: "",
            name: "陀螺大師",
            time: "10 分鐘前",
            comment: "11"
          },
          {
            id: 3,
            type: "配裝開箱",
            title: "極稀有！黃金版天翼戰神開箱實拍分享",
            content: "等了三個月終於從日本寄過來了，這電鍍的質感真的沒話說...",
            imgArticle: "",
            imgWriter: "",
            name: "收藏家 A",
            time: "1 小時前",
            comment: "0"
          },
          {
            id: 4,
            type: "賽事情報",
            title: "台北大賽各組別賽程表公佈（附導航地圖）",
            content: "請各位家長與選手注意，本次報到地點位於二樓多功能大廳...",
            imgArticle: `battle_empty_state.png`,
            imgWriter: "",
            name: "賽事委員555555555555555",
            time: "3 小時前",
            comment: "5"
          },
        ]
      }
    }
  }
</script>

<style lang="scss" scoped>
@use "sass:map";
@use '@/assets/scss/var' as *;

  .article-list {
    display: flex;
    flex-direction: column;
  }
</style>