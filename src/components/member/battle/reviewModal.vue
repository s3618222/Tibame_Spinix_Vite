<template>
  <!-- 外圍遮罩；.self → 只有點擊遮罩本身時才會觸發關閉燈箱 -->
  <div class="review-overlay" @click.self="closeModal">
    <!-- 評價燈箱本體 -->
    <div class="review-modal">
      <button type="button" class="modal-close" @click="closeModal">×</button>

      <!-- 評價燈箱標題 -->
      <div class="modal-heading">
        <h2>評價對方</h2>
        <p>這次的約戰體驗如何？給對方一個真實的評價吧。</p>
      </div>

      <!-- 星等評分 -->
      <div class="review-section">
        <h3 class="section-title">星等評分</h3>
        <!-- 星星列表 -->
        <div class="star-list">
          <!-- 當目前星星數字小於或等於使用者選擇的分數時，就套上 active class -->
          <!-- rating = star → 使用者點第幾顆星星，rating 評分就存成多少。 -->
          <button
            v-for="star in 5"
            :key="star"
            type="button"
            class="star-btn"
            :class="{ active: star <= rating }"
            @click="rating = star"
          >
            {{ star <= rating ? "★" : "☆" }}
          </button>
        </div>
      </div>

      <!-- 評論輸入框 -->
      <div class="review-section">
        <h3 class="section-title">評論內容</h3>
        <!-- 雙向綁定、存入使用者輸入的評論內容 -->
        <textarea
          v-model.trim="comment"
          maxlength="250"
          placeholder="可以簡短留下對方是否準時、溝通是否清楚、現場體驗是否良好。"
        ></textarea>
      </div>

      <!-- 操作按鈕區 -->
      <div class="modal-actions">
        <button 
          type="button" 
          class="btnFill"
          @click="submitReview">送出評論</button>
        <button type="button" class="btnNoFill" @click="closeModal">下次再說</button>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  name: "ReviewModal",

  emits: [
    "close",
    "submit-review"
  ],

  data() {
    return {
      //燈箱內自己的資料：評分、評論內容
      rating: 0,
      comment: ""
    };
  },

  methods: {
    closeModal() { //關閉燈箱函式，emit通知父元件close事件，再由父元件執行關閉燈箱動作的實際函式
      this.$emit("close");
    },

    submitReview() { //提交評價函式
      if (this.rating === 0) { //最低必須選1
        alert("請先選擇星等");
        return;
      }

      const isConfirmed = confirm("確定要送出這則評價嗎？");

      if (!isConfirmed) return;

      this.$emit("submit-review", { 
        //提交評價時，往父元件送出評分跟評論內容資料
        rating: this.rating,
        comment: this.comment
      });
    }
  }
};
</script>

<style lang="scss" scoped>
  // 外圍遮罩
  .review-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 24px;
    background-color: rgba(0, 0, 0, 0.24);
  }

  //燈箱本體
  .review-modal {
    position: relative;
    width: min(452px, 100%);
    padding: 28px;
    border: 1px solid #ffffff;
    border-radius: 12px;
    background-color: #f7f5f3;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.18);
  }

  //關閉按鈕
  .modal-close {
    position: absolute;
    top: 18px;
    right: 24px;

    padding: 0;
    border: 0;
    background: transparent;

    color: #6b6b6b;
    font-size: 30px;
    line-height: 1;
    cursor: pointer;
  }

  .modal-heading {
    padding-right: 40px;

    h2 {
      margin: 0;
      color: #141c26;
      font-size: 22px;
      font-weight: 700;
    }

    p {
      margin: 12px 0 0;
      color: #64748b;
      font-size: 16px;
      line-height: 1.5;
    }
  }

  .review-section {
    margin-top: 28px;
  }

  .section-title {
    position: relative;
    margin: 0 0 12px;
    padding-left: 12px;

    color: #141c26;
    font-size: 18px;
    font-weight: 700;

    //左側橘色長條偽元素
    &::before {
      content: "";
      position: absolute;
      top: 0px;
      bottom: 0px;
      left: 0;

      width: 5px;
      border-radius: 3px;
      background-color: #fec96b;
    }
  }

  //星星評等列表
  .star-list {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  //單顆星星樣式
  .star-btn {
    padding: 0;
    border: 0;
    background: transparent;

    color: #141c26;
    font-size: 40px;
    line-height: 1;
    cursor: pointer;

    &.active {
      color: #f29b00;
    }
  }

  //評論輸入文字框
  textarea {
    width: 100%;
    min-height: 86px;
    padding: 12px;
    resize: vertical;

    border: 1px solid #b3b3b3;
    border-radius: 8px;
    background-color: #ffffff;

    color: #141c26;
    font: inherit;
    font-size: 14px;
    line-height: 1.5;

    &::placeholder {
      color: #64748b;
    }

    &:focus {
      border-color: #fec96b;
      outline: none;
    }
  }

  // 操作按鈕區
  .modal-actions {
    margin-top: 40px;

    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 20px;

    button {
      padding: 6px 18px;
      font-size: 14px;
    }
  }
</style>