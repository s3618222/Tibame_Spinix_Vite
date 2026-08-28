<template>
  <section class="forum-manage">
    <div class="forum-manage-header">
      <h2 class="forum-manage-title">論壇管理</h2>
    </div>

    <!-- 篩選工具列 -->
    <div class="forum-manage-filter">
      <div class="filter-item">
        <select v-model="filters.showStatus">
          <option value="">全部狀態</option>
          <option value="visible">上架中</option>
          <option value="admin_removed">已下架</option>
          <option value="self_deleted">使用者已刪除</option>
        </select>
      </div>

      <div class="filter-item">
        <select v-model="filters.appealStatus">
          <option value="">全部</option>
          <option value="pending">僅顯示待審申訴</option>
        </select>
      </div>

      <div class="filter-item">
        <select v-model="filters.sortOrder">
          <option value="newest">依發佈時間：最新在前</option>
          <option value="oldest">依發佈時間：最舊在前</option>
        </select>
      </div>

      <div class="filter-item">
        <select v-model="filters.category">
          <option value="">全部分類</option>
          <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
        </select>
      </div>

      <div class="filter-search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input
          type="text"
          v-model="filters.keyword"
          placeholder="搜尋文章標題、關鍵字或作者..."
        >
      </div>
    </div>

    <!-- 資料列表 -->
    <div class="forum-manage-list">
      <div class="forum-table-wrap">
        <div class="forum-row forum-row--head">
          <div class="col col-id">文章ID</div>
          <div class="col col-category">文章分類</div>
          <div class="col col-title">文章標題</div>
          <div class="col col-author">作者</div>
          <div class="col col-time">發布時間</div>
          <div class="col col-status">文章狀態</div>
          <div class="col col-appeal">待審申訴</div>
          <div class="col col-action">操作</div>
        </div>

        <template v-if="paginatedArticles.length">
          <div class="forum-row" v-for="article in paginatedArticles" :key="article.id">
            <div class="col col-id">{{ article.id }}</div>
            <div class="col col-category">
              <span class="chip chip--neutral">{{ article.category }}</span>
            </div>
            <div class="col col-title">{{ article.title }}</div>
            <div class="col col-author">{{ article.author }}</div>
            <div class="col col-time">{{ article.createTime }}</div>
            <div class="col col-status">
              <span
                class="status-badge"
                :class="getArticleStatusClass(article)"
              >
                {{ getArticleStatusText(article) }}
              </span>
            </div>
            <div class="col col-appeal">
              <span v-if="article.has_pending_appeal" class="status-badge status-badge--appeal">
                待審申訴
              </span>
              <span v-else>—</span>
            </div>
            <div class="col col-action">
              <RouterLink
                :to="{ name: 'backend-forum-detail', params: { id: article.id } }"
                class="btn-view"
              >
                查看
              </RouterLink>
            </div>
          </div>
        </template>

        <p v-else class="forum-manage-empty">目前沒有符合條件的文章資料</p>
      </div>

      <div class="forum-manage-list-footer">
        <p>顯示 {{ displayRangeStart }}-{{ displayRangeEnd }} 筆，共 {{ filteredArticles.length }} 筆</p>

        <div class="forum-manage-paginator">
          <Pagination
            v-model:current-page="currentPage"
            :page-size="pageSize"
            :total="filteredArticles.length"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import Pagination from "@/components/pagination.vue";
import { CATEGORY_LABELS } from "@/assets/js/utils/articleCategory.js";

