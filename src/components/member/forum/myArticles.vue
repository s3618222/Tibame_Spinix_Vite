<template>
  <div class="my-article-table" v-if="articles.length > 0">
    <div class="th">
      <p class="col-type">類別</p>
      <p class="col-title">貼文標題</p>
      <p class="col-date">發文日期</p>
      <p class="col-comments">留言數</p>
      <p class="col-status">狀態</p>
      <p class="col-action">操作</p>
    </div>
    <div class="t-body">
      <div class="article-card" v-for="article in visibleArticles" :key="article.id">
        <p class="col-type">{{ CATEGORY_LABELS[article.category] ?? article.category }}</p>
        <p class="col-title">
          <a :href="`${baseUrl}forumArticle.html?id=${article.id}`" target="_blank">{{ article.title }}</a>
        </p>
        <p class="col-date">{{ article.date }}</p>
        <p class="col-comments">
          <i class="fa-regular fa-message"></i>
          <span>{{ article.commentCount }}</span>
        </p>
        <hr class="divider">
        <p class="col-status">
          <span :class="['chip', article.isShow ? 'chip--exchangeable' : 'chip--completed']">
            {{ article.isShow ? '上架中' : '已下架' }}
          </span>
        </p>
        <div class="col-action">
          <a type="button" class="btn-edit btn-action" v-if="article.isShow" 
          :href="`${baseUrl}forumForm.html?id=${article.id}`"
          >編輯</a>
          <button type="button" class="btn-del btn-action" @click="handleDeleteClick(article.id)" v-if="article.isShow">刪除</button>
        </div>
      </div>
    </div>
    
  </div>
  <div class="empty-state" v-else><p>目前沒有發文</p></div>
  <div class="t-footer" v-if="hasMore">
    <LoadMoreButton @click="showMore">顯示更多貼文</LoadMoreButton>
  </div>

</template>

<script>
import { CATEGORY_LABELS } from '@/assets/js/utils/articleCategory.js';
import LoadMoreButton from '@/components/LoadMoreButton.vue';

export default {
  name: "myArticles",

  components: {
    LoadMoreButton
  },

  emits: ["delete-article"],

  data() {
    return {
      CATEGORY_LABELS,
      baseUrl: import.meta.env.BASE_URL,
      visibleCount: 5   // 一開始只顯示 5 筆貼文
    };
  },

  props: {
    articles: {
      type: Array,
      default: () => []
    }
  },

  computed: {
    visibleArticles() {
      return this.articles.slice(0, this.visibleCount);
    },
    hasMore() {
      // 判斷還有沒有更多貼文可以顯示
      return this.visibleCount < this.articles.length;
    }
  },

  methods: {
    handleDeleteClick(articleId) {
      const confirmed = confirm("確定要刪除這篇文章嗎？此動作無法在畫面上復原。");
      if (!confirmed) return;

      this.$emit("delete-article", articleId);
    },
    showMore() {
      this.visibleCount += 5;
    }
  }
}
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/mixin' as *;

.btn-action {
  font-size: 16px;
  line-height: 16px;
  font-weight: 600;
  
  &.btn-edit {
    color: map-get($color, secondary2 );
    &:hover{
      color: lighten(map-get($color, secondary2 ), 10%);
    }
  }
  &.btn-del {
    color: map-get($color, error );
    &:hover{
        color: lighten(map-get($color, error ), 10%);
    }
  }
}

.my-article-table {
  width: 100%;
  text-align: center;
}

/* ==========================================================================
   一、預設：手機版樣式 (Mobile - Grid 卡片流)
   ========================================================================== */

// 手機版隱藏表頭
.th {
  display: none;
}

.t-body {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

// 卡片主體 (Grid 布局)
.article-card {
  background-color: #fff;
  display: grid;
  grid-template-columns: auto 1fr;
  grid-template-areas:
    "type title"
    "date comments"
    "divider divider"
    "status action";
  gap: 12px;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);

  .col-type {
    grid-area: type;
    line-height: 18px;
    min-height: 20px;
    color: map-get($color, secondary2);
    font-weight: 600;
    font-size: 14px;
    align-self: center;
  }

  .col-title {
    grid-area: title;
    font-size: 18px;
    min-height: 20px;
    line-height: 18px;
    text-align: left;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;

    a {
      color: inherit;
      text-decoration: none;

      &:hover {
        // text-decoration: underline;
        color: map-get($color, neutral);
      }
    }
  }

  .col-date {
    grid-area: date;
    color: map-get($color, neutral);
    font-size: 14px;
    text-align: left;
  }

  .col-comments {
    grid-area: comments;
    justify-self: start;
    color: map-get($color, neutral);
    font-size: 14px;

    span {
      margin-left: 4px;
    }
  }

  .divider {
    grid-area: divider;
    border: none;
    border-top: 1px solid map-get($color, warmGray);
    width: 100%;
    margin: 0;
  }

  .col-status {
    grid-area: status;
    justify-self: start;
  }

  .col-action {
    grid-area: action;
    display: flex;
    gap: 8px;
    justify-content: end;
  }
}
.empty-state {
  background-color: #fff;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
  padding: 40px 20px;
  text-align: center;
  color: map-get($color, neutral);
  font-size: 16px;
}
.t-footer {
  margin-top: 32px;
  display: flex;
  justify-content: center;
}


/* ==========================================================================
   二、平板 / 電腦版樣式 (Tablet & Desktop - Flex 表格流)
   ========================================================================== */
@include rwd("tablet") {
  .t-body {
    gap: 0;
  }

  // 1. 表格外框與陰影設定
  .my-article-table {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
  }

  // 2. 顯示表頭 (Flex)
  .th {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: map-get($color, pending);
    color: map-get($color, black);
    padding: 12px;
    font-weight: 600;
  }

  // 桌機版不需要手機版專用的分隔線
  .divider {
    display: none;
  }

  // 3. 重置卡片為表格列 (Flex)
  .article-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px;
    border-radius: 0;
    box-shadow: none;
    
    &:not(:last-child){
      border-bottom: 1px solid map-get($color, warmGray);
    }

    // 隱藏留言圖示
    .col-comments i {
      display: none;
    }

    // 重置操作欄位的邊框與內距，按鈕改為置中
    .col-action {
      border-top: none;
      padding-top: 0;
      justify-content: center;
    }
  }

  // 4. 重置頁尾樣式
  .t-footer {
    margin: 20px ;
  }

  /* ------------------------------------------------------------------------
     5. 桌機與平板版「欄位寬度」與「字體統一」集中管理
     （這段會同時作用在 .th 與 .article-card 底下的欄位）
     ------------------------------------------------------------------------ */
  .th,
  .article-card {
    .col-type,
    .col-title,
    .col-date,
    .col-comments,
    .col-status,
    .col-action {
      font-size: 16px; // 💡 強制重置平板/桌機版所有欄位字體為 16px
    }

    .col-type {
      min-width: 80px;
      flex-shrink: 0;
    }

    .col-title {
      flex: 1;
      min-width: 0;
      text-align: left;
    }

    .col-date {
      width: 110px;
      flex-shrink: 0;
    }

    .col-comments {
      min-width: 50px;
      flex-shrink: 0;
    }

    .col-status {
      min-width: 90px;
      flex-shrink: 0;
    }

    .col-action {
      min-width: 120px;
      flex-shrink: 0;
    }
  }
}
</style>