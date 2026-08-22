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
      <div class="article-card" v-for="article in articles" :key="article.id">
        <p class="col-type">{{ CATEGORY_LABELS[article.category] ?? article.category }}</p>
        <p class="col-title">
          {{ article.title }}
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
          <button type="button" class="btn-edit">編輯</button>
          <button type="button" class="btn-del">刪除</button>
        </div>
      </div>
    </div>
    <div class="t-footer">
      <button type="button" class="btnNoFill">顯示更多貼文</button>
    </div>
  </div>

</template>

<script>
  import { CATEGORY_LABELS } from '@/assets/js/utils/articleCategory.js';

  export default {
    name: "myArticles",

    data() {
      return {
        CATEGORY_LABELS,
        articles: []
      };
    },

    created() {
      this.fetchMyArticles();
    },

    methods: {
      async fetchMyArticles() {
        try {
          const res = await fetch("http://localhost:8888/Spinix/php/forum/getMyArticles.php", {
            method: "GET",
            credentials: "include"
          });
          const result = await res.json();

          if (result.success) {
            this.articles = result.data.map(article => ({
              id: article.art_id,
              title: article.title,
              category: article.category,
              date: article.create_time.split(" ")[0],
              commentCount: article.comment_count,
              isShow: Number(article.is_show) === 1,
              removeReason: article.remove_reason
            }));
          }
        } catch (error) {
          console.error("我的文章列表載入失敗", error);
        }
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
    padding: 12px;
    border-radius: 0;
    box-shadow: none;
    border-bottom: 1px solid map-get($color, warmGray);

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