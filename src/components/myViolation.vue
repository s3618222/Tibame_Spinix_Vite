<template>
  <section class="my-violation">
    <div class="violation-header">
      <h1>違規紀錄</h1>
      <p>依據網站的安全準則，針對違規的會員，給與懲處。</p>
    </div>

    <!-- 篩選區 -->
    <div class="violation-filters">
      <!-- 排序 -->
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

      <!-- 類型 -->
      <div class="filter-row">
        <span class="filter-label">類型：</span>

        <div class="select-wrap">
          <select v-model="typeFilter">
            <option value="all">全部</option>
            <option value="battle">約戰糾紛</option>
            <option value="forum">論壇糾紛</option>
            <option value="exchange">交換糾紛</option>
          </select>

          <i class="fa-solid fa-chevron-down"></i>
        </div>
      </div>

    </div>

    <template v-if="filteredViolations.length">
      <div class="violation-table-wrap">
        <div class="violation-table">
          <div class="table-row table-head">
            <p>類型</p>
            <p>回覆時間</p>
            <p></p>
          </div>

          <div class="table-row" v-for="violation in filteredViolations" :key="`${violation.sourceType}-${violation.id}`">
            <p>{{ violation.type }}</p>
            <p>{{ formatDateTime(violation.respondedAt) }}</p>
            
            <RouterLink
              :to="{ name: 'member-violation-detail', 
              params: { 
                type: violation.sourceType,
                id: violation.id 
                } 
              }"
              class="detail-btn"
            >
              查看詳情
            </RouterLink>
          </div>
        </div>
      </div>

      <div class="violation-notice">
        <p class="notice-title">提醒</p>
        <p class="notice-text">
          為保護申訴雙方隱私，本頁僅顯示經管理員審核成立的違規紀錄與處理說明。原始申訴內容與佐證資料不予公開。
        </p>
      </div>
    </template>

    <div v-else class="violation-empty">
      <i class="fa-solid fa-shield-halved"></i>
      <p v-if="violationRecords.length">
        目前沒有符合篩選條件的違規紀錄
      </p>

      <p v-else>
        目前沒有違規紀錄
      </p>
    </div>
  </section>
</template>

<script>
// import myViolationData from "@/data/myViolationData.js";

export default {
  name: "MyViolation",

  data() {
    return {
      // violationRecords: myViolationData, //違規紀錄假資料
      violationRecords: [], //接收後端回傳目前會員被申訴成立的紀錄

      sortOrder: "desc",
      typeFilter: "all",

      sortOptions: [
        {
          value: "desc",
          label: "由新到舊"
        },
        {
          value: "asc",
          label: "由舊到新"
        }
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

    filteredViolations() {
      let result = [...this.violationRecords];

      // 類型篩選
      if (this.typeFilter !== "all") {
        result = result.filter((violation) => {
          return violation.sourceType === this.typeFilter;
        });
      }

      // 時間排序
      result.sort((a, b) => {
        const timeA = new Date(a.respondedAt).getTime();
        const timeB = new Date(b.respondedAt).getTime();

        if (this.sortOrder === "asc") {
          return timeA - timeB;
        }

        return timeB - timeA;
      });

      return result;
    }
  },

  methods: {
    async fetchViolationRecords() { //串皆取得當前會員申訴成立資料API
      try {
        const response = await fetch(`${this.phpBaseUrl}/member/my_violation_get.php`, {
          credentials: "include"
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
          console.error(data.message);
          return;
        }

        //儲存後端回傳的申訴成立違規紀錄
        this.violationRecords = data.violations;

      } catch (error) {
        console.error("取得違規紀錄失敗：", error);
      }
    },

    formatDateTime(dateTime) {
      if (!dateTime) return "-";

      return new Date(dateTime).toLocaleString("zh-TW", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false
    });
  }
  },

  mounted() {
    this.fetchViolationRecords();
  }
};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .my-violation {
    width: 100%;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .violation-header {
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

  //篩選列表
  .violation-filters {
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

  @media screen and (max-width: 576px) {
    .filter-row {
      flex-wrap: wrap;
      row-gap: 8px;
    }

    .select-wrap {
      width: 160px;
    }
  }

  .violation-table-wrap {
    width: 100%;
    overflow-x: auto;
  }

  .violation-table {
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

  // .punishment-badge {
  //   padding: 4px 8px;
  //   border-radius: 4px;
  //   font-size: 11px;

  //   background-color: map.get($color, lightGreen);
  //   color: map.get($color, secondary);
  // }

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

  .violation-notice {
    width: 100%;
    padding: 16px;
    border: 1px solid map.get($color, neutral);
    border-radius: 8px;
    background-color: map.get($color, white);

    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .notice-title {
    font-size: 13px;
    font-weight: 600;
    color: map.get($color, secondary2);
  }

  .notice-text {
    font-size: 12px;
    color: map.get($color, secondary);
  }

  .violation-empty {
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
</style>