export default {
  name: "ForumManage",

  components: {
    Pagination
  },

  data() {
    return {
      currentPage: 1,
      pageSize: 5,

      categoryOptions: Object.values(CATEGORY_LABELS),

      filters: {
        showStatus: "",
        appealStatus: "",
        sortOrder: "newest",
        category: "",
        keyword: ""
      },

      // 論壇文章假資料
      articles: []
    };
  },

  computed: {
    filteredArticles() {
      const keyword = this.filters.keyword.trim().toLowerCase();

      const result = this.articles.filter(article => {
        const matchShowStatus =
          !this.filters.showStatus ||
          (this.filters.showStatus === "visible"
            ? article.is_show === 1
            : article.is_show === 0 && article.delete_type === this.filters.showStatus);

        const matchAppealStatus =
          !this.filters.appealStatus ||
          (this.filters.appealStatus === "pending" && article.has_pending_appeal === true);

        const matchCategory =
          !this.filters.category ||
          article.category === this.filters.category;

        const matchKeyword =
          !keyword ||
          article.title.toLowerCase().includes(keyword) ||
          article.author.toLowerCase().includes(keyword);

        return matchShowStatus && matchAppealStatus && matchCategory && matchKeyword;
      });

      const sorted = [...result].sort((a, b) => {
        const diff = new Date(b.createTime) - new Date(a.createTime);
        return this.filters.sortOrder === "oldest" ? -diff : diff;
      });

      return sorted;
    },

    paginatedArticles() {
      const start = (this.currentPage - 1) * this.pageSize;
      return this.filteredArticles.slice(start, start + this.pageSize);
    },

    displayRangeStart() {
      if (this.filteredArticles.length === 0) {
        return 0;
      }
      return (this.currentPage - 1) * this.pageSize + 1;
    },

    displayRangeEnd() {
      return Math.min(this.currentPage * this.pageSize, this.filteredArticles.length);
    }
  },

  async created() {
    try {
      const res = await fetch("http://localhost:8888/Spinix/php/forum/getForumManageList.php");
      const result = await res.json();

      if (result.success) {
        this.articles = result.data.map(article => ({
          id: article.art_id,
          category: CATEGORY_LABELS[article.category] || article.category,
          title: article.title,
          author: article.author_name,
          createTime: article.create_time,
          is_show: article.is_show,
          delete_type: article.delete_type,
          has_pending_appeal: Number(article.has_pending_appeal) === 1
        }));
      } else {
        alert(result.message);
      }
    } catch (err) {
      console.error("取得文章列表失敗", err);
      alert("取得文章列表失敗，請稍後再試");
    }
  },

  watch: {
    filters: {
      handler() {
        this.currentPage = 1;
      },
      deep: true
    }
  },

  methods: {
    // 依 is_show / delete_type 轉換成畫面顯示用的文章狀態文字
    getArticleStatusText(article) {
      if (article.is_show === 1) {
        return "上架中";
      }
      if (article.delete_type === "admin_removed") {
        return "已下架";
      }
      if (article.delete_type === "self_deleted") {
        return "使用者已刪除";
      }
      return "";
    },

    // 依 is_show / delete_type 轉換成畫面顯示用的 badge class
    getArticleStatusClass(article) {
      if (article.is_show === 1) {
        return "status-badge--success";
      }
      if (article.delete_type === "admin_removed") {
        return "status-badge--error";
      }
      if (article.delete_type === "self_deleted") {
        return "status-badge--muted";
      }
      return "";
    }
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.forum-manage {
  width: 100%;
}

.forum-manage-title {
  color: map-get($color, secondary2);
  font-weight: 600;
  font-size: map-get($fontSize, h1);
  margin-bottom: 28px;
}

/* 篩選工具列卡片 */
.forum-manage-filter {
  width: 100%;
  padding: 20px;
  margin-bottom: 32px;

  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 16px;

  background-color: map-get($color, white);
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
}

.filter-item select {
  min-width: 160px;
  padding: 8px 30px 8px 12px;

  border: 1px solid map-get($color, warmGray);
  border-radius: 10px;
  outline: none;

  background-color: map-get($color, tertiary);
  color: map-get($color, secondary);
  font-size: 14px;

  transition: border-color 0.24s;

  &:focus {
    border-color: map-get($color, secondary2);
  }
}

.filter-search {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  margin-left: auto;

  border: 1px solid map-get($color, warmGray);
  border-radius: 10px;
  background-color: map-get($color, tertiary);

  i {
    color: map-get($color, neutral);
  }

  input {
    width: 240px;
    border: none;
    outline: none;
    background-color: transparent;
    font-size: 14px;
    color: map-get($color, secondary);

    &::placeholder {
      color: map-get($color, hint);
    }
  }
}

/* 資料列表卡片 */
.forum-manage-list {
  width: 100%;

  border-radius: 16px;
  overflow: hidden;

  background-color: map-get($color, white);
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
}

.forum-table-wrap {
  width: 100%;
  overflow-x: auto;
}

.forum-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 720px;
  padding: 16px 20px;

  &:not(.forum-row--head) {
    border-top: 1px solid map-get($color, warmGray);
    transition: background-color 0.2s;

    &:hover {
      background-color: map-get($color, tertiary);
    }
  }
}

.forum-row--head {
  background-color: rgba(254, 201, 107, 0.4);
  font-weight: 600;
  color: map-get($color, secondary);
}

.col {
  min-width: 0;
  font-size: 14px;
  color: map-get($color, secondary);
  text-align: center;
}

.col-id {
  width: 64px;
  flex-shrink: 0;
}

.col-category {
  width: 100px;
  flex-shrink: 0;
}

// 本頁面內覆蓋 .chip--neutral 的膠囊比例，讓它跟 _mixin.scss 的 .status-badge
// 呈現一致的寬高比例（border-radius: 999px、padding: 4px 12px，數值取自 .status-badge 本體）。
// 僅覆蓋 border-radius 與 padding，其餘（background-color、文字顏色等）維持 _mixin.scss 原樣。
// 因為此區塊是 <style scoped>，Vue 編譯時會自動幫這條選擇器加上本元件專屬的
// data-v-xxxxxxxx attribute selector（例如 .chip--neutral[data-v-xxxxxxxx]），
// 只有這個元件範本渲染出來、帶有相同 data-v 屬性的節點才會命中，其他頁面（如
// ProdDetail.vue／productCard.vue）用到的 .chip--neutral 節點帶的是不同的 data-v
// 屬性（或沒有），完全不會被這條規則選中，因此不需要額外用 !important 或提高選擇器優先度。
.chip--neutral {
  border-radius: 999px;
  padding: 4px 12px;
}

// 本頁面專屬的 status-badge modifier（_mixin.scss 沒有對應語意的既有樣式，
// 故不動 _mixin.scss，改在此 scoped 樣式內新增，尺寸/形狀比照 .status-badge 本體）。

// 灰階：使用者已自行刪除的文章，中性色取自 _var.scss 既有變數
.status-badge--muted {
  border-color: rgba(100, 116, 139, 0.4);
  background-color: map-get($color, gray);
  color: map-get($color, neutral);
}

// 橘黃色：待審申訴提醒，顏色取自 _var.scss 既有變數，刻意與 status-badge--error
// 的紅色區隔，避免跟「已下架」狀態混淆
.status-badge--appeal {
  border-color: rgba(254, 201, 107, 0.75);
  background-color: map-get($color, pending);
  color: map-get($color, brown);
}

.col-title {
  flex: 1;
  min-width: 0;
  text-align: left;

  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.col-author {
  width: 120px;
  flex-shrink: 0;
}

.col-time {
  width: 150px;
  flex-shrink: 0;
}

.col-status {
  width: 100px;
  flex-shrink: 0;
}

.col-appeal {
  width: 100px;
  flex-shrink: 0;
}

.col-action {
  width: 90px;
  flex-shrink: 0;
}

.btn-view {
  display: inline-flex;
  align-items: center;
  justify-content: center;

  padding: 5px 16px;

  border: 1px solid map-get($color, secondary);
  border-radius: 8px;

  background-color: transparent;
  color: map-get($color, secondary);

  font-size: 14px;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;

  &:hover {
    background-color: map-get($color, secondary);
    color: map-get($color, white);
  }
}

.forum-manage-empty {
  padding: 48px 16px;
  text-align: center;
  color: map-get($color, neutral);
}

.forum-manage-list-footer {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 16px;

  padding: 16px 20px;
  border-top: 1px solid map-get($color, warmGray);

  p {
    font-size: 14px;
    color: map-get($color, secondary);
    white-space: nowrap;
  }
}

.forum-manage-paginator {
  display: flex;
  justify-content: flex-end;
}

@media screen and (max-width: 768px) {
  .forum-manage-filter {
    padding: 16px;
  }

  .filter-item select {
    min-width: 140px;
  }

  .filter-search {
    margin-left: 0;
    width: 100%;

    input {
      width: 100%;
    }
  }
}

@media screen and (max-width: 576px) {
  .forum-manage-filter {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-item select {
    width: 100%;
  }

  .forum-manage-list-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .forum-manage-paginator {
    justify-content: center;
  }
}
</style>
