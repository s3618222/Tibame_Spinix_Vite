<template>
  <article class="battle-record">
    
    <div class="record-main">
      <!-- 卡片封面 -->
      <div class="record-cover">
        <img :src="battle.coverImage" :alt="battle.title">
        <span class="target-tag">{{ battle.target }}</span>
      </div>

      <div class="record-content">
        <!-- 卡片標題 -->
        <div class="record-heading">
          <p class="record-title">
            {{ battle.title }}
            <!-- 約戰模式備註文字 -->
            <span class="record-mode">（{{ modeText }}）</span>
          </p>
          <span 
            class="record-status"
            :class="`status-${battle.status}`">
            {{ statusText }}
          </span>
        </div>

        <!-- 卡片約戰資訊 -->
        <div class="record-info">
          <p class="record-date">
            <i class="fa-regular fa-calendar"></i>
            {{ battle.battleDate }}（{{ battle.weekday }}）{{ battle.battleTime }}
          </p>

          <p class="record-location">
            <i class="fa-solid fa-location-dot"></i>
            {{ battle.city }}・{{ battle.district }}
          </p>
        </div>

        <!-- 卡片下半部資訊 -->
        <div class="record-detail">
          <!-- 依卡片狀態，決定要呈現的資訊元件 -->
          <BattlePendingContent 
            v-if="battle.status == 'pending'" 
            :battle="battle"  
            @open-history="$emit('open-history', $event)"
            @accept-battle="$emit('accept-battle', $event)"
            @reject-battle="$emit('reject-battle', $event)"
            >
          </BattlePendingContent>
          <BattleCancelledContent v-if="battle.status == 'cancelled'" :battle="battle" @open-history="$emit('open-history', $event)"></BattleCancelledContent>
          <BattleConfirmedContent 
            v-if="battle.status == 'confirmed'" 
            :battle="battle" 
            @open-history="$emit('open-history', $event)"
            @submit-result="$emit('submit-result', $event)"
            @open-review="$emit('open-review', $event)">
          </BattleConfirmedContent>
        </div>

      </div>
    </div>

    <!-- 已確認狀態下，才顯示申訴區 -->
  <div v-if="battle.status === 'confirmed'" class="record-appeal">
    <div class="appeal-message">
      <i class="fa-regular fa-circle-question"></i>
      <span>對此邀約或對戰過程有任何問題？</span>
    </div>

    <button type="button" class="appeal-btn" @click="openAppeal">
      <span>提出申訴</span>
      <i class="fa-solid fa-arrow-up-right-from-square"></i>
    </button>
  </div>

  </article>
</template>

<script>
  import BattlePendingContent from "./BattlePendingContent.vue";
  import BattleCancelledContent from "./BattleCancelledContent.vue";
  import BattleConfirmedContent from "./BattleConfirmedContent.vue";

  export default {
    name: "BattleRecord",

    components: {
      BattlePendingContent,
      BattleCancelledContent,
      BattleConfirmedContent
    },

    props: { //由父元件myBattle.vue傳進來的約戰紀錄資料，因為有這筆資料，上面的模板才可以寫battle.id、battle.role...
      battle: {
        type: Object,
        required: true
      }
    },

    emits: [
      "open-history", //打開燈箱
      "accept-battle", //接受對戰申請事件
      "reject-battle", //拒絕申請
      "submit-result", //提交對戰結果
      "open-appeal", //提出申訴
      "open-review", //打開評價燈箱
    ],

    computed: {
      statusText() { //將英文狀態資料轉換為中文
        const statusMap = {
          pending: "待確認",
          confirmed: "已確認",
          cancelled: "已取消"
        };

        return statusMap[this.battle.status];
      },

      modeText() {
        const modeMap = { //競技模式文字切換
          competitive: "競技",
          casual: "休閒"
        };

        return modeMap[this.battle.mode] || "";
      }

    },

    methods: {
      openAppeal() { //提出申訴時，將此筆資料的類型(約戰)與此筆約戰紀錄的id送出給父元件
        this.$emit("open-appeal", {
          appealType: "battle",
          recordId: this.battle.id
        });
      }
    }

  };
</script>

<style lang="scss" scoped>
  .battle-record {
    width: 100%;
    padding: 12px;
    border: 1px solid #e3ddd5;
    border-radius: 20px;
    background-color: #ffffff;
    box-shadow: 0 4px 4px rgba(20, 28, 38, 0.07);

    transition: box-shadow 0.24s ease, transform 0.24s ease;

    &:hover {
      box-shadow: 0 6px 12px rgba(20, 28, 38, 0.1);
      transform: translateY(-2px);
    }
  }

  .record-main {
    display: flex;
    // align-items: stretch;
    // align-items: flex-start;
    align-items: center;
    gap: 20px;
  }

  //卡片封面圖
  .record-cover {
    position: relative;
    width: 192px;
    // min-height: 180px;
    height: 184px;
    flex-shrink: 0;
    overflow: hidden;
    border-radius: 12px;

    img {
      width: 100%;
      height: 100%;
      display: block;
      object-fit: cover;
      object-position: center;
    }
  }

  //適合對象標籤
  .target-tag {
    position: absolute;
    top: 8px;
    left: 12px;
    padding: 4px 12px;

    border: 1px solid rgba(254, 201, 107, 0.75);
    border-radius: 16px;
    box-shadow: 0 3px 4px rgba(20, 28, 38, 0.28);

    background-color: #141c26;
    color: #fec96b;
    font-size: 16px;
  }

  .record-content {
    flex: 1;
    min-width: 0;

    display: flex;
    flex-direction: column;
  }

  .record-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
  }

  .record-title {
    color: #141c26;
    font-size: 20px;
    font-weight: 500;

    display: flex;
    align-items: center;
    gap: 4px;
  }

  .record-mode {
    color: #64748b;
    font-size: 16px;
    font-weight: 400;
  }

  .record-status {
    flex-shrink: 0;
    padding: 4px 12px;
    border: 1px solid;
    border-radius: 8px;
    font-size: 16px;
  }
  
  //待確認標籤樣式
  .status-pending {
    border-color: rgba(254, 201, 107, 0.75);
    background-color: #fff2d6;
    color: #a86b00;
  }

    //已確認標籤樣式
  .status-confirmed {
    border-color: rgba(79, 138, 91, 0.35);
    background-color: #e4eee7;
    color: #4f8a5b;
  }

  //已取消標籤樣式
  .status-cancelled {
    border-color: rgba(230, 57, 70, 0.35);
    background-color: #f9d7da;
    color: #e63946;
  }

  .record-info {
    display: flex;
    align-items: center;
    gap: 40px;
    margin-top: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #e3ddd5;
  }

  .record-date,
  .record-location {
    color: #141c26;
    font-size: 18px;
  }

  .record-detail {
    display: flex;
    align-items: center;
    min-height: 100px;
    padding-top: 16px;

    p {
      color: #64748b;
      font-size: 16px;
      line-height: 1.5;
    }
  }

  .record-appeal {
    width: 100%;
    margin-top: 12px;

    display: flex;
    align-items: center;
    gap: 20px;

    color: #141c26;
    font-size: 16px;
  }

  .appeal-message,
  .appeal-btn {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .appeal-btn {
    padding: 0;
    border: 0;
    background: transparent;

    color: #141c26;
    font: inherit;
    text-decoration: underline;
    cursor: pointer;
  }

</style>