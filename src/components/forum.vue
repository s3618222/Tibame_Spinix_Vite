<template>
  <div class="forum-page">
    <div class="forum-container">
      <div class="title">
        <h1>Spinix 社群</h1>
        <p>與全台陀螺好手交流戰術、分享零件配置與最新賽事心得。</p>
      </div>

      <ForumToolbar :search-keyword="searchKeyword" :sort-by="sortBy" @search="updateSearch" @sort="updateSort"/>
      <CategoryTabs :current-tab="currentTab" @change-tab="switchTab"/>
      <ArticleList :article-list="articlesToShow"/>
      <div class="pagination-wrap" v-if="!isMobile && displayArticles.length > 0">
        <Pagination v-model:current-page="currentPage" :page-size="6" :total="displayArticles.length" />
      </div>
      <div class="load-more-wrap" v-if="isMobile && hasMoreMobile">
        <LoadMoreButton @click="mobileVisibleCount += 6">顯示更多文章</LoadMoreButton>
      </div>

    </div>
  </div>

</template>

<script>
import ForumToolbar from "@/components/forum/forumToolbar.vue";
import CategoryTabs from "@/components/forum/categoryTabs.vue";
import ArticleList from "@/components/forum/articleList.vue";
import { CATEGORY_LABELS } from '@/assets/js/utils/articleCategory.js';
import Pagination from "@/components/pagination.vue";
import LoadMoreButton from '@/components/LoadMoreButton.vue';
import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl";

export default {
  name: "ForumView",

  data(){
    const now = Date.now();
    return {
      currentTab: "all",  // 預設值
      searchKeyword: "",
      sortBy: "latestPost",
      allArticles: [],
      currentPage: 1,
      isMobile: false,
      mobileVisibleCount: 6
    }
  },

  created(){
    this.fetchArticles();
  },

  mounted(){
    this.checkIsMobile();
    // 只要視窗寬度有改變，就會重新確認當下是否為手機版斷點
    window.addEventListener('resize', this.checkIsMobile);
  },

  beforeUnmount(){
    // 清除瀏覽器記憶體(監聽視窗寬度)
    window.removeEventListener('resize', this.checkIsMobile);
  },

  components: {
    ForumToolbar,
    CategoryTabs,
    ArticleList,
    Pagination,
    LoadMoreButton
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
    },

    paginatedArticles() {
      const start = (this.currentPage - 1) * 6;
      return this.displayArticles.slice(start, start + 6);
    },

    mobileVisibleArticles(){
      return this.displayArticles.slice(0, this.mobileVisibleCount);
    },

    //判斷畫面上所有文章是否都顯示完了，讓「顯示更多」按鈕消失不給使用者按
    hasMoreMobile(){
      return this.mobileVisibleCount < this.displayArticles.length;
    },

    //總開關，依當下是不是手機版，決定要用顯示更多按鈕還是分頁器
    articlesToShow(){
      return this.isMobile ? this.mobileVisibleArticles : this.paginatedArticles;
    }
  },

  watch: {
    //使用者在第 3 頁的時候切換分類 tab，畫面重置回第1頁
    currentTab() { this.currentPage = 1; this.mobileVisibleCount = 6;},
    searchKeyword() { this.currentPage = 1; this.mobileVisibleCount = 6;},
    sortBy() { this.currentPage = 1; this.mobileVisibleCount = 6;},
  }, 

  methods: {
    async fetchArticles(){
      try{
        const res = await fetch(`${phpBaseUrl}/forum/getArticles.php`);
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
    },
    checkIsMobile(){
      this.isMobile = window.innerWidth < 992;
    }
  }
}


</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

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

.pagination-wrap {
  display: flex;
  justify-content: center;
  padding: 20px;
  // margin-top: 12px;
  background-color: map-get($color, white);
  border-radius: 0 0 12px 12px;
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
}

.load-more-wrap{
  display: flex;
  justify-content: center;
}

</style>