<template>
  <!-- 約戰紀錄下半部資訊:已確認狀態 -->
  <div class="confirmed-content">

    <!-- 已確認後顯示的詳細資訊；依照會員身分為發起人/參加人，顯示另一方的資訊 -->
    <div class="confirmed-details" :class="{ 'is-casual': battle.mode === 'casual' }">
      <!-- 對戰玩家頭像/暱稱 -->
      <div class="confirmed-player">
        <span class="detail-label">對戰玩家</span>
        <button
          type="button"
          class="confirmed-user"
          @click="$emit('open-history', opponentId)"
        >
          <img 
            :src="`${baseUrl}${opponentAvatar}`" 
            :alt="opponentName"
          >
          <span>{{ opponentName }}</span>
        </button>

        <button 
          type="button" 
          class="btnNeutral review-btn" 
          @click="openReview"
          :disabled="battle.hasReviewed"
        >
          {{ battle.hasReviewed ? "已評價" : "評價對方" }} 
        </button>
      </div>

      <!-- 集合地點 -->
      <div class="confirmed-location">
        <span class="detail-label">詳細集合地點</span>
        <p>{{ battle.meetingPlace }}</p>
      </div>

      <!-- 對方聯絡方式 -->
      <div class="confirmed-contact">
        <span class="detail-label">聯絡方式</span>
        <p>對方（{{ opponentName }}）</p>
        <p>{{ opponentContact }}</p>
      </div>

      <!-- 對戰結果顯示: 只有在競技模式時才顯示 -->
      <div v-if="battle.mode === 'competitive'" class="confirmed-result">
        
        <!-- 已經有勝者結果時 -->
        <template v-if="battle.winner">
          <span class="detail-label">對戰結果</span>
          <p class="result-status">
            勝者：
            {{
              battle.winner === "initiator"
                ? battle.initiatorName
                : battle.participantName
            }}
          </p>
          <p class="result-hint">
            本次對戰結果已完成回填
          </p>
        </template>

        <!-- 尚未回填，且目前會員為發起人時 -->
        <template v-else-if="battle.role === 'initiator'">
          <span class="result-title">請回傳本次對戰勝者</span>
          <!-- v-model雙向綁定所選勝者人選，這樣可以同步影響vue中存的data -->
          <label class="result-option">
            <input v-model="selectedWinner" type="radio" value="initiator">
            <span>我（{{ battle.initiatorName }}）</span>
          </label>

          <label class="result-option">
            <input v-model="selectedWinner" type="radio" value="participant">
            <span>對方（{{ opponentName }}）</span>
          </label>

          <button type="button" 
            class="btnFill result-submit-btn"
            @click="submitResult"
          >
            提交結果
          </button>
        </template>

        <!-- 尚未回填，且目前會員為參加人時 -->
        <template v-else>
          <span class="detail-label">對戰結果</span>
          <p class="result-status">等待發起人回傳勝者</p>
          <p class="result-hint">對戰結束後，將由發起人回填勝者資訊</p>
        </template>

      </div>

    </div>

  </div>
</template>

<script>
  export default {
    name: "BattleConfirmedContent",

    data() {
      return {
        baseUrl: import.meta.env.BASE_URL,
        selectedWinner: ""
      };
    },

    props: {
      battle: {
        type: Object,
        required: true
      }
    },

    emits: [
      "open-history",
      "submit-result",
      "open-review"
    ],

    computed: {
      opponentId() { //判斷當下會員是約戰的發起人還是參加人，再顯示另一方的資訊
        return this.battle.role === "initiator"
          ? this.battle.participantId
          : this.battle.initiatorId;
      },

      opponentName() {
        return this.battle.role === "initiator"
          ? this.battle.participantName
          : this.battle.initiatorName;
      },

      opponentAvatar() {
        return this.battle.role === "initiator"
          ? this.battle.participantAvatar
          : this.battle.initiatorAvatar;
      },

      opponentContact() {
        return this.battle.role === "initiator"
          ? this.battle.participantContact
          : this.battle.initiatorContact;
      }
    },

    methods: {
      submitResult() { //回傳勝者函式
        if (!this.selectedWinner) {
          alert("請先選擇本次對戰勝者");
          return;
        }

        const isConfirmed = confirm("確定提交此次對戰結果嗎？");

        if (!isConfirmed) return;

        this.$emit("submit-result", {
          battleId: this.battle.id, //傳出此筆約戰紀錄id
          winner: this.selectedWinner //勝者
        });
      },

      openReview() { //傳出此筆約戰紀錄id、對手id、對手名稱、頭像連結，供父元件打開評價燈箱時套用
        this.$emit("open-review", {
          battleId: this.battle.id,
          opponentId: this.opponentId,
          opponentName: this.opponentName,
          opponentAvatar: this.opponentAvatar
        });
      }

    }

  };
</script>

