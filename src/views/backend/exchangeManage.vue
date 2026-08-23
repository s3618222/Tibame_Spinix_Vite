<template>
<section class="exchange-manage">
   <div class="exchange-manage-header">
      <h1 class="exchange-manage-title">交換專區</h1>
   </div>

   <!-- 篩選工具列 -->
   <div class="exchange-manage-filter">
      <div class="filter-item">
         <label for="">狀態：</label>
         <select v-model="filters.status">
            <option value="">全部</option>
            <option value="exchanging">交換中</option>
            <option value="removed">已下架</option>
            <option value="exchanged">已交換</option>
         </select>
      </div>

      <div class="filter-item">
         <label for="">類型：</label>
         <select v-model="filters.type">
            <option value="">全部</option>
            <option v-for="(label, key) in typeLabelMap" :key="key" :value="key">{{ label }}</option>
         </select>
      </div>

      <div class="filter-item">
         <label for="">排序：</label>
         <select v-model="filters.sortOrder">
            <option value="newest">由新到舊</option>
            <option value="oldest">由舊到新</option>
         </select>
      </div>

      <div class="filter-search">
         <i class="fa-solid fa-magnifying-glass"></i>
         <input
            type="text"
            v-model="filters.keyword"
            placeholder="搜尋"
         >
      </div>
   </div>

   <!-- 資料列表 -->
   <div class="exchange-manage-list">
      <div class="exchange-table-wrap">
         <div class="exchange-row exchange-row--head">
            <div class="col">標題</div>
            <div class="col">發文者</div>
            <div class="col">類型</div>
            <div class="col">狀態</div>
            <div class="col">刊登時間</div>
            <div class="col">操作</div>
         </div>

         <template v-if="paginatedItems.length">
            <div class="exchange-row" v-for="item in paginatedItems" :key="item.post_id">
               <div class="col col-author">{{ item.title }}</div>
               <div class="col col-author">{{ item.name }}</div>
               <div class="col col-type">{{ typeLabelMap[item.type] || item.type }}</div>
               <div class="col col-status">
                  <span
                     class="status-badge"
                     :class="statusClass(item)"
                  >
                     {{ statusLabel(item) }}
                  </span>
               </div>
               <div class="col col-time">{{ item.create_time }}</div>
               <div class="col col-action">
                  <RouterLink
                     :to="{ name: 'product_detail', query: { id: item.post_id, from: 'backend' } }"
                     class="btn-view"
                  >
                     查看
                  </RouterLink>
               </div>
            </div>
         </template>

      <p v-else class="exchange-manage-empty">目前沒有符合條件的案件</p>
      </div>

      <div class="exchange-manage-list-footer">
         <p>顯示 {{ displayRangeStart }}-{{ displayRangeEnd }} 筆，共 {{ filteredItems.length }} 筆</p>

         <div class="exchange-manage-paginator">
            <Pagination
               v-model:current-page="currentPage"
               :page-size="pageSize"
               :total="filteredItems.length"
            />
         </div>
      </div>
   </div>
</section>
</template>

<script>
import Pagination from "@/components/pagination.vue";
import { exchangeList, typeLabelMap, statusLabelMap } from "@/data/mockExchangeData";

