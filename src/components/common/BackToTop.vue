<template>
  <button
    type="button"
    class="back-to-top-btn"
    :class="{ 'is-show': isMobile && isVisible }"
    @click="scrollToTarget"
  >
    <i class="fa-solid fa-arrow-up"></i>
  </button>
</template>

<script>
export default {
  name: "BackToTop",

  props: {
    // 是否為手機版，由父層既有的 isMobile 狀態直接傳入，不重複判斷
    isMobile: {
      type: Boolean,
      default: false
    },
    // 要監聽的基準元素，由父層透過 ref 傳進來
    targetEl: {
      type: [Object, null],
      default: null
    }
  },

  data() {
    return {
      isVisible: false
    };
  },

  mounted() {
    window.addEventListener("scroll", this.handleScroll);
  },

  beforeUnmount() {
    window.removeEventListener("scroll", this.handleScroll);
  },

  methods: {
    handleScroll() {
      if (!this.targetEl) {
        this.isVisible = false;
        return;
      }

      const targetTop = this.targetEl.getBoundingClientRect().top;
      this.isVisible = targetTop < -200;
    },

    scrollToTarget() {
      if (!this.targetEl) return;
      this.targetEl.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
    }
  }
};
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

.back-to-top-btn {
  position: fixed;
  right: 20px; // 改成跟發文按鈕對齊，同一條垂直線比較整齊
  bottom: 84px;
  z-index: 100;

  width: 48px;
  height: 48px;
  padding: 0;

  display: flex;
  justify-content: center;
  align-items: center;

  border: 1px solid #FEC96B;
  border-radius: 50%;

  background-color: #141C26;
  color: #FEC96B;

  font-size: 18px;
  cursor: pointer;

  opacity: 0;
  visibility: hidden;
  pointer-events: none;

  transform: translateY(12px);

  transition:
    opacity 0.25s ease,
    visibility 0.25s ease,
    transform 0.25s ease,
    background-color 0.25s ease,
    color 0.25s ease;

  &.is-show {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translateY(0);
  }

  &.is-show:hover {
    transform: translateY(-4px);
    background-color: #FEC96B;
    color: #141C26;
  }
}
</style>