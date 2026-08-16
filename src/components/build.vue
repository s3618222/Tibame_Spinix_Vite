<!-- BuildView.vue -->
<template>
  <div class="build-page">
    <div class="build-container">
      <!-- 標題區 -->
      <header class="build-header">
        <!-- <h1>陀螺配置工坊</h1> -->
      </header>

      <!-- 主要工作區 (電腦版左右雙欄) -->
      <main class="build-main">

        <!-- 左欄：結果卡片與按鈕組 -->
        <section class="left-col">
          <ResultCard :selected-parts="selectedParts" />
          <ActionGroup />
        </section>

        <!-- 右欄：頁籤與零件列表  -->
        <section class="right-col">
          <!-- 父元件記錄的 currentTab 傳給 CategoryTabs 判斷高亮 -->
          <CategoryTabs :current-tab="currentTab" @change-tab="handleTabChange"/>
          <PartGrid :parts="filteredParts" :selected-id="currentSelectedId" @select-part="handleSelectPart"/>
          <p style="color: #fff;">右欄區域（頁籤與零件網格）</p>
        </section>

      </main>
    </div>
  </div>
</template>

<script>
  import ResultCard from "@/components/build/resultCard.vue";
  import ActionGroup from "@/components/build/actionGroup.vue";
  import CategoryTabs from "@/components/build/categoryTabs.vue";
  import PartGrid from "@/components/build/partGrid.vue";

  export default {
    name: "BuildView",

    components: {
      ActionGroup,
      ResultCard,
      CategoryTabs,
      PartGrid
    },

    data(){
      return {
        // 目前的分類tab，預設blade
        currentTab: "blade",
        // 各分類目前已選擇的零件，key 為 category，value 為零件物件
        selectedParts: {},
        // 零件們的假資料，待補數值
        allParts: [
        {
          id: 1,
          name: "蒼龍突擊",
          image: `build/blade/BX-49 蒼龍突擊.png`,
          type: "戰刃",
          category: "blade",

        },
        {
          id: 2,
          name: "衝擊龍神",
          image: `build/blade/BX-50-03 衝擊龍神.png`,
          type: "戰刃",
          category: "blade"
        },
        {
          id: 3,
          name: "暴風天馬",
          image: `build/blade/BXG-47 暴風天馬.png`,
          type: "戰刃",
          category: "blade"
        },
        {
          id: 4,
          name: "蒼龍神劍",
          image: `build/blade/BXG-49 蒼龍神劍.png`,
          type: "戰刃",
          category: "blade",
          atk: 60,
          def: 27,
          sta: 23,
          wei: 37.4
        },
        { id: 5, type: "固鎖", name: "BX-50-04", image: "/build/ratchet/BX-50-04.png", category: "ratchet" },
        { id: 6, type: "軸心", name: "UX-21-01 Z", image: "/build/bit/UX-21-01 Z.png", category: "bit" }
      ]
      }
    },

    computed: {
      /* 
        動態過濾器 (Computed Property)：
        當 this.currentTab 發生改變時，Vue 會自動重新執行這個函式，
        從 allParts 大水庫中只篩選出 category 等於 currentTab 的項目，組成新陣列傳出。
      */
      filteredParts() {
        return this.allParts.filter(part => {
          return part.category === this.currentTab;
        });
      },
      /* 目前分類已選擇的零件 id，供 PartGrid/PartCard 判斷高亮用 */
      currentSelectedId() {
        return this.selectedParts[this.currentTab]?.id ?? null;
      }
    },

    methods: {
      /*
        事件接收函式：
        當收到來自 CategoryTabs 的廣播時觸發。
        newTabId 就是子元件帶過來的參數 (例如 "ratchet")。
      */
      handleTabChange(newTabId) {
        this.currentTab = newTabId; // 修改中央狀態，這會自動觸發上方 computed: filteredParts 重新計算
      },
      /*
        接收來自 PartGrid 轉發的 select-part 事件，
        依零件的 category 記錄該分類目前選擇的零件。
      */
      handleSelectPart(part) {
        this.selectedParts = { ...this.selectedParts, [part.category]: part };
      }
    }
  }
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;
@use '@/assets/scss/reset' as *;

.build-page {
  // background-color: map-get($color, secondary);
  min-height: 100vh;
  padding: 32px 20px 100px;
  background-color: map-get($color, tertiary);

  .build-container {
    max-width: 1280px;
    margin: 0 auto;
  }

  .build-header {
    margin-bottom: 24px;
    h1 { color: map-get($color, secondary2); font-size: map-get($fontSize, h1 ); font-weight: bold;}
  }

  .build-main {
    display: flex;
    flex-direction: column;
    gap: 24px;

    .left-col {
      width: 100%;
      min-width: 0;
    }
    .right-col {
      width: 100%;
      min-width: 0;
      background-color: map-get($color, white );
      border: 1px solid map-get($color, gray );
      border-radius: 16px;
      padding: 16px 12px;
    }

    @media (min-width: 768px) {
      flex-direction: row;
      align-items: flex-start;

      .left-col {
        width: 380px;
        flex-shrink: 0;
      }

      .right-col {
        flex: 1;
      }
    }
  }
}
</style>