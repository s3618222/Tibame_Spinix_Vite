<template>
  <div class="member-history-modal">
    <div class="history-mask" @click="$emit('close')"></div>

    <div class="history-main">
      <button type="button" class="close-modal-btn" @click="$emit('close')">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <div class="history-header">
        <img
          :src="member.avatar"
          :alt="`${member.name} 的會員頭像`"
          class="history-avatar"
        >

        <div>
          <h3><span>{{ member.name }}</span>的約戰參考資訊</h3>
          <p>加入前可先快速了解對方的過往紀錄</p>
        </div>
      </div>

      <!-- 約戰統計資訊 -->
      <div class="member-stat-list">

        <!-- 約戰總場次 -->
        <div class="member-stat">
          <p>約戰總場次</p>

          <strong>
            {{ member.totalBattles }}
            <span>場</span>
          </strong>
        </div>

        <!-- 平均約戰評價 -->
        <div class="member-stat">
          <p>平均約戰評價</p>

          <strong>
            {{ formattedAverageRating }}
            <span
              v-if="member.averageRating !== null"
              class="rating-star"
            >
              ★
            </span>
          </strong>
        </div>

        <!-- 競技勝率 -->
        <div class="member-stat">
          <p>競技勝率</p>

          <strong>
            {{ formattedWinRate }}
          </strong>

          <span class="competitive-record">
            {{ competitiveRecord }}
          </span>
        </div>

      </div>

      <!-- 過往評價列表 -->
      <section class="history-review">
        <h4>近期約戰評價</h4>
        <div class="review-list">
          <article v-for="review in member.reviews" :key="review.reviewId" class="review-item">
            <div class="review-info">
              <span class="reviewer-name">{{ review.reviewerName }}</span>
              <span class="review-rating">
                {{ review.rating }}
                <span class="rating-star">★</span>
              </span>
            </div>

            <p class="review-content">{{ review.content }}</p>

            <span class="review-date">{{ review.createdAt }}</span>
          </article>
        </div>
      </section>

    </div>
  </div>
</template>

<script>
  export default {
    name: "HistoryModal",

    data() {
      return {
        baseUrl: import.meta.env.BASE_URL
      };
    },

    props: {
      member: {
        type: Object,
        required: true
      }
    },

    computed: {

      // 平均評價顯示格式
      formattedAverageRating() {
        if (this.member.averageRating === null) {
        return "--";
        }

        return Number(this.member.averageRating).toFixed(1);
      },

      // 競技勝率顯示格式
      formattedWinRate() {
        if (this.member.winRate === null) {
          return "--";
        }

        return `${Math.round(this.member.winRate)}%`;
      },

        // 競技戰績顯示格式
      competitiveRecord() {
        if (this.member.winRate === null) {
          return "尚無紀錄";
        }

        return `${this.member.competitiveWins} 勝 / ${this.member.competitiveTotal} 場`;
      }

    },

    emits: ["close"],

    mounted() {
      window.addEventListener("keydown", this.handleKeydown);
    },

    beforeUnmount() {
      window.removeEventListener("keydown", this.handleKeydown);
    },

    methods: {
      handleKeydown(event) {
        if (event.key === "Escape") {
          this.$emit("close"); //如果會員打開燈箱後，有按esc，就對父元件傳出close事件；該事件已在父元件上設地定會執行關閉燈箱的函式
        }
      }
    }
  };
</script>

<style lang="scss" scoped>

  .member-history-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;

    display: flex;
    align-items: center;
    justify-content: center;
  }

  .history-mask {
    position: absolute;
    inset: 0;
    background-color: rgba(20, 28, 38, 0.58);
  }

  .history-main {
    position: relative;
    z-index: 1;

    width: min(500px, calc(100% - 32px));
    max-height: calc(100vh - 48px);
    padding: 26px;

    display: flex;
    flex-direction: column;
    gap: 28px;

    background-color: #f7f5f3;
    border: 1px solid #ffffff;
    border-radius: 14px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.16);
  }

  .member-stat-list {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
  }

  .member-stat {
    min-height: 88px;
    padding: 12px 16px;

    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 8px;

    background-color: #ffffff;
    border: 1px solid #aaaaaa;
    border-radius: 10px;

    p {
      color: #3a3a3a;
      font-size: 14px;
    }

    strong {
      color: #141c26;
      font-size: 26px;
      line-height: 1.2;
    }
  }

  .competitive-record {
    color: #64748b;
    font-size: 12px;
  }

  .rating-star {
    color: #f29b00;
  }

  .close-modal-btn {
    position: absolute;
    top: 18px;
    right: 18px;

    width: 36px;
    height: 36px;

    border: 0;
    background-color: transparent;
    color: #64748b;
    font-size: 22px;
    cursor: pointer;
  }

  .history-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-right: 24px;

    h3 {
      color: #141c26;
      font-size: 20px;
      line-height: 1.4;

      span {
        color: #f29b00;
      }
    }

    p {
      margin-top: 8px;
      color: #64748b;
      font-size: 14px;
    }
  }

  .history-avatar {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
    border-radius: 50%;
    object-fit: cover;
  }

  @media screen and (max-width: 460px) {

    .member-stat-list {
      grid-template-columns: 1fr;
      gap: 16px;
    }
  }

</style>