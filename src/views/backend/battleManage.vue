<template>
  <section class="battle-manage">
    <!-- 頁面標題 -->
    <div class="battle-manage-header">
      <h2 class="battle-manage-title">約戰管理</h2>
    </div>

    <!-- 篩選區 -->
    <div class="battle-manage-filter">
      <!-- 第一列：對戰模式、縣市、狀態 -->
      <div class="battle-filter-main">

        <!-- 對戰模式篩選 -->
        <div class="filter-item">
          <label for="battleType">
            <i class="fa-solid fa-trophy"></i>
            對戰模式
          </label>
          <select id="battleType" v-model="filters.mode">
            <option value="">全部模式</option>
            <option
              v-for="option in modeOptions"
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

      </div>

      <!-- 第二列：約戰日期與重置按鈕 -->
      <div class="battle-filter-bottom">

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
                  <span class="battle-status" :class="getDisplayStatusClass(battle)">
                    {{ getDisplayStatusLabel(battle) }}
                  </span>
                </td>
                <td>
                  <button type="button" @click="openBattleDetail(battle)">查看</button>
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

    <!-- 約戰詳情燈箱 -->
    <el-dialog v-model="isDetailDialogOpen" class="battle-detail-dialog">
      <!-- #header：讓element plus的燈箱可以使用自己設計的標題內容與樣式 -->
      <template #header>
        <div v-if="selectedBattle" class="battle-detail-header">
          <div class="battle-detail-heading">
            <span class="battle-detail-heading-accent"></span>
            <h3>約戰資訊</h3>
          </div>

          <span class="battle-status" :class="getDisplayStatusClass(selectedBattle)">
            {{ getDisplayStatusLabel(selectedBattle) }}
          </span>
        </div>
      </template>

      <!-- 燈箱上方摘要 -->
      <div v-if="selectedBattle" class="battle-detail-summary">
        <div class="battle-detail-meta">
          <span>{{ selectedBattle.battleId }}</span>
          <span class="battle-detail-divider"></span>
          <span>{{ getModeLabel(selectedBattle.mode) }}</span>
          <span class="battle-detail-divider"></span>
          <span>發起人：{{ selectedBattle.hostName }}</span>
        </div>

        <p class="battle-detail-created-time">
          建立時間：{{ selectedBattle.createdAt }}
        </p>
      </div>

      <!-- 詳情主要內容 -->
      <div v-if="selectedBattle" class="battle-detail-content">
        <!-- 左側：公開約戰內容 -->
        <section class="battle-detail-public">
          <div class="battle-detail-section-title">
            <i class="fa-regular fa-eye"></i>
            <h4>公開約戰內容</h4>
          </div>

          <div class="battle-public-main">
            <div class="battle-public-cover">
              <img
                v-if="selectedBattle.coverImage"
                :src="selectedBattle.coverImage"
                :alt="selectedBattle.title"
              >

              <div
                v-else
                class="battle-public-cover-empty"
              >
                暫無封面
              </div>
            </div>

            <div class="battle-public-text">
              <div class="battle-public-field">
                <span class="battle-public-label">約戰標題</span>
                <p>{{ selectedBattle.title }}</p>
              </div>

              <div class="battle-public-field">
                <span class="battle-public-label">約戰說明</span>
                <p>{{ selectedBattle.description }}</p>
              </div>
            </div>

            <div class="battle-public-info">
              <div class="battle-public-info-row">
                <span>約戰日期</span>
                <p>
                  {{ selectedBattle.battleDate }}，{{ selectedBattle.battleTime }}
                </p>
              </div>

              <div class="battle-public-info-row">
                <span>報名截止</span>
                <p>{{ selectedBattle.deadline }}</p>
              </div>

              <div class="battle-public-info-row">
                <span>集合地點</span>
                <p>{{ selectedBattle.meetingPlace }}</p>
              </div>

              <div class="battle-public-info-row">
                <span>玩家程度</span>
                <p>{{ selectedBattle.playerLevel }}</p>
              </div>

              <div class="battle-public-info-row">
                <span>適合對象</span>
                <p>{{ selectedBattle.targetAudience }}</p>
              </div>
            </div>

          </div>

        </section>

        <!-- 右側：聯絡資訊、勝者與管理處置 -->
        <div class="battle-detail-side">

          <!-- 雙方聯絡資訊 -->
          <section class="battle-detail-contact">
            <div class="battle-detail-section-title">
              <i class="fa-regular fa-address-card"></i>
              <h4>聯絡資訊</h4>
            </div>
            <!-- 發起人聯絡資訊 -->
            <div class="battle-contact-list">
              <div class="battle-contact-item">
                <p class="battle-contact-person">
                  發起人<strong>{{ selectedBattle.hostName }}</strong>
                </p>
                <div class="battle-contact-value">
                  {{ selectedBattle.hostContact || "尚未提供" }}
                </div>
              </div>
              <!-- 參加人聯絡資訊 -->
              <div class="battle-contact-item">
                <p class="battle-contact-person">
                  參加人<strong>{{ selectedBattle.participantName || "尚無參加人" }}</strong>
                </p>
                <div class="battle-contact-value">
                  {{ selectedBattle.participantContact || "尚未提供" }}
                </div>
              </div>
            </div>
          </section>

          <!-- 勝者記錄：當約戰為競技模式，且狀態為配對成功時才顯示 -->
          <section
            v-if="selectedBattle.mode === 'competitive' && selectedBattle.status === 'success'"
            class="battle-detail-winner"
          >
            <div class="battle-detail-section-title">
              <i class="fa-solid fa-trophy"></i>
              <h4>勝者記錄</h4>
            </div>

            <p class="battle-winner-text">
              由
              <strong>{{ getWinnerName(selectedBattle) }}</strong>
              獲勝
            </p>
          </section>

          <!-- 管理員操作處置 -->
          <section class="battle-detail-handle">
            <div class="battle-detail-handle-title">
              <span class="battle-detail-handle-accent"></span>
              <i class="fa-solid fa-shield-halved"></i>
              <h4>管理處置</h4>
            </div>

            <div class="battle-handle-options">
              <label>
                <input type="radio" value="keep" v-model="manageAction">
                保留邀約
              </label>
              <label class="battle-handle-remove">
                <input type="radio" value="remove" v-model="manageAction">
                下架邀約
              </label>
            </div>
            
            <!-- 當radio選項被切換至與該筆紀錄本身狀態不同的處置時，才會出現處置確認按鈕 -->
            <template v-if="hasManageActionChanged">
              <!-- 處置通知內容輸入 -->
              <div class="battle-handle-notice">
                <label for="memberNotice">
                  通知會員內容（將顯示給發起人）
                </label>
                <!-- 當管理者輸入訊息時，就自動清除下方的錯誤提示文字 -->
                <textarea 
                  id="memberNotice" 
                  v-model.trim="memberNotice" 
                  placeholder="請輸入將通知會員的內容"
                  @input="memberNoticeError = ''" 
                >
                </textarea>
                <!-- 錯誤提示文字 -->
                <p v-if="memberNoticeError" class="battle-handle-error">
                  {{ memberNoticeError }}
                </p>
              </div>
              <!-- 處置操作按鈕 -->
              <div class="battle-handle-buttons">
                <button type="button" class="battle-handle-cancel" @click="cancelManageAction">
                  取消
                </button>

                <button 
                  type="button" 
                  class="battle-handle-confirm"
                  :class="{'battle-handle-confirm-restore': manageAction === 'keep'}"
                  @click="confirmManageAction"
                >
                  {{ manageAction === "remove" ? "確認下架" : "恢復上架" }}
                </button>
              </div>
            </template>
          </section>

        </div>
      </div>

    </el-dialog>

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
      pageSize: 10,

      selectedBattle: null, //用來存取被點選的那筆約戰紀錄資料
      isDetailDialogOpen: false, //詳情燈箱是否開啟

      manageAction: "keep", //管理員選擇約戰紀錄保留或下架
      memberNotice: "", //管理員處置的通知內容
      memberNoticeError: "", //未輸入通知內容時，出現的錯誤訊息

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
          (
            this.filters.status === "removed"
              ? battle.is_show === false
              : battle.is_show === true &&
                battle.status === this.filters.status
          );

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
    },

    currentManageStatus() { //根據約戰紀錄原先的上下架狀態，決定打開燈箱時，預設勾選的radio處置
      if (!this.selectedBattle) {
        return "";
      }

      return this.selectedBattle.is_show ? "keep" : "remove";
    },

    hasManageActionChanged() { //比較管理員目前勾選的radio處置，是否和該筆資料目前的is_show狀態不同
      return this.manageAction !== this.currentManageStatus; //只有不同時，才顯示處置操作按鈕
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
    },

    getDisplayStatusLabel(battle) { //決定畫面要顯示什麼狀態文字
      if (!battle.is_show) { //被下架的紀錄，直接顯示已下架
        return "已下架";
      }
      return this.getStatusLabel(battle.status); //未下架的紀錄，才進一步顯示實際配對狀態
    },

    getDisplayStatusClass(battle) { //用來決定狀態標籤要套上什麼class樣式
      if (!battle.is_show) { //當記錄為已下架時，套用removed class
        return "battle-status-removed";
      }

      return `battle-status-${battle.status}`; //其餘套用原先配對狀態的對應class
    },

    openBattleDetail(battle) { //點擊查看按鈕,開啟燈箱函式
      this.selectedBattle = battle; //把被選中的約戰紀錄資料先放進data裡的selectedBattle變數中
      this.isDetailDialogOpen = true; //開啟詳情燈箱

      // 根據is_show決定radio處置的預設位置
      this.manageAction = battle.is_show ? "keep" : "remove";
      this.memberNotice = "";
    },

    getModeLabel(mode) { //對戰模式標籤英中切換
      const modeItem = this.modeOptions.find(
        option => option.value === mode
      );

      return modeItem ? modeItem.label : mode;
    },

    getWinnerName(battle) { //取得約戰勝者資訊
      if (battle.winner === 0) { //資料為0時，代表發起人勝利
        return battle.hostName;
      }

      if (battle.winner === 1) { //資料為1時，代表參加人勝利
        return battle.participantName;
      }

      return "尚未回傳";
    },

    confirmManageAction() {  //確認處置操作
      if (!this.selectedBattle) {
        return;
      }

      // 未填寫通知內容時，停止後續動作
      if (!this.memberNotice) {
        this.memberNoticeError = "請先輸入通知會員的內容";
        return;
      }

      // 根據目前選擇，決定confrim時的顯示文字
      const actionText =
        this.manageAction === "remove"
          ? "下架"
          : "恢復上架";

      const isConfirmed = confirm(
        `確定要${actionText}這筆約戰邀約嗎？`
      );

      if (!isConfirmed) {
        return;
      }

      // 確認後才真正修改上下架狀態
      //判斷管理員最終選擇的處置操作是否為keep，不是的話為false，判斷下架約戰
      this.selectedBattle.is_show = this.manageAction === "keep";
      this.currentPage = 1; //將畫面跳回列表第一頁
      this.memberNotice = "";
      this.isDetailDialogOpen = false;
    },

    cancelManageAction() { //取消處置操作按鈕
      this.manageAction = this.currentManageStatus; //將預設的radio選項先恢復成該筆約戰紀錄本身的真實狀態
      this.memberNotice = ""; //清空未送出的通知內容
      this.isDetailDialogOpen = false; //關閉燈箱
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

  h2.battle-manage-title {
    color: #F29B00;
    font-weight: 700;
    font-size: 30px;
    margin-bottom: 28px;
  }

  /* 篩選區外層 */
  .battle-manage-filter {
    width: 100%;
    padding: 20px;

    display: flex;
    flex-direction: column;
    gap: 20px;

    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(20, 28, 38, 0.05);
  }

  /* 篩選第一列：對戰模式、縣市、約戰狀態 */
  .battle-filter-main {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 24px;
  }

  /* 第二列：約戰日期與重置按鈕 */
  .battle-filter-bottom {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 24px;
  }

  /* 單一篩選欄位共同樣式 */
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

      //label左側小icon
      i {
        width: 16px;
        margin-right: 8px;
        text-align: center;
        color: #64748b;
      }
    }

    select {
      min-width: 160px;
      padding: 8px 30px 8px 12px;
    }

    select,
    .date-range input {
      background-color: #F7F5F3;
      border: 1px solid #ddd6c8;
      border-radius: 10px;
      outline: none;

      font-size: 14px;
      line-height: 1.5;
      color: #141c26;

      transition: border-color 0.24s;

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
    min-width: 92px;
    padding: 8px 16px;

    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    border: 1px solid #141c26;
    border-radius: 12px;

    background-color: #141c26;
    color: #ffffff;

    font-size: 16px;
    cursor: pointer;
    transition: background-color 0.24s, transform 0.24s;

    &:hover {
      background-color: #253240;
    }

    &:active {
      transform: translateY(1px);
    }
  }

  /* 列表底部區域：分頁器、總筆數顯示 */
  .battle-manage-list-footer {
    width: 100%;
    padding: 16px;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;

    border-top: 1px solid #dddddd;
    background-color: #ffffff;

    //資料總筆數顯示文字
    p {
      font-size: 14px;
      line-height: 1.5;
      white-space: nowrap;
    }

  }

  .battle-manage-paginator {
    display: flex;
    justify-content: flex-end;
    align-items: center;
  }

  //約戰資訊列表
  .battle-manage-list {
    width: 100%;
    margin-top: 32px;

    overflow: hidden;

    border: 1px solid #e4e2e0;
    border-radius: 12px;

    background-color: #ffffff;
    box-shadow: 0 4px 20px rgba(20, 28, 38, 0.05);
  }

  .battle-table-wrap {
    width: 100%;
    overflow-x: auto;  //表格可橫向捲動外層
  }

  //約戰資料表格內容
  .battle-table {
    width: 100%;
    // min-width: 900px;
    min-width: 820px;
    table-layout: fixed;
    border-collapse: collapse;

    color: #141c26;
    font-size: 16px;

    th,
    td {
      padding: 16px 8px;
      text-align: center;
      vertical-align: middle;
    }

    //表頭標題
    thead {
      background-color: #ffe9c4;
    }

    th {
      color: #141c26;
      font-weight: 500;
      white-space: nowrap;
    }

    //表單內容列
    tbody tr {
      border-top: 1px solid rgba(228, 226, 224, 0.5);
      transition: background-color 0.2s;
    }

    tbody tr:hover {
      background-color: #fffaf2;
    }

    td {
      line-height: 1.5;
    }

    //約戰日期欄
    td:nth-child(5) {
      color: #808080;
      white-space: nowrap;
    }

    //操作欄按鈕不折行
    td:last-child {
      white-space: nowrap;
    }

    td button {
      padding: 5px 12px;

      border: 1px solid #141c26;
      border-radius: 6px;

      background-color: #ffffff;
      color: #141c26;

      font-size: 14px;
      cursor: pointer;

      transition:
        background-color 0.2s,
        color 0.2s,
        box-shadow 0.2s;

      &:hover {
        background-color: #141c26;
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(20, 28, 38, 0.12);
      }
    }

    //  調整特定欄位寬度
    // 邀約 ID
    th:nth-child(1),
    td:nth-child(1) {
      width: 80px;
    }

    th:nth-child(2),
    td:nth-child(2) {
      width: 160px;
    }

    //發起人
    th:nth-child(3),
    td:nth-child(3) {
      width: 96px;
    }

    //地區
    th:nth-child(4),
    td:nth-child(4) {
      width: 180px;
    }

    // 日期
    th:nth-child(5),
    td:nth-child(5) {
      width: 100px;
    }

    // 狀態
    th:nth-child(6),
    td:nth-child(6) {
      width: 100px;
    }

    // 操作
    th:nth-child(7),
    td:nth-child(7) {
      width: 80px;
    }

    
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

  // 約戰詳情燈箱本體
  :deep(.battle-detail-dialog) {
    width: min(760px, 90vw);

    background-color: #f7f5f3;
    border-radius: 12px;

    padding: 28px;
  }

  .battle-detail-header {
    display: flex;
    align-items: center;
    gap: 36px;
  }

  .battle-detail-heading {
    min-width: 0;

    display: flex;
    align-items: center;
    gap: 12px;

    h3 {
      margin: 0;
      font-size: 24px;
      font-weight: 700;
      line-height: 1.4;
      color: #141c26;
    }
  }

  //右上角關閉按鈕
  :deep(.battle-detail-dialog .el-dialog__headerbtn) {
    top: 12px;
    right: 12px;

    width: 40px;
    height: 40px;

    display: flex;
    align-items: center;
    justify-content: center;
  }

  //關閉圖案X本身
  :deep(.battle-detail-dialog .el-dialog__headerbtn .el-dialog__close) {
    color: #64748b;
    font-size: 24px;
    transition: color 0.24s;
  }

  :deep(.battle-detail-dialog .el-dialog__headerbtn:hover .el-dialog__close) {
    color: #141c26;
  }

  //左側橘色裝飾條
  .battle-detail-heading-accent {
    width: 4px;
    height: 28px;
    border-radius: 3px;
    background-color: #f29b00;
  }

  //詳情燈箱上方摘要區
  .battle-detail-summary {
    width: 100%;

    display: flex;
    flex-direction: column;
    gap: 12px;

    padding-inline: 4px 12px;

    margin-bottom: 20px;
  }

  //約戰編號、模式、發起人資訊列
  .battle-detail-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;

    gap: 12px;
    font-size: 18px;
    font-weight: 500;
    line-height: 1.5;
    color: #141c26;
  }

  .battle-detail-divider {
    width: 2px;
    height: 24px;

    flex-shrink: 0;

    background-color: #ddd6c8;
  }

  .battle-detail-created-time {
    margin: 0;
    font-size: 16px;
    color: #aaaaaa;
  }

  //燈箱內主要資訊
  .battle-detail-content {
    width: 100%;

    display: grid;
    grid-template-columns: //內容分左右兩欄
      minmax(0, 0.95fr)
      minmax(0, 1fr);
    align-items: start;
    gap: 24px;
  }

  //主內容左側欄：約戰公開資訊
    .battle-detail-public {
    min-width: 0;
    padding: 16px;

    display: flex;
    flex-direction: column;
    gap: 20px;

    border: 1px solid #e3ddd5;
    border-radius: 12px;

    background-color: #ffffff;
    box-shadow: 0 2px 2px rgba(0, 0, 0, 0.12);
  }

  //主內容右側欄：勝者資訊、聯絡方式、操作處置
  .battle-detail-side {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 12px;

    //因左側公開資訊區域高度較高，設定sticky，讓右側內容可以跟著捲動，同時看得到兩邊資訊
    position: sticky;
    top: 16px;
    align-self: start;
  }

  .battle-detail-section-title {
    display: flex;
    align-items: center;
    gap: 8px;

    //區塊標題左側圖示icon
    i {
      width: 20px;
      color: #f29b00;
      text-align: center;
    }

    h4 {
      font-size: 20px;
      font-weight: 500;
      color: #141c26;
    }
  }

  @media (max-width: 900px) {
    .battle-detail-content {
      grid-template-columns: 1fr;
    }

    .battle-detail-side {
      position: static;
    }
  }

  .battle-public-main {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
  }

  //約戰封面圖
  .battle-public-cover {
    width: min(100%, 180px);
    aspect-ratio: 8 / 7;

    overflow: hidden;
    border: 1px solid #e3ddd5;
    border-radius: 12px;

    background-color: #f7f5f3;
    box-shadow: 0 8px 8px rgba(20, 28, 38, 0.06);

    img {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: cover;
    }
  }

  //沒有讀取到封面圖時的空狀態樣式
  .battle-public-cover-empty {
    width: 100%;
    height: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    background-color: #f7f5f3;
    color: #aaaaaa;
    font-size: 16px;
  }

  .battle-public-text {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .battle-public-field {
    display: flex;
    flex-direction: column;
    gap: 6px;

    p {
      margin: 0;
      color: #141c26;
      font-size: 16px;
      font-weight: 600;
      line-height: 1.5;
      overflow-wrap: anywhere;
    }

    //約戰標題文字
    &:first-child p {
      font-size: 18px;
      font-weight: 600;
    }
  }

  .battle-public-label {
    color: #808080;
    font-size: 16px;
  }

  .battle-public-info {
    width: 100%;

    display: flex;
    flex-direction: column;
    gap: 12px;

    padding: 6px;
    border-radius: 8px;
    background-color: #f7f5f3;
  }

  .battle-public-info-row {
    display: grid;
    grid-template-columns: minmax(100px, 32%) minmax(0, 1fr);
    align-items: center;
    gap: 16px;
    padding: 6px 0;
    border-bottom: 1px solid #e3ddd5;
    
    &:first-child {
      padding-top: 0;
    }

    &:last-child {
      padding-bottom: 0;
      border-bottom: 0;
    }

    span {
      color: #808080;
      font-size: 16px;
    }

    p {
      min-width: 0;
      margin: 0;

      color: #141c26;
      font-size: 16px;
      font-weight: 500;
      line-height: 1.5;
      overflow-wrap: anywhere;
    }
  }

  // 聯絡資訊
  .battle-detail-contact {
    padding: 16px;

    display: flex;
    flex-direction: column;
    gap: 20px;

    border: 1px solid #e3ddd5;
    border-radius: 12px;

    background-color: #ffffff;
  }

  .battle-contact-list {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .battle-contact-item {
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .battle-contact-person {
    color: #64748b;
    font-size: 16px;

    display: flex;
    align-items: center;
    gap: 8px;

    strong {
      color: #141c26;
      font-weight: 500;
    }
  }

  .battle-contact-value {
    width: 100%;
    min-height: 36px;
    padding: 8px 12px;

    display: flex;
    align-items: center;

    background-color: #f7f5f3;
    border: 1px solid #ddd6c8;
    border-radius: 8px;

    color: #141c26;
    font-size: 16px;
    line-height: 1.5;
    overflow-wrap: anywhere;
  }

  //勝者記錄區
  .battle-detail-winner {
    padding: 16px;

    display: flex;
    flex-direction: column;
    gap: 16px;

    border: 1px solid rgba(254, 201, 107, 0.7);
    border-radius: 12px;

    background-color: #fff9ed;
  }

  .battle-winner-text {
    margin: 0;
    text-align: center;

    color: #64748b;
    font-size: 18px;

    strong {
      margin: 0 4px;
      color: #f29b00;
      font-weight: 600;
    }
  }

  //處置操作區
  .battle-detail-handle {
    padding: 16px;

    display: flex;
    flex-direction: column;
    gap: 20px;

    border: 1px solid #e3ddd5;
    border-radius: 12px;
    background-color: #ffffff;
  }

  .battle-detail-handle-title {
    display: flex;
    align-items: center;
    gap: 8px;

    i {
      width: 20px;
      color: #64748b;
      text-align: center;
    }

    h4 {
      margin: 0;
      color: #141c26;
      font-size: 20px;
      font-weight: 500;
    }
  }

  .battle-detail-handle-accent {
    width: 4px;
    height: 24px;

    flex-shrink: 0;

    border-radius: 3px;
    background-color: #e63946;
  }

  .battle-handle-options {
    display: flex;
    justify-content: center;
    gap: 28px;

    label {
      display: flex;
      align-items: center;
      gap: 8px;

      color: #141c26;
      font-size: 16px;
      cursor: pointer;
    }
  }

  .battle-handle-remove {
    color: #e63946 !important;
  }

  .battle-handle-notice {
    display: flex;
    flex-direction: column;
    gap: 8px;

    label {
      color: #141c26;
      font-size: 16px;
    }

    textarea {
      width: 100%;
      min-height: 120px;
      padding: 12px;

      resize: vertical;

      border: 1px solid #ddd6c8;
      border-radius: 8px;
      outline: none;

      color: #141c26;
      font-size: 16px;

      &:focus {
        border-color: #f29b00;
      }
    }
  }

  //錯誤提示文字
  .battle-handle-error {
    display: inline-block;
    margin-inline: auto;
    margin-bottom: 8px;

    color: #e63946;
    font-size: 14px;
    line-height: 1.4;
  }

  .battle-handle-buttons {
    display: flex;
    justify-content: center;
    gap: 36px;

    button {
      padding: 6px 20px;
      border: 0;
      border-radius: 16px;

      font-size: 18px;
      cursor: pointer;
    }
  }

  .battle-handle-cancel {
    background-color: #ffffff;
    color: #141c26;
  }

  //下架按鈕樣式
  .battle-handle-confirm {
    background-color: rgba(230, 57, 70, 0.7);
    color: #ffffff;
    transition: all .3s;

    &:hover {
      background-color: #e63946;
    }

  }

  //恢復上架按鈕樣式
  .battle-handle-confirm-restore {
    background-color: #64748b;

    &:hover {
      background-color: darken(#64748b, 10%);
    }
  }

</style>