export default {
   name: "ExchangeManage",

   components: {
      Pagination
   },

   data() {
      return {
         currentPage: 1,
         pageSize: 10,

         typeLabelMap,
         statusLabelMap,

         filters: {
            status: "",
            type: "",
            sortOrder: "newest",
            keyword: ""
         },

         // 交換專區資料（來自 mockExchangeData）
         items: exchangeList
      };
   },

   computed: {
      filteredItems() {
         const keyword = this.filters.keyword.trim().toLowerCase();

         const result = this.items.filter(item => {
            const matchStatus = !this.filters.status || this.derivedStatusKey(item) === this.filters.status;

         const matchType = !this.filters.type || item.type === this.filters.type;

            const matchKeyword = !keyword || item.name.toLowerCase().includes(keyword) || item.title.toLowerCase().includes(keyword);

         return matchStatus && matchType && matchKeyword;
         });

         const sorted = [...result].sort((a, b) => {
            const diff = new Date(b.create_time) - new Date(a.create_time);
            return this.filters.sortOrder === "oldest" ? -diff : diff;
         });

         return sorted;
      },

      paginatedItems() {
         const start = (this.currentPage - 1) * this.pageSize;
         return this.filteredItems.slice(start, start + this.pageSize);
      },

      displayRangeStart() {
         if (this.filteredItems.length === 0) {
            return 0;
         }
         return (this.currentPage - 1) * this.pageSize + 1;
      },

      displayRangeEnd() {
         return Math.min(this.currentPage * this.pageSize, this.filteredItems.length);
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
    // 把 is_show / is_exchanged / status 整合成後台要顯示的三種狀態之一
    // removed（已下架）> exchanged（已交換）> exchanging（交換中）> 其餘（可交換／待確認）
   derivedStatusKey(item) {
      if (!item.is_show) return "removed";
      if (item.status === 'exchanging' || item.status === "pending") return "exchanging";

      return item.status; // available / pending
   },

   statusLabel(item) {
      const key = this.derivedStatusKey(item);
      const map = {
         removed: "已下架",
         completed: "交換完成",
         exchanging: "交換中"
      };
      return map[key] || this.statusLabelMap[key] || key;
   },

   statusClass(item) {
      const key = this.derivedStatusKey(item);
      switch (key) {
         case "exchanging":
            return "status-badge--pending";
         case "removed":
            return "status-badge--error";
         case "completed":
            return "status-badge--disabled";
         default:
            return "status-badge--success";
      }
   },

    // 跟商品卡 goToDetail() 用同一套規則導到商品詳情頁，
    // from 固定帶 'backend'，讓詳情頁知道是從後台管理進來的
   goToDetail(postId) {
      const params = new URLSearchParams({
         id: postId,
         from: "backend"
      });
      window.location.href = `product_detail?${params.toString()}`;
   }
}
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.exchange-manage {
  width: 100%;
}

.exchange-manage-title {

  font-weight: 600;
  font-size: map-get($fontSize, h1);
  margin-bottom: 28px;
}

/* 篩選工具列卡片 */
.exchange-manage-filter {
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

.filter-item{
   flex-direction: row;
   align-items: center;
}

.filter-item select {
  min-width: 120px;
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
.exchange-manage-list {
  width: 100%;

  border-radius: 16px;
  overflow: hidden;

  background-color: map-get($color, white);
  box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
}

.exchange-table-wrap {
  width: 100%;
  overflow-x: auto;
}

.exchange-row {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 640px;
  padding: 16px 20px;

  &:not(.exchange-row--head) {
    border-top: 1px solid map-get($color, warmGray);
    transition: background-color 0.2s;

    &:hover {
      background-color: map-get($color, tertiary);
    }
  }
}

.exchange-row--head {
  background-color: rgba(254, 201, 107, 0.4);
  font-weight: 600;
  color: map-get($color, secondary);
}

.col {
  min-width: 0;
  font-size: 14px;
  color: map-get($color, secondary);
  text-align: center;
  flex: 1;
  flex-shrink: 0;
}


.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 500;
}

.status-badge--active {
  background-color: rgba(76, 175, 108, 0.15);
  color: #3b9c5a;
}

.status-badge--error {
  background-color: rgba(230, 90, 90, 0.15);
  color: #d84c4c;
}

.status-badge--pending {
  background-color: rgba(254, 201, 107, 0.35);
  color: #b8791a;
}

.status-badge--neutral {
  background-color: rgba(150, 150, 150, 0.15);
  color: #6b6b6b;
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
  font-family: inherit;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s;
  outline: none;
  appearance: none;

  &:hover {
    background-color: map-get($color, secondary);
    color: map-get($color, white);
  }
}

.exchange-manage-empty {
  padding: 48px 16px;
  text-align: center;
  color: map-get($color, neutral);
}

.exchange-manage-list-footer {
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

.exchange-manage-paginator {
  display: flex;
  justify-content: flex-end;
}

@media screen and (max-width: 768px) {
  .exchange-manage-filter {
    padding: 16px;
  }

  .filter-item select {
    min-width: 100px;
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
  .exchange-manage-filter {
    flex-direction: column;
    align-items: stretch;
  }

  .filter-item select {
    width: 100%;
  }

  .exchange-manage-list-footer {
    flex-direction: column;
    align-items: stretch;
  }

  .exchange-manage-paginator {
    justify-content: center;
  }
}
</style>