<template>
  <section class="battle-manage">
    <!-- 頁面標題 -->
    <div class="battle-manage-header">
      <h2 class="battle-manage-title">約戰管理</h2>
    </div>

    <!-- 篩選區 -->
    <div class="battle-manage-filter">
      <div class="battle-filter-fields">

        <!-- 對戰模式篩選 -->
        <div class="filter-item">
          <label for="battleType">
            <i class="fa-solid fa-trophy"></i>
            對戰模式
          </label>
          <select id="battleType" v-model="filters.mode">
            <option value="">全部模式</option>
            <option v-for="option in modeOptions" 
              :key="option.value" 
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- 縣市篩選 -->
        <div class="filter-item">
          <label for="citySelect">
            <i class="fa-solid fa-location-dot"></i>
            縣市
          </label>

          <select id="citySelect" v-model="filters.city">
            <option value="">全部縣市</option>
            <option
              v-for="city in cityOptions"
              :key="city.value"
              :value="city.value"
            >
              {{ city.label }}
            </option>
          </select>
        </div>

        <!-- 約戰狀態篩選 -->
        <div class="filter-item">
          <label for="battleStatus">
            <i class="fa-solid fa-circle-info"></i>
            約戰狀態
          </label>

          <select id="battleStatus" v-model="filters.status">
            <option value="">全部狀態</option>
            <option
              v-for="option in statusOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- 約戰日期篩選 -->
        <div class="filter-item filter-date">
          <label>
            <i class="fa-solid fa-calendar-days"></i>
            約戰日期
          </label>

          <div class="date-range">
            <input
              type="date"
              id="startDate"
              v-model="filters.startDate"
            >

            <span>至</span>

            <input
              type="date"
              id="endDate"
              v-model="filters.endDate"
            >
          </div>
        </div>
      </div>

      <!-- 重置按鈕 -->
      <button
        type="button"
        class="battle-filter-reset"
        id="resetBtn"
        @click="resetFilters"
      >
        <i class="fa-solid fa-rotate-left"></i>
        重置
      </button>
    </div>

    <!-- 約戰列表區 -->
    <div class="battle-manage-list">
      <div class="battle-table-wrap">
        
        <!-- 平台約戰資訊表單 -->
        <table class="battle-table">
          <!-- 表頭 -->
          <thead>
            <tr>
              <th scope="col">邀約 ID</th>
              <th scope="col">標題</th>
              <th scope="col">發起人</th>
              <th scope="col">地區</th>
              <th scope="col">約戰日期</th>
              <th scope="col">狀態</th>
              <th scope="col">操作</th>
            </tr>
          </thead>

          <!-- 實際約戰資訊內容 -->
          <tbody>
            <!-- 當平台目前完全沒有約戰資料時，顯示的空狀態 -->
            <template v-if="battles.length === 0">
              <tr>
                <td colspan="7" class="battle-table-empty">目前尚無任何約戰資料</td>
              </tr>
            </template>

            <!-- 平台有資料時 -->
            <template v-else>
              <tr v-for="battle in paginatedBattles" :key="battle.battleId">
                <td>{{ battle.battleId }}</td>
                <td>{{ battle.title }}</td>
                <td>{{ battle.hostName }}</td>
                <td>{{ battle.cityLabel }}・{{ battle.district }}</td>
                <td>{{ battle.battleDate }}</td>
                <!-- 約戰狀態欄位；根據不同狀態，顯示對應膠囊樣式 -->
                <td>
                  <span class="battle-status" :class="`battle-status-${battle.status}`">
                    {{ getStatusLabel(battle.status) }}
                  </span>
                </td>
                <td>
                  <button type="button">查看</button>
                </td>
              </tr>

              <!-- 當有約戰資料，但篩選後沒有結果時的空狀態 -->
              <tr v-if="filteredBattles.length === 0">
                <td colspan="7" class="battle-table-empty">
                  目前沒有符合條件的約戰資料
                </td>
              </tr>
            </template>
          </tbody>

        </table>
      </div>

      <!-- 列表底部 -->
      <div class="battle-manage-list-footer">
        <p>共 {{ filteredBattles.length }} 筆</p>

        <!-- 分頁器：放入element的分頁元件 -->
        <div class="battle-manage-paginator">
          <Pagination
            v-model:current-page="currentPage"
            :page-size="pageSize"
            :total="filteredBattles.length"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
import battleManageData from "../../data/battleManageData.js";
import Pagination from "../../components/pagination.vue";

