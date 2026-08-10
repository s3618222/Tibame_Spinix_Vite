<template>
  <div class="forum-page">
    <div class="forum-container">
      <div class="title">
        <h1>Spinix 社群</h1>
        <p>與全台陀螺好手交流戰術、分享零件配置與最新賽事心得。</p>
      </div>

      <ForumToolbar/>
      <CategoryTabs :current-tab="currentTab" @change-tab="switchTab"/>
      <ArticleList/>

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
        currentTab: "all"  // 預設值
      }
    },

    components: {
      ForumToolbar,
      CategoryTabs,
      ArticleList
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