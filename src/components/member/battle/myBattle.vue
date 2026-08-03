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
          已取消 <span>({{ statusCounts.cancelled }})</span>
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
          href="./battle.html"
          class="empty-link btnFill"
        >
          前往約戰配對
        </a>

        <a
          v-else-if="currentStatus === 'all' && currentRole === 'initiator'"
          href="./createBattle.html"
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
        currentRole: "initiator", //預設顯示作為發起人的紀錄區
        currentStatus: "all", //預設顯示全部的紀錄卡
        isHistoryModalOpen: false, //歷史燈箱是否開啟，預設關閉
        reviewModalOpen: false, // 評價燈箱是否開啟，預設關閉
        selectedMember: null, //燈箱要顯示的會員

        //對戰紀錄假資料
        battleRecords: [
          //發起的約戰
          {
            id: 1,
            role: "initiator",

            initiatorId: 100,
            participantId: 101,

            initiatorName: "Bill0714",
            participantName: "陀螺小宇",

            initiatorAvatar: "/spinix_member_default.png",
            participantAvatar: "/spinix_member_default.png",

            status: "pending",
            title: "新手友善！一起開心打陀螺",
            battleDate: "2026/08/15",
            weekday: "六",
            battleTime: "14:00",
            city: "桃園市",
            district: "中壢區",
            coverImage: "/battle_card_default.jpg",
            target: "不限對象",

            meetingPlace: "桃園市中壢區中央西路一段",
            initiatorContact: "LINE：bill0714",
            participantContact: "LINE：spin_yu101"
          },
          {
            id: 2,
            role: "initiator",

            initiatorId: 100,
            participantId: 101,

            initiatorName: "Bill0714",
            participantName: "陀螺殺手",

            initiatorAvatar: "/spinix_member_default.png",
            participantAvatar: "/spinix_member_test1.png",

            status: "confirmed",
            title: "週末競技交流場",
            battleDate: "2026/08/20",
            weekday: "四",
            battleTime: "10:00",
            city: "新北市",
            district: "板橋區",
            coverImage: "/battle_card_test1.jpg",
            target: "不限對象",

            meetingPlace: "新北板橋火車站一樓北3門外",
            initiatorContact: "LINE：bill0714",
            participantContact: "LINE：Liao_1701"
          },
          {
            id: 3,
            role: "initiator",

            initiatorName: "Bill0714",
            participantName: "打到你怕",

            initiatorId: 100,
            participantId: 101,

            initiatorAvatar: "/spinix_member_default.png",
            participantAvatar: "/spinix_member_default.png",

            status: "cancelled",
            title: "下班後來場陀螺對戰吧",
            battleDate: "2026/08/25",
            weekday: "二",
            battleTime: "19:00",
            city: "桃園市",
            district: "八德區",
            coverImage: "/battle_card_default.jpg",
            target: "成人限定"
          },
          //參加的約戰紀錄
          {
            id: 4,
            role: "participant",

            initiatorName: "飛天粉紅豬",
            participantName: "Bill0714",

            initiatorId: 101,
            participantId: 100,

            initiatorAvatar: "/spinix_member_test2.jpg",
            participantAvatar: "/spinix_member_default.png",

            status: "pending",
            title: "桃園玩家友誼交流戰",
            battleDate: "2026/08/17",
            weekday: "一",
            battleTime: "20:00",
            city: "桃園市",
            district: "中壢區",
            coverImage: "/battle_card_test2.jpg",
            target: "親子友善",

            meetingPlace: "桃園市中壢區中央西路一段",
            initiatorContact: "Discord：pink_flying_pig",
            participantContact: "LINE：bill0714"
          },
          {
            id: 5,
            role: "participant",

            initiatorId: 101,
            participantId: 100,

            initiatorName: "陀螺著火啦",
            participantName: "Bill0714",

            initiatorAvatar: "/spinix_member_default.png",
            participantAvatar: "/spinix_member_default.png",

            status: "confirmed",
            title: "中壢車站陀螺挑戰",
            battleDate: "2026/08/13",
            weekday: "六",
            battleTime: "21:00",
            city: "桃園市",
            district: "中壢區",
            coverImage: "/battle_card_default.jpg",
            target: "不限對象",

            meetingPlace: "桃園市中壢區中壢火車站大廳",
            initiatorContact: "手機：0979455781",
            participantContact: "LINE：bill0714"
          },
          {
            id: 6,
            role: "participant",

            initiatorId: 101,
            participantId: 100,

            initiatorName: "擱共",
            participantName: "Bill0714",

            initiatorAvatar: "/spinix_member_test3.png",
            participantAvatar: "/spinix_member_default.png",
            
            status: "cancelled",
            title: "成人限定競技對戰",
            battleDate: "2026/08/23",
            weekday: "日",
            battleTime: "15:00",
            city: "台北市",
            district: "中山區",
            coverImage: "/battle_card_default.jpg",
            target: "成人限定"
          }
        ],

        //約戰歷史燈箱假資料
        memberHistoryData: { 
          101: {
            memberId: 101,
            name: "陀螺小宇",
            avatar: "/spinix_member_default.png",
            totalBattles: 18,
            averageRating: 4.7,
            reviews: [
              {
                reviewId: 1001,
                reviewerName: "BladeKen",
                rating: 5,
                content: "約戰很準時，現場交流氣氛很好！",
                createdAt: "2026-07-15"
              },
              {
                reviewId: 1002,
                reviewerName: "阿哲",
                rating: 4,
                content: "場地資訊說明清楚，下次還會想再約。",
                createdAt: "2026-07-08"
              },
              {
                reviewId: 1003,
                reviewerName: "Ray",
                rating: 5,
                content: "交流過程很友善，整體體驗很好。",
                createdAt: "2026-06-29"
              }
            ]
          }
        },

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

    computed: {
      roleBattles() { //計算不同角色時的卡片數量
        return this.battleRecords.filter((battle) => {
          return battle.role == this.currentRole;
        });
      },

      statusCounts() { //計算不同狀態下的卡片數量
        return {
          all: this.roleBattles.length,

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
      }
    },

    methods: {
        changeRole(role) {
          this.currentRole = role;
          this.currentStatus = "all"; //切換腳色的同時，卡片狀態預設顯示全部
        },
        changeStatus(status) {
          this.currentStatus = status;
        },
        closeHistoryModal() {
          this.isHistoryModalOpen = false;
          this.selectedMember = null; //關閉燈箱時，同時清空資料
        },
        openHistoryModal(memberId) { //打開約戰歷史燈箱
          this.selectedMember = this.memberHistoryData[memberId]; //抓取指定會員的約戰歷史紀錄

          if (!this.selectedMember) {
            return;
          }

          this.isHistoryModalOpen = true;
        },
        acceptBattle(battleId) { //接受申請對戰函式
          const isConfirmed = confirm(
              "確定要接受這位會員的約戰申請嗎？"
            );

            if (!isConfirmed) {
              return;
            };

          const battle = this.battleRecords.find((item) => { //利用對戰id找到該筆對戰資料
            return item.id === battleId;
          });

          if (!battle) {
            return;
          }

          battle.status = "confirmed"; //將對戰狀態更改為已確認
        },

        rejectBattle(battleId) { //拒絕申請函式
          const isConfirmed = confirm(
            "確定無法參加此次約戰嗎？確認後，此次約戰將會取消。"
          );

            if (!isConfirmed) {
            return;
          };

          const battle = this.battleRecords.find((item) => {
            return item.id === battleId;
          });

          if (!battle) {
            return;
          }

          battle.status = "cancelled"; //將對戰狀態更改為已取消
        },

        submitBattleResult(resultData) { 
          //resultData 為子元件傳過來的資料
          const battle = this.battleRecords.find(
            item => item.id === resultData.battleId
          );

          if (!battle) return;

          battle.winner = resultData.winner;
        },

        goToAppeal(appealData) {
          //送出申訴函式，將子元件傳來的資訊：1.此筆申訴屬約戰類型 2.此筆約戰紀錄的id，攜帶跳轉至申訴分頁

          //將JS物件轉為網址查詢參數
          const query = new URLSearchParams({
            type: appealData.appealType,
            id: appealData.recordId
          });

          //!!!!!!跳轉到申訴分頁 (記得到時候要更換成真的申訴分頁的名稱)
          window.location.href = `appeal.html?${query.toString()}`;
        },

        openReviewModal(reviewData) { //開啟評價燈箱時，帶入對手的相關資訊
          this.reviewTarget = reviewData;
          this.reviewModalOpen = true;
        },

        closeReviewModal() { //關閉評價燈箱
          this.reviewModalOpen = false;
        },

        submitReview(reviewData) { //提交申訴函式
          //抓取來自評價燈箱子元件的申訴資料
          const finalReviewData = {
            battleId: this.reviewTarget.battleId,
            opponentId: this.reviewTarget.opponentId,
            rating: reviewData.rating,
            comment: reviewData.comment
          };

          console.log(finalReviewData);

          //找出資料中目前被評價的約戰紀錄
          const battle = this.battleRecords.find(
            item => item.id === this.reviewTarget.battleId
          );

          if (!battle) return;
          //註記該筆約戰已評分過，避免重複評分
          battle.hasReviewed = true;

          //送出申訴後，同時執行關閉燈箱函式
          this.closeReviewModal();
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
    font-size: 20px;
    line-height: 1.4;

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

</style>

