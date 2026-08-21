<template>
  <section class="my-battle">
    <!-- 我的約戰標題 -->
    <div class="battle-title">
      <h2>我的約戰</h2>
      <p>管理你發起和參加的對戰邀約</p>
    </div>

    <!-- 發起/參加人切換、篩選列 -->
    <div class="battle-controls">
      <div class="battle-role-tabs">
        <button type="button" 
          class="role-tab"
          :class="{ active: currentRole == 'initiator'}"
          @click="changeRole('initiator')">
          你發起的對戰
        </button>
        <button type="button" 
        class="role-tab"
        :class="{ active: currentRole == 'participant' }"
        @click="changeRole('participant')">
          你參加的對戰
        </button>
      </div>
      <div class="battle-status-tabs">
        <button type="button" class="status-tab"
          :class="{ active: currentStatus == 'all' }"
          @click="changeStatus('all')">
          全部 <span>({{ statusCounts.all }})</span>
        </button>
        <button type="button" class="status-tab"
          v-if="currentRole == 'initiator'"
          :class="{ active: currentStatus == 'matching' }"
          @click="changeStatus('matching')">
          待加入 <span>({{ statusCounts.matching }})</span>
        </button>
        <button type="button" class="status-tab"
          :class="{ active: currentStatus == 'pending' }"
          @click="changeStatus('pending')">
          待確認 <span>({{ statusCounts.pending }})</span>
        </button>
        <button type="button" class="status-tab"
          :class="{ active: currentStatus == 'confirmed' }"
          @click="changeStatus('confirmed')">
          已確認 <span>({{ statusCounts.confirmed }})</span>
        </button>
        <button type="button" class="status-tab"
          :class="{ active: currentStatus == 'cancelled' }"
          @click="changeStatus('cancelled')">
          已失效 <span>({{ statusCounts.cancelled }})</span>
        </button>
      </div>
    </div>

    <!-- 對戰紀錄卡列表 -->
    <div class="my-battle-records">
                                                                      <!-- 將每筆約戰資料，傳給battleRecord子元件中取名是battle的prop -->
      <BattleRecord 
        v-for="battle in filteredBattles" :key="battle.id" :battle="battle" @open-history="openHistoryModal"
        @accept-battle="acceptBattle"
        @reject-battle="rejectBattle"
        @submit-result="submitBattleResult"
        @open-appeal="goToAppeal"
        @open-review="openReviewModal">
      </BattleRecord>

      <!-- 當特定分類沒有約戰紀錄時顯示 -->
      <div v-if="filteredBattles.length === 0" class="battle-empty">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <p class="empty-message">目前沒有相關約戰紀錄</p>

        <!-- 若完全沒有資料，則依目前腳色區塊，顯示對應導覽連結 -->
        <a
          v-if="currentStatus === 'all' && currentRole === 'participant'"
          :href="`${baseUrl}battle.html`"
          class="empty-link btnFill"
        >
          前往約戰配對
        </a>

        <a
          v-else-if="currentStatus === 'all' && currentRole === 'initiator'"
          :href="`${baseUrl}createBattle.html`"
          class="empty-link btnFill"
        >
          立即建立約戰
        </a>

      </div>
    </div>

    <!-- 約戰歷史燈箱 -->
    <HistoryModal 
      v-if="isHistoryModalOpen && selectedMember" 
      :member="selectedMember"
      @close="closeHistoryModal"
      >
    </HistoryModal>

    <!-- 評價燈箱 -->
    <ReviewModal 
      v-if="reviewModalOpen" 
      @close="closeReviewModal"
      @submit-review="submitReview"
      >
    </ReviewModal>

  </section>
</template>

