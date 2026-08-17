<!-- ActionGroup.vue -->
<template>
  <div class="action-group">
    <button class="btn-download">
      <i class="fa-solid fa-download"></i> 
      <span>下載配方圖片</span>
    </button>
    <button class="btn-reset" @click="handleClick">
      <i class="fa-solid fa-rotate-right"></i>
      <span class="btn-text">再做一顆</span>
    </button>
  </div>
</template>

<script>
export default {
  name: "ActionGroup",

  methods: {
    handleClick() {
      this.$emit('clear-all');
    }
  }
}
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.action-group {
  display: flex;
  gap: 12px;
  margin-top: 16px;

  /* ----------------------------------------------------------------
   * 1. 手機版預設 (< 992px)：fixed 吸附在螢幕最底部
   * ---------------------------------------------------------------- */
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 999;
  padding: 12px 16px;
  background-color: #ffffff; /* 手機版底部面板加白色底背景 */
  box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.1);

  .btn-download {
    flex: 1; /* 手機版黃色按鈕佔據大部分寬度 */
    background-color: map-get($color, primary);
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
  }

  .btn-reset {
    width: 48px; /* 手機版變成右邊正方形重置按鈕 */
    border: 1px solid #ccc;
    background: #fff;
    border-radius: 8px;
    cursor: pointer;
    .btn-text { display: none; } /* 手機版隱藏「再做一顆」文字，只留圖示 */
  }
  button {
    font-size: map-get($fontSize, default );
    
  }

  /* ----------------------------------------------------------------
   * 2. 電腦版 (>= 768px)：恢復為普通排版，跟在 ResultCard 下方
   * ---------------------------------------------------------------- */
  @media (min-width: 768px) {
    position: static; /* 取消 fixed 定位 */
    padding: 0;
    background-color: transparent;
    box-shadow: none;

    .btn-download {
      padding: 10px 16px;
    }

    .btn-reset {
      width: auto; /* 恢復自動寬度 */
      padding: 10px 16px;
      .btn-text { display: inline; } /* 顯示「再做一顆」文字 */
    }
  }
}
</style>