<template>
  <section class="my-appeal">
    <div class="appeal-header">
      <h1>我的申訴</h1>
      <p>追蹤過去你提出的所有申訴</p>
    </div>

    <div class="appeal-filters">
      <div class="filter-row">
        <span class="filter-label">排序：</span>
        <button
          v-for="option in sortOptions"
          :key="option.value"
          type="button"
          class="pill-btn"
          :class="{ active: sortOrder === option.value }"
          @click="sortOrder = option.value"
        >
          {{ option.label }}
        </button>
      </div>

      <div class="filter-row">
        <span class="filter-label">狀態：</span>
        <button
          v-for="option in statusOptions"
          :key="option.value"
          type="button"
          class="pill-btn"
          :class="{ active: statusFilter === option.value }"
          @click="statusFilter = option.value"
        >
          {{ option.label }}
        </button>
      </div>

      <div class="filter-row">
        <span class="filter-label">類型：</span>
        <div class="select-wrap">
          <select v-model="typeFilter">
            <option value="all">全部</option>
            <option v-for="type in typeOptions" :key="type" :value="type">
              {{ type }}
            </option>
          </select>
          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>
    </div>

    <div class="appeal-table-wrap" v-if="filteredAppeals.length">
      <div class="appeal-table">
        <div class="table-row table-head">
          <!-- <p>申訴編號</p> -->
          <p>類型</p>
          <p>狀態</p>
          <p>提交時間</p>
          <p></p>
        </div>

        <div class="table-row" v-for="appeal in filteredAppeals" :key="appeal.id">
          <!-- <p>#{{ appeal.id }}</p> -->
          <p>{{ appeal.type }}</p>
          <p>
            <span class="status-badge" :class="appeal.status">
              {{ statusLabel(appeal.status) }}
            </span>
          </p>
          <p>{{ appeal.createdAt }}</p>
          <RouterLink
            :to="{ name: 'member-appeal-detail', params: { type: appeal.sourceType, id: appeal.id } }"
            class="detail-btn"
          >
            查看詳情
          </RouterLink>
        </div>
      </div>
    </div>

    <div v-else class="appeal-empty">
      <i class="fa-regular fa-flag"></i>
      <p>目前沒有符合條件的申訴紀錄</p>
    </div>
  </section>
</template>

<script>
// import myAppealData from "@/data/myAppealData.js";