export default {
  name: "BattleManagement",

  components: {
    Pagination
  },

  data() {
    return {
      battles: [], //用來存放約戰紀錄的陣列

      currentPage: 1,
      pageSize: 2,

      filters: {
        mode: "",
        city: "",
        status: "",
        startDate: "",
        endDate: ""
      },

      modeOptions: [ 
        {
          label: "休閒模式",
          value: "casual"
        },
        {
          label: "競技模式",
          value: "competitive"
        }
      ],

      statusOptions: [
        {
          label: "配對中",
          value: "matching"
        },
        {
          label: "待確認",
          value: "pending"
        },
        {
          label: "配對成功",
          value: "success"
        },
        {
          label: "配對失敗",
          value: "failed"
        },
        {
          label: "已下架",
          value: "removed"
        }
      ],

      cityOptions: [
        {
          label: "台北市",
          value: "taipei"
        },
        {
          label: "新北市",
          value: "new-taipei"
        },
        {
          label: "桃園市",
          value: "taoyuan"
        }
      ]

    };
  },

  computed: {
    filteredBattles() { //根據data中的分類選項，篩選對戰資料
      return this.battles.filter((battle) => {
        const matchMode =
          !this.filters.mode ||
          battle.mode === this.filters.mode;

        const matchCity =
          !this.filters.city ||
          battle.city === this.filters.city;

        const matchStatus =
          !this.filters.status ||
          battle.status === this.filters.status;

        const matchStartDate =
          !this.filters.startDate ||
          battle.battleDate >= this.filters.startDate;

        const matchEndDate =
          !this.filters.endDate ||
          battle.battleDate <= this.filters.endDate;

        return (
          matchMode && 
          matchCity && 
          matchStatus && 
          matchStartDate && 
          matchEndDate);
      });
    },

    paginatedBattles() {
      const startIndex = (this.currentPage - 1) * this.pageSize;
      const endIndex = startIndex + this.pageSize;

      return this.filteredBattles.slice(startIndex, endIndex);
    }
  },

  watch: { 
    filters: { //監看filter物件；當篩選條件有變換時，分頁就切換回第一頁，從第一頁顯示
      handler() {
        this.currentPage = 1;
      },
      deep: true //因filters是一個物件，需加入deep，才可以進一步監看到內部各個屬性的變換
    }
  },

  methods: {
    resetFilters() { //重置篩選條件函式
      this.filters.mode = "";
      this.filters.city = "";
      this.filters.status = "";
      this.filters.startDate = "";
      this.filters.endDate = "";
    },

    getBattles() { //複製、取得 battleManageData.js中的約戰假資料陣列
      this.battles = [...battleManageData];
    },

    getStatusLabel(status) { //把表單上的約戰狀態轉換回中文
      const statusItem = this.statusOptions.find(
        option => option.value === status
      );

      return statusItem ? statusItem.label : status;
    }
  },

  mounted() {
    this.getBattles();
  },

};
</script>

<style lang="scss" scoped>
  .battle-manage {
    width: 100%;
  }

  .battle-manage-header,
  .battle-manage-filter,
  .battle-manage-list {
    width: 100%;
  }

  .battle-manage-title {
    margin: 0;
  }

  /* 篩選區外層 */
  .battle-manage-filter {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
  }

  /* 篩選欄位容器 */
  .battle-filter-fields {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 24px;
  }

  /* 單一篩選欄位 */
  .filter-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;

    label {
      display: flex;
      align-items: center;

      font-size: 16px;
      color: #141c26;

      i {
        width: 18px;
        margin-right: 8px;

        text-align: center;
        color: #64748b;
      }
    }

    select {
      min-width: 160px;
      padding: 8px 36px 8px 12px;
    }

    select,
    .date-range input {
      border: 1px solid #ddd6c8;
      border-radius: 10px;
      outline: none;

      background-color: #ffffff;

      font-size: 16px;
      line-height: 1.5;
      color: #141c26;

      &:focus {
        border-color: #f29b00;
      }
    }
  }

  /* 日期範圍 */
  .date-range {
    display: flex;
    align-items: center;
    gap: 12px;

    input {
      width: 160px;
      padding: 8px 12px;
    }
  }

  /* 重置按鈕 */
  .battle-filter-reset {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 8px;

    padding: 8px 12px;

    border: 1px solid #141c26;
    border-radius: 12px;

    background-color: #141c26;
    color: #ffffff;

    font-size: 16px;
    cursor: pointer;
  }

  /* 列表底部 */
  .battle-manage-list-footer {
    width: 100%;

    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .battle-manage-paginator {
    display: flex;
    justify-content: flex-end;
  }

  //約戰資訊列表區
  .battle-table-wrap {
    width: 100%;
    overflow-x: auto;
  }

  .battle-table {
      width: 100%;
      border-collapse: collapse;
    }

  //篩選後，未有符合資料時的空狀態
  .battle-table-empty {
    padding: 48px 16px;

    text-align: center;
    color: #64748b;
  }

  //對戰狀態膠囊共同樣式
  .battle-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    padding: 4px 12px;
    border: 1px solid transparent;
    border-radius: 999px;

    font-size: 14px;
    line-height: 1.4;
    white-space: nowrap;
  }

  // 配對中、待確認
  .battle-status-matching,
  .battle-status-pending {
    border-color: rgba(254, 201, 107, 0.75);
    background-color: #fff2d6;
    color: #a86b00;
  }

  //配對成功
  .battle-status-success {
    border-color: rgba(79, 138, 91, 0.35);
    background-color: #e4eee7;
    color: #4f8a5b;
  }

  //配對失敗
  .battle-status-failed {
    border-color: rgba(230, 57, 70, 0.35);
    background-color: #f9d7da;
    color: #e63946;
  }

  //下架
  .battle-status-removed {
    border-color: rgba(100, 116, 139, 0.4);
    background-color: #eeeeee;
    color: #64748b;
  }
</style>