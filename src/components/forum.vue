<template>
  <div class="forum-page">
    <div class="forum-container">
      <div class="title">
        <h1>Spinix 社群</h1>
        <p>與全台陀螺好手交流戰術、分享零件配置與最新賽事心得。</p>
      </div>

      <ForumToolbar/>
      <CategoryTabs :current-tab="currentTab" @change-tab="switchTab"/>
      <ArticleList :article-list="filteredArticles"/>

    </div>
  </div>

</template>

<script>
  import ForumToolbar from "@/components/forum/forumToolbar.vue";
  import CategoryTabs from "@/components/forum/categoryTabs.vue";
  import ArticleList from "@/components/forum/articleList.vue";

  export default {
    name: "ForumView",

    data(){
      return {
        currentTab: "all",  // 預設值
        allArticles: [
          {
            id: 1,
            type: "公告",
            categoryId: "announcement",
            title: "2026 年度 SPINIX 全國大賽規章與獎金細節發佈",
            content: "請所有參賽選手務必在 12/20 前完成設備檢核...",
            imgArticle: "",
            imgWriter: "spinix_member_test1.png",
            name: "官方管理員",
            time: "2 小時前",
            comment: "1258"
          },
          {
            id: 2,
            type: "配裝開箱",
            categoryId: "unboxing",
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
            categoryId: "unboxing",
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
            categoryId: "event",
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
    },

    components: {
      ForumToolbar,
      CategoryTabs,
      ArticleList
    },

    computed: {
      filteredArticles(){
        if(this.currentTab === "all"){
          return this.allArticles;
        }
        return this.allArticles.filter(article => article.categoryId === this.currentTab);
      }
    },

    methods: {
      switchTab(tabId){
        this.currentTab = tabId;
      }
    }
  }


</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/reset' as *;

  .forum-page {
    // background-color: map-get($color, secondary);
    min-height: 100vh;
    padding: 32px 20px 100px;
    background-color: map-get($color, tertiary);

    .forum-container {
      max-width: 1280px;
      margin: 0 auto;
    }

    .title {
      display: flex;
      flex-direction: column;
      gap: 8px;
      margin-bottom: 20px;
      h1 {
        font-size: map-get($fontSize, h1 );
        color: map-get($color , secondary2 );
        font-weight: 600;
      }
    }
    
  }


</style>