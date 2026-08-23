<template>
  <!-- 約戰紀錄下半部資訊:待確認狀態 -->
  <div class="pending-content">
      <!-- 會員是發起人，顯示是否接受約戰 -->
    <div v-if="battle.role === 'initiator'">
      <div class="pending-row">
        <button type="button" class="pending-user" @click="$emit('open-history', battle.participantId)">
          <!-- 顯示申請人頭像 -->
          <img 
            :src="battle.participantAvatar" 
            :alt="battle.participantName"
          >
          <span>
            申請人：{{ battle.participantName }}
          </span>
        </button>
        <p class="pending-message">
          已收到參加申請，請確認是否接受此次約戰。
        </p>
      </div>

      <!-- 回覆按鈕 -->
      <div class="pending-actions">
        <button type="button" class="pending-btn btnFill" @click="$emit('accept-battle', battle.id)">
          接受申請
        </button>

        <button type="button" class="pending-btn btnNoFill" @click="$emit('reject-battle', battle.id)">
          無法赴約
        </button>

      </div>

    </div>

    <!-- 會員是參加人，則顯示待發起人確認 -->
    <div v-else-if="battle.role === 'participant'">

      <div class="pending-row">
        <button type="button" class="pending-user" @click="$emit('open-history', battle.initiatorId)">
          <!-- 顯示發起人頭像 -->
          <img 
            :src="battle.initiatorAvatar" 
            :alt="battle.initiatorName"
          >
          <span>
            發起人：{{ battle.initiatorName }}
          </span>
        </button>
        <p class="pending-message">
          申請已送出，正等待發起人確認。
        </p>
      </div>

    </div>
  </div>
</template>

<script>
  export default {
    name: "BattlePendingContent",

    props: {
      battle: {
        type: Object,
        required: true
      }
    },

    emits: [
      "open-history", //打開燈箱事件
      "accept-battle", //接受對戰申請事件
      "reject-battle" //拒絕對戰申請事件
    ],
  };
</script>

<style lang="scss" scoped>
  .pending-content {
    width: 100%;
  }

  .pending-row {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 28px;
  }

  .pending-message {
    width: fit-content;
    max-width: 100%;
    margin: 0;
    padding: 10px 12px;

    border-left: 1px solid #dddddd;
    border-radius: 10px;
    background-color: #fff7e5;

    color: #575555;
    font-size: 16px;
    line-height: 1.5;
  }

  //會員頭像燈箱按鈕
  .pending-user {
    display: flex;
    align-items: center;
    gap: 8px;
    // margin-bottom: 8px;
    margin-bottom: 0px;
    flex-shrink: 0;
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

  // 按鈕樣式設定
  .pending-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 16px;
  }

  .pending-btn {
    min-width: 96px;
    padding: 6px 16px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 500;
  }

  // ===================== RWD調整 ==========================

  //約戰紀錄下方資訊改上下單欄呈現
  @media screen and (max-width: 900px) {
    .pending-content {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-evenly;
    }

    .pending-row {
      flex-direction: column;
      align-items: stretch;
      gap: 12px;
    }

    .pending-user {
      width: fit-content;
    }

    .pending-message {
      width: 100%;
    }

    .pending-actions {
      width: 100%;
      justify-content: flex-end;
      margin-top: 20px;
    }
  }

</style>