<template>
  <!-- 約戰紀錄下半部資訊:已取消狀態 -->
  <div class="cancelled-content">

    <div class="cancelled-row">
      <!-- 會員是發起人，顯示參加人資訊 -->
      <button v-if="battle.role === 'initiator'" type="button" class="cancelled-user" @click="$emit('open-history', battle.participantId)">
        <img :src="battle.participantAvatar" :alt="battle.participantName">
        <span>
          參加人：{{ battle.participantName }}
        </span>
      </button>

      <!-- 會員是參加人，顯示發起人資訊 -->
      <button v-else-if="battle.role === 'participant'" type="button" class="cancelled-user" @click="$emit('open-history', battle.initiatorId)">
        <img :src="battle.initiatorAvatar" :alt="battle.initiatorName">
        <span>
          發起人：{{ battle.initiatorName }}
        </span>
      </button>

      <div class="cancelled-message">
        <i class="fa-solid fa-circle-xmark"></i>
        <p>此次約戰已取消。</p>
      </div>
    </div>

  </div>
</template>

<script>
  export default {
    name: "BattleCancelledContent",

    props: {
      battle: {
        type: Object,
        required: true
      }
    },

    emits: [
      "open-history"
    ]
  };
</script>

<style lang="scss" scoped>
  .cancelled-content {
    width: 100%;
  }

  .cancelled-row {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 28px;
  }

  .cancelled-user {
    display: flex;
    align-items: center;
    gap: 8px;

    margin-bottom: 0;
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

  // .cancelled-message {
  //   display: flex;
  //   align-items: center;
  //   gap: 8px;
  //   color: #64748b;
  //   font-size: 16px;

  //   i {
  //     color: #e63946;
  //     font-size: 18px;
  //   }
  // }

  .cancelled-message {
    width: fit-content;
    max-width: 100%;
    margin: 0;
    padding: 10px 12px;

    display: flex;
    align-items: center;
    gap: 8px;

    border-radius: 10px;
    background-color: #fcebed;

    color: #a72b36;
    font-size: 16px;
    line-height: 1.5;

    p {
      margin: 0;
    }

    i {
      flex-shrink: 0;
      color: #e63946;
      font-size: 18px;
    }
  }

  // ========================= RWD調整 =============================
  
  //約戰紀錄下方資訊改單欄呈現
  @media screen and (max-width: 900px) {
    .cancelled-content {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-evenly;
    }

    .cancelled-row {
      flex-direction: column;
      align-items: stretch;
      gap: 12px;
    }

    .cancelled-user {
      width: fit-content;
    }

    .cancelled-message {
      width: 100%;
    }
  }

</style>