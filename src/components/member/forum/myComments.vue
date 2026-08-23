<template>
  <div class="my-comment-table">
    <div class="th">
      <p class="col-article">所屬文章</p>
      <p class="col-content">留言內容</p>
      <p class="col-date">留言日期</p>
      <p class="col-status">狀態</p>
      <p class="col-action">操作</p>
    </div>
    <div class="t-body">
      <div class="comment-card" v-for="comment in comments" :key="comment.id">
        <p class="col-article">
          <a :href="`${baseUrl}forumArticle.html?id=${comment.articleId}`" target="_blank">{{ comment.articleTitle }}</a>
        </p>
        <p class="col-content">{{ comment.content }}</p>
        <p class="col-date">{{ comment.date }}</p>
        <p class="col-status">
          <span :class="['chip', comment.isShow ? 'chip--exchangeable' : 'chip--completed']">
            {{ comment.isShow ? '上架中' : '已下架' }}
          </span>
        </p>
        <div class="col-action">
          <button type="button" class="btn-del">刪除</button>
        </div>
      </div>
    </div>
    <div class="t-footer">
      <button type="button" class="btnNoFill">顯示更多留言</button>
    </div>
  </div>

</template>

<script>
  export default {
    name: "myComments",

    data() {
      return {
        baseUrl: import.meta.env.BASE_URL
      };
    },

    props: {
      comments: {
        type: Array,
        default: () => []
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
    "date status"
    "action action";
  gap: 12px;
  padding: 20px;
  border-radius: 12px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);

  .col-article {
    grid-area: article;
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

  .col-content {
    grid-area: content;
    color: map-get($color, secondary2);
    font-size: 14px;
    text-align: left;
  }

  .col-date {
    grid-area: date;
    color: map-get($color, neutral);
    font-size: 14px;
    align-self: center;
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
  // 1. 表格外框與陰影設定
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

  // 3. 重置卡片為表格列 (Flex)
  .comment-card {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    border-radius: 0;
    box-shadow: none;
    border-bottom: 1px solid map-get($color, warmGray);

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
