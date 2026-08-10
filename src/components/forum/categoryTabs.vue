<!-- src/components/forum/CategoryTabs.vue -->
<template>
  <div class="category-tabs">
    <!-- 
      1. v-for: 動態印出 categories 陣列 
      2. :key: 綁定獨一無二的 tab.id (讓 Vue 追蹤節點)
      3. :class: 當點選的 id 等於目前 currentTab 時，自動加上 .active 樣式 
      4. @click: 點擊時觸發切換方法，並傳入該頁籤的 id
    -->
    <button
      v-for="tab in categories"
      :key="tab.id"
      type="button"
      :class="['tab-btn', { active: currentTab === tab.id }]"
      @click="$emit('change-tab', tab.id)"
    >
      {{ tab.name }}
    </button>
  </div>
</template>

<script>
export default {
  name: "CategoryTabs",

  // 接收父元件（ForumView）傳進來的當前選中頁籤，預設為 'all'
  props: {
    currentTab: {
      type: String,
      default: "all"
    }
  },

  data() {
    return {
      // 未來串接 API 時，這些資料可以改由父元件傳入，或是保持寫在元件內
      // id: 傳給後端做資料過濾的代碼 (Key)
      // name: 畫面上顯示給使用者看的文字
      categories: [
        { id: "all", name: "全部文章" },
        { id: "announcement", name: "公告" },
        { id: "unboxing", name: "配裝開箱" },
        { id: "event", name: "賽事情報" },
        { id: "chat", name: "玩家閒聊" },
        { id: "strategy", name: "戰術討論" },
        { id: "faq", name: "新手發問" }
      ]
    };
  },

  emits: ["change-tab"]
};
</script>

<style lang="scss" scoped>
@use "sass:map";
@use '@/assets/scss/var' as *;
@use "@/assets/scss/mixin" as *;

.category-tabs {
  display: flex;
  gap: 8px;
  margin-bottom: 20px;

  /* -------------------------------------------------------------
   * 手機版 RWD
   * ------------------------------------------------------------- */
  overflow-x: auto;
  white-space: nowrap;
  padding-bottom: 4px; /* 留點空間避免外框陰影被切掉 */

  /* 隱藏原生滑動條，維持設計圖乾淨視覺 */
  &::-webkit-scrollbar {
    display: none;
  }
  -ms-overflow-style: none;
  scrollbar-width: none;

  .tab-btn {
    padding: 8px 20px;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    background-color: #ffffff;
    color: map.get($color, secondary);
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0; /* 關鍵：防止手機版空間不夠時按鈕被擠壓變形 */

    @include rwd("desktop") {
      &:hover {
      background-color: map.get($color, lightYellow);
    }
    }
    

    /* 點選亮起 (Active) 樣式 - 套用設計圖的橘色主色 */
    &.active {
      background-color: map.get($color, primary);
      border-color: map.get($color, primary);
      color: map.get($color, secondary);
      font-weight: bold;
    }
  }
}
</style>