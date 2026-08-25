<template>
  <div class="my-article-table">
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
        <p class="col-status">
          <span :class="['chip', article.isShow ? 'chip--exchangeable' : 'chip--completed']">
            {{ article.isShow ? '上架中' : '已下架' }}
          </span>
        </p>
        <div class="col-action">
          <button type="button" class="btn-edit" v-if="article.isShow">編輯</button>
          <button type="button" class="btn-del" @click="handleDeleteClick(article.id)" v-if="article.isShow">刪除</button>
        </div>
      </div>
    </div>
    <div class="t-footer" v-if="hasMore">
      <LoadMoreButton @click="showMore">顯示更多貼文</LoadMoreButton>
    </div>
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

button {
  font-size: 16px;
  &.btn-edit {color: map-get($color, secondary2 );}
  &.btn-del {color: map-get($color, error );}
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
    "status status"
    "action action";
  gap: 12px;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);

  .col-type {
    grid-area: type;
    color: map-get($color, secondary2);
    font-weight: 600;
    font-size: 14px;
    align-self: center;
  }

  .col-title {
    grid-area: title;
    font-size: 18px;
    text-align: left;
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

  .col-status {
    grid-area: status;
  }

  .col-action {
    grid-area: action;
    display: flex;
    gap: 8px;
    border-top: 1px solid map-get($color, warmGray);
    padding-top: 12px; // 按鈕靠左預設生效，並推開頂部線條
    justify-content: end;
  }
}

.t-footer {
  margin-top: 32px;
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
    margin-top: 0;
    background-color: #fff;
    padding-block: 16px;
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