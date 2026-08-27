<template>
  <div class="my-comment-table" v-if="comments.length > 0">
    <div class="th">
      <p class="col-article">所屬文章</p>
      <p class="col-content">留言內容</p>
      <p class="col-date">留言日期</p>
      <p class="col-status">狀態</p>
      <p class="col-action">操作</p>
    </div>
    <div class="t-body">
      <div class="comment-card" v-for="comment in visibleComments" :key="comment.id">
        <p class="col-article">
          <a :href="`${baseUrl}forumArticle.html?id=${comment.articleId}`" target="_blank">{{ comment.articleTitle }}</a>
        </p>
        <p class="col-content">{{ comment.content }}</p>
        <p class="col-date">{{ comment.date }}</p>
        <hr class="divider">
        <p class="col-status">
          <span :class="['chip', comment.isShow ? 'chip--exchangeable' : 'chip--completed']">
            {{ comment.isShow ? '上架中' : '已下架' }}
          </span>
        </p>
        <div class="col-action">
          <button type="button" class="btn-del" @click="handleDeleteClick(comment.id)" v-if="comment.isShow">刪除</button>
        </div>
      </div>
    </div>
  </div>
  <div class="empty-state" v-else><p>目前沒有留言</p></div>
  <div class="t-footer" v-if="hasMore">
    <LoadMoreButton @click="showMore" class="btn-load">顯示更多留言</LoadMoreButton>
  </div>
  
</template>

<script>
import LoadMoreButton from '@/components/LoadMoreButton.vue';

export default {
  name: "myComments",

  components: {
    LoadMoreButton
  },

  emits: ["delete-message"],

  data() {
    return {
      baseUrl: import.meta.env.BASE_URL,
      visibleCount: 5   // 一開始只顯示 5 筆留言
    };
  },

  props: {
    comments: {
      type: Array,
      default: () => []
    }
  },

  computed: {
    visibleComments() {
      return this.comments.slice(0, this.visibleCount);
    },
    hasMore() {
      return this.visibleCount < this.comments.length;
    }
  },

  methods: {
    handleDeleteClick(commentId) {
      const confirmed = confirm("確定要刪除這則留言嗎？此動作無法在畫面上復原。");
      if (!confirmed) return;

      this.$emit("delete-message", commentId);
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
  &.btn-del {color: map-get($color, error );}
}

.my-comment-table {
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
.comment-card {
  background-color: #fff;
  display: grid;
  grid-template-columns: auto 1fr;
  grid-template-areas:
    "article article"
    "content content"
    "date date"
    "divider divider"
    "status action";
  gap: 12px;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);

  .col-article {
    grid-area: article;
    font-size: 18px;
    text-align: left;
    min-width: 0;
    line-height: 18px;
    min-height: 20px;
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

  .col-content {
    grid-area: content;
    color: map-get($color, secondary2);
    font-size: 14px;
    text-align: left;
    min-width: 0;
    line-height: 18px;
    min-height: 20px;
  }

  .col-date {
    grid-area: date;
    color: map-get($color, neutral);
    font-size: 14px;
    align-self: center;
    text-align: left;
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
  // 1. 表格外框與陰影設定
  .t-body {
    gap: 0;
  }

  .my-comment-table {
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
  .comment-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px;
    border-radius: 0;
    box-shadow: none;
    
    &:not(:last-child){
      border-bottom: 1px solid map-get($color, warmGray);
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
    // padding-block: 16px;
  }

  /* ------------------------------------------------------------------------
     5. 桌機與平板版「欄位寬度」與「字體統一」集中管理
     （這段會同時作用在 .th 與 .comment-card 底下的欄位）
     ------------------------------------------------------------------------ */
  .th,
  .comment-card {
    .col-article,
    .col-content,
    .col-date,
    .col-status,
    .col-action {
      font-size: 16px; // 💡 強制重置平板/桌機版所有欄位字體為 16px
    }

    .col-article {
      flex: 1;
      min-width: 0;
      text-align: left;
    }

    .col-content {
      flex: 1;
      min-width: 0;
      text-align: left;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .col-date {
      width: 110px;
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