<style lang="scss" scoped>
  .confirmed-content {
    width: 100%;
  }

  .confirmed-user {
    display: flex;
    align-items: center;
    gap: 8px;

    margin-bottom: 8px;
    padding: 0;
    border: 0;
    background: transparent;

    color: #141c26;
    font: inherit;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;

    img {
      width: 40px;
      height: 40px;
      display: block;
      flex-shrink: 0;
      border-radius: 50%;
      object-fit: cover;
    }

    &:hover span {
      text-decoration: underline;
    }
  }

  .confirmed-message {
    color: #64748b;
    font-size: 16px;
  }

  //確認後顯示的對方資訊區塊
  .confirmed-details {
    width: 100%;
    display: grid;
    grid-template-columns:
      minmax(120px, 1fr)
      minmax(0, 1fr)
      minmax(0, 1.1fr)
      minmax(168px, 1.3fr);
    align-items: stretch;
    // align-items: flex-start;
    gap: 12px;
    padding-block: 4px;
  }

  //當約戰模式為休閒時，因底部資訊只剩三欄，另增加對應class，調整排版
  .confirmed-details.is-casual {
    grid-template-columns:
      minmax(160px, 1fr)
      minmax(0, 1.3fr)
      minmax(0, 1.3fr);
  }

  .confirmed-player,
  .confirmed-location,
  .confirmed-contact,
  .confirmed-result {
    min-width: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;
  }

  .confirmed-player {
    min-width: 0;
  }

  .confirmed-location {
    min-width: 0;
    padding-left: 12px;
    border-left: 1px solid #dddddd;
  }

  .confirmed-contact {
    min-width: 0;
    padding-left: 12px;
    border-left: 1px solid #dddddd;
  }

  .detail-label {
    color: #64748b;
    font-size: 18px;
    font-weight: 400;
  }

  .confirmed-location p,
  .confirmed-contact p {
    margin: 0;
    color: #141c26;
    font-size: 16px;
    line-height: 1.5;
  }

  .confirmed-location p,
  .confirmed-contact p,
  .result-status,
  .result-hint {
    overflow-wrap: anywhere; //當文字內容過長時，允許在任何適合的位置做斷行
  }

  //確認底部顯示的對方頭像按鈕
  .confirmed-details .confirmed-user {
    margin-bottom: 0;
    font-size: 18px;
    font-weight: 400;

    img {
      width: 32px;
      height: 32px;
    }
  }

  .review-btn {
    align-self: flex-start;
    padding: 4px 16px;
    font-size: 16px;
  }

  //對戰結果顯示區
  .confirmed-result {
    min-width: 0;
    padding: 8px;

    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    gap: 8px;

    border: 1px solid rgba(254, 201, 107, 0.65);
    border-radius: 10px;
    background-color: #fff7e5;
  }

  .result-title,
  .result-status {
    margin: 0;
    color: #141c26;
    font-size: 16px;
    line-height: 1.5;
  }

  .result-option {
    display: flex;
    align-items: center;
    gap: 8px;

    color: #141c26;
    font-size: 16px;
    cursor: pointer;

    input {
      width: 16px;
      height: 16px;
      margin: 0;
      cursor: pointer;
    }
  }

  .result-hint {
    margin: 0;
    color: #575555;
    font-size: 16px;
    line-height: 1.5;
  }

  .result-submit-btn {
    align-self: center;
    padding: 4px 16px;
    font-size: 16px;
  }

  .review-btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
  }

  // ================ RWD調整 ==========================
@media screen and (max-width: 1180px) {
  .confirmed-details {
      grid-template-columns:
        minmax(100px, 0.85fr)
        minmax(0, 1fr)
        minmax(0, 1fr)
        minmax(158px, 1.2fr);

      gap: 8px;
    }

    .confirmed-location,
    .confirmed-contact {
      padding-left: 8px;
    }

    .detail-label {
      font-size: 16px;
    }

    .confirmed-details .confirmed-user {
      font-size: 16px;
    }

    .confirmed-location p,
    .confirmed-contact p,
    .result-title,
    .result-status,
    .result-option,
    .result-hint {
      font-size: 14px;
    }

    .review-btn,
    .result-submit-btn {
      padding: 4px 12px;
      font-size: 14px;
    }

    .confirmed-result {
      padding: 8px 6px;
      gap: 6px;
    }
  }

  //約戰紀錄下半部資訊改單欄呈現
  @media screen and (max-width: 900px) {
    .confirmed-details,
    .confirmed-details.is-casual {
      grid-template-columns: 1fr;
      gap: 0;
    }

    .confirmed-player,
    .confirmed-location,
    .confirmed-contact,
    .confirmed-result {
      width: 100%;
      padding: 16px 12px;
    }

    .confirmed-location,
    .confirmed-contact {
      border-left: 0;
      border-top: 1px solid #dddddd;
    }

    /* 對戰玩家區 */
    .confirmed-player {
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      gap: 8px 16px;
    }

    .confirmed-player .detail-label {
      grid-column: 1 / -1;
    }

    .confirmed-user {
      grid-column: 1;
      justify-self: start;
      margin-bottom: 0;
    }

    .review-btn {
      grid-column: 2;
      justify-self: end;
      align-self: center;
      margin-left: 0;
    }

    /* 集合地點 */
    .confirmed-location {
      display: block;
    }

    /* 聯絡方式 */
    .confirmed-contact {
      display: block;
    }

    /* 對戰結果區 */
    .confirmed-result {
      display: flex;
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }

    .result-option {
      align-self: flex-start;
    }

    .result-submit-btn {
      align-self: flex-end;
      margin-top: 8px;
    }
  }

</style>