<script>
  import BattleRecord from "./battleRecord.vue";
  import HistoryModal from "./historyModal.vue";
  import ReviewModal from "./reviewModal.vue";

  export default {
    name: "MyBattle",

    data () {
      return {
        baseUrl: import.meta.env.BASE_URL,

        currentRole: "initiator", //預設顯示作為發起人的紀錄區
        currentStatus: "all", //預設顯示全部的紀錄卡
        isHistoryModalOpen: false, //歷史燈箱是否開啟，預設關閉
        reviewModalOpen: false, // 評價燈箱是否開啟，預設關閉
        selectedMember: null, //燈箱要顯示的會員

        //儲存後端傳回的當前會員相關約戰紀錄
        battleRecords: [],

        //填入評價燈箱的資料
        reviewTarget: {
          battleId: null,
          opponentId: null,
          opponentName: "",
          opponentAvatar: ""
        }

      };
    },

    components: {
      BattleRecord,
      HistoryModal,
      ReviewModal
    },

    mounted() { //一載入元件時，就先抓取當前會員的相關約戰資料
      this.fetchMyBattles();
    },

    computed: {
      roleBattles() { //計算不同角色時的卡片數量
        return this.battleRecords.filter((battle) => {
          return battle.role == this.currentRole;
        });
      },

      statusCounts() { //計算不同狀態下的卡片數量
        return {
          all: this.roleBattles.length,

          matching: this.roleBattles.filter((battle) => {
            return battle.status == "matching";
          }).length,

          pending: this.roleBattles.filter((battle) => {
            return battle.status == "pending";
          }).length,

          confirmed: this.roleBattles.filter((battle) => {
            return battle.status == "confirmed";
          }).length,

          cancelled: this.roleBattles.filter((battle) => {
            return battle.status == "cancelled";
          }).length,
        }
      },

      filteredBattles() { //篩選不同狀態時，回傳最終要呈現的卡片記錄
        if (this.currentStatus == "all") {
          return this.roleBattles;
        }

        return this.roleBattles.filter((battle) => {
          return battle.status == this.currentStatus;
        });
      },

      // PHP API 路徑
      phpBaseUrl() {
        return (
          location.hostname === "localhost" ||
          location.hostname === "127.0.0.1"
        )
          ? "http://localhost:8888/Spinix/php"
          : "/ckd101/g2/php";
      }
    },

    methods: {
        async fetchMyBattles() { //串接取得當前會員相關約戰API
          try {
            const response = await fetch(`${this.phpBaseUrl}/battle/my_battle_get.php`, {
              credentials: "include"
            });

            const data = await response.json();
            const targetMap = {
              ALL: "不限對象",
              ADULT: "成人限定",
              FAMILY: "親子友善"
            };

            const statusMap = {
              MATCHING: "matching",
              PENDING: "pending",
              CONFIRMED: "confirmed",
              FAILED: "cancelled",
              CANCELLED: "cancelled"
            };

            console.log("API原始資料：", data);

            //將後端取回資料進行格式轉換，存進battleRecords中
            this.battleRecords = data.map((battle) => {
              //將約戰日期資料先提前轉換為前端需要顯的格式
              const dateObj = new Date(battle.BATTLE_DATE);

              const year = dateObj.getFullYear();
              const month = String(dateObj.getMonth() + 1).padStart(2, "0");
              const day = String(dateObj.getDate()).padStart(2, "0");

              const hour = String(dateObj.getHours()).padStart(2, "0");
              const minute = String(dateObj.getMinutes()).padStart(2, "0");

              const weekdayMap = ["日", "一", "二", "三", "四", "五", "六"];
              const weekday = weekdayMap[dateObj.getDay()];

              return {
                id: Number(battle.BATTLE_ID),

                role: battle.MY_ROLE === "INITIATOR"
                  ? "initiator"
                  : "participant",

                initiatorId: Number(battle.INITIATOR_ID),

                participantId: battle.PARTICIPANT_ID
                  ? Number(battle.PARTICIPANT_ID)
                  : null,

                initiatorName: battle.INITIATOR_NAME,
                participantName: battle.PARTICIPANT_NAME,

                initiatorAvatar: this.getMemberPhotoUrl(battle.INITIATOR_PHOTO),
                participantAvatar: battle.PARTICIPANT_PHOTO ? this.getMemberPhotoUrl(battle.PARTICIPANT_PHOTO) : null,

                status: statusMap[battle.BATTLE_STATUS], //前端UI分類
                rawStatus: battle.BATTLE_STATUS, //保留資料庫原始狀態

                mode: battle.BATTLE_MODE.toLowerCase(),

                title: battle.BATTLE_TITLE,

                city: battle.CITY_NAME,
                district: battle.DISTRICT_NAME,

                target: targetMap[battle.BATTLE_TARGET] || battle.BATTLE_TARGET,


                meetingPlace: battle.BATTLE_LOC,

                initiatorContact: battle.INI_CONTACT,
                participantContact: battle.PAR_CONTACT,

                winner: 
                  battle.WINNER === null 
                  ? null 
                  : Number(battle.WINNER) === 0 
                    ? "initiator" 
                    : "participant",

                hasReviewed: Number(battle.HAS_REVIEWED) === 1,
                reviewedAt: battle.MY_REVIEWED_AT,

                coverImage: this.getBattleImageUrl(battle.BATTLE_IMG),

                battleDate: `${year}/${month}/${day}`,
                weekday: weekday,
                battleTime: `${hour}:${minute}`,

                // 保留原始約戰日期格式，供後續邏輯判斷使用
                battleDateTime: battle.BATTLE_DATE
              };
            });

            console.log("轉換後 battleRecords：", this.battleRecords);

          } catch (error) {
            console.error("取得我的約戰失敗", error);
          }
        },

        getBattleImageUrl(imagePath) { //處理約戰封面圖路徑
          if (!imagePath) {
            return "";
          }

          // 動態上傳的圖片位置 PHP/uploads/battle
          if (imagePath.startsWith("uploads/")) {
            return `${this.phpBaseUrl}/${imagePath}`;
          }

          // 其他圖片則維持原本路徑
          return `${import.meta.env.BASE_URL}${imagePath}`;
        },

        getMemberPhotoUrl(photoPath) { //處理約戰紀錄中的會員頭像路徑
          if (!photoPath) {
            return "";
          }

          return `${import.meta.env.BASE_URL}${photoPath}`;
        },

        changeRole(role) {
          this.currentRole = role;
          this.currentStatus = "all"; //切換腳色的同時，卡片狀態預設顯示全部

          //this.$nextTick() → 資料變動後，讓vue將畫面也更新完成後，才接著執行 () 內的事
          this.$nextTick(() => {
            // this.$el 只從目前myBattle.vue自己產生出的畫面範圍中，去找出約戰紀錄列表.my-battle-records
            const records = this.$el.querySelector(".my-battle-records");

            //找到列表後，將列表區的水平卷軸拉回最左邊起始處
            if (records) {
              records.scrollLeft = 0;
            }
          });
        },
        changeStatus(status) {
          this.currentStatus = status;

          this.$nextTick(() => {
            const records = this.$el.querySelector(".my-battle-records");

            if (records) {
              records.scrollLeft = 0;
            }
          });
        },
        closeHistoryModal() {
          this.isHistoryModalOpen = false;
          this.selectedMember = null; //關閉燈箱時，同時清空資料
        },
        async openHistoryModal(memberId) { //打開約戰歷史燈箱
          if(!memberId) {
            return;
          }

          try {
            const response = await fetch(`${this.phpBaseUrl}/battle/host_history_get.php?host_id=${memberId}`, {
              credentials: "include"
            });

            const data = await response.json();

            if (!response.ok) {
              alert(data.message || "會員歷史資料取得失敗");
              return;
            }

            //取得API資料後，先將其整理為歷史燈箱中需要的格式
            this.selectedMember = {
              memberId: Number(data.host.MEM_ID),
              name: data.host.MEM_NAME,
              avatar: data.host.MEM_PHOTO,

              // 約戰統計
              totalBattles: Number(data.host.TOTAL_BATTLES),

              averageRating:
                data.host.AVERAGE_RATING === null ? null : Number(data.host.AVERAGE_RATING),

              // 競技模式統計
              competitiveTotal: Number(data.host.COMPETITIVE_TOTAL),
              competitiveWins: Number(data.host.COMPETITIVE_WINS),
              winRate: data.host.WIN_RATE === null ? null : Number(data.host.WIN_RATE),

              // 最新評價
              reviews: data.reviews.map(review => {
                return {
                  reviewerName: review.REVIEWER_NAME,
                  rating: Number(review.RATING),
                  content: review.COMMENT,
                  createdAt: review.COMMENTED_AT
                };
              })
            };

            console.log("整理後的會員歷史資料：", this.selectedMember);

            //資料準備完成後，再打開燈箱
            this.isHistoryModalOpen = true;

          } catch (error) {
            console.error("取得會員歷史資料失敗：", error);
            alert("系統發生錯誤，請稍後再試");
          }
          
        },
        async acceptBattle(battleId) { //接受申請對戰函式
          const isConfirmed = confirm(
              "確定要接受這位會員的約戰申請嗎？"
            );

          if (!isConfirmed) {
            return;
          };

          try {
            //建立要傳給後端API的此筆約戰ID資料
            const formData = new FormData();
            formData.append("battle_id", battleId);

            //呼叫接受申請約戰API
            const response = await fetch(`${this.phpBaseUrl}/battle/battle_accept_post.php`,{
              method: "POST",
              credentials: "include",
              body: formData
            });

            //將PHP回傳的JSON 再轉成JS物件
            const data = await response.json();

            // API 回傳失敗時
            if (!response.ok || !data.success) {
              alert(data.message || "接受約戰申請失敗");
              return;
            }

            // 成功文字提示
            alert(data.message);

            // 重新取得當前會員的約戰紀錄，更新畫面上的約戰紀錄狀態
            await this.fetchMyBattles();

          } catch (error) {
            console.error("接受約戰申請失敗：", error);
            alert("系統發生錯誤，請稍後再試");
          }
        },

        async rejectBattle(battleId) { //拒絕申請函式
          const isConfirmed = confirm(
            "確定無法參加此次約戰嗎？確認後，此次約戰將會取消。"
          );

            if (!isConfirmed) {
            return;
          };

          try {
            const formData = new FormData();
            formData.append("battle_id", battleId);

            const response = await fetch(`${this.phpBaseUrl}/battle/battle_reject_post.php`, {
              method: "POST",
              credentials: "include",
              body: formData
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
              alert(data.message || "拒絕約戰申請失敗");
              return;
            }

            //拒絕約戰-操作成功文字提示
            alert(data.message);

            // 拒絕申請後，再重新抓取一次目前會員的相關約戰紀錄，更新狀態
            await this.fetchMyBattles();

          } catch (error) {
            console.error("拒絕約戰申請失敗：", error);
            alert("系統發生錯誤，請稍後再試");
          }
        },

        async submitBattleResult(resultData) { 
          //resultData 為子元件(battleConfirmedContent)傳過來的約戰id與winner資料
          const battle = this.battleRecords.find(
            item => item.id === resultData.battleId
          );

          if (!battle) return;

          //當原訂的約戰時間還未到時，先提醒會員
          const battleDateTime = new Date(battle.battleDateTime);
          const now = new Date();

          //初始確認提交文字
          let confirmMessage = "確定提交此次對戰結果嗎？";

          //當約戰時間還沒到時的提醒文字
          if (now < battleDateTime) {
            confirmMessage = "目前尚未到原訂約戰時間，請確認雙方已實際完成對戰後再回填結果。\n確定要繼續送出嗎？";
          }

          const isConfirmed = confirm(confirmMessage);

          if(!isConfirmed) {
            return;
          }

          //確定要送出winner時，先將前端目前前端顯示用的initiator、participant，轉成後端資料庫要存的0、1
          const winnerValue = resultData.winner === "initiator" ? 0 : 1;

          //接著準備串接後端API，將勝者資料回傳
          const formData = new FormData();
          formData.append("battle_id", resultData.battleId);
          formData.append("winner", winnerValue);

          try {
            const response = await fetch(`${this.phpBaseUrl}/battle/battle_result_post.php`, {
              method: "POST",
              credentials: "include",
              body: formData
            });

            const data = await response.json();

            //API回傳失敗時
            if (!response.ok || !data.success) {
              alert(data.message || "回填對戰結果失敗");
              return;
            }

            //回傳成功
            alert(data.message);

            //頁面重新取得最新狀態的約戰資料
            await this.fetchMyBattles();

          } catch (error) {

            console.error("回填對戰結果失敗：", error);
            alert("系統發生錯誤，請稍後再試");
          }
          
        },

        goToAppeal(appealData) {
          //送出申訴函式，將子元件傳來的資訊：1.此筆申訴屬約戰類型 2.此筆約戰紀錄的id，攜帶跳轉至申訴分頁

          //將JS物件轉為網址查詢參數
          const query = new URLSearchParams({
            type: appealData.appealType,
            id: appealData.recordId
          });

          //!!!!!!跳轉到申訴分頁 (記得到時候要更換成真的申訴分頁的名稱)
          window.location.href = `complaint.html?${query.toString()}`;
        },

        openReviewModal(reviewData) { //開啟評價燈箱時，帶入對手的相關資訊
          this.reviewTarget = reviewData;
          this.reviewModalOpen = true;
        },

        closeReviewModal() { //關閉評價燈箱
          this.reviewModalOpen = false;
        },

        async submitReview(reviewData) { //提交評價函式
          //抓取來自評價燈箱子元件的評論資料(星等、評論內容)
          
          //找出目前要進行評價的約戰
          const battle = this.battleRecords.find(
            item => item.id === this.reviewTarget.battleId
          );

          if(!battle) {
            return;
          }

          //根據是否已達約戰時間，決定確認訊息
          const now = new Date();
          const battleDate = new Date(battle.battleDateTime);

          let confirmMessage = "確定要送出這則評價嗎？";

          // 尚未到原訂約戰時間時
          if (now < battleDate) {
            confirmMessage =
              "目前尚未到原訂約戰時間，請確認雙方已實際完成約戰後再留下評價。\n確定要繼續送出嗎？";
          }

          const isConfirmed = confirm(confirmMessage);

          if (!isConfirmed) {
            return;
          }

          //將要傳回後端API的資料打包進formData
          const formData = new FormData();
          formData.append("battle_id", this.reviewTarget.battleId);
          formData.append("stars", reviewData.rating);
          formData.append("comment", reviewData.comment);

          try {
            const response = await fetch(`${this.phpBaseUrl}/battle/battle_review_post.php`, {
              method: "POST",
              credentials: "include",
              body: formData
            });

            const data = await response.json();

            //當API回傳失敗時
            if (!response.ok || !data.success) {
              alert(data.message || "評價送出失敗");
              return;
            }

            //寫入評價成功，顯示成功提示文字、關閉燈箱，並再次更新最新約戰紀錄
            alert(data.message);
            this.closeReviewModal();
            await this.fetchMyBattles();


          } catch (error) {
            console.error("送出評價失敗：", error);
            alert("系統發生錯誤，請稍後再試");
          }
        }
    }
  };
</script>

<style lang="scss" scoped>

  .my-battle {
    width: 100%;
    min-width: 0;
    padding: 4px 0 36px;

    display: flex;
    flex-direction: column;
    gap:32px;

    background-color: #fcfcfc;
    color: #141c26;
  }

  h2 {
    color: #f29b00;
    font-size: 30px;
    font-weight: 700;

    margin-bottom: 12px;
  }

  p {
    color: #141c26;
    font-size: 18px;
  }

  .battle-controls {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  // 發起人參加人區塊切換
  .battle-role-tabs {
    display: flex;
    gap: 12px;
  }

  .role-tab {
    color: rgba(20, 28, 38, 0.5);
    font-size: 16px;
    line-height: 1.5;

    background-color: transparent;
    padding: 0 12px;
    border: 0;
    border-left: 3px solid rgba(20, 28, 38, 0.5);
    cursor: pointer;

    // 點擊切換時的樣式
    &.active {
    border-left-color: #f29b00;
    color: #141c26;
    }
  }

  //卡片狀態篩選列
  .battle-status-tabs {
    display: flex;
    gap: 40px;
    border-bottom: 1px solid rgba(20, 28, 38, 0.5);
    padding-bottom: 12px;
  }

  .status-tab {
    padding: 0;
    border: 0;
    background: transparent;

    color: rgba(20, 28, 38, 0.5);
    font-size: 18px;
    line-height: 1.5;

    cursor: pointer;

    &.active {
      color: #141c26;
    }
  }

  //卡片記錄列表
  .my-battle-records {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 28px;

    max-height: 780px; //桌機模式瀏覽下，約最多呈現三筆紀錄後，出現垂直卷軸
    overflow-y: auto;
    padding-right: 12px;
  }

  // 沒有約戰紀錄時的空狀態
  .battle-empty {
    width: 100%;
    min-height: 200px;
    padding: 40px 24px;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;

    // border: 1px dashed #d8d2ca;
    border: 1px dashed rgba(242, 155, 0, 0.35);
    border-radius: 20px;
    // background-color: #ffffff;
    background-color: #fffaf2;

    text-align: center;

    i {
      color: #f29b00;
      font-size: 34px;
    }
  }

  .empty-message {
    color: #64748b;
    font-size: 18px;
    line-height: 1.5;
  }

  //完全沒約戰紀錄時的導覽連結
  .empty-link {
    margin-top: 12px;
    padding: 12px 24px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 12px;
    text-decoration: none;
    font-size: 18px;
    font-weight: 600;
  }

  // ====================== RWD調整 ============================
  
  //約戰紀錄改橫向排列
  @media screen and (max-width: 900px) {
  .my-battle-records {
    max-height: none; //清除桌機模式下的高度限制設定
    width: 100%;

    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    gap: 24px;

    overflow-x: auto;
    overflow-y: hidden;

    padding: 24px 16px;
    background-color: #fffaf2;
    border-radius: 8px;
    scroll-behavior: smooth;
  }

  .my-battle-records > * {
    flex: 0 0 380px;
  }

  .battle-empty {
    flex: 0 0 100%;
  }
  }

  @media screen and (max-width: 576px) {
  
    .my-battle-records > * {
      flex: 0 0 calc(100vw - 100px);
    }

    //約戰狀態篩選列改 auto scroll
    .battle-status-tabs {
      width: 100%;
      display: flex;
      flex-wrap: nowrap;
      gap: 24px;

      overflow-x: auto;
      overflow-y: hidden;

      white-space: nowrap;
      padding-bottom: 12px;
    }

    .status-tab {
      flex: 0 0 auto;
      white-space: nowrap;
    }
  }

  @media screen and (max-width: 375px) {

    .my-battle-records {
      padding-inline: 12px;
      gap: 16px;
    }

    // .my-battle-records > * {
    //   flex: 0 0 calc(100vw - 48px);
    // }

    .battle-status-tabs {
      gap: 20px;
      padding-block: 8px;
    }

    .status-tab {
      font-size: 16px;
    }
  }

</style>

