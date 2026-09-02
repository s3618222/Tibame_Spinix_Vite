<!-- BuildView.vue -->
<template>
  <div class="build-page">
    <div class="build-container">
      <header class="build-header">
        <h1>陀螺配置工坊</h1>
        <button type="button" class="btn-intro-help" @click="openIntroModal">
          <i class="fa-regular fa-circle-question"></i>
        </button>
      </header>

      <main class="build-main">
        <section class="left-col">
          <ResultCard class="result-card-target" :selected-parts="selectedParts" />
          <ActionGroup @clear-all="handleClearAll" @download="downloadImage"/>
        </section>

        <section class="right-col">
          <CategoryTabs :current-tab="currentTab" @change-tab="handleTabChange"/>
          <PartGrid :parts="filteredParts" :selected-id="currentSelectedId" @select-part="handleSelectPart"/>
          <p style="color: #fff;">右欄區域（頁籤與零件網格）</p>
        </section>
      </main>
    </div>

    <el-dialog v-model="showIntroModal" title="陀螺配置器使用說明" width="90%" style="max-width: 500px" :show-close="true" @close="handleIntroClose">
      <div class="intro-content">
        <p class="intro-cta">
          想打造屬於你的無敵戰陀嗎？只要三步驟，就能組出獨一無二的配置！
        </p>

        <div class="intro-section">
          <h3><span class="step-num">1</span> 選擇零件</h3>
          <p>依序切換「戰刃」「固鎖」「軸心」三個分類頁籤，點擊你想使用的零件。</p>
        </div>

        <div class="intro-section">
          <h3><span class="step-num">2</span> 查看數值</h3>
          <p>左側面板會即時顯示你目前配置的攻擊、防守、持久、重量四項數值加總。</p>
        </div>

        <div class="intro-section">
          <h3><span class="step-num">3</span> 下載分享</h3>
          <p>組好之後，點擊「下載配方圖片」，就能把你的配置存成圖片分享給朋友。</p>
        </div>
      </div>

      <template #footer>
        <button type="button" class="btnFill" @click="showIntroModal = false">開始配置</button>
      </template>
    </el-dialog>
  </div>
</template>

<script>
  import ResultCard from "@/components/build/resultCard.vue";
  import ActionGroup from "@/components/build/actionGroup.vue";
  import CategoryTabs from "@/components/build/categoryTabs.vue";
  import PartGrid from "@/components/build/partGrid.vue";
  import { phpBaseUrl } from "@/assets/js/utils/phpBaseUrl.js";
  import html2canvas from "html2canvas";

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
        allParts: [],
        showIntroModal: false
      }
    },

    computed: {
      /* 
        動態過濾器 (Computed Property)：
        當 this.currentTab 發生改變時，Vue 會自動重新執行這個函式，
        從 allParts 大水庫中只篩選出 category 等於 currentTab 的項目，組成新陣列傳出。
      */

      // 資料庫裡沒有存中文標籤，所以前端自己維護這份對照
      categoryLabelMap() {
        return { Blade: "戰刃", Ratchet: "固鎖", Bit: "軸心" };
      },
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

    created() {
      this.fetchParts();

      const hasSeenIntro = localStorage.getItem("build_intro_seen");
      if (!hasSeenIntro) {
        this.showIntroModal = true;
      }
    },

    methods: {
      /*
        事件接收函式：
        當收到來自 CategoryTabs 的廣播時觸發。
        newTabId 就是子元件帶過來的參數 (例如 "ratchet")。
      */

      async fetchParts() {
        try {
          const res = await fetch(`${phpBaseUrl}/build/getParts.php`);
          const result = await res.json();

          if (result.success) {
            // 轉換：把 API 回傳的原始欄位，對應成子元件原本熟悉的欄位名稱
            this.allParts = result.data.map(item => ({
              id: item.beyblade_id,
              name: item.name,
              image: item.pic,
              type: this.categoryLabelMap[item.category] ?? item.category,
              category: item.category.toLowerCase(),  // 轉小寫，對應 CategoryTabs 的 currentTab 值
              atk: Number(item.attack),
              def: Number(item.defense),
              sta: Number(item.stamina),
              wei: Number(item.weight)
            }));
          }
        } catch (error) {
          console.error("零件資料載入失敗", error);
        }
      },

      handleTabChange(newTabId) {
        this.currentTab = newTabId; // 修改中央狀態，這會自動觸發上方 computed: filteredParts 重新計算
      },
      /*
        接收來自 PartGrid 轉發的 select-part 事件，
        依零件的 category 記錄該分類目前選擇的零件。
      */
      handleSelectPart(part) {
        this.selectedParts = { ...this.selectedParts, [part.category]: part };
      },
      /*
        接收來自 ActionGroup 的 clear-all 事件，清空所有已選零件。
      */
      handleClearAll() {
        this.selectedParts = {};
      },

      handleIntroClose() {
        localStorage.setItem("build_intro_seen", "true");
      },

      openIntroModal() {
        this.showIntroModal = true;
      },

      async downloadImage(){
        const {blade, ratchet, bit} = this.selectedParts;
        if(!blade || !ratchet || !bit){
          alert("請先選擇戰刃、固鎖、軸心三個部件，才能下載配方圖片");
          return;
        }

        const target = document.querySelector(".result-card-target");

        if(!target){
          alert("找不到要下載的配置卡片");
          return;
        }

        try {
          const canvas = await html2canvas(target, {
            backgroundColor: "#141C26",
            useCORS: true,
            scale: 2
          });

          const link = document.createElement("a");
          link.download = "我的陀螺配方.png";
          link.href = canvas.toDataURL("image/png");
          link.click();

        } catch (error) {
          console.error("下載配方圖片失敗：", error);
          alert("下載失敗，請稍後再試");
        }
      }
    }
  }
</script>

<style lang="scss" scoped>
@use '@/assets/scss/var' as *;

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
    display: flex;
    align-items: flex-end;
    gap: 8px;
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

.btn-intro-help {
  display: inline-flex;
  align-items: center;
  gap: 6px;

  background: none;
  border: none;
  color: map-get($color, neutral);
  font-size: 14px;
  cursor: pointer;

  &:hover {
    color: map-get($color, primary);
  }
}

.intro-content {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.intro-cta {
  font-size: 15px;
  font-weight: 600;
  color: map-get($color, secondary);
  margin: 0;
}

.intro-section {
  h3 {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 16px;
    color: map-get($color, secondary2);
    margin: 0 0 6px;
  }

  p {
    margin: 0;
    font-size: 14px;
    color: map-get($color, secondary);
    line-height: 1.6;
  }
}

.step-num {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background-color: map-get($color, secondary2);
  color: white;
  font-size: 13px;
  font-weight: 700;
  margin-right: 4px;
}
</style>