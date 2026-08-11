<template>
  <section class="appeal-detail">
    <RouterLink :to="{ name: 'member-appeal' }" class="back-link">
      <i class="fa-solid fa-arrow-left"></i>
      返回列表
    </RouterLink>

    <template v-if="appeal">
      <h1>申訴表單詳情</h1>

      <div class="meta-bar">
        <div class="meta-item">
          <p class="meta-label">申訴編號</p>
          <p class="meta-value">#{{ appeal.id }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">申訴人</p>
          <p class="meta-value">{{ appeal.reporter }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">被申訴對象</p>
          <p class="meta-value">{{ appeal.target }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">申訴類型</p>
          <p class="meta-value">{{ appeal.type }}</p>
        </div>
        <div class="meta-item">
          <p class="meta-label">建立時間</p>
          <p class="meta-value">{{ appeal.createdAt }}</p>
        </div>
      </div>

      <section class="detail-card">
        <h2>申訴內容</h2>
        <p class="detail-text">{{ appeal.content }}</p>

        <h2>證據截圖</h2>
        <div class="evidence-grid">
          <div class="evidence-item" v-for="n in appeal.images" :key="n">IMG</div>
        </div>
      </section>

      <section class="detail-card result-card" v-if="appeal.result">
        <h2>處份結果</h2>
        <p class="detail-text">{{ appeal.result }}</p>
        <p class="result-date">回復時間：{{ appeal.resultDate }}</p>
        <p class="result-hint">如果對處置有疑問請聯絡我們，或撥打客服電話：0900-000-000</p>
      </section>
    </template>

    <div v-else class="appeal-not-found">
      <p>找不到這筆申訴紀錄</p>
      <RouterLink :to="{ name: 'member-appeal' }" class="btnFill">返回列表</RouterLink>
    </div>
  </section>
</template>

<script>
import myAppealData from "@/data/myAppealData.js";

export default {
  name: "MyAppealDetail",

  computed: {
    appeal() {
      return myAppealData.find((item) => item.id === this.$route.params.id) || null;
    }
  }
};
</script>

<style lang="scss" scoped>
  @use "sass:map";
  @use "@/assets/scss/var" as *;

  .appeal-detail {
    width: 100%;
    min-width: 0;

    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .back-link {
    align-self: flex-start;
    display: flex;
    align-items: center;
    gap: 8px;

    font-size: map.get($fontSize, hint);
    color: map.get($color, neutral);
    text-decoration: none;
  }

  h1 {
    font-size: map.get($fontSize, h1);
    font-weight: 500;
    color: map.get($color, secondary2);
  }

  .meta-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 32px;

    padding: 16px;
    border: 1px solid map.get($color, hint);
    border-radius: 8px;
    background-color: map.get($color, white);
  }

  .meta-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .meta-label {
    font-size: map.get($fontSize, hint);
    color: map.get($color, secondary2);
  }

  .meta-value {
    font-size: map.get($fontSize, default);
    color: map.get($color, secondary);
  }

  .detail-card {
    width: 100%;
    padding: 32px 16px;
    border: 1px solid map.get($color, gray);
    border-radius: 8px;
    background-color: map.get($color, white);

    display: flex;
    flex-direction: column;
    gap: 16px;

    h2 {
      font-size: map.get($fontSize, h4);
      font-weight: 500;
      color: map.get($color, black);
    }
  }

  .detail-text {
    font-size: map.get($fontSize, default);
    color: map.get($color, black);
    line-height: 1.6;
  }

  .evidence-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .evidence-item {
    width: 120px;
    height: 90px;
    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    background-color: map.get($color, secondary);
    color: map.get($color, neutral);
    font-size: 12px;
  }

  .result-date {
    font-size: map.get($fontSize, hint);
    color: map.get($color, black);
  }

  .result-hint {
    padding-top: 4px;
    border-top: 1px solid map.get($color, neutral);

    font-size: 10px;
    color: map.get($color, neutral);
  }

  .appeal-not-found {
    padding: 60px 0;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;

    color: map.get($color, neutral);
  }

  // ====================== RWD調整 ============================

  @media screen and (max-width: 900px) {
    .meta-bar {
      gap: 16px;
    }
  }

  @media screen and (max-width: 576px) {
    .meta-bar {
      flex-direction: column;
      gap: 12px;
    }

    .detail-card {
      padding: 20px 16px;
    }
  }
</style>
