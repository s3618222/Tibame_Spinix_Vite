<template>
  <section class="complaint-manage">
    <!-- 標題 -->
    <div class="complaint-manage__title">
      <h1>申訴管理</h1>
    </div>

    <!-- 篩選列 -->
    <div class="complaint-filters">
      <div class="filter-item">
        <span class="filter-item__label">狀態：</span>
        <div class="select-wrap">
          <select v-model="statusFilter">
            <option value="all">全部</option>
            <option value="pending">待處理</option>
            <option value="confirmed">成立</option>
            <option value="rejected">不成立</option>
          </select>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>

      <div class="filter-item">
        <span class="filter-item__label">類型：</span>
        <div class="select-wrap">
          <select v-model="typeFilter">
            <option value="all">全部</option>
            <option value="對戰">對戰</option>
            <option value="二手交換">二手交換</option>
            <option value="論壇">論壇</option>
          </select>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>

      <div class="filter-item">
        <span class="filter-item__label">排序：</span>
        <div class="select-wrap">
          <select v-model="sortOrder">
            <option value="desc">由新到舊</option>
            <option value="asc">由舊到新</option>
          </select>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>

      <div class="filter-item filter-item--search">
        <span class="filter-item__label">搜尋</span>
        <input
          v-model="searchText"
          type="text"
          class="filter-search__input"
          placeholder="申訴編號 / 申訴人 / 被申訴對象"
        >
      </div>
    </div>

    <!-- 表格 -->
    <div class="complaint-table">
      <!-- 表頭 -->
      <div class="complaint-table__caption">
        <div class="col">申訴編號</div>
        <div class="col">申訴人</div>
        <div class="col">被申訴對象</div>
        <div class="col">申訴類型</div>
        <div class="col">狀態</div>
        <div class="col">提交時間</div>
        <div class="col">處理人</div>
        <div class="col col--action">操作</div>
      </div>

      <!-- 資料列 -->
      <div
        v-for="row in pagedList"
        :key="`${row.sourceType}-${row.id}`"
        class="complaint-table__row"
      >
        <div class="col col--id">#{{ row.id }}</div>
        <div class="col">{{ row.complainant }}</div>
        <div class="col">{{ row.respondent }}</div>
        <div class="col">{{ row.type }}</div>
        <div class="col">
          <span class="status-badge" :class="statusMeta[row.status].cls">
            {{ statusMeta[row.status].label }}
          </span>
        </div>
        <div class="col col--time">{{ formatDate(row.createdAt) }}</div>
        <div class="col col--handler">{{ row.handler }}</div>
        <div class="col col--action">
          <button
            type="button"
            class="view-btn"
            @click="goDetail(row)"
          >
            查看
          </button>
        </div>
      </div>

      <!-- 空狀態 -->
      <div
        v-if="!pagedList.length"
        class="complaint-table__empty"
      >
        目前沒有符合條件的申訴案件
      </div>

      <!-- 分頁 -->
      <div class="complaint-table__footer">
        <p class="footer-count">顯示：{{ filteredList.length }} 筆</p>
        <Pagination
          v-if="filteredList.length"
          v-model:current-page="currentPage"
          :page-size="pageSize"
          :total="filteredList.length"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
  import { ref, computed, watch } from "vue";
  import { useRouter } from "vue-router";
  import complaintManageData from "@/data/complaintManageData.js";
  import Pagination from "@/components/pagination.vue";

  const router = useRouter();

  // 狀態 value → 顯示標籤 + badge 樣式
  const statusMeta = {
    pending: { label: "待處理", cls: "is-pending" },
    confirmed: { label: "成立", cls: "is-confirmed" },
    rejected: { label: "不成立", cls: "is-rejected" }
  };

  const rows = ref(complaintManageData);

  const statusFilter = ref("all");
  const typeFilter = ref("all");
  const sortOrder = ref("desc");
  const searchText = ref("");

  const currentPage = ref(1);
  const pageSize = 10;

  // 過濾 + 搜尋 + 排序
  const filteredList = computed(() => {
    const keyword = searchText.value.trim().toLowerCase();

    const matched = rows.value.filter((item) => {
      const matchStatus = statusFilter.value === "all" || item.status === statusFilter.value;
      const matchType = typeFilter.value === "all" || item.type === typeFilter.value;
      const matchSearch =
        !keyword ||
        item.id.toLowerCase().includes(keyword) ||
        item.complainant.toLowerCase().includes(keyword) ||
        item.respondent.toLowerCase().includes(keyword);
      return matchStatus && matchType && matchSearch;
    });

    return [...matched].sort((a, b) => {
      if (sortOrder.value === "asc") {
        return a.createdAt.localeCompare(b.createdAt);
      }
      return b.createdAt.localeCompare(a.createdAt);
    });
  });

  // 分頁後的當頁資料
  const pagedList = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredList.value.slice(start, start + pageSize);
  });

  // 篩選條件變動時回到第 1 頁
  watch([statusFilter, typeFilter, sortOrder, searchText], () => {
    currentPage.value = 1;
  });

  function formatDate(value) {
    // 列表只顯示日期
    return value.split(" ")[0];
  }

  function goDetail(row) {
    router.push({
      name: "backend-complaint-detail",
      params: { sourceType: row.sourceType, id: row.id }
    });
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .complaint-manage {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .complaint-manage__title h1 {
    font-size: map-get($fontSize, h1);
    color: map-get($color, secondary2);
  }

  // 篩選列
  .complaint-filters {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }

  .filter-item {
    display: flex;
    align-items: center;

    &__label {
      padding-right: 12px;
      font-size: map-get($fontSize, default);
      font-weight: 500;
      color: map-get($color, secondary);
      white-space: nowrap;
    }

    &--search {
      flex: 1;
      min-width: 220px;
    }
  }

  // 下拉選單外框
  .select-wrap {
    position: relative;
    min-width: 140px;

    select {
      width: 100%;
      padding: 8px 36px 8px 12px;
      appearance: none;

      font-size: map-get($fontSize, default);
      color: map-get($color, secondary);
      background-color: map-get($color, white);
      border: 1px solid #ddd6c8;
      border-radius: 10px;
      cursor: pointer;
    }

    i {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      pointer-events: none;
      color: map-get($color, secondary);
    }
  }

  .filter-search__input {
    width: 100%;
    padding: 9px 12px;

    font-size: map-get($fontSize, default);
    color: map-get($color, secondary);
    background-color: map-get($color, white);
    border: 1px solid map-get($color, hint);
    border-radius: 6px;
    outline: none;

    &::placeholder {
      color: map-get($color, hint);
    }

    &:focus {
      border-color: map-get($color, secondary2);
    }
  }

  // 表格
  .complaint-table {
    width: 100%;

    background-color: map-get($color, white);
    border: 1px solid #cccccc;
    border-radius: 8px;
    overflow: hidden;

    &__caption,
    &__row {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
    }

    &__caption {
      background-color: rgba(254, 201, 107, 0.4);
      padding-block: 20px;
      font-weight: 500;
      color: map-get($color, secondary);
    }

    &__row {
      border-top: 1px solid map-get($color, gray);
    }

    &__empty {
      padding: 48px 20px;
      text-align: center;
      color: map-get($color, hint);
      font-size: map-get($fontSize, default);
      border-top: 1px solid map-get($color, gray);
    }

    &__footer {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      border-top: 1px solid map-get($color, hint);
    }
  }

  .col {
    flex: 1;
    min-width: 0;
    font-size: map-get($fontSize, default);
    color: #262626;

    &--id {
      color: #262626;
    }

    &--time,
    &--handler {
      color: #808080;
    }

    &--action {
      display: flex;
      justify-content: center;
    }
  }

  // 狀態 badge
  .status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 12px;

    font-size: map-get($fontSize, hint);
    border-radius: 100px;
    white-space: nowrap;

    &.is-pending {
      color: map-get($color, brown);
      background-color: map-get($color, pending);
      border: 1px solid rgba(254, 201, 107, 0.75);
    }

    &.is-confirmed {
      color: map-get($color, olive);
      background-color: map-get($color, lightGreen);
      border: 1px solid rgba(79, 138, 91, 0.35);
    }

    &.is-rejected {
      color: map-get($color, error);
      background-color: map-get($color, lightRed);
      border: 1px solid rgba(230, 57, 70, 0.35);
    }
  }

  // 查看按鈕
  .view-btn {
    width: 100%;
    padding: 10px 12px;

    font-size: map-get($fontSize, default);
    font-weight: 500;
    color: map-get($color, neutral);
    background-color: map-get($color, white);
    border: 1px solid map-get($color, neutral);
    border-radius: 6px;
    cursor: pointer;
  }

  .footer-count {
    font-size: map-get($fontSize, default);
    color: #262626;
    white-space: nowrap;
  }

  .complaint-table__footer :deep(.pagination) {
    flex: 1;
    justify-content: flex-end;
  }

  // 窄螢幕：表格橫向捲動，避免欄位擠壓
  @media screen and (max-width: 1024px) {
    .complaint-table {
      overflow-x: auto;
    }

    .complaint-table__caption,
    .complaint-table__row {
      min-width: 900px;
    }

    .complaint-table__footer {
      min-width: 900px;
    }
  }
</style>