export default {
  name: "MyAppeal",

  data() {
    return {
      appealRecords: [], //接收後端傳回當前會員提出的相關申訴

      sortOrder: "desc", //預設由新到舊
      statusFilter: "all", //預設顯示全部狀態
      typeFilter: "all", //預設顯示全部類型

      sortOptions: [
        { value: "desc", label: "由新到舊" },
        { value: "asc", label: "由舊到新" }
      ],
      statusOptions: [
        { value: "all", label: "全部" },
        { value: "pending", label: "待處理" },
        { value: "closed", label: "已結案" }
      ]
    };
  },

  computed: {
    phpBaseUrl() { //判斷目前php的執行環境，調整網址前綴
      return (
        location.hostname === "localhost" ||
        location.hostname === "127.0.0.1"
          ? "http://localhost:8888/Spinix/php"
          : "/ckd101/g2/php"
      );
    },

    typeOptions() {
      //從假資料中動態整理出出現過的申訴類型，供類型篩選下拉使用
      return [...new Set(this.appealRecords.map((item) => item.type))];
    },

    filteredAppeals() {
      const matched = this.appealRecords.filter((item) => {
        const matchStatus = this.statusFilter === "all" || item.status === this.statusFilter;
        const matchType = this.typeFilter === "all" || item.type === this.typeFilter;
        return matchStatus && matchType;
      });

      return [...matched].sort((a, b) => {
        if (this.sortOrder === "asc") {
          return a.createdAt.localeCompare(b.createdAt);
        }
        return b.createdAt.localeCompare(a.createdAt);
      });
    }
  },

  methods: {
    statusLabel(status) {
      return status === "pending" ? "待處理" : "已結案";
    },

    async fetchMyAppeals() { //串接API，取得會員提出的申訴
      try {
        const response = await fetch(`${this.phpBaseUrl}/member/my_appeal_get.php`, {
          credentials: "include"
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(
            data.message || "取得申訴紀錄失敗"
          );
        }

        //將後端取得資料存入appealRecords中
        this.appealRecords = data.appeals.map((appeal) => {
          return {
            id: appeal.APPEAL_ID, //申訴編號
            sourceType: appeal.SOURCE_TYPE, //申訴資料來源
            type: appeal.APPEAL_TYPE, //申訴類型

            status: //申訴狀態 (待處理/已結案)
              appeal.APPEAL_STATUS === "PENDING"
                ? "pending"
                : "closed",

            createdAt: appeal.CREATED_AT, //建立時間
            target: appeal.TARGET_NAME, //申訴對象
            content: appeal.APPEAL_CONTENT //申訴內容
          }
        });

      } catch (error) {
        console.error("取得申訴紀錄失敗：", error);
      }

    }
  },

  mounted() { //載入我的申訴後，先取得當前會員提出的申訴紀錄
    this.fetchMyAppeals();
  }
};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .my-appeal {
    width: 100%;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .appeal-header {
    display: flex;
    flex-direction: column;
    gap: 12px;

    h1 {
      font-size: map.get($fontSize, h1);
      font-weight: 500;
      color: map.get($color, secondary2);
    }

    p {
      font-size: map.get($fontSize, default);
      color: map.get($color, secondary);
    }
  }

  .appeal-filters {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .filter-row {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .filter-label {
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
  }

  .pill-btn {
    padding: 4px 8px;
    border: 0;
    border-radius: 4px;

    background-color: map.get($color, lightGreen);
    color: map.get($color, secondary);
    font-size: map.get($fontSize, hint);
    cursor: pointer;
    transition: all 0.2s ease;

    &.active {
      background-color: map.get($color, olive);
      color: map.get($color, white);
    }
  }

  .select-wrap {
    position: relative;
    width: 200px;

    select {
      width: 100%;
      height: 40px;
      padding: 8px 32px 8px 8px;
      appearance: none;
      border: 1px solid map.get($color, gray);
      border-radius: 6px;
      outline: none;
      background-color: map.get($color, white);
      font-size: map.get($fontSize, hint);
      color: map.get($color, secondary);
      cursor: pointer;

      &:focus {
        border-color: map.get($color, secondary2);
      }
    }

    i {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      font-size: 12px;
      color: map.get($color, secondary);
      pointer-events: none;
    }
  }

  .appeal-table-wrap {
    width: 100%;
    overflow-x: auto;
  }

  .appeal-table {
    width: 100%;
    min-width: 700px;
    border: 1px solid map.get($color, secondary);
    border-radius: 8px;
    overflow: hidden;
  }

  .table-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;

    border-bottom: 1px solid map.get($color, gray);

    &:last-child {
      border-bottom: 0;
    }

    > p {
      flex: 1 0 0;
      min-width: 0;
      font-size: map.get($fontSize, hint);
      color: map.get($color, secondary);
    }
  }

  .table-head {
    background-color: map.get($color, neutral);
    border-bottom: 0;

    > p {
      color: map.get($color, white);
      font-weight: 600;
      font-size: 12px;
    }
  }

  .status-badge {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;

    &.pending {
      background-color: map.get($color, lightGreen);
      color: map.get($color, secondary);
    }

    &.closed {
      background-color: map.get($color, olive);
      color: map.get($color, white);
    }
  }

  .detail-btn {
    flex: 1 0 0;
    min-width: 0;

    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px 12px;
    border-radius: 6px;

    background-color: map.get($color, primary);
    color: map.get($color, secondary);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    text-align: center;
  }

  .appeal-empty {
    width: 100%;
    min-height: 200px;
    padding: 40px 24px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;

    border: 1px dashed rgba(242, 155, 0, 0.35);
    border-radius: 20px;
    background-color: #fffaf2;

    text-align: center;

    i {
      color: map.get($color, secondary2);
      font-size: 34px;
    }

    p {
      color: map.get($color, neutral);
      font-size: map.get($fontSize, default);
    }
  }

  // ====================== RWD調整 ============================

  @media screen and (max-width: 576px) {
    .filter-row {
      flex-wrap: wrap;
      row-gap: 8px;
    }

    .select-wrap {
      width: 160px;
    }
  }
</style>
