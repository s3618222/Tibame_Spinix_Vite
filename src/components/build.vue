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
          <ResultCard />
          <ActionGroup />
        </section>

        <!-- 右欄：頁籤與零件列表  -->
        <section class="right-col">
          <CategoryTabs />
          <PartGrid />
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