<template>
  <section class="forum-manage">
    <div class="forum-manage-header">
      <h2 class="forum-manage-title">論壇管理</h2>
    </div>

    <!-- 篩選工具列 -->
    <div class="forum-manage-filter">
      <div class="filter-item">
        <select v-model="filters.reportStatus">
          <option value="">全部文章</option>
          <option value="正常">正常</option>
          <option value="遭檢舉">遭檢舉</option>
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
          <div class="col col-status">被檢舉狀態</div>
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
                :class="article.reportStatus === '遭檢舉' ? 'status-badge--error' : 'status-badge--success'"
              >
                {{ article.reportStatus }}
              </span>
            </div>
            <div class="col col-action">
              <button type="button" class="btn-view" @click="viewArticle(article.id)">查看</button>
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

export default {
  name: "ForumManage",

  components: {
    Pagination
  },

  data() {
    return {
      currentPage: 1,
      pageSize: 5,

      categoryOptions: ["公告", "零件搭配", "賽事討論"],

      filters: {
        reportStatus: "",
        sortOrder: "newest",
        category: "",
        keyword: ""
      },

      // 論壇文章假資料
      articles: [
        {
          id: 1,
          category: "零件搭配",
          title: "WX-01爆裂天龍最佳配重環測試報告",
          author: "Blader_X",
          createTime: "2026-05-12 14:30",
          reportStatus: "正常"
        },
        {
          id: 2,
          category: "賽事討論",
          title: "全國大賽初賽心得與戰術分享",
          author: "SpinMaster99",
          createTime: "2026-05-10 09:15",
          reportStatus: "遭檢舉"
        },
        {
          id: 3,
          category: "零件搭配",
          title: "新手推薦：高穩定性防禦型配置",
          author: "NewbieGamer",
          createTime: "2026-05-08 20:40",
          reportStatus: "正常"
        },
        {
          id: 4,
          category: "公告",
          title: "伺服器維護與版本更新說明 v2.4",
          author: "SystemAdmin",
          createTime: "2026-05-05 08:00",
          reportStatus: "正常"
        },
        {
          id: 5,
          category: "賽事討論",
          title: "關於裁判判定爭議的討論串",
          author: "FairPlayer",
          createTime: "2026-05-03 17:22",
          reportStatus: "遭檢舉"
        },
        {
          id: 6,
          category: "公告",
          title: "論壇發文規範與檢舉機制說明",
          author: "SystemAdmin",
          createTime: "2026-04-28 11:10",
          reportStatus: "正常"
        }
      ]
    };
  },

  computed: {
    filteredArticles() {
      const keyword = this.filters.keyword.trim().toLowerCase();

      const result = this.articles.filter(article => {
        const matchStatus =
          !this.filters.reportStatus ||
          article.reportStatus === this.filters.reportStatus;

        const matchCategory =
          !this.filters.category ||
          article.category === this.filters.category;

        const matchKeyword =
          !keyword ||
          article.title.toLowerCase().includes(keyword) ||
          article.author.toLowerCase().includes(keyword);

        return matchStatus && matchCategory && matchKeyword;
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

  watch: {
    filters: {
      handler() {
        this.currentPage = 1;
      },
      deep: true
    }
  },

  methods: {
    viewArticle(articleId) {
      console.log(articleId);
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
  font-weight: 700;
  font-size: map-get($fontSize, h2);
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

.col-action {
  width: 90px;
  flex-shrink: 0;
}

.btn-view {
  padding: 5px 16px;

  border: 1px solid map-get($color, secondary);
  border-radius: 8px;

  background-color: transparent;
  color: map-get($color, secondary);

  font-size: 14px;
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
