<template>
  <div class="forum-page">
    <div class="forum-container">
      <div class="title">
        <h1>Spinix 社群</h1>
        <p>與全台陀螺好手交流戰術、分享零件配置與最新賽事心得。</p>
      </div>

      <ForumToolbar :search-keyword="searchKeyword" :sort-by="sortBy" @search="updateSearch" @sort="updateSort"/>
      <CategoryTabs :current-tab="currentTab" @change-tab="switchTab"/>
      <ArticleList :article-list="displayArticles"/>

    </div>
  </div>

</template>

<script>
import ForumToolbar from "@/components/forum/forumToolbar.vue";
import CategoryTabs from "@/components/forum/categoryTabs.vue";
import ArticleList from "@/components/forum/articleList.vue";
import { CATEGORY_LABELS } from '@/assets/js/utils/articleCategory.js';

export default {
  name: "ForumView",

  data(){
    const now = Date.now();
    return {
      currentTab: "all",  // 預設值
      searchKeyword: "",
      sortBy: "latestPost",
      allArticles: []
    }
  },

  created(){
    this.fetchArticles();
  },

  components: {
    ForumToolbar,
    CategoryTabs,
    ArticleList
  },

  computed: {
    filteredArticles() {
      let result = this.allArticles;

      if (this.currentTab !== "all") {
        result = result.filter(article => article.categoryId === this.currentTab);
      }

      const keyword = this.searchKeyword.trim().toLowerCase();
      if (keyword) {
        result = result.filter(article =>
          article.title.toLowerCase().includes(keyword)
        );
      }

      return result;
    },

    displayArticles() {
      const sorted = [...this.filteredArticles];
      if (this.sortBy === "latestComment") {
        sorted.sort((a, b) => new Date(b.lastCommentTime) - new Date(a.lastCommentTime));
      } else {
        sorted.sort((a, b) => new Date(b.createTime) - new Date(a.createTime));
      }
      return sorted;
    }
  },

  methods: {
    async fetchArticles(){
      try{
        const res = await fetch("http://localhost:8888/Spinix/php/forum/getArticles.php");
        const result = await res.json();

        if(result.success){
          this.allArticles = result.data.map(article => ({
            id: article.art_id,
            type: CATEGORY_LABELS[article.category] ?? article.category,
            categoryId: article.category,
            title: article.title,
            content: article.content,
            imgArticle: article.pic ?? "",
            imgWriter: article.author_photo,
            name: article.author_name,
            createTime: article.create_time,
            lastCommentTime: article.last_comment_time ?? article.create_time,
            comment: article.comment_count
          }));
        }

      }catch(error){
        console.error("文章列表載入失敗", error);
      }
    },
    switchTab(tabId){
      this.currentTab = tabId;
    },
    updateSearch(keyword) {
      this.searchKeyword = keyword;
    },
    updateSort(value) {
      this.sortBy = value;
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