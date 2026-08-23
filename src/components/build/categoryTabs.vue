<template>
  <div class="category-tabs">
    <button  type="button" 
    v-for="item in types" :key="item.id"
    :class="{active: currentTab == item.id}"
    @click="selectTab(item.id)"
    >{{ item.name }}</button>
    <!-- <button type="button" class="active">戰刃</button>
    <button type="button">固鎖</button>
    <button type="button">軸心</button> -->
  </div>
</template>

<script>
  export default {
    name: "CategoryTabs",

    props: {
      currentTab: {
        type: String,   // 必須是字串（如 "blade", "ratchet", "bit"）
        required: true  // 父元件一定要傳，確保能正常判斷哪個按鈕要亮起
      }
    },

    data(){
      return {
        types: [
          { id: "blade", name: "戰刃" },
          { id: "ratchet", name: "固鎖" },
          { id: "bit", name: "軸心" }
        ]
      };
    },

    methods: {
      // 按鈕點擊時觸發的事件處理函式
      selectTab(tabId) {
        /* 
          - 第一個參數 "change-tab"：自訂事件名稱 (父元件會用語法 @change-tab 監聽)
          - 第二個參數 tabId：隨廣播帶出去的資料 (例如 "ratchet")
        */
        this.$emit("change-tab", tabId);
      }
    }
  }
</script>

<style lang="scss" scoped>
  @use "@/assets/scss/var" as *;

  .category-tabs {
    display: flex;
    justify-content: space-around;
    gap: 4px;
  }

  button {
    flex: 1;
    background-color: map-get($color, white );
    font-size: map-get($fontSize, h4 );
    color: map-get($color, secondary );
    border: 1px solid map-get($color, gray );
    cursor: pointer;
    transition: all 0.2s;
    padding-block: 4px;
    border-radius: 8px 8px 0 0;
    
    &.active {
      background-color: map-get($color, secondary );
      color: map-get($color, white );
    }
    &:not(.active):hover{
      background-color: map-get($color, tertiary );
      
    }
  }


</